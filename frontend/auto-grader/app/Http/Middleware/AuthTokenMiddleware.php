<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the auth_token exists in the session
        if (!$request->session()->has('auth_token')) {
            // If the auth_token is not present, redirect to the login page
            return redirect()->route('login')->with('error', 'Unauthorized. Please log in.');
        }

        // Continue with the request if the auth_token exists
        return $next($request);
    }
}
