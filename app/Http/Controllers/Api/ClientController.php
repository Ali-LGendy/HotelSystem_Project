<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Get approved clients for the current receptionist.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function approvedClients()
    {
        $clients = User::where('role', 'client')
            ->where('is_approved', true)
            ->where('manager_id', auth()->id())
            ->select(['id', 'name', 'email'])
            ->get();

        return response()->json($clients);
    }
}