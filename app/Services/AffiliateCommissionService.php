<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AffiliateCommissionService
{
    public function processReferral(string $referralCode, string $customerEmail, ?int $orderId = null, ?float $orderAmount = null): ?array
    {
        try {
            $affiliate = DB::table('affiliates')
                ->where('referral_code', $referralCode)
                ->where('status', 'active')
                ->first();

            if (!$affiliate) {
                return null;
            }

            if ($affiliate->email === $customerEmail) {
                Log::info("AffiliateCommission: Self-referral blocked for {$customerEmail}");
                return null;
            }

            $existingUse = DB::table('referral_uses')
                ->where('referred_email', $customerEmail)
                ->where('referral_code', $referralCode)
                ->first();

            if ($existingUse) {
                Log::info("AffiliateCommission: Duplicate referral blocked for {$customerEmail}");
                return null;
            }

            $commissionRate = $affiliate->commission_rate ?? 20;
            $creditEarned = 0;

            if ($orderAmount && $orderAmount > 0) {
                $commission = ($orderAmount * $commissionRate) / 100;

                DB::table('affiliate_commissions')->insert([
                    'affiliate_id' => $affiliate->id,
                    'order_id' => $orderId,
                    'amount' => $commission,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('affiliates')
                    ->where('id', $affiliate->id)
                    ->increment('total_earned', $commission);

                DB::table('affiliates')
                    ->where('id', $affiliate->id)
                    ->increment('total_clicks');

                $creditEarned = $commission;
            } else {
                DB::table('affiliates')
                    ->where('id', $affiliate->id)
                    ->increment('total_clicks');
            }

            DB::table('referral_uses')->insert([
                'referral_code' => $referralCode,
                'referred_email' => $customerEmail,
                'referred_name' => null,
                'order_id' => $orderId,
                'order_amount' => $orderAmount ?? 0,
                'credit_earned' => $creditEarned,
                'status' => $orderAmount ? 'completed' : 'registered',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->updateReferralStats($referralCode);

            Log::info("AffiliateCommission: Processed referral for {$customerEmail} via {$referralCode}");

            return [
                'affiliate_id' => $affiliate->id,
                'commission' => $creditEarned,
                'rate' => $commissionRate,
            ];
        } catch (\Exception $e) {
            Log::error("AffiliateCommission: Error processing referral: " . $e->getMessage());
            return null;
        }
    }

    public function approveCommissions(int $daysThreshold = 7): int
    {
        $cutoff = now()->subDays($daysThreshold);

        $count = DB::table('affiliate_commissions')
            ->where('status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->update(['status' => 'approved', 'updated_at' => now()]);

        Log::info("AffiliateCommission: Approved {$count} commissions");
        return $count;
    }

    public function processPayout(int $affiliateId, float $amount, string $method = 'bank_transfer', array $bankDetails = [], ?string $notes = null): ?array
    {
        $affiliate = DB::table('affiliates')->where('id', $affiliateId)->first();

        if (!$affiliate) {
            return null;
        }

        $availableBalance = $affiliate->total_earned - $affiliate->total_paid;

        if ($availableBalance < $amount) {
            return ['error' => 'Insufficient balance'];
        }

        $minPayout = $affiliate->min_payout ?? 5000;

        if ($amount < $minPayout) {
            return ['error' => "Minimum payout amount is ₦" . number_format($minPayout)];
        }

        $reference = 'PAY-' . strtoupper(uniqid());

        DB::table('affiliate_payouts')->insert([
            'affiliate_id' => $affiliateId,
            'amount' => $amount,
            'method' => $method,
            'status' => 'processing',
            'reference' => $reference,
            'bank_details' => json_encode($bankDetails),
            'notes' => $notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('affiliates')
            ->where('id', $affiliateId)
            ->increment('total_paid', $amount);

        DB::table('affiliate_commissions')
            ->where('affiliate_id', $affiliateId)
            ->where('status', 'approved')
            ->update(['status' => 'paid', 'paid_at' => now(), 'updated_at' => now()]);

        Log::info("AffiliateCommission: Payout of ₦{$amount} initiated for affiliate {$affiliateId}");

        return [
            'reference' => $reference,
            'amount' => $amount,
            'method' => $method,
        ];
    }

    public function completePayout(int $payoutId): bool
    {
        return DB::table('affiliate_payouts')
            ->where('id', $payoutId)
            ->where('status', 'processing')
            ->update([
                'status' => 'completed',
                'paid_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function rejectPayout(int $payoutId, string $reason = ''): bool
    {
        $payout = DB::table('affiliate_payouts')->where('id', $payoutId)->first();

        if (!$payout) return false;

        DB::table('affiliate_payouts')
            ->where('id', $payoutId)
            ->update(['status' => 'rejected', 'notes' => $reason, 'updated_at' => now()]);

        DB::table('affiliates')
            ->where('id', $payout->affiliate_id)
            ->decrement('total_paid', $payout->amount);

        return true;
    }

    public function getAffiliateStats(int $affiliateId): array
    {
        $affiliate = DB::table('affiliates')->where('id', $affiliateId)->first();

        if (!$affiliate) return [];

        $totalCommissions = DB::table('affiliate_commissions')
            ->where('affiliate_id', $affiliateId)
            ->sum('amount');

        $pendingCommissions = DB::table('affiliate_commissions')
            ->where('affiliate_id', $affiliateId)
            ->where('status', 'pending')
            ->sum('amount');

        $approvedCommissions = DB::table('affiliate_commissions')
            ->where('affiliate_id', $affiliateId)
            ->where('status', 'approved')
            ->sum('amount');

        $paidCommissions = DB::table('affiliate_commissions')
            ->where('affiliate_id', $affiliateId)
            ->where('status', 'paid')
            ->sum('amount');

        $totalConversions = DB::table('affiliate_commissions')
            ->where('affiliate_id', $affiliateId)
            ->count();

        $payouts = DB::table('affiliate_payouts')
            ->where('affiliate_id', $affiliateId)
            ->orderBy('created_at', 'desc')
            ->get();

        $recentCommissions = DB::table('affiliate_commissions')
            ->where('affiliate_id', $affiliateId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return [
            'total_earned' => $totalCommissions,
            'pending' => $pendingCommissions,
            'approved' => $approvedCommissions,
            'paid' => $paidCommissions,
            'available_balance' => $affiliate->total_earned - $affiliate->total_paid,
            'total_conversions' => $totalConversions,
            'total_clicks' => $affiliate->total_clicks ?? 0,
            'conversion_rate' => $affiliate->total_clicks > 0
                ? round(($totalConversions / $affiliate->total_clicks) * 100, 1)
                : 0,
            'payouts' => $payouts,
            'recent_commissions' => $recentCommissions,
        ];
    }

    private function updateReferralStats(string $referralCode): void
    {
        DB::table('customer_referrals')
            ->where('referral_code', $referralCode)
            ->increment('total_referrals');

        $referral = DB::table('customer_referrals')
            ->where('referral_code', $referralCode)
            ->first();

        if ($referral) {
            $totalCredits = DB::table('referral_uses')
                ->where('referral_code', $referralCode)
                ->where('status', 'completed')
                ->sum('credit_earned');

            DB::table('customer_referrals')
                ->where('id', $referral->id)
                ->update(['total_credits' => $totalCredits]);
        }
    }
}