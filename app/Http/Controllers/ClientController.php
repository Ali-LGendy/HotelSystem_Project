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
        $clients = User::role('client')->paginate(10);
        $user = auth()->user()->role;
        return Inertia::render('Client/Index', [
            'clients' => $clients,
            'menuLinks' => $this->getAdminMenuLinks(),
        ]);
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return Inertia::render('Client/Create', [
            'menuLinks' => $this->getAdminMenuLinks(),
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
    public function destroy(string $id)
    {
        //
    }
}
