<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated via Sanctum
        if (!$request->user()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        // You can add additional admin checks here if needed
        // For example, check user role or permissions

        return $next($request);
    }
}
