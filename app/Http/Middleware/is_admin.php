<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class is_admin // Class name matches the error "Target class [is_admin] does not exist"
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated and has the 'admin' role
        // Adjust the condition 'Auth::user()->role === "admin"' if your user model
        // uses a different attribute or value to identify administrators.
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request);
        }

        // If not an admin, redirect to home or show an unauthorized error.
        // You can customize this part based on your application's needs.
        // For example, abort(403, 'Unauthorized action.');
        return redirect('/')->with('error', 'You do not have permission to access this page.');
    }
}

