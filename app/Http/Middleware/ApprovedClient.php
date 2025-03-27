<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApprovedClient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // if (Auth::check()) {
            // Check if the authenticated user is banned
            if (!Auth::user()->is_approved) {
                // Logout the user
                Auth::logout();

                // Invalidate the session
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Redirect with an error message using Inertia for Vue.js
                return redirect()->route('login')->with([
                    'error' => 'Your account has not been approved yet. an email will be sent to you upon approval.',
                ]);
            }
        // }
        return $next($request);
    }
}
