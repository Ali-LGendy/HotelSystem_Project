<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'national_id' => 'required|digits:14|unique:users,national_id',
            'mobile' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'gender' => 'required|in:male,female',
            'avatar_img' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Handle avatar upload if provided
        $avatarPath = null;
        if ($request->hasFile('avatar_img')) {
            $avatarPath = $request->file('avatar_img')->store('avatars', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'national_id' => $request->national_id,
            'mobile' => $request->mobile,
            'country' => $request->country,
            'gender' => $request->gender,
            'avatar_img' => $avatarPath,
            'role' => 'client',
            'is_approved' => 0, // Client starts as not approved (using 0 instead of false)
        ]);

        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard')->with('success', 'Registration successful! Your account is pending approval by a receptionist.');
    }
}
