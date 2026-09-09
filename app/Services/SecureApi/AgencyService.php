<?php

namespace App\Services\SecureApi;

use App\Models\SecureApiAgencyClient;
use App\Models\SecureApiLicense;

/**
 * Agency licenses with client seat allocation.
 * An agency license is a license row (type=agency, max_activations=seats);
 * each client is a license row (type=client) linked via agency_clients.
 */
class AgencyService
{
    public function __construct(private LicenseService $licenses)
    {
    }

    public function createAgency(string $email, string $plan = 'pro', int $seats = 10): array
    {
        $seats = max(1, min(500, $seats));

        $result = $this->licenses->issue($email, $plan, 'agency', 31, $seats);

        return array_merge($result, ['seats' => $seats]);
    }

    public function createClients(int $parentLicenseId, int $count = 1, string $plan = 'pro'): array
    {
        $parent = SecureApiLicense::find($parentLicenseId);

        if (!$parent || !in_array($parent->type, ['agency', 'enterprise'], true)) {
            return ['error' => 'parent_not_agency'];
        }

        $count = max(1, min(100, $count));
        $ids = [];

        for ($i = 0; $i < $count; $i++) {
            $issued = $this->licenses->issue('', $plan, 'client', 31, 1);

            SecureApiAgencyClient::create([
                'license_id'        => $issued['id'],
                'parent_license_id' => $parentLicenseId,
                'email'             => '',
                'status'            => 'active',
            ]);

            $ids[] = $issued['id'];
        }

        return ['ids' => $ids];
    }

    public function setClientEmail(int $licenseId, string $email): bool
    {
        $email = (string) filter_var($email, FILTER_VALIDATE_EMAIL);
        if ($email === '') {
            return false;
        }

        SecureApiLicense::where('id', $licenseId)
            ->update(['meta->email' => $email, 'meta->subject' => $email]);

        SecureApiAgencyClient::where('license_id', $licenseId)
            ->update(['email' => $email]);

        return true;
    }

    public function revokeClient(int $licenseId): bool
    {
        SecureApiAgencyClient::where('license_id', $licenseId)
            ->update(['status' => 'revoked']);

        return $this->licenses->setStatus($licenseId, 'revoked');
    }

    public function seatsUsed(int $parentLicenseId): int
    {
        return SecureApiAgencyClient::where('parent_license_id', $parentLicenseId)
            ->where('status', '!=', 'revoked')
            ->count();
    }

    public function listClients(int $parentLicenseId, int $limit = 100): array
    {
        return SecureApiAgencyClient::with('license')
            ->where('parent_license_id', $parentLicenseId)
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->all();
    }

    public function agencyLicenses()
    {
        return SecureApiLicense::where('type', 'agency')->orderByDesc('id')->get();
    }
}
