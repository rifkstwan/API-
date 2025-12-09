<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\Token;
use League\OAuth2\Server\ResourceServer;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

class CustomClientAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please provide Bearer token.',
                'error' => 'Token not provided'
            ], 401);
        }

        try {
            // Verify token using Passport's Token model
            $tokenId = (new \Laravel\Passport\TokenRepository)->find($token);

            if (!$tokenId || $tokenId->revoked) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or revoked token',
                    'error' => 'Token verification failed'
                ], 401);
            }

            // Check if token is expired
            if ($tokenId->expires_at < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token has expired',
                    'error' => 'Token expired'
                ], 401);
            }

            return $next($request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token validation error',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}
