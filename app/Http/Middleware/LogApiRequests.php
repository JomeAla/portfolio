<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'response_size' => strlen($response->getContent()),
        ];

        if (auth()->check()) {
            $logData['user_id'] = auth()->id();
        }

        if ($response->getStatusCode() >= 500) {
            Log::error('API Error', $logData);
        } elseif ($response->getStatusCode() >= 400) {
            Log::warning('API Warning', $logData);
        } else {
            Log::info('API Request', $logData);
        }

        return $response;
    }
}