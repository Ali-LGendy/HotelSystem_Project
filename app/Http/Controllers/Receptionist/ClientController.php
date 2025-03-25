<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\GreetingApprovedClient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    /**
     * Fields to select from users table
     */
    private const USER_FIELDS = [
        'id', 'name', 'email', 'mobile', 'country', 'gender'
    ];



    /**
     * Display a listing of pending clients (not yet approved).
     * This is the "Manage Clients" page.
     */
    public function approvedClients(): Response
    {
        // Get only pending clients (not yet approved)
        $pendingClients = User::role('client')
            ->where('is_approved', 0)
            ->select(self::USER_FIELDS)
            ->paginate(10);

        // Get count of approved clients
        $approvedClientsCount = User::role('client')
            ->where('is_approved', 1)
            ->count();

        // Get recently approved clients (last 5)
        $recentlyApprovedClients = User::role('client')
            ->where('is_approved', 1)
            ->where('manager_id', Auth::id())
            ->select(self::USER_FIELDS)
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($client) {
                $client->approved_at = $client->updated_at;
                return $client;
            });

        // Get pending reservations for approved clients
        $pendingReservationsForApprovedClients = Reservation::with(['client', 'room'])
            ->whereHas('client', function ($query) {
                $query->where('is_approved', 1)
                      ->where('manager_id', Auth::id());
            })
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Receptionist/Client/ApprovedClients', [
            'pendingClients' => $pendingClients,
            'approvedClientsCount' => $approvedClientsCount,
            'recentlyApprovedClients' => $recentlyApprovedClients,
            'pendingReservationsForApprovedClients' => $pendingReservationsForApprovedClients,
        ]);
    }

    /**
     * Display a listing of clients approved by the current receptionist.
     * This is the "My Approved Clients" page.
     */
    public function myApprovedClients(): Response
    {
        // Get only clients approved by the current receptionist
        $myApprovedClients = User::role('client')
            ->where('is_approved', 1)
            ->where('manager_id', Auth::id())
            ->select(self::USER_FIELDS)
            ->paginate(10);

        // Get statistics for approved clients
        $totalApprovedCount = User::role('client')
            ->where('is_approved', 1)
            ->where('manager_id', Auth::id())
            ->count();

        $activeReservationsCount = Reservation::whereHas('client', function ($query) {
                $query->where('is_approved', 1)
                      ->where('manager_id', Auth::id());
            })
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->count();

        // Get recent reservations for approved clients
        $recentReservations = Reservation::with(['client', 'room'])
            ->whereHas('client', function ($query) {
                $query->where('is_approved', 1)
                      ->where('manager_id', Auth::id());
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Receptionist/Client/MyApprovedClients', [
            'myApprovedClients' => $myApprovedClients,
            'clientStats' => [
                'totalApproved' => $totalApprovedCount,
                'activeReservations' => $activeReservationsCount,
                'pendingReservations' => $recentReservations->where('status', 'pending')->count(),
            ],
            'recentReservations' => $recentReservations,
        ]);
    }

    /**
     * Approve a client and send welcome notification.
     * Also update any pending reservations to confirmed status.
     */
    public function approveClient(int $id, Request $request)
    {
        $client = User::findOrFail($id);

        // If client is already approved, return early
        if ($client->is_approved) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client is already approved.'
                ]);
            }
            return redirect()->back()->with('info', 'Client is already approved.');
        }

        try {
            DB::transaction(function () use ($client) {
                // Update client status
                $client->is_approved = 1;
                $client->manager_id = Auth::id();
                $client->save();

                // Update all pending reservations to confirmed
                $pendingReservationsCount = $client->reservations()
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'confirmed',
                        'receptionist_id' => Auth::id(),
                    ]);

                // Send greeting notification
                $client->notify((new GreetingApprovedClient())->delay(now()->addSeconds(10)));

                // Log the approval
                Log::info('Client approved', [
                    'client_id' => $client->id,
                    'client_name' => $client->name,
                    'receptionist_id' => Auth::id(),
                    'receptionist_name' => Auth::user()->name,
                    'pending_reservations_updated' => $pendingReservationsCount
                ]);
            });

            // Get updated client data with approved_at field
            $updatedClient = User::find($client->id);
            $updatedClient->approved_at = $updatedClient->updated_at;

            // Get pending reservations that were updated
            $updatedReservations = Reservation::with(['client', 'room'])
                ->where('client_id', $client->id)
                ->where('status', 'confirmed')
                ->orderBy('updated_at', 'desc')
                ->get();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Client approved successfully.',
                    'client' => $updatedClient,
                    'updatedReservations' => $updatedReservations
                ]);
            }

            return redirect()->route('receptionist.clients.my-approved')->with('success', 'Client approved successfully and added to your approved clients list.');
        } catch (\Exception $e) {
            Log::error('Error approving client', [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while approving the client.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'An error occurred while approving the client.');
        }
    }

    /**
     * Display reservations for clients approved by the current receptionist.
     * Shows client name, accompany number, room number, and paid price.
     */
    public function clientReservations(int $id = null): Response
    {
        $query = Reservation::with([
                'client:id,name',
                'room:id,room_number'
            ])
            ->whereHas('client', function (Builder $query) {
                $query->role('client')
                    ->where('is_approved', 1)
                    ->where('manager_id', Auth::id());
            })
            ->select([
                'id',
                'client_id',
                'room_id',
                'accompany_number',
                'price_paid'
            ]);

        if ($id) {
            $query->where('client_id', $id);
        }

        $reservations = $query->latest()->paginate(10);

        // Get client name if specific client is selected
        $clientName = null;
        if ($id) {
            $client = User::select(['id', 'name'])->find($id);
            $clientName = $client?->name;
        }

        return Inertia::render('Receptionist/Client/ClientsReservations', [
            'clientsReservations' => $reservations,
            'clientId' => $id,
            'clientName' => $clientName,
        ]);
    }

    /**
     * Reject a client's registration.
     */
    public function rejectClient(int $id): RedirectResponse
    {
        $client = User::findOrFail($id);

        try {
            // Store client info before deletion for logging
            $clientName = $client->name;

            // Delete the client account (will cascade delete reservations)
            $client->delete();

            // Log the rejection
            Log::info('Client registration rejected', [
                'client_id' => $id,
                'client_name' => $clientName,
                'receptionist_id' => Auth::id(),
                'receptionist_name' => Auth::user()->name
            ]);

            return redirect()->route('receptionist.clients.index')->with('success', 'Client registration rejected successfully.');
        } catch (\Exception $e) {
            Log::error('Error rejecting client', [
                'client_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'An error occurred while rejecting the client.');
        }
    }

    /**
     * Ban a client.
     */
    public function banClient(int $id): RedirectResponse
    {
        $client = User::findOrFail($id);

        // Ensure the client was approved by this receptionist
        if ($client->manager_id !== Auth::id()) {
            return back()->with('error', 'You can only ban clients that you have approved.');
        }

        try {
            $client->update(['is_banned' => 1]);

            Log::info('Client banned', [
                'client_id' => $id,
                'client_name' => $client->name,
                'receptionist_id' => Auth::id()
            ]);

            return back()->with('success', 'Client banned successfully.');
        } catch (\Exception $e) {
            Log::error('Error banning client', [
                'client_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'An error occurred while banning the client.');
        }
    }

    /**
     * Unban a client.
     */
    public function unbanClient(int $id): RedirectResponse
    {
        $client = User::findOrFail($id);

        // Ensure the client was approved by this receptionist
        if ($client->manager_id !== Auth::id()) {
            return back()->with('error', 'You can only unban clients that you have approved.');
        }

        try {
            $client->update(['is_banned' => 0]);

            Log::info('Client unbanned', [
                'client_id' => $id,
                'client_name' => $client->name,
                'receptionist_id' => Auth::id()
            ]);

            return back()->with('success', 'Client unbanned successfully.');
        } catch (\Exception $e) {
            Log::error('Error unbanning client', [
                'client_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'An error occurred while unbanning the client.');
        }
    }

    /**
     * API endpoint to approve a client and send welcome notification.
     * Also update any pending reservations to confirmed status.
     * Returns JSON response instead of redirecting.
     */
    public function approveClientApi(int $id): JsonResponse
    {
        $client = User::findOrFail($id);

        // If client is already approved, return early
        if ($client->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Client is already approved.'
            ]);
        }

        try {
            DB::transaction(function () use ($client) {
                // Update client status
                $client->is_approved = 1;
                $client->manager_id = Auth::id();
                $client->save();

                // Update all pending reservations to confirmed
                $pendingReservationsCount = $client->reservations()
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'confirmed',
                        'receptionist_id' => Auth::id(),
                    ]);

                // Send greeting notification
                $client->notify((new GreetingApprovedClient())->delay(now()->addSeconds(10)));

                // Log the approval
                Log::info('Client approved via API', [
                    'client_id' => $client->id,
                    'client_name' => $client->name,
                    'receptionist_id' => Auth::id(),
                    'receptionist_name' => Auth::user()->name,
                    'pending_reservations_updated' => $pendingReservationsCount
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Client approved successfully and added to your approved clients list.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error approving client via API', [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while approving the client.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
