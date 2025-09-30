<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureParticipantAuthenticated
{
    /**
     * Handle an incoming request by ensuring a student or employee is authenticated.
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if (Auth::guard('students')->check() || Auth::guard('employees')->check()) {
            return $next($request);
        }

        return redirect()->route('bro.login');
    }
}
