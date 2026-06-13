<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CaptureUtmParams
{
    public function handle(Request $request, Closure $next)
    {
        $utmParams = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
        
        foreach ($utmParams as $param) {
            $value = $request->query($param) ?? $request->input($param);
            if ($value) {
                session([$param => $value]);
            }
        }
        
        if ($request->headers->has('referer')) {
            $referer = $request->headers->get('referer');
            if ($referer && !session('referrer_url')) {
                session(['referrer_url' => $referer]);
            }
        }
        
        return $next($request);
    }
}