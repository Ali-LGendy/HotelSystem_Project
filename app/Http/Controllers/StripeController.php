<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeController extends Controller
{
    public function checkout(Request $request, $reservation_id)
    {
        try {
            $reservation = Reservation::findOrFail($reservation_id);

            // Validate that the reservation belongs to the current user
            if ($reservation->client_id !== auth()->id()) {
                return redirect()->back()->with('error', 'Unauthorized access to this reservation.');
            }

            // Validate reservation status
            if ($reservation->status !== 'pending') {
                return redirect()->back()->with('error', 'This reservation cannot be processed for payment.');
            }

            // Set Stripe API key
            Stripe::setApiKey(config('services.stripe.secret'));

            // Calculate dates for product description
            $checkInDate = date('M d, Y', strtotime($reservation->check_in_date));
            $checkOutDate = date('M d, Y', strtotime($reservation->check_out_date));

            // Create a Stripe Checkout Session
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => "Room {$reservation->room->room_number} Reservation",
                            'description' => "Check-in: {$checkInDate}, Check-out: {$checkOutDate}",
                        ],
                        'unit_amount' => $reservation->price_paid,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => url('/stripe/success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('stripe.cancel', ['reservation_id' => $reservation->id]),
                'metadata' => [
                    'reservation_id' => $reservation->id,
                ],
            ]);

            // Create payment record in pending state
            $payment = Payment::create([
                'reservation_id' => $reservation->id,
                'amount' => $reservation->price_paid,
                'stripe_payment_id' => $session->id,
                'status' => 'pending',
            ]);

            Log::info('this is session,', ['sessionid' => $session]);

            // Redirect to Stripe Checkout
            return redirect($session->url);

        } catch (ApiErrorException $e) {
            Log::error('Stripe API Error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Payment processing error. Please try again later.');
        } catch (\Exception $e) {
            Log::error('Checkout Error: '.$e->getMessage());

            return redirect()->back()->with('error', 'An error occurred. Please try again later.');
        }
    }

    public function success(Request $request)
    {
        try {
            $session_id = $request->get('session_id');
            Log::info('this is, ', ['sessionId' => $session_id]);

            if (! $session_id) {
                return redirect()->route('dashboard')->with('error', 'Invalid session.');
            }

            // Set Stripe API key
            Stripe::setApiKey(config('services.stripe.secret'));

            // Retrieve the session to verify payment
            $session = Session::retrieve($session_id);

            if ($session->payment_status !== 'paid') {
                return redirect()->route('dashboard')->with('error', 'Payment not completed.');
            }
            $payment = Payment::where('stripe_payment_id', $session_id)->firstOrFail();

            $amountPaid = $session->amount_total;
            if ($amountPaid != $payment->amount) {
                Log::error('Payment amount mismatch', [
                    'expected' => $payment->amount,
                    'received' => $amountPaid,
                    'payment_id' => $payment->id,
                ]);

                return redirect()->route('dashboard')->with('error', 'Payment verification failed.');
            }

            DB::beginTransaction();
            
            $payment->status = 'paid';
            $payment->save();

            // Update the reservation status
            $reservation = Reservation::findOrFail($payment->reservation_id);
            $reservation->status = 'confirmed';
            $reservation->save();

            // Update room status if needed
            $room = $reservation->room;
                $room->status = 'occupied';
                $room->save();
            

            DB::commit();

            Log::info('Payment successful', [
                'payment_id' => $payment->id,
                'reservation_id' => $reservation->id,
                'stripe_session_id' => $session_id,
            ]);

            return redirect()->route('dashboard')->with('success', 'Your reservation has been confirmed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment success handling error: '.$e->getMessage());

            return redirect()->route('dashboard')->with('error', 'There was an issue processing your payment confirmation.');
        }
    }

    public function cancel(Request $request, $reservation_id = null)
    {
        try {
            if ($reservation_id) {
                $payment = Payment::where('reservation_id', $reservation_id)
                    ->where('status', 'pending')
                    ->latest()
                    ->first();

                if ($payment) {
                    $payment->status = 'failed';
                    $payment->save();

                    Log::info('Payment cancelled by user', [
                        'payment_id' => $payment->id,
                        'reservation_id' => $reservation_id,
                    ]);
                }
            }

            return redirect()->route('dashboard')->with('info', 'Payment was cancelled.');
        } catch (\Exception $e) {
            Log::error('Cancel payment error: '.$e->getMessage());

            return redirect()->route('dashboard')->with('error', 'An error occurred while cancelling the payment.');
        }
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');
        Log::info('Payment webhook: starting payment check');

        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );

            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    $this->handleSuccessfulPayment($session);
                    break;
                case 'checkout.session.expired':
                    $session = $event->data->object;
                    $this->handleFailedPayment($session);
                    break;
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Webhook error: '.$e->getMessage());

            return response()->json(['status' => 'error'], 400);
        }
    }

    private function handleSuccessfulPayment($session)
    {
        DB::beginTransaction();
        try {

            $payment = Payment::where('stripe_payment_id', $session->id)->first();
            Log::info('Payment webhook: starting payment check', [
                'payment_id' => $payment->id,
            ]);
            if (! $payment) {
                Log::error('Payment not found for session', ['session_id' => $session->id]);

                return;
            }
            $amountPaid = $session->amount_total; // Convert cents to dollars
            if ($amountPaid != $payment->amount) {
                Log::error('Webhook: Payment amount mismatch', [
                    'expected' => $payment->amount,
                    'received' => $amountPaid,
                    'payment_id' => $payment->id,
                ]);

                return;
            }

            if ($payment->status !== 'paid') {
                $payment->status = 'paid';
                $payment->save();

                $reservation = Reservation::findOrFail($payment->reservation_id);
                $reservation->status = 'confirmed';
                $reservation->save();

                Log::info('Payment webhook: successful payment processed', [
                    'payment_id' => $payment->id,
                    'reservation_id' => $reservation->id,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error handling successful payment webhook: '.$e->getMessage());
        }
    }

    private function handleFailedPayment($session)
    {
        try {
            $payment = Payment::where('stripe_payment_id', $session->id)->first();

            if (! $payment) {
                Log::error('Payment not found for session', ['session_id' => $session->id]);

                return;
            }

            if ($payment->status === 'pending') {
                $payment->status = 'failed';
                $payment->save();

                Log::info('Payment webhook: failed payment processed', [
                    'payment_id' => $payment->id,
                    'reservation_id' => $payment->reservation_id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error handling failed payment webhook: '.$e->getMessage());
        }
    }
}
