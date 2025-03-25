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
    public function index()
    {
        $reservations = Reservation::with(['room:id,room_number,room_capacity,price', 'client:id,name,email'])
            ->where('status', 'pending')
            ->select(['id', 'client_id', 'room_id', 'accompany_number', 'price_paid', 'check_in_date', 'check_out_date', 'status', 'receptionist_id'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Receptionist/Reservation/Index', ['reservations' => $reservations]);
    }

    /**
     * Display a listing of all reservations.
     */
    public function allReservations()
    {
        $reservations = Reservation::with(['room:id,room_number,room_capacity,price', 'client:id,name,email'])
            ->select(['id', 'client_id', 'room_id', 'accompany_number', 'price_paid', 'check_in_date', 'check_out_date', 'status', 'receptionist_id'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Receptionist/Reservation/Index', [
            'reservations' => $reservations,
            'showAll' => true
        ]);
    }

    /**
     * Show the form for creating a new reservation.
     */
    public function create()
    {
        $availableRooms = Room::where('status', 'available')->get();

        // Get approved clients for the current receptionist
        $approvedClients = User::where('role', 'client')
            ->where('is_approved', true)
            ->where('manager_id', Auth::id())
            ->select(['id', 'name', 'email'])
            ->get();

        return Inertia::render('Receptionist/Reservation/Create', [
            'rooms' => $availableRooms,
            'approvedClients' => $approvedClients,
        ]);
    }

    /**
     * Store a newly created reservation in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'client_id' => 'required|exists:users,id',
            'accompany_number' => 'required|integer|min:1',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'status' => 'required|in:pending,confirmed,checked_in,checked-in,checked_out,checked-out,cancelled',
            'price_paid' => 'required|integer|min:0',
        ]);

        $room = Room::find($validated['room_id']);
        if ($validated['accompany_number'] > $room->room_capacity) {
            return back()->withErrors(['accompany_number' => 'Accompanying number exceeds the room capacity.']);
        }

        // Standardize status format if needed
        if ($validated['status'] === 'checked-in') {
            $validated['status'] = 'checked_in';
        } elseif ($validated['status'] === 'checked-out') {
            $validated['status'] = 'checked_out';
        }

        // Add receptionist_id
        $validated['receptionist_id'] = Auth::id();

        $reservation = Reservation::create($validated);

        // Update room status if needed
        if ($validated['status'] === 'checked_in') {
            $room->update(['status' => 'occupied']);
        }

        return redirect()->route('receptionist.reservations.index')->with('success', 'Reservation created successfully.');
    }

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

                // Also approve the client if they're not already approved
                if (isset($validated['client_id'])) {
                    $client = \App\Models\User::find($validated['client_id']);
                    if ($client && !$client->is_approved) {
                        $client->is_approved = 1;
                        $client->manager_id = Auth::id();
                        $client->save();

                        // Log the client approval
                        \Log::info('Client automatically approved when confirming reservation', [
                            'client_id' => $client->id,
                            'client_name' => $client->name,
                            'reservation_id' => $reservation->id,
                            'receptionist_id' => Auth::id()
                        ]);
                    }
                }
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
                'reservation' => $reservation->fresh()->load(['client', 'room']),
                'client_approved' => isset($validated['client_id']) ? User::find($validated['client_id'])->is_approved : false
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
}
