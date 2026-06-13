<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;

class ShareSettings
{
    public function handle(Request $request, Closure $next)
    {
        view()->share('settings', Setting::getAll() ?: []);
        return $next($request);
    }
}