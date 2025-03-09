<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$abilities)
    {
        if (!$request->bearerToken()) {
            return response()->json([
                'success' => false,
                'message' => 'API token not provided',
                'error' => 'authentication_error'
            ], 401);
        }

        $token = hash('sha256', $request->bearerToken());
        $apiToken = ApiToken::where('token', $token)->first();

        if (!$apiToken) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API token',
                'error' => 'authentication_error'
            ], 401);
        }

        if ($apiToken->expires_at && now()->gt($apiToken->expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'API token has expired',
                'error' => 'token_expired'
            ], 401);
        }

        $apiToken->update(['last_used_at' => now()]);
        Auth::login($apiToken->user);

        if (empty($abilities)) {
            return $next($request);
        }

        foreach ($abilities as $ability) {
            if ($apiToken->abilities === ['*'] ||
                in_array($ability, $apiToken->abilities)) {
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Token does not have the required abilities',
            'error' => 'insufficient_permissions',
            'required_ability' => $abilities
        ], 403);
    }
}