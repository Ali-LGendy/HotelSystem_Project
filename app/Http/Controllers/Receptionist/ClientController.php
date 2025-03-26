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
        'id', 'name', 'email', 'mobile', 'country', 'gender', 'is_approved', 'is_banned', 'manager_id', 'created_at', 'updated_at'
    ];

    /**
     * Get paginated data for a data table with optional filters
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Base query to paginate
     * @param array $filters Optional filters to apply (key => value pairs)
     * @param int $perPage Number of items per page
     * @param string $sortBy Column to sort by
     * @param string $sortDir Sort direction (asc or desc)
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    private function paginateData($query, array $filters = [], int $perPage = 10, string $sortBy = 'created_at', string $sortDir = 'desc')
    {
        // Apply filters
        foreach ($filters as $field => $value) {
            if ($value !== null && $value !== '') {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortDir);

        // Return paginated results
        return $query->paginate($perPage)->withQueryString();
    }


    /**
     * Display a listing of pending clients (not yet approved).
     * This is the main clients management page.
     */
    public function index(Request $request): Response
    {
        // Get the current user ID
        $currentUserId = Auth::id();

        // Get request parameters for sorting and pagination
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        // Get only pending clients (not yet approved)
        $pendingClients = $this->paginateData(
            User::role('client')->select(self::USER_FIELDS),
            ['is_approved' => 0],
            $perPage,
            $sortBy,
            $sortDir
        );

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

        // Get current user role and admin status
        $userRole = Auth::user()->getRoleNames()->first();
        $isAdmin = Auth::user()->hasRole('admin');
        $isManager = Auth::user()->hasRole('manager');

        if($isAdmin){
            $menuLinks = $this->getAdminMenuLinks();
        }else if($isManager){
            $menuLinks = $this->getManagerMenuLinks();
        }else {
            $menuLinks = $this->getreceptionistMenuLinks();
        }
        return Inertia::render('Receptionist/Client/Index', [
            'pendingClients' => $pendingClients,
            'approvedClientsCount' => $approvedClientsCount,
            'myApprovedClientsCount' => $myApprovedClientsCount,
            'recentlyApprovedClients' => $recentlyApprovedClients,
            'pendingReservationsForApprovedClients' => $pendingReservationsForApprovedClients,
            'currentUserId' => $currentUserId,
            'userRole' => $userRole,
            'isAdmin' => $isAdmin,
            'menuLinks' => $menuLinks
            
        ]);
    }

    /**
     * Display a listing of clients approved by the current receptionist.
     * This is the "My Approved Clients" page.
     */
    public function myApprovedClients(Request $request): Response
    {
        // Get the current user ID
        $currentUserId = Auth::id();

        // Get all clients (for debugging)
        $allClients = User::role('client')
            ->count();

        // Get request parameters for sorting and pagination
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        // Get only clients managed by the current receptionist
        $myApprovedClients = $this->paginateData(
            User::role('client')->select(self::USER_FIELDS),
            [
                'manager_id' => $currentUserId,
                'is_approved' => 1
            ],
            $perPage,
            $sortBy,
            $sortDir
        );

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

        // Get current user role
        $userRole = Auth::user()->getRoleNames()->first();
        $isAdmin = Auth::user()->hasRole('admin');

        // Add debug information
        $debug = [
            'userRole' => $userRole,
            'hasAdminRole' => $isAdmin,
            'allRoles' => Auth::user()->getRoleNames(),
            'userId' => Auth::id(),
            'userName' => Auth::user()->name,
        ];
        $isAdmin = Auth::user()->hasRole('admin');

        // Add debug information
        $debug = [
            'userRole' => $userRole,
            'hasAdminRole' => $isAdmin,
            'allRoles' => Auth::user()->getRoleNames(),
            'userId' => Auth::id(),
            'userName' => Auth::user()->name,
        ];

        return Inertia::render('Receptionist/Client/MyApprovedClients', [
            'myApprovedClients' => $myApprovedClients,
            'clientStats' => [
            'isAdmin' => $isAdmin, // Pass the boolean directly
                'totalApproved' => $totalApprovedCount,
                'activeReservations' => $activeReservationsCount,
                'pendingReservations' => $recentReservations->where('status', 'pending')->count(),
            ],
            'recentReservations' => $recentReservations,
            'userRole' => $userRole,
            'isAdmin' => $isAdmin, // Pass the boolean directly
            'debug' => $debug,
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





    public function clientReservations(Request $request, int $id = null): Response
    {
        // Get request parameters for sorting and pagination
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        // Build base query
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
                'status',
                'created_at',
                'updated_at'
            ]);

        // Apply client filter if specified
        $filters = [];
        if ($id) {
            $filters['client_id'] = $id;
        }

        // Get paginated results
        $reservations = $this->paginateData($query, $filters, $perPage, $sortBy, $sortDir);

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

    /**
     * Display a listing of all clients in the system.
     * This is only accessible to admin users.
     */
    public function allClients(Request $request): Response
    {
        // Check if user has admin role
        if (!Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        // Get request parameters for sorting and pagination
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        // Get all clients regardless of approval status or manager
        $allClients = $this->paginateData(
            User::role('client')->select(self::USER_FIELDS),
            [], // No filters - get all clients
            $perPage,
            $sortBy,
            $sortDir
        );

        // Get statistics
        $totalClientsCount = User::role('client')->count();
        $approvedClientsCount = User::role('client')->where('is_approved', 1)->count();
        $pendingClientsCount = User::role('client')->where('is_approved', 0)->count();
        $bannedClientsCount = User::role('client')->where('is_banned', 1)->count();

        // Get recent reservations (system-wide)
        $recentReservations = Reservation::with(['client', 'room'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Receptionist/Client/AllClients', [
            'allClients' => $allClients,
            'isAdmin' => Auth::user()->hasRole('admin'),
            'clientStats' => [
                'totalClients' => $totalClientsCount,
                'approvedClients' => $approvedClientsCount,
                'pendingClients' => $pendingClientsCount,
                'bannedClients' => $bannedClientsCount,
            ],
            'recentReservations' => $recentReservations,
            'userRole' => Auth::user()->getRoleNames()->first(),
            'currentUserId' => Auth::id(),
        ]);
    }

    /**
     * Show the form for editing the specified client.
     * Admin only.
     */
    public function edit(string $id): Response
    {
        // Check if user is admin
        if (!Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        // Find the client with reservations
        $client = User::role('client')
            ->with(['reservations' => function($query) {
                $query->with('room')
                    ->orderBy('created_at', 'desc');
            }])
            ->findOrFail($id);

        return Inertia::render('Receptionist/Client/EditClient', [
            'client' => $client,
            'isAdmin' => Auth::user()->hasRole('admin'),
            'currentUserId' => Auth::id(),
        ]);
    }

    /**
     * Update the specified client in storage.
     * Admin only.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        // Check if user is admin
        if (!Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'mobile' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'gender' => 'nullable|string|in:male,female,other',
            'is_approved' => 'boolean',
            'is_banned' => 'boolean',
        ]);

        // Find the client
        $client = User::role('client')->findOrFail($id);

        // Update the client
        $client->update($validated);

        return redirect()->route('receptionist.clients.all')
            ->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified client from storage.
     * Admin only.
     */
    public function destroy(string $id): JsonResponse
    {
        // Check if user is admin
        if (!Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        // Find the client
        $client = User::role('client')->findOrFail($id);

        // Delete associated reservations
        $client->reservations()->delete();

        // Delete the client
        $client->delete();

        return response()->json(['message' => 'Client deleted successfully.']);
    }
}
