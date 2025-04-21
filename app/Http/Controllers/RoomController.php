<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Floor;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
        $isManager = $user->hasRole('manager');

        $perPage = $request->input('per_page', 10);

        // Query rooms with relationships
        $query = Room::with(['manager', 'floor']);

        // If user is a manager, show all rooms but only allow editing/deleting own rooms
        $rooms = $query->paginate($perPage);

        // Transform rooms for Inertia
        $rooms->getCollection()->transform(function ($room) use ($isAdmin, $isManager, $user) {
            return [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'price' => $room->price,
                'status' => $room->status,
                'room_capacity' => $room->room_capacity,
                'floor_name' => $room->floor ? $room->floor->name : null,
                'floor_number' => $room->floor ? $room->floor->number : null,
                'manager_name' => $isAdmin ? ($room->manager ? $room->manager->name : null) : null,
                'can_edit' => $isAdmin || ($isManager && $room->manager_id === $user->id), // Allow only assigned manager
            ];
        });

        if($isAdmin){
            $menuLinks = $this->getAdminMenuLinks();
        }else {
            $menuLinks = $this->getManagerMenuLinks();
        }

        return Inertia::render('Rooms/Index', [
            'rooms' => $rooms,
            'isAdmin' => $isAdmin,
            'isManager' => $isManager,
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'success' => session('success'),
            'menuLinks' => $menuLinks

        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');

        // Get managers for dropdown
        $managers = $isAdmin ? User::role('manager')->get(['id', 'name']) : collect([$user])->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name
            ];
        });

        // Get floors for dropdown
        $floors = Floor::all(['id', 'name', 'number']);

        if($isAdmin){
            $menuLinks = $this->getAdminMenuLinks();
        }else {
            $menuLinks = $this->getManagerMenuLinks();
        }
        return Inertia::render('Rooms/Create', [
            'managers' => $managers,
            'floors' => $floors,
            'statuses' => ['available', 'occupied', 'maintenance'],
            'menuLinks' => $menuLinks

        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');

        $validated = $request->validate([
            'room_number' => 'required|unique:rooms,room_number|integer|min:1000|max:9999',
            'price' => 'required|numeric|min:10|max:1000000',
            'room_capacity' => 'required|integer|min:1|max:100',
            'status' => 'required|in:available,occupied,maintenance',
            'floor_id' => 'required|exists:floors,id',
            'manager_id' => 'nullable|exists:users,id'
        ]);

        if (!$isAdmin) {
            $validated['manager_id'] = $user->id;
        }

        Room::create($validated);

        return redirect()->route('rooms.index')
            ->with('success', 'Room created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');

        if (!$isAdmin && $room->manager_id !== $user->id) {
            return redirect()->route('rooms.index')
                ->withErrors(['error' => 'You are not authorized to view this room.']);
        }

        if($isAdmin){
            $menuLinks = $this->getAdminMenuLinks();
        }else {
            $menuLinks = $this->getManagerMenuLinks();
        }

        return Inertia::render('Rooms/Show', [
            'room' => [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'price' => $room->price,
                'status' => $room->status,
                'room_capacity' => $room->room_capacity,
                'floor' => $room->floor ? [
                    'id' => $room->floor->id,
                    'name' => $room->floor->name,
                    'number' => $room->floor->number
                ] : null,
                'manager' => $room->manager ? [
                    'id' => $room->manager->id,
                    'name' => $room->manager->name
                ] : null,
                'menuLinks' => $menuLinks

            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');

        if (!$isAdmin && $room->manager_id !== $user->id) {
            return redirect()->route('rooms.index')
                ->withErrors(['error' => 'You are not authorized to edit this room.']);
        }

        // Get managers for dropdown
        $managers = $isAdmin ? User::role('manager')->get(['id', 'name']) : collect([$user])->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name
            ];
        });

        // Get floors for dropdown
        $floors = Floor::all(['id', 'name', 'number']);

        if($isAdmin){
            $menuLinks = $this->getAdminMenuLinks();
        }else {
            $menuLinks = $this->getManagerMenuLinks();
        }
        return Inertia::render('Rooms/Edit', [
            'room' => $room,
            'managers' => $managers,
            'floors' => $floors,
            'statuses' => ['available', 'occupied', 'maintenance'],
            'isAdmin' => $isAdmin,
            'menuLinks' => $menuLinks

        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');

        if (!$isAdmin && $room->manager_id !== $user->id) {
            return redirect()->route('rooms.index')
                ->withErrors(['error' => 'You are not authorized to update this room.']);
        }

        $validated = $request->validate([
            // 'room_number' => 'required|string|unique:rooms,room_number,' . $room->id,
            'price' => 'required|numeric|min:10|max:10000',
            'room_capacity' => 'required|integer|min:1|max:100',
            'status' => 'required|in:available,occupied,maintenance',
            'floor_id' => 'required|exists:floors,id',
            'manager_id' => 'nullable|exists:users,id'
        ]);

        // if (!$isAdmin) {
        //     $validated['manager_id'] = $room->manager_id;
        // }
        $validated['price'] *= 100;

        $room->update($validated);

        return redirect()->route('rooms.index')
            ->with('success', 'Room updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');

        if (!$isAdmin && $room->manager_id !== $user->id) {
            return request()->expectsJson()
                ? response()->json(['error' => 'You are not authorized to delete this room.'], 403)
                : redirect()->route('rooms.index')->withErrors(['error' => 'You are not authorized to delete    this room.']);
        }

        // Check if room has reservations with different statuses
        $hasActiveReservations = $room->reservations()->where(function($query) {
            $query->where('status', 'pending')
                  ->orWhere('status', 'confirmed')
                  ->orWhere('status', 'in-progress');
        })->exists();

        if ($hasActiveReservations) {
            return request()->expectsJson()
                ? response()->json([
                    'error' => 'Cannot delete a room with active or pending reservations', 
                    'message' => 'This room has active reservations that prevent deletion.'
                ], 422)
                : back()->withErrors(['error' => 'Cannot delete a room with active reservations']);
        }

        $room->delete();

        return request()->expectsJson()
            ? response()->json(['success' => 'Room deleted successfully'])
            : redirect()->route('rooms.index')->with('success', 'Room deleted successfully');
    }

    /**
     * Display a listing of available rooms for clients.
     */
    public function clientIndex(Request $request)
    {
        // Get available rooms with pagination
        $rooms = Room::with(['floor', 'manager'])
            ->where('status', 'available')
            ->when($request->filled('capacity'), function (Builder $query) use ($request) {
                return $query->where('room_capacity', '>=', $request->capacity);
            })
            ->when($request->filled('price_min'), function (Builder $query) use ($request) {
                return $query->where('price', '>=', $request->price_min);
            })
            ->when($request->filled('price_max'), function (Builder $query) use ($request) {
                return $query->where('price', '<=', $request->price_max);
            })
            ->paginate(9)
            ->through(function ($room) {
                return [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'room_capacity' => $room->room_capacity,
                    'image' => $room->image,
                    'price' => $room->price,
                    'status' => $room->status,
                    'floor_id' => $room->floor_id,
                    'floor_name' => $room->floor->name ?? 'Unknown',
                    'floor_number' => $room->floor->number ?? 'N/A',
                    'manager_id' => $room->manager_id,
                    'manager_name' => $room->manager->name ?? 'Not Assigned',
                ];
            });

        return Inertia::render('Client/landing', [
            'rooms' => $rooms->items(),
            'pagination' => [
                'total' => $rooms->total(),
                'per_page' => $rooms->perPage(),
                'current_page' => $rooms->currentPage(),
                'last_page' => $rooms->lastPage(),
                'from' => $rooms->firstItem(),
                'to' => $rooms->lastItem(),
                'path' => $request->url(),
            ],
        ]);
    }
}
