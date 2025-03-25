<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;
use App\Http\Requests\StoreManagerRequest;

class ManagersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $managers = User::role('manager')->paginate(10);

        return Inertia::render('Admin/Users/Managers/Index', [
            'managers' => $managers,
            'menuLinks' => $this->getAdminMenuLinks()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return Inertia::render('Admin/Users/Managers/Create', [
            'menuLinks' => $this->getAdminMenuLinks()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreManagerRequest $request)
    {
        $validated = $request->validated();
        if($request->hasFile('avatar_img')){
            $path = $request->file('avatar_img')->store('managers','public');
            $validated['avatar_img'] = $path;
        }else{
            $validated['avatar_img'] = 'defaults/user.png';
        }
 
        $user = User::create([
            'email' => $validated['email'],
            'password' => $validated['password'],
            'name' => $validated['name'],
            'national_id' => $validated['national_id'],
            'avatar_img' => $validated['avatar_img'],
        ]);
        $user->assignRole('manager');
        return redirect()->route('admin.users.managers.index')->with([
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
    public function edit(User $user)
    {
        //
         return Inertia::render('Admin/Users/Managers/Edit', [
            'manager' => $user,
            'menuLinks' => $this->getAdminMenuLinks()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreManagerRequest $request, User $user)
    {
        //
        // dd($request->all());
        $validated = $request->validated();
        if($request->hasFile('avatar_img')){
            $path = $request->file('avatar_img')->store('managers','public');
            $validated['avatar_img'] = $path;
        }else {
        // Keep existing avatar if no new file is uploaded
        $validated['avatar_img'] = $user->avatar_img;
    }
    if (!empty($validated['password'])) {
        $validated['password'] = bcrypt($validated['password']);
    }
        // dd( $title, $description);
        
        $user->update([
            'email' => $validated['email'],
            'password' => $validated['password'],
            'name' => $validated['name'],
            'national_id' => $validated['national_id'],
            'avatar_img' => $validated['avatar_img'],
        ]);
        return redirect()->route('admin.users.managers.index')->with([
            'success' => 'User created successfully.',
            'user' => $user,
        ]);  

    }

    /**
     * Remove the specified resource from storage.
     */
    public function ban(User $user)
    {
          $user->is_banned = !$user->is_banned;
        $user->save();

        // Prepare message based on new ban status
        $message = $user->is_banned 
            ? 'User banned successfully.' 
            : 'User unbanned successfully.';

        return redirect()
            ->route('admin.users.managers.index')
            ->with('success', $message);
    }
    public function destroy(User $user)
    {
        //
        $user->delete();
        return redirect()->route('admin.users.managers.index')->with('error', 'You cannot delete an admin user.');
        
    }
}
