<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SecureApi\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public activation endpoint consumed by the WP Secure API Gateway plugin
 * (client mode): POST /api/secure-api/activate
 *   {license_key, site_url} -> {valid, plan, expires_at, max_activations, type, message}
 */
class SecureApiActivationController extends Controller
{
    public function activate(Request $request, LicenseService $licenses): JsonResponse
    {
        $key = (string) $request->input('license_key', '');
        $siteUrl = (string) $request->input('site_url', '');

        if ($key === '') {
            return response()->json(['valid' => false, 'message' => 'license_key is required'], 400);
        }

        return response()->json($licenses->activate($key, $siteUrl));
    }
}
