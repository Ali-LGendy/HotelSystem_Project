<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;
use App\Http\Requests\StoreClientRequest;


class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $user = auth()->user();
        if($user->hasRole('admin') || $user->hasRole('manager') )
        {
            $clients = User::role('client')->paginate(10);
        }else{
            $clients = User::role('client')->where('is_approved',false)->paginate(10);
        }   


        return Inertia::render('Client/Index', [
            'clients' => $clients,
            'menuLinks' => $this->getreceptionistMenuLinks(),
        ]);
        
    }
    public function myApproved(User $user)
    {
        //
        $loggedInUser = auth()->user();
        $clients = User::role('client')->where('is_approved',true)->where('manager_id',$loggedInUser->id)->paginate(10);
        return Inertia::render('Client/MyApproved', [
            'clients' => $clients,
            'menuLinks' => $this->getreceptionistMenuLinks(),
        ]);
        
    }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return Inertia::render('Client/Create', [
            'menuLinks' => $this->getreceptionistMenuLinks(),
        ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request)
    {
        //
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
            'avatar_img' => $validated['avatar_img'],
            'gender' => $validated['gender'],
            'mobile' => $validated['mobile'],
            'is_approved' =>false,
        ]);
        $user->assignRole('client');
        return redirect()->route('client.index')->with([
            'success' => 'User created successfully.',
            'user' => $user,
        ]);  
    }
    public function approve(User $user)
    {
        $loggedInUser = auth()->user();
        $user->is_approved = !$user->is_approved;
        $user->manager_id = $loggedInUser->id;
        $user->save();
        // $user->ban();

        // Prepare message based on new ban status
        $message = $user->is_approved 
            ? 'User approved successfully.' 
            : 'User Unapproved successfully.';

        return redirect()
            ->route('client.index')
            ->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
        return Inertia::render('Client/Show', [
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
    public function destroy(string $id)
    {
        //
    }
}
