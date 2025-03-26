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
use App\Rules\EgyMobile;

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
            'name' => 'required|string|max:255|min:3',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'min:6', 'confirmed', Rules\Password::defaults()],
            'gender' => 'required|in:male,female',
            'mobile' => ['required', 'numeric', new EgyMobile],
            'avatar_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'country' => 'required',

        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gender'=> $request->gender,
            'mobile' => $request->mobile,
            'country' => $request->country,
            'avatar_img' => $request->avatar_image 
                ? $request->file('avatar_image')->store('avatars', 'public') 
                : null,
            // 'role' => 'client'
        ]);
        $user->assignRole('client');

        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard');
    }
}
