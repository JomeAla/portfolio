<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SecureApi\PaystackWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Paystack webhook for Secure API license sales (HMAC-SHA512 verified):
 * POST /api/secure-api/webhook
 */
class SecureApiWebhookController extends Controller
{
    public function handle(Request $request, PaystackWebhookService $service): JsonResponse
    {
        $signature = (string) $request->header('x-paystack-signature', '');
        $rawBody   = $request->getContent();
        $payload   = $request->all();

        if (!is_array($payload) || $payload === []) {
            return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
        }

        if (!$service->verify($signature, $rawBody)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
        }

        $result = $service->handle($payload, $rawBody);

        return response()->json($result, ($result['status'] ?? 'ok') === 'error' ? 500 : 200);
    }
}
