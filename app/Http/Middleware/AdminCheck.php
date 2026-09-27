<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminCheck
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?: $request->attributes->get('user');

        if (! $user || $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized. Admin access required.',
                'code' => 'ADMIN_ONLY',
            ], 403);
        }

        return $next($request);
    }
}
