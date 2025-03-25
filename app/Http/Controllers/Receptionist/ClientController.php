<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\GreetingApprovedClient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        // Get the current user ID
        $currentUserId = Auth::id();

        // Get only pending clients (not yet approved)
        $pendingClients = User::role('client')
            ->where('is_approved', 0)
            ->select(self::USER_FIELDS)
            ->paginate(10);

        // Get count of all approved clients in the system
        $approvedClientsCount = User::role('client')
            ->where('is_approved', 1)
            ->count();

        // Get count of clients approved by the current receptionist
        $myApprovedClientsCount = User::role('client')
            ->where('is_approved', 1)
            ->where('manager_id', $currentUserId)
            ->count();

        // Get recently approved clients (last 5)
        $recentlyApprovedClients = User::role('client')
            ->where('is_approved', 1)
            ->where('manager_id', $currentUserId)
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
            ->whereHas('client', function ($query) use ($currentUserId) {
                $query->where('is_approved', 1)
                      ->where('manager_id', $currentUserId);
            })
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Receptionist/Client/ApprovedClients', [
            'pendingClients' => $pendingClients,
            'approvedClientsCount' => $approvedClientsCount,
            'myApprovedClientsCount' => $myApprovedClientsCount,
            'recentlyApprovedClients' => $recentlyApprovedClients,
            'pendingReservationsForApprovedClients' => $pendingReservationsForApprovedClients,
            'currentUserId' => $currentUserId,
        ]);
    }

    /**
     * Display a listing of clients approved by the current receptionist.
     * This is the "My Approved Clients" page.
     */
    public function myApprovedClients(): Response
    {
        // Get the current user ID
        $currentUserId = Auth::id();

        // Get all clients (for debugging)
        $allClients = User::role('client')
            ->count();

        // Get only clients managed by the current receptionist
        $myApprovedClients = User::role('client')
            ->where('manager_id', $currentUserId)
            ->where('is_approved', 1)
            ->select(self::USER_FIELDS)
            ->paginate(10);

        // Get statistics for managed clients
        $totalApprovedCount = User::role('client')
            ->where('manager_id', $currentUserId)
            ->where('is_approved', 1)
            ->count();

        $activeReservationsCount = Reservation::whereHas('client', function ($query) use ($currentUserId) {
                $query->where('manager_id', $currentUserId)
                      ->where('is_approved', 1);
            })
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->count();

        // Get recent reservations for managed clients
        $recentReservations = Reservation::with(['client', 'room'])
            ->whereHas('client', function ($query) use ($currentUserId) {
                $query->where('manager_id', $currentUserId)
                      ->where('is_approved', 1);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Add debug information
        // $debug = [
        //     'currentUserId' => $currentUserId,
        //     'totalClientsInSystem' => $allClients,
        //     'myManagedClientsCount' => $totalApprovedCount,
        //     'approvedClientsQuery' => User::role('client')
        //         ->where('manager_id', $currentUserId)
        //         ->where('is_approved', 1)
        //         ->select(['id', 'name', 'email', 'manager_id', 'is_approved'])
        //         ->get(),
        //     'allApprovedClients' => User::role('client')
        //         ->where('is_approved', 1)
        //         ->select(['id', 'name', 'email', 'manager_id', 'is_approved'])
        //         ->get(),
        // ];

        return Inertia::render('Receptionist/Client/MyApprovedClients', [
            'myApprovedClients' => $myApprovedClients,
            'clientStats' => [
                'totalApproved' => $totalApprovedCount,
                'activeReservations' => $activeReservationsCount,
                'pendingReservations' => $recentReservations->where('status', 'pending')->count(),
            ],
            'recentReservations' => $recentReservations,
            //'debug' => $debug,
        ]);
    }

    /**
     * Approve a client and send welcome notification.
     * Also update any pending reservations to confirmed status.
     */

     public function approveClient($id)
     {
         $approvedBy =  Auth::user()->id;
 
         $client = User::findOrFail($id);
 
         $client->update(['is_approved' => true, 'manager_id' => $approvedBy]);
 
         // $client->notify(new ClientApprovedNotification());
 
         return back()->with('success', 'Client approved successfully.');
     }





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
                'price_paid',
                'status'
            ]);

        if ($id) {
            $query->where('client_id', $id);
        }

        // Use latest by default
        $query->latest();

        $reservations = $query->paginate(10);

        // Get client name if specific client is selected
        $clientName = null;
        if ($id) {
            $client = User::select(['id', 'name'])->find($id);
            $clientName = $client?->name;
        }

        return Inertia::render('Receptionist/Client/ClientsReservations', [
            'clientsReservations' => $reservations,
            'clientId' => $id,
            'clientName' => $clientName
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

        // Ensure the client is managed by this receptionist
        if ($client->manager_id !== Auth::id()) {
            return back()->with('error', 'You can only ban clients that you manage.');
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

        // Ensure the client is managed by this receptionist
        if ($client->manager_id !== Auth::id()) {
            return back()->with('error', 'You can only unban clients that you manage.');
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

            // Try to send notification, but don't fail if it doesn't work
            try {
                $client->notify((new GreetingApprovedClient())->delay(now()->addSeconds(10)));
            } catch (\Exception $notifyException) {
                Log::warning('Failed to send approval notification', [
                    'client_id' => $client->id,
                    'error' => $notifyException->getMessage()
                ]);
            }

            // Log the approval
            Log::info('Client approved via API', [
                'client_id' => $client->id,
                'client_name' => $client->name,
                'receptionist_id' => Auth::id(),
                'receptionist_name' => Auth::user()->name,
                'pending_reservations_updated' => $pendingReservationsCount
            ]);

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
