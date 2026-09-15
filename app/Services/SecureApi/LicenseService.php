<?php

namespace App\Services\SecureApi;

use App\Models\SecureApiLicense;
use App\Models\SecureApiPlan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Central license issuance + the public activation contract consumed by
 * the WP Secure API Gateway plugin (v2.0.0 client mode).
 *
 * Key format is identical to the plugin: XXXX-XXXX-XXXX-XXXX with a
 * checksum over the first 15 chars (32-char unambiguous alphabet).
 */
class LicenseService
{
    private const CHARSET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    // ----- key generation (plugin-compatible) -----

    public function generateKey(): string
    {
        $payload = '';
        for ($i = 0; $i < 15; $i++) {
            $payload .= self::CHARSET[random_int(0, 31)];
        }

        return implode('-', str_split($payload . self::checksum($payload), 4));
    }

    public function normalizeKey(string $key): string
    {
        return strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $key));
    }

    public function isValidFormat(string $key): bool
    {
        $normalized = $this->normalizeKey($key);

        if (strlen($normalized) !== 16) {
            return false;
        }

        return self::checksum(substr($normalized, 0, 15)) === $normalized[15];
    }

    protected static function checksum(string $payload): string
    {
        $sum = 0;
        foreach (str_split($payload) as $char) {
            $sum += strpos(self::CHARSET, $char) + 1;
        }

        return self::CHARSET[$sum % 32];
    }

    /**
     * HMAC hash of a normalized key (server-side secret: APP_KEY).
     */
    public function hashKey(string $key): string
    {
        return hash_hmac('sha256', $this->normalizeKey($key), (string) config('app.key'));
    }

    // ----- issuance -----

    /**
     * Issue a license (payment flow or manual admin creation).
     */
    public function issue(string $email, string $plan = 'pro', string $type = 'subscription', int $days = 365, int $maxActivations = 1, ?string $domain = null): array
    {
        $plan = in_array($plan, ['free', 'pro', 'enterprise', 'lifetime', 'agency'], true) ? $plan : 'pro';
        $type = in_array($type, ['subscription', 'lifetime', 'agency', 'enterprise', 'client'], true) ? $type : 'subscription';

        $key = $this->generateKey();

        $license = SecureApiLicense::create([
            'license_hash'    => $this->hashKey($key),
            'key_suffix'      => substr(str_replace('-', '', $key), -8),
            'type'            => $type,
            'plan'            => $plan,
            'status'          => 'active',
            'domain'          => $domain,
            'expires_at'      => $type === 'subscription' && $days > 0 ? now()->addDays($days) : null,
            'max_activations' => $maxActivations,
            'meta'            => ['subject' => $email, 'email' => $email],
        ]);

        return ['id' => $license->id, 'key' => $key, 'plan' => $plan, 'type' => $type];
    }

    /**
     * Reuse (reactivate) an existing license for the same email instead of
     * issuing duplicates on repeated payment events.
     */
    public function issueOrReuse(string $email, string $plan = 'pro', int $days = 365): array
    {
        $existing = SecureApiLicense::where('meta->email', $email)
            ->whereIn('status', ['active', 'suspended', 'expired'])
            ->where('type', '!=', 'client')
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            $existing->update([
                'plan'       => $plan,
                'status'     => 'active',
                'expires_at' => $existing->type === 'subscription' ? now()->addDays($days) : $existing->expires_at,
            ]);

            return ['id' => $existing->id, 'key' => null, 'reused' => true, 'plan' => $plan];
        }

        $issued = $this->issue($email, $plan, 'subscription', $days);

        return ['id' => $issued['id'], 'key' => $issued['key'], 'reused' => false, 'plan' => $plan];
    }

    /**
     * Public activation contract for the WP plugin.
     * Request:  {license_key, site_url}
     * Response: {valid, plan, expires_at, max_activations, type, message}
     */
    public function activate(string $rawKey, ?string $siteUrl = null): array
    {
        $key = $this->normalizeKey($rawKey);

        if (!$this->isValidFormat($key)) {
            return ['valid' => false, 'message' => 'invalid_format'];
        }

        $license = SecureApiLicense::where('license_hash', $this->hashKey($key))->first();

        if (!$license) {
            return ['valid' => false, 'message' => 'not_found'];
        }

        if ($license->status === 'revoked') {
            return ['valid' => false, 'message' => 'revoked'];
        }

        if ($license->status === 'suspended') {
            return ['valid' => false, 'message' => 'suspended'];
        }

        if ($license->expires_at && $license->expires_at->isPast()) {
            return ['valid' => false, 'message' => 'expired'];
        }

        $host = $siteUrl ? strtolower((string) parse_url($siteUrl, PHP_URL_HOST)) : null;

        // Domain binding: first activation binds; mismatches reject.
        if ($license->domain) {
            if (!$host || strtolower($license->domain) !== $host) {
                return ['valid' => false, 'message' => 'domain_mismatch'];
            }
        } elseif ($host) {
            $license->update(['domain' => $host]);
        }

        // Activation room: max_activations (0 = unlimited) or same-domain re-activation.
        $sameDomain = $host && strtolower((string) $license->domain) === $host;
        $room = (int) $license->max_activations === 0 || $sameDomain || (int) $license->activations_count < (int) $license->max_activations;

        if (!$room) {
            return ['valid' => false, 'message' => 'activation_limit'];
        }

        if ($host && !$sameDomain) {
            $license->increment('activations_count');
        }

        return [
            'valid'            => true,
            'plan'             => $license->plan,
            'expires_at'       => $license->expires_at?->toDateTimeString() ?? '',
            'max_activations'  => (int) $license->max_activations,
            'type'             => $license->type,
            'rpm'              => $this->effectiveRpm($license->plan),
            'message'          => 'ok',
        ];
    }

    /**
     * Effective RPM for a plan: admin override wins over the catalog value.
     */
    public function effectiveRpm(string $planSlug): int
    {
        $plan = SecureApiPlan::where('slug', $planSlug)->first();

        if (!$plan) {
            return 600;
        }

        return $plan->rpm_override !== null && (int) $plan->rpm_override > 0
            ? (int) $plan->rpm_override
            : (int) $plan->rate_rpm;
    }

    public function setStatus(int $id, string $status): bool
    {
        $license = SecureApiLicense::find($id);

        if (!$license || !in_array($status, ['active', 'suspended', 'expired', 'revoked'], true)) {
            return false;
        }

        $license->update(['status' => $status]);

        return true;
    }

    /**
     * Suspend the active license(s) for an email (subscription cancelled).
     */
    public function suspendForEmail(string $email): int
    {
        return SecureApiLicense::where('meta->email', $email)
            ->whereIn('status', ['active', 'expired'])
            ->update(['status' => 'suspended']);
    }

    /**
     * Renew: extend the subscription window (recurring payment).
     */
    public function renewForEmail(string $email, string $plan, int $days = 365): void
    {
        SecureApiLicense::where('meta->email', $email)
            ->whereIn('status', ['active', 'suspended', 'expired'])
            ->where('type', 'subscription')
            ->update([
                'plan'       => $plan,
                'status'     => 'active',
                'expires_at' => now()->addDays($days),
            ]);
    }

    /**
     * Expiry milestones (7/3/1 days) for the daily scheduler command.
     */
    public function expiringSoon(): array
    {
        return SecureApiLicense::where('status', 'active')
            ->where('type', 'subscription')
            ->whereNotNull('expires_at')
            ->get()
            ->filter(function (SecureApiLicense $license) {
                $days = (int) ceil(now()->diffInSeconds($license->expires_at, false) / 86400);

                return in_array($days, [1, 3, 7], true);
            })
            ->values()
            ->all();
    }

    /**
     * Plan name for display.
     */
    public function planName(string $slug): string
    {
        $plan = SecureApiPlan::where('slug', $slug)->first();

        return $plan?->name ?? Str::ucfirst($slug);
    }
}
