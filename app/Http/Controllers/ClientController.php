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

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = User::role('client')->orderBy('created_at', 'desc');

        if (Auth::user()->hasRole('receptionist')) {
            $query = $query->where('is_approved', '=', false); // Show only unapproved clients
        }

        return Inertia::render('Clients/Index', [
            'clients' => $query->paginate(10)
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
    public function approve(User $client)
    {
        $approvedBy =  Auth::user()->id;

        $client->update(['is_approved' => true, 'manager_id' => $approvedBy]);

        // $client->notify(new ClientApprovedNotification());

        return back()->with('success', 'Client approved successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $client)
    {
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }
}
