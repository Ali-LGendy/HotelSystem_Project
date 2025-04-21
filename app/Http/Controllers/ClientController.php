<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Reservation;
use Inertia\Inertia;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Notifications\GreetingApprovedClient;


class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $user = auth()->user();
        if($user->hasRole('admin'))
        {
            $clients = User::role('client')->paginate(10);
            $menuLinks = $this->getAdminMenuLinks();
        }else if( $user->hasRole('manager')){
            $clients = User::role('client')->where('is_approved',false)->paginate(10);
            $menuLinks = $this->getManagerMenuLinks();
        }else{
            $clients = User::role('client')->where('is_approved',false)->paginate(10);
            $menuLinks = $this->getreceptionistMenuLinks();
        }   


        return Inertia::render('Client/Index', [
            'clients' => $clients,
            'menuLinks' => $menuLinks,
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
    public function clientsReservations(User $user)
    {
        //
        $loggedInUser = auth()->user();
        $reservations = Reservation::with([
            'client:id,name',  // Load client's name
            'room:id,room_number' // Load room number
            ])->whereHas('client', function ($query) use ($loggedInUser) {
                $query->role('client')->where('is_approved', true)->where('manager_id', $loggedInUser->id);
                })->paginate(10);
//         $clientIds = User::role('client')
//     ->where('is_approved', true)
//     ->where('manager_id', $loggedInUser->id)
//     ->pluck('id'); // Get only client IDs

// $reservations = Reservation::whereIn('client_id', $clientIds)
//     ->paginate(10);

        return Inertia::render('Client/ClientsReservations', [
            'reservations' => $reservations,
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
        // Send notification to the user
        if ($user->is_approved) {
            $user->notify(new GreetingApprovedClient($user));
        }

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
    public function edit(User $user)
    {
        //
        if(auth()->user()->hasRole('admin')){
            return Inertia::render('Client/Edit', [
            'client' => $user,
            'menuLinks' => $this->getAdminMenuLinks()
            ]);
        }else{
            
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, User $user)
    {
        //
         if(!auth()->user()->hasRole('admin')){
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
            'mobile' => $validated['mobile'] ?? null,
            'avatar_img' => $validated['avatar_img'] ?? null,
        ]);

        // Remove null values
        $updateData = array_filter($updateData, function ($value) {
            return $value !== null;
        });

        // Perform the update
        $user->update($updateData);

        // Redirect with success message
        return redirect()->route('client.index')->with([
            'success' => 'User updated successfully.',
            'user' => $user,
        ]);
    }
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
            ->route('client.index')
            ->with('success', $message);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
        if (!auth()->user()->hasRole('admin')) {
        abort(403, 'Unauthorized action.');
    }

    // Delete the receptionist
    $user->delete();

    return redirect()->route('client.index')
        ->with('success', 'Client deleted successfully.');
        
   
    }
    public function showMyReservation(){

        $client = auth()->user();
        // $client = User::user()->where('id',$currentUserId);
        $reservations = $client->reservations()->with('room')->get();


        return Inertia::render('Receptionist/Client/MyReservation', [
           
            'reservations' => $reservations,
        ]);
    }
}
