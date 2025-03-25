<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ClientApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->role === 'client') {
            if (!Auth::user()->is_approved) {
                return redirect()->route('dashboard')
                    ->with('error', 'Your account is pending approval. Please wait for a receptionist to approve your account.');
            }

            if (Auth::user()->is_banned) {
                return redirect()->route('dashboard')
                    ->with('error', 'Your account has been banned. Please contact support for assistance.');
            }
        }

        return $next($request);
    }
}