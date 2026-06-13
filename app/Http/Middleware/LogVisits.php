<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\VisitorLog;

class LogVisits
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (!$request->is('admin*') && !$request->is('_debugbar*') && !$request->ajax()) {
            VisitorLog::logVisit();
        }

        return $response;
    }
}
