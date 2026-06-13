<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class PaystackRefundService
{
    private function getSecretKey(): string
    {
        return Setting::get('paystack_secret_key') ?? config('services.paystack.secret', '');
    }

    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->getSecretKey(),
            'Content-Type' => 'application/json',
        ];
    }

    public function refund(string $transactionReference, ?int $amountKobo = null, string $reason = 'Customer requested refund'): ?array
    {
        try {
            $payload = [
                'transaction' => $transactionReference,
                'reason' => $reason,
            ];

            if ($amountKobo) {
                $payload['amount'] = $amountKobo;
            }

            $response = Http::withHeaders($this->headers())
                ->post('https://api.paystack.co/refund', $payload);

            if ($response->successful()) {
                $data = $response->json('data');
                Log::info("Paystack refund initiated: {$transactionReference}", ['data' => $data]);
                return $data;
            }

            Log::error('Paystack refund failed', [
                'reference' => $transactionReference,
                'response' => $response->body(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Paystack refund error: ' . $e->getMessage());
            return null;
        }
    }

    public function partialRefund(string $transactionReference, int $amountKobo, string $reason = 'Partial refund'): ?array
    {
        return $this->refund($transactionReference, $amountKobo, $reason);
    }

    public function verifyRefund(string $refundId): ?array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("https://api.paystack.co/refund/{$refundId}");

            if ($response->successful()) {
                return $response->json('data');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Paystack verify refund error: ' . $e->getMessage());
            return null;
        }
    }

    public function processAdminRefund(int $refundRequestId): array
    {
        $pdo = db_pdo();

        $stmt = $pdo->prepare("SELECT r.*, o.payment_reference, o.final_amount, o.customer_email, o.order_number FROM refund_requests r JOIN orders o ON r.order_id = o.id WHERE r.id = ?");
        $stmt->execute([$refundRequestId]);
        $refund = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$refund) {
            return ['success' => false, 'message' => 'Refund request not found'];
        }

        if (!$refund['payment_reference']) {
            return ['success' => false, 'message' => 'No payment reference found'];
        }

        $refundAmount = $refund['refund_amount'] ?? $refund['final_amount'];
        $amountKobo = (int) ($refundAmount * 100);

        $result = $this->refund(
            $refund['payment_reference'],
            $amountKobo,
            $refund['reason'] ?? 'Admin approved refund'
        );

        if ($result) {
            $pdo->prepare("UPDATE refund_requests SET status = 'approved', processed_at = NOW(), paystack_refund_id = ?, refund_amount = ? WHERE id = ?")
                ->execute([$result['id'] ?? null, $refundAmount, $refundRequestId]);

            $pdo->prepare("UPDATE orders SET payment_status = 'refunded' WHERE id = ?")
                ->execute([$refund['order_id']]);

            return ['success' => true, 'message' => 'Refund processed via Paystack', 'data' => $result];
        }

        $pdo->prepare("UPDATE refund_requests SET status = 'approved', processed_at = NOW() WHERE id = ?")
            ->execute([$refundRequestId]);

        $pdo->prepare("UPDATE orders SET payment_status = 'refunded' WHERE id = ?")
            ->execute([$refund['order_id']]);

        return ['success' => true, 'message' => 'Refund approved locally but Paystack refund may require manual processing'];
    }
}