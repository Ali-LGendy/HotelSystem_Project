<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use App\Notifications\ClientApprovedNotification;
use Spatie\Permission\Models\Role;

class EssamClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $allClients = User::role('client')
            ->with('manager') // Eager load the manager relationship
            ->orderBy('created_at', 'desc');
            
        if (Auth::user()->hasRole('receptionist')) {
            $unapprovedClients = $allClients->where('is_approved', '=', false); // Show only unapproved clients

            $receptionist_id = Auth::user()->id;
    
            $approvedClients = $allClients->where('manager_id', '=', $receptionist_id); // Show only clients approved by this receptionist

            return Inertia::render('Admin/Users/Clients/Index', [
                'unapprovedClients' => $unapprovedClients->paginate(10),
                'approvedClients' => $approvedClients->paginate(10),
                'isReceptionist' => true,
                'menuLinks' => $this->getAdminMenuLinks()
            ]);
        }

        if (Auth::user()->hasRole('admin')){
            $isAdmin=true;
        }else{
            $isAdmin=false;
        }


        return Inertia::render('Admin/Users/Clients/Index', [
            'allClients' => $allClients->paginate(10),
            'isAdmin' => $isAdmin,
            'menuLinks' => $this->getAdminMenuLinks()
        ]);
    }

    /**
     * Display a listing of approved resource.
     */
    public function indexApproved(Request $request): Response
    {
        $receptionist_id = Auth::user()->id;

        $query = User::role('client')->orderBy('created_at', 'desc');

        $query = $query->where('manager_id', '=', $receptionist_id); // Show only clients approved by this receptionist

        return Inertia::render('Clients/Index', [
            'clients' => $query->paginate(10)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Clients/Create', [
            // 'countries' => config('countries.list') // Load countries from config
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request)
    {
        $client = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'country' => $request->input('country'),
            'gender' => $request->input('gender'),
            'avatar_image' => $request->file('avatar_image') 
                ? $request->file('avatar_image')->store('avatars', 'public') 
                : null,
        ]);

        $client->assignRole('client');

        return redirect()->route('clients.index')->with('success', 'Client registered successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $client): Response
    {
        return Inertia::render('Clients/Show', [
            'client' => $client
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $client): Response
    {
        return Inertia::render('Clients/Edit', [
            'client' => $client,
            'countries' => config('countries.list')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, User $client)
    {
        $client->update([
            'name' => $client->name,
            'email' => $client->email,
            'password' => Hash::make($client->password),
            'country' => $client->country,
            'gender' => $client->gender,
            'avatar_image' => $request->file('avatar_image') 
                ? $request->file('avatar_image')->store('avatars', 'public') 
                : $client->avatar_image,
        ]);

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    /**
     * Approve a client (Receptionist Only).
     */
    public function approve($id)
    {
        $approvedBy =  Auth::user()->id;

        $client = User::findOrFail($id);

        $client->update(['is_approved' => true, 'manager_id' => $approvedBy]);

        // $client->notify(new ClientApprovedNotification());

        return back()->with('success', 'Client approved successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $client = User::findOrFail($id);

        $client->delete();

        return back()->with('success', 'Client deleted successfully.');
    }
}
