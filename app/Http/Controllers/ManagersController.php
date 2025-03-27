<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateManagerRequest;
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
            'menuLinks' => $this->getAdminMenuLinks(),
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
   public function show(User $user)
{
    return Inertia::render('Admin/Users/Managers/Show', [
        'user' => [
            'data' => $user,
            'created_at' => $user->created_at,
            'id' => $user->id,
            
        ],
        'menuLinks' => $this->getAdminMenuLinks()
    ]);
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
    public function update(UpdateManagerRequest $request, User $user)
    {
        //
       \Log::info('Received request data:', $request->all());

    // Validate the request
    $validated = $request->validated();

    // Handle file upload
    if ($request->hasFile('avatar_img')) {
        $path = $request->file('avatar_img')->store('managers', 'public');
        $validated['avatar_img'] = $path;
    } else {
        // Keep existing avatar if no new file is uploaded
        $validated['avatar_img'] = $user->avatar_img;
    }

    // Handle password update
    if (!empty($validated['password'])) {
        $validated['password'] = bcrypt($validated['password']);
    } else {
        // Remove password from update if it's empty
        unset($validated['password']);
    }

    // Prepare update data
    $updateData = array_filter([
        'name' => $validated['name'] ?? null,
        'email' => $validated['email'] ?? null,
        'password' => $validated['password'] ?? null,
        'national_id' => $validated['national_id'] ?? null,
        'avatar_img' => $validated['avatar_img'] ?? null,
    ]);

    // Remove null values
    $updateData = array_filter($updateData, function ($value) {
        return $value !== null;
    });

    // Perform the update
    $user->update($updateData);

    // Redirect with success message
    return redirect()->route('admin.users.managers.index')->with([
        'success' => 'User updated successfully.',
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
        // $user->ban();

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
