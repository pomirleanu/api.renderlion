<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiLoggerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log the incoming request
        $requestId = uniqid('req_');

        // Get authenticated user if available
        $user = $request->user();
        $userId = $user ? $user->id : 'unauthenticated';

        // Log request information
        Log::channel('api')->info('API Request', [
            'id' => $requestId,
            'user_id' => $userId,
            'method' => $request->method(),
            'path' => $request->path(),
            'url' => $request->fullUrl(),
            'payload' => $this->sanitizeData($request->all()),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Process the request
        $response = $next($request);

        // Get response content (only for json responses)
        $responseContent = null;
        if ($response->headers->get('Content-Type') === 'application/json') {
            $responseContent = json_decode($response->getContent(), true);
        }

        // Log response information
        Log::channel('api')->info('API Response', [
            'id' => $requestId,
            'status' => $response->getStatusCode(),
            'payload' => $this->sanitizeData($responseContent),
        ]);

        return $response;
    }

    /**
     * Sanitize sensitive data before logging.
     *
     * @param mixed $data
     * @return mixed
     */
    protected function sanitizeData($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $sensitiveFields = ['password', 'password_confirmation', 'token', 'auth', 'key', 'secret'];

        foreach ($data as $key => &$value) {
            if (in_array(strtolower($key), $sensitiveFields)) {
                $value = '[REDACTED]';
            } else if (is_array($value)) {
                $value = $this->sanitizeData($value);
            }
        }

        return $data;
    }
}