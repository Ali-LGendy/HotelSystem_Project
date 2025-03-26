<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReservationController extends Controller
{
    /**
     * Display a listing of the client's reservations.
     */
    public function index()
    {
        $reservations = Reservation::with(['room:id,room_number,room_capacity,price,floor_id', 'room.floor:id,name'])
            ->where('client_id', Auth::id())
            ->select(['id', 'room_id', 'accompany_number', 'price_paid', 'check_in_date', 'check_out_date', 'status'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Client/Reservation/Index', ['reservations' => $reservations]);
    }

    /**
     * Show available rooms for reservation.
     */
    public function availableRooms()
    {
        $availableRooms = Room::with('floor:id,name')
            ->where('status', 'available')
            ->select(['id', 'room_number', 'room_capacity', 'price', 'floor_id'])
            ->paginate(10);

        return Inertia::render('Client/Reservation/AvailableRooms', ['availableRooms' => $availableRooms]);
    }

    /**
     * Show the form for creating a new reservation for a specific room.
     */
    public function create($roomId)
    {
        $room = Room::where('status', 'available')
            ->where('id', $roomId)
            ->firstOrFail();

        return Inertia::render('Client/Reservation/Create', [
            'room' => $room,
        ]);
    }

    /**
     * Store a new reservation request and redirect to payment.
     */
    public function store(Request $request, $roomId)
    {
        $room = Room::where('status', 'available')
            ->where('id', $roomId)
            ->firstOrFail();

        $validated = $request->validate([
            'accompany_number' => 'required|integer|min:1|max:' . $room->room_capacity,
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        // Create the reservation with pending status
        $reservation = Reservation::create([
            'client_id' => Auth::id(),
            'room_id' => $room->id,
            'accompany_number' => $validated['accompany_number'],
            'price_paid' => $room->price, // Store in cents as per requirements
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'status' => 'pending',
        ]);

        // Redirect to Stripe checkout
        return redirect()->route('stripe.checkout', $reservation->id);
    }

    /**
     * Display the specified reservation.
     */
    public function show(Reservation $reservation)
    {
        // Ensure the reservation belongs to the authenticated client
        if ($reservation->client_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $reservation->load(['room:id,room_number,room_capacity,price,floor_id', 'room.floor:id,name']);

        return Inertia::render('Client/Reservation/Show', ['reservation' => $reservation]);
    }

    /**
     * Cancel a pending reservation.
     */
    public function cancel(Reservation $reservation)
    {
        // Ensure the reservation belongs to the authenticated client
        if ($reservation->client_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Only allow cancellation of pending reservations
        if ($reservation->status !== 'pending') {
            return back()->with('error', 'Only pending reservations can be cancelled.');
        }

        $reservation->update(['status' => 'cancelled']);

        return redirect()->route('client.reservations.index')
            ->with('success', 'Reservation cancelled successfully.');
    }
}