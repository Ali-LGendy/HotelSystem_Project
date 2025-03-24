<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;

class ReceptionistsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $receptionists = User::role('receptionist')->paginate(10);

        return Inertia::render('Admin/Users/Receptionists/Index', [
            'receptionists' => $receptionists,
            'menuLinks' => $this->getAdminMenuLinks()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return Inertia::render('Admin/Users/Receptionists/Create', [
            'menuLinks' => $this->getAdminMenuLinks()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validated();
        if($request->hasFile('avatar_img')){
            $path = $request->file('photo')->store('photos','public');
            $validated['avatar_img'] = $path;
        }
 
        // dd( $title, $description);
        $user = User::create([
            'email' => $validated['email'],
            'password' => $validated['password'],
            'name' => $validated['name'],
            'national_id' => $validated['national_id'],
            'avatar_img' => $validated['avatar_img'],
        ]);
        $user->assignRole('receptionist');
        return redirect()->route('admin.users.receptionists.index')->with([
            'success' => 'User created successfully.',
            'user' => $user,
        ]);    
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
        $user->delete();
        return redirect()->route('admin.users.receptionists.index')->with('error', 'You cannot delete an admin user.');
        
    }
}
