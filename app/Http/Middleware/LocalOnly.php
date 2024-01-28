<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocalOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIps = ['127.0.0.1', '::1']; // List of local IP addresses

        if (in_array($request->ip(), $allowedIps)) {
            return $next($request); // Allow the request to proceed
        }

        return response()->json(['error' => 'Access denied.'], 403); // Forbidden
    }
}
