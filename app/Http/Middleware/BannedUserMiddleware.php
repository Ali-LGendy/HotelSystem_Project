<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BannedUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (Auth::check()) {
            // Check if the authenticated user is banned
            if (Auth::user()->is_banned) {
                // Logout the user
                Auth::logout();

                // Invalidate the session
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Redirect with an error message using Inertia for Vue.js
                return redirect()->route('login')->with([
                    'error' => 'Your account has been banned. Please contact the administrator.',
                ]);
            }
        }

        return $next($request);
    }
}