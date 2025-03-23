<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with(['room:id,room_number,room_capacity,price', 'client:id,name,email'])
            ->select(['id', 'client_id', 'room_id', 'accompany_number', 'price_paid', 'check_in_date', 'check_out_date', 'status'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Receptionist/Reservation/Index', ['reservations' => $reservations]);
    }

    // Client-related methods have been moved to ClientController

    public function create(){
        $availableRooms = Room::where('status', 'available')->get();

        // Get approved clients for the current receptionist
        $approvedClients = User::where('role', 'client')
            ->where('is_approved', true)
            ->where('manager_id', auth()->id())
            ->select(['id', 'name', 'email'])
            ->get();

        return Inertia::render('Receptionist/Reservation/Create', [
            'rooms' => $availableRooms,
            'approvedClients' => $approvedClients
        ]);
    }
    public function store(Request $request){
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
        if($validated['accompany_number'] > $room->room_capacity){
            return back()->withErrors('error', 'Accompanying number exceeds the room capacity.');
        }

        // Standardize status format if needed
        if ($validated['status'] === 'checked-in') {
            $validated['status'] = 'checked_in';
        } else if ($validated['status'] === 'checked-out') {
            $validated['status'] = 'checked_out';
        }

        // Add receptionist_id
        $validated['receptionist_id'] = auth()->id();

        $reservation = Reservation::create($validated);
        return redirect()->route('receptionist.reservations.index')->with('success', 'Reservation created successfully.');
    }
    public function show(Reservation $reservation){
        $reservation->load('room','client');
        // Use the Pages directory with capital P
        return Inertia::render('Receptionist/Reservation/Show',['reservation' => $reservation]);
    }
    public function edit(Reservation $reservation){
        $availableRooms = Room::where('status' , 'available')->orWhere('id' , $reservation->room_id)->get();
        // Use the Pages directory with capital P
        return Inertia::render('Receptionist/Reservation/Edit', ['reservation' => $reservation, 'rooms' => $availableRooms]);
    }
    public function update(Request $request, Reservation $reservation){
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'accompany_number' => 'required|integer|min:1',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'status' => 'sometimes|in:pending,confirmed,checked_in,checked-in,checked_out,checked-out,cancelled',
            'price_paid' => 'required|numeric|min:0',
        ]);

        $room = Room::find($validated['room_id']);
        if($validated['accompany_number'] > $room->room_capacity){
            return back()->withErrors(['accompany_number' => 'Accompanying number exceeds the room capacity.']);
        }

        // Standardize status format if needed
        if (isset($validated['status'])) {
            if ($validated['status'] === 'checked-in') {
                $validated['status'] = 'checked_in';
            } else if ($validated['status'] === 'checked-out') {
                $validated['status'] = 'checked_out';
            }

            // Update room status based on reservation status
            if ($validated['status'] === 'checked_in') {
                // When checking in, mark the room as occupied
                $room->update(['status' => 'occupied']);
            } else if ($validated['status'] === 'checked_out' || $validated['status'] === 'cancelled') {
                // When checking out or cancelling, mark the room as available
                $room->update(['status' => 'available']);
            }
        }

        $reservation->update($validated);

        // Determine where to redirect based on the request
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Reservation updated successfully.']);
        }

        // If the request came from the show page and includes a redirect_back parameter
        if ($request->has('redirect_back') && $request->redirect_back) {
            return redirect()->route('receptionist.reservations.show', $reservation->id)
                ->with('success', 'Reservation updated successfully.');
        }

        return redirect()->route('receptionist.reservations.index')
            ->with('success', 'Reservation updated successfully.');
    }
    public function destroy (Reservation $reservation){
        $reservation->delete();
        return redirect()->route('receptionist.reservations.index')->with('success', 'Reservation deleted successfully.');
    }
}
