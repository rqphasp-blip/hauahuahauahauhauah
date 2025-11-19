<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Headers
{
    public function handle(Request $request, Closure $next)
    {
        // Check if FORCE_HTTPS is set to true
        if (env('FORCE_HTTPS') == 'true') {
            \URL::forceScheme('https'); // Force HTTPS
            header("Content-Security-Policy: upgrade-insecure-requests");
        }

		        // Apply a restrictive Content Security Policy to block untrusted trackers such as
        // Contentsquare while still allowing the app's known external dependencies.
        $contentSecurityPolicy = [
            "default-src 'self' data: blob:",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://ajax.googleapis.com https://cdn.jsdelivr.net https://maps.googleapis.com https://maps.gstatic.com https://fonts.bunny.net https://www.googletagmanager.com",
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
            "img-src 'self' data: https:",
            "font-src 'self' https://fonts.bunny.net data:",
            "connect-src 'self' https://ajax.googleapis.com https://maps.googleapis.com https://maps.gstatic.com https://start.bio6.me https://bio6.click https://lgw.one https://www.google.com https://www.googletagmanager.com",
            "frame-src 'self' https://www.google.com https://www.youtube.com https://maps.google.com",
            'upgrade-insecure-requests',
        ];

        header('Content-Security-Policy: ' . implode('; ', $contentSecurityPolicy));
		
		
        // Check if FORCE_ROUTE_HTTPS is set to true
        if (env('FORCE_ROUTE_HTTPS') == 'true' && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off')) {
            $redirect_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location: $redirect_url");
            exit();
        }

        return $next($request);
    }
}
