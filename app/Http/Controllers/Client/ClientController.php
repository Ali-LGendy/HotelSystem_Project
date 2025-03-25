<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ClientController extends Controller
{
    /**
     * Display the client's profile.
     */
    public function profile()
    {
        $client = User::where('id', Auth::id())
            ->select(['id', 'name', 'email', 'national_id', 'country', 'gender', 'avatar_img', 'is_approved'])
            ->firstOrFail();

        return Inertia::render('Client/Profile', ['client' => $client]);
    }

    /**
     * Update the client's profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'national_id' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'gender' => 'required|in:male,female',
            'avatar_img' => 'nullable|image|mimes:jpg,jpeg|max:2048',
        ]);

        // Handle avatar upload if provided
        if ($request->hasFile('avatar_img')) {
            // Delete old avatar if it exists and is not the default
            if ($user->avatar_img && !str_contains($user->avatar_img, 'default-avatar')) {
                Storage::disk('public')->delete($user->avatar_img);
            }

            // Store the new avatar
            $avatarPath = $request->file('avatar_img')->store('avatars', 'public');
            $validated['avatar_img'] = $avatarPath;
        }

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Display the client's dashboard with summary information.
     */
    public function dashboard()
    {
        $client = Auth::user();
        
        // Get reservation statistics
        $totalReservations = $client->reservations()->count();
        $pendingReservations = $client->reservations()->where('status', 'pending')->count();
        $confirmedReservations = $client->reservations()->where('status', 'confirmed')->count();
        $checkedInReservations = $client->reservations()->where('status', 'checked_in')->count();
        
        // Get recent reservations
        $recentReservations = $client->reservations()
            ->with('room:id,room_number,price')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Client/Dashboard', [
            'stats' => [
                'totalReservations' => $totalReservations,
                'pendingReservations' => $pendingReservations,
                'confirmedReservations' => $confirmedReservations,
                'checkedInReservations' => $checkedInReservations,
            ],
            'recentReservations' => $recentReservations,
        ]);
    }
}