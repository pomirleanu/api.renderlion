<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Auth\AuthenticationException;
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
            throw new AuthenticationException('API token not provided');
        }

        $token = hash('sha256', $request->bearerToken());
        $apiToken = ApiToken::where('token', $token)->first();

        if (!$apiToken) {
            throw new AuthenticationException('Invalid API token');
        }

        if ($apiToken->expires_at && now()->gt($apiToken->expires_at)) {
            throw new AuthenticationException('API token has expired');
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

        throw new AuthenticationException('Token does not have the required abilities');
    }
}
