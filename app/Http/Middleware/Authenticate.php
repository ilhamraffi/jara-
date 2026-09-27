<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    public function __construct(protected JwtService $jwtService) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        if (! $header || ! preg_match('/Bearer\s+(\S+)/', $header, $matches)) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized. Token missing or invalid.',
                'code' => 'UNAUTHORIZED',
            ], 401);
        }

        $token = $matches[1];
        $payload = $this->jwtService->validateToken($token);

        if (! $payload || ! isset($payload['sub'])) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized. Token missing or invalid.',
                'code' => 'UNAUTHORIZED',
            ], 401);
        }

        $user = User::find($payload['sub']);

        if (! $user || ! $user->is_active) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized. Account is inactive or does not exist.',
                'code' => 'UNAUTHORIZED',
            ], 401);
        }

        // Attach user to request and auth guard
        $request->setUserResolver(fn () => $user);
        $request->attributes->set('user', $user);
        $request->attributes->set('token', $token);

        return $next($request);
    }
}
