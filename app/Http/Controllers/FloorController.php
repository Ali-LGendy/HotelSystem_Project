<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Floor;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Collection;

class FloorController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
        $isManager = $user->hasRole('manager');

        $perPage = $request->input('per_page', 10);
        $floors = Floor::with('manager')->withCount('room')->paginate($perPage);
    
        $isManager = $user->hasRole('manager');

        if($isAdmin){
            $menuLinks = $this->getAdminMenuLinks();
        }elseif($isManager){
            $menuLinks = $this->getManagerMenuLinks();
        }
        else{
            $menuLinks = $this->getreceptionistMenuLinks();
        }
        $floors->getCollection()->transform(function ($floor) use ($isAdmin, $user) {
            return [
                'id' => $floor->id,
                'name' => $floor->name,
                'number' => $floor->number,
                'manager_name' => $isAdmin ? ($floor->manager ? $floor->manager->name : null) : null,
                'rooms_count' => $floor->room_count,
                'can_edit' => $isAdmin || $floor->manager_id === $user->id,
                
            ];
        }); // logical error???????????????????????????????????????????????????

        // Debug the role and permissions  // i think i dont need that anymore
        \Log::debug('User role and permissions', [
            'isAdmin' => $isAdmin,
            'isManager' => $isManager,
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);

    
        return Inertia::render('Floors/index', [
            'floors' => $floors,
            'isAdmin' => $isAdmin,
            'isManager' => $isManager,
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'success' => session('success'),
            'menuLinks' => $menuLinks

        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
       $isManager = $user->hasRole('manager');

        if($isAdmin){
            $menuLinks = $this->getAdminMenuLinks();
        }elseif($isManager){
            $menuLinks = $this->getManagerMenuLinks();
        }
        else{
            $menuLinks = $this->getreceptionistMenuLinks();
        }
        $managers = $isAdmin ? User::role('manager')->get(['id', 'name']) : collect([$user])->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name
            ];
        });

        return Inertia::render('Floors/Create', [
            'managers' => $managers,
            'menuLinks'=> $menuLinks
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
        
        $validated = $request->validate([
            'name' => 'required|unique:floors,name|string|min:3',
            'manager_id' => 'nullable|exists:users,id'
        ], [
            'name.required' => 'The floor name is required.',
            'name.unique' => 'This floor name is already taken.',
            'name.min' => 'The floor name must be at least 3 characters.'
        ]);

        if (!$isAdmin) {
            $validated['manager_id'] = $user->id;
        }

        Floor::create($validated);

        return redirect()->route('floors.index')
            ->with('success', 'Floor created successfully');
    }

    public function edit(Floor $floor)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
        $isManager = $user->hasRole('manager');

        if($isAdmin){
            $menuLinks = $this->getAdminMenuLinks();
        }elseif($isManager){
            $menuLinks = $this->getManagerMenuLinks();
        }
        else{
            $menuLinks = $this->getreceptionistMenuLinks();
        }

        if (!$isAdmin && $floor->manager_id !== $user->id) {
            return redirect()->route('floors.index')
                ->withErrors(['error' => 'You are not authorized to edit this floor.']);
        }

        $managers = $isAdmin ? User::role('manager')->get(['id', 'name']) : collect([$user])->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
            ];
        });

        return Inertia::render('Floors/Edit', [
            'floor' => $floor,
            'managers' => $managers,
                'menuLinks'=> $menuLinks

        ]);
    }

    public function update(Request $request, Floor $floor)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
    
        if (!$isAdmin && $floor->manager_id !== $user->id) {
            return redirect()->route('floors.index')
                ->withErrors(['error' => 'You are not authorized to update this floor.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|min:3|unique:floors,name,' . $floor->id,
            'manager_id' => 'nullable|exists:users,id'
        ]);

        if (!$isAdmin) {
            $validated['manager_id'] = $floor->manager_id;
        }

        $floor->update($validated);

        return redirect()->route('floors.index')
            ->with('success', 'Floor updated successfully');
    }

    public function destroy(Floor $floor)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');

        if (!$isAdmin && $floor->manager_id !== $user->id) {
            return request()->expectsJson()
                ? response()->json(['error' => 'You are not authorized to delete this floor.'], 403)
                : redirect()->route('floors.index')->withErrors(['error' => 'You are not authorized to delete this floor.']);
        }

        if ($floor->room()->exists()) {
            return request()->expectsJson()
                ? response()->json(['error' => 'Cannot delete a floor with associated rooms'], 422)
                : back()->withErrors(['error' => 'Cannot delete a floor with associated rooms']);
        }

        $floor->delete();

        return request()->expectsJson()
            ? response()->json(['success' => 'Floor deleted successfully'])
            : redirect()->route('floors.index')->with('success', 'Floor deleted successfully');
    }
}