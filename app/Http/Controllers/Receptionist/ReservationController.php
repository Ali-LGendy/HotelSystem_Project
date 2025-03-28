<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Notifications\GreetingApprovedClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReservationController extends Controller
{
    /**
     * Display a listing of pending reservations.
     */
    public function index(Request $request)
    {
        // Check if user is admin
        $isAdmin = Auth::user()->hasRole('admin');

        // Only allow admins to access this page
        // if (!$isAdmin) {
        //     return redirect()->route('receptionist.dashboard')
        //         ->with('error', 'You do not have permission to access this page.');
        // }

        // Get request parameters for sorting and pagination
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        // Get pending reservations
        $pendingReservations = Reservation::with(['room:id,room_number,room_capacity,price', 'client:id,name,email'])
            ->where('status', 'pending')
            ->select(['id', 'client_id', 'room_id', 'accompany_number', 'price_paid', 'check_in_date', 'check_out_date', 'status', 'receptionist_id', 'created_at'])
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage);

        // Get statistics for the dashboard
        $pendingCount = Reservation::where('status', 'pending')->count();
        $confirmedCount = Reservation::where('status', 'confirmed')->count();
        $checkedInCount = Reservation::whereIn('status', ['checked_in', 'checked-in'])->count();

        // if( Auth::user()->hasRole('client')){
        //     return Inertia::render('Receptionist/Reservation/Show', [
        //     'reservations' => $pendingReservations,
        //     'reservationStats' => [
        //         'totalPending' => $pendingCount,
        //         'confirmedReservations' => $confirmedCount,
        //         'checkedInGuests' => $checkedInCount,
        //     ],
            
        // ]);
        // }
        

        return Inertia::render('Receptionist/Reservation/PendingReservations', [
            'reservations' => $pendingReservations,
            'reservationStats' => [
                'totalPending' => $pendingCount,
                'confirmedReservations' => $confirmedCount,
                'checkedInGuests' => $checkedInCount,
            ],
            'isAdmin' => $isAdmin
        ]);

        
    }

    /**
     * Display a listing of all reservations.
     */
    public function allReservations(Request $request)
    {
        // Get request parameters for sorting and pagination
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        // Get all reservations
        $reservations = Reservation::with(['room:id,room_number,room_capacity,price', 'client:id,name,email'])
            ->select(['id', 'client_id', 'room_id', 'accompany_number', 'price_paid', 'check_in_date', 'check_out_date', 'status', 'receptionist_id', 'created_at'])
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage);

        // Get statistics for the dashboard
        $pendingCount = Reservation::where('status', 'pending')->count();
        $confirmedCount = Reservation::where('status', 'confirmed')->count();
        $checkedInCount = Reservation::whereIn('status', ['checked_in', 'checked-in'])->count();
        $totalRevenue = Reservation::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->sum('price_paid') / 100; // Convert cents to dollars for display

        // Check if user is admin
        $isAdmin = Auth::user()->hasRole('admin');

        return Inertia::render('Receptionist/Reservation/AllReservations', [
            'reservations' => $reservations,
            'reservationStats' => [
                'totalPending' => $pendingCount,
                'confirmedReservations' => $confirmedCount,
                'checkedInGuests' => $checkedInCount,
                'totalRevenue' => $totalRevenue
            ],
            'isAdmin' => $isAdmin
        ]);
    }

    // Create and store methods have been removed

    /**
     * Display the specified reservation.
     */
    public function show(Reservation $reservation)
    {
        $reservation->load(['room', 'client', 'receptionist:id,name']);

        return Inertia::render('Receptionist/Reservation/Show', ['reservation' => $reservation]);
    }

    /**
     * Show the form for editing the specified reservation.
     */
    public function edit(Reservation $reservation)
    {
        $availableRooms = Room::where('status', 'available')
            ->orWhere('id', $reservation->room_id)
            ->get();

        $client = User::where('id', $reservation->client_id)
            ->select(['id', 'name', 'email'])
            ->first();

        return Inertia::render('Receptionist/Reservation/Edit', [
            'reservation' => $reservation,
            'rooms' => $availableRooms,
            'client' => $client
        ]);
    }

    /**
     * Update the specified reservation in storage.
     */
    public function update(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'accompany_number' => 'required|integer|min:1',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'status' => 'sometimes|in:pending,confirmed,checked_in,checked-in,checked_out,checked-out,cancelled',
            'price_paid' => 'required|integer|min:0',
            'client_id' => 'sometimes|exists:users,id',
            'receptionist_id' => 'nullable|exists:users,id',
        ]);

        // If receptionist_id is not provided, set it to the current user
        if (!isset($validated['receptionist_id']) || !$validated['receptionist_id']) {
            $validated['receptionist_id'] = Auth::id();
        }

        $room = Room::find($validated['room_id']);
        if ($validated['accompany_number'] > $room->room_capacity) {
            return back()->withErrors(['accompany_number' => 'Accompanying number exceeds the room capacity.']);
        }

        // Standardize status format if needed
        if (isset($validated['status'])) {
            if ($validated['status'] === 'checked-in') {
                $validated['status'] = 'checked_in';
            } elseif ($validated['status'] === 'checked-out') {
                $validated['status'] = 'checked_out';
            }

            // Update room status based on reservation status
            if ($validated['status'] === 'checked_in') {
                // When checking in, mark the room as occupied
                $room->update(['status' => 'occupied']);
            } elseif ($validated['status'] === 'checked_out' || $validated['status'] === 'cancelled') {
                // When checking out or cancelling, mark the room as available
                $room->update(['status' => 'available']);
            } elseif ($validated['status'] === 'confirmed') {
                // When confirming a reservation, ensure the receptionist is set
                $validated['receptionist_id'] = Auth::id();
            }
        }

        // Log the update for debugging
        \Log::info('Updating reservation', [
            'id' => $reservation->id,
            'data' => $validated,
            'previous' => $reservation->toArray()
        ]);

        $reservation->update($validated);

        // Determine where to redirect based on the request
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Reservation updated successfully.',
                'reservation' => $reservation->fresh()->load(['client', 'room'])
            ]);
        }

        // If the request came from the show page and includes a redirect_back parameter
        if ($request->has('redirect_back') && $request->redirect_back) {
            return redirect()->route('receptionist.reservations.show', $reservation->id)
                ->with('success', 'Reservation updated successfully.');
        }

        return redirect()->route('receptionist.reservations.index')
            ->with('success', 'Reservation updated successfully.');
    }

    /**
     * Remove the specified reservation from storage.
     */
    public function destroy(Reservation $reservation)
    {
        // Check if the reservation is not already checked in
        if ($reservation->status === 'checked_in') {
            return back()->with('error', 'Cannot delete a checked-in reservation.');
        }

        $reservation->delete();

        return redirect()->route('receptionist.reservations.index')
            ->with('success', 'Reservation deleted successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'guests' => 'required|integer|min:1',
            'special_requests' => 'nullable|string',
            'total_price' => 'required|numeric|min:0'
        ]);

        // Create the reservation
        $reservation = Reservation::create([
            'client_id' => Auth::id(),
            'room_id' => $validated['room_id'],
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'accompany_number' => $validated['guests'],
            'price_paid' => $validated['total_price'] * 100,
            'status' => 'pending',
            'receptionist_id' => null,
        ]);

        // Redirect to checkout with reservation_id
        return redirect()->route('stripe.checkout', ['reservation_id' => $reservation->id]);
    }


}
