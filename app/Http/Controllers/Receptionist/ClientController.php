<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function pendingClients()
    {
        $clients = User::where('role', 'client')
            ->where('is_approved', false)
            ->select(['id', 'name', 'email', 'mobile', 'country', 'gender', 'created_at'])
            ->paginate(10);

        return Inertia::render('Receptionist/Client/PendingClients', [
            'pendingClients' => $clients,
        ]);
    }

    public function approvedClients()
    {
        $clients = User::where('role', 'client')
            ->where('is_approved', true)
            ->where('manager_id', auth()->id())
            ->select(['id', 'name', 'email', 'mobile', 'country', 'gender', 'created_at'])
            ->paginate(10);

        return Inertia::render('Receptionist/Client/ApprovedClients', [
            'approvedClients' => $clients,
        ]);
    }

    public function approveClient($id)
    {
        $client = User::findOrFail($id);
        $client->update([
            'is_approved' => true,
            'manager_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Client approved successfully');
    }

    public function clientReservations($id = null)
    {
        $query = Reservation::with(['client', 'room'])
            ->whereHas('client', function ($q) {
                $q->where('role', 'client')
                    ->where('is_approved', true)
                    ->where('manager_id', auth()->id());
            });

        if ($id) {
            $query->where('client_id', $id);
        }

        $reservations = $query->paginate(10);

        return Inertia::render('Receptionist/Client/ClientsReservations', [
            'clientsReservations' => $reservations,
            'clientId' => $id,
        ]);
    }
}
