<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;
use App\Http\Requests\StoreManagerRequest;

class ReceptionistsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $receptionists = User::role('receptionist')
        // ->with(['manager' => function ($query) {
        //     $query->select('name'); // Select manager details
        // }])
        // ->paginate(10);
        $receptionists = User::role('receptionist')->with('manager')->paginate(10);

        $user = auth()->user();
        $is_admin = false;
        // $managers = null;

        if ($user->hasRole('admin')) {
            $is_admin = true;
            // Retrieve distinct managers for the receptionists
           
            }
        if($is_admin){
            $menuLinks = $this->getAdminMenuLinks();
        }else {
            $menuLinks = $this->getManagerMenuLinks();
        }

        return Inertia::render('Admin/Users/Receptionists/Index', [
            'receptionists' => $receptionists,
            'is_admin' => $is_admin,
            'user' => $user->id,
            // 'managers' => $managers,

            'menuLinks' => $menuLinks
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
        $user->assignRole('receptionist');
        return redirect()->route('admin.users.receptionists.index')->with([
            'success' => 'User created successfully.',
            'user' => $user,
        ]);  
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
         return Inertia::render('Admin/Users/Receptionists/Show', [
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
        if(auth()->user()->id == $user->manager_id && auth()->user()->hasRole('admin')){
            return Inertia::render('Admin/Users/Receptionists/Edit', [
            'receptionist' => $user,
            'menuLinks' => $this->getAdminMenuLinks()
            ]);
        }else{
            // dd(auth()->user()->id);
            dd($user);
            // abort(403, 'Unauthorized action.');
        }
        
         
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreManagerRequest $request, User $user)
    {
         if(auth()->user()->id !== $user->manager_id && !auth()->user()->hasRole('admin')){
            abort(403, 'Unauthorized action.');
        }
        //\Log::info('Received request data:', $request->all());

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
    return redirect()->route('admin.users.receptionists.index')->with([
        'success' => 'User updated successfully.',
        'user' => $user,
    ]);  

    }

    /**
     * Remove the specified resource from storage.
     */
      public function ban(User $user)
    {
        if(auth()->user()->id !== $user->manager_id && !auth()->user()->hasRole('admin')){
            abort(403, 'Unauthorized action.');
        }
        
        $user->is_banned = !$user->is_banned;
        $user->save();

        // Prepare message based on new ban status
        $message = $user->is_banned 
            ? 'User banned successfully.' 
            : 'User unbanned successfully.';

        return redirect()
            ->route('admin.users.receptionists.index')
            ->with('success', $message);
    }
    public function destroy(User $user)
    {
        if (auth()->user()->id !== $user->manager_id && !auth()->user()->hasRole('admin')) {
        abort(403, 'Unauthorized action.');
    }

    // Delete the receptionist
    $user->delete();

    return redirect()->route('admin.users.receptionists.index')
        ->with('success', 'Receptionist deleted successfully.');
        
    }
}
