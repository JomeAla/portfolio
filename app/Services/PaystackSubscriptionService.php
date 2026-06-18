<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;

class PaystackSubscriptionService
{
    private function getSecretKey(): string
    {
        return Setting::get('paystack_secret_key') ?? config('services.paystack.secret', '');
    }

    private function getPublicKey(): string
    {
        return Setting::get('paystack_public_key') ?? config('services.paystack.public', '');
    }

    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->getSecretKey(),
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache',
        ];
    }

    private function baseUrl(): string
    {
        $isTest = Setting::get('paystack_test_mode') ?? true;
        return $isTest
            ? 'https://api.paystack.co'
            : 'https://api.paystack.co';
    }

    public function createPlan(SubscriptionPlan $plan): ?array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->post($this->baseUrl() . '/plan', [
                    'name' => $plan->name,
                    'amount' => (int) ($plan->price * 100),
                    'interval' => $plan->interval,
                    'description' => $plan->description,
                    'currency' => 'NGN',
                ]);

            if ($response->successful()) {
                $data = $response->json('data');
                $plan->update(['paystack_plan_code' => $data['plan_code']]);
                return $data;
            }

            Log::error('Paystack create plan failed', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Paystack create plan error: ' . $e->getMessage());
            return null;
        }
    }

    public function subscribe(string $email, SubscriptionPlan $plan, string $customerName = null, string $customerPhone = null): ?array
    {
        try {
            $payload = [
                'email' => $email,
                'plan' => $plan->paystack_plan_code,
                'amount' => (int) ($plan->price * 100),
                'currency' => 'NGN',
                'channels' => ['card', 'bank', 'ussd', 'qr', 'mobile_money', 'bank_transfer'],
                'metadata' => [
                    'custom_fields' => [
                        ['display_name' => 'Customer Name', 'variable_name' => 'customer_name', 'value' => $customerName ?? ''],
                        ['display_name' => 'Customer Phone', 'variable_name' => 'customer_phone', 'value' => $customerPhone ?? ''],
                        ['display_name' => 'Plan', 'variable_name' => 'plan_name', 'value' => $plan->name],
                    ],
                ],
            ];

            $response = Http::withHeaders($this->headers())
                ->post($this->baseUrl() . '/transaction/initialize', $payload);

            if ($response->successful()) {
                return $response->json('data');
            }

            Log::error('Paystack subscribe failed', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Paystack subscribe error: ' . $e->getMessage());
            return null;
        }
    }

    public function handleWebhook(array $payload): array
    {
        $event = $payload['event'] ?? '';
        $data = $payload['data'] ?? [];

        switch ($event) {
            case 'charge.success':
                return $this->handleChargeSuccess($data);

            case 'subscription.create':
                return $this->handleSubscriptionCreate($data);

            case 'subscription.disable':
                return $this->handleSubscriptionDisable($data);

            case 'subscription.enable':
                return $this->handleSubscriptionEnable($data);

            case 'invoice.payment_failed':
                return $this->handlePaymentFailed($data);

            default:
                Log::info('Paystack webhook unhandled event: ' . $event);
                return ['status' => 'ignored', 'event' => $event];
        }
    }

    private function handleChargeSuccess(array $data): array
    {
        $email = $data['customer']['email'] ?? '';
        $reference = $data['reference'] ?? '';
        $amount = ($data['amount'] ?? 0) / 100;

        Log::info("Paystack charge success: $email - $reference - ₦$amount");

        return ['status' => 'processed', 'email' => $email, 'reference' => $reference];
    }

    private function handleSubscriptionCreate(array $data): array
    {
        $email = $data['customer']['email'] ?? '';
        $subscriptionCode = $data['subscription_code'] ?? $data['id'] ?? '';
        $planCode = $data['plan']['plan_code'] ?? '';

        $plan = SubscriptionPlan::where('paystack_plan_code', $planCode)->first();

        if ($plan) {
            Subscription::updateOrCreate(
                [
                    'customer_email' => $email,
                    'plan_id' => $plan->id,
                ],
                [
                    'paystack_subscription_code' => $subscriptionCode,
                    'paystack_email_token' => $data['email_token'] ?? null,
                    'status' => 'active',
                    'started_at' => now(),
                    'current_period_end' => now()->addMonths($plan->isYearly() ? 12 : 1),
                    'next_billing_date' => now()->addMonths($plan->isYearly() ? 12 : 1),
                ]
            );
        }

        return ['status' => 'created', 'email' => $email];
    }

    private function handleSubscriptionDisable(array $data): array
    {
        $subscriptionCode = $data['subscription_code'] ?? '';

        $subscription = Subscription::where('paystack_subscription_code', $subscriptionCode)->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
        }

        return ['status' => 'cancelled'];
    }

    private function handleSubscriptionEnable(array $data): array
    {
        $subscriptionCode = $data['subscription_code'] ?? '';

        $subscription = Subscription::where('paystack_subscription_code', $subscriptionCode)->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'active',
                'cancelled_at' => null,
                'next_billing_date' => now()->addMonth(),
            ]);
        }

        return ['status' => 'reactivated'];
    }

    private function handlePaymentFailed(array $data): array
    {
        $email = $data['customer']['email'] ?? '';

        $subscription = Subscription::where('customer_email', $email)
            ->where('status', 'active')
            ->first();

        if ($subscription) {
            $subscription->update(['status' => 'past_due']);
        }

        return ['status' => 'payment_failed', 'email' => $email];
    }

    public function cancelSubscription(string $subscriptionCode): bool
    {
        try {
            $subscription = Subscription::where('paystack_subscription_code', $subscriptionCode)->first();

            if (!$subscription) {
                return false;
            }

            $emailToken = $subscription->paystack_email_token;

            $response = Http::withHeaders($this->headers())
                ->post($this->baseUrl() . "/subscription/disable", [
                    'code' => $subscriptionCode,
                    'token' => $emailToken,
                ]);

            if ($response->successful()) {
                $subscription->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Paystack cancel subscription error: ' . $e->getMessage());
            return false;
        }
    }

    public function getAuthorizationUrl(string $email, SubscriptionPlan $plan, ?string $reference = null): ?string
    {
        $data = $this->subscribe($email, $plan);

        return $data['authorization_url'] ?? null;
    }
}