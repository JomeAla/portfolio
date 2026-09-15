<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecureApiAgencyClient;
use App\Models\SecureApiLicense;
use App\Models\SecureApiPlan;
use App\Models\SecureApiWebhookEvent;
use App\Services\SecureApi\AgencyService;
use App\Services\SecureApi\LicenseService;
use Illuminate\Http\Request;

/**
 * Secure API Gateway admin section (joala.com.ng issuer dashboard):
 * licenses, plans, agency clients and the webhook ledger.
 */
class SecureApiAdminController extends Controller
{
    // ---------------- Licenses ----------------

    public function licenses(LicenseService $licenses)
    {
        $items = SecureApiLicense::orderByDesc('id')->paginate(30);

        return view('admin.secure-api.licenses', compact('items', 'licenses'));
    }

    public function createLicense(Request $request, LicenseService $licenses)
    {
        $data = $request->validate([
            'email'           => 'required|email',
            'plan'            => 'required|in:pro,enterprise,lifetime,agency',
            'type'            => 'required|in:subscription,lifetime,agency,enterprise',
            'days'            => 'nullable|integer|min:0|max:3650',
            'max_activations' => 'nullable|integer|min:0|max:500',
            'domain'          => 'nullable|string|max:255',
        ]);

        $issued = $licenses->issue(
            $data['email'],
            $data['plan'],
            $data['type'],
            (int) ($data['days'] ?? 365),
            (int) ($data['max_activations'] ?? 1),
            $data['domain'] !== '' ? $data['domain'] : null
        );

        return back()->with('issued_key', $issued['key']);
    }

    public function licenseAction(Request $request, int $id, LicenseService $licenses)
    {
        $action = $request->input('action', '');
        $map = [
            'suspend'   => 'suspended',
            'unsuspend' => 'active',
            'revoke'    => 'revoked',
        ];

        if (isset($map[$action])) {
            $licenses->setStatus($id, $map[$action]);
        } elseif ($action === 'delete') {
            SecureApiLicense::where('id', $id)->delete();
            SecureApiAgencyClient::where('license_id', $id)->delete();
        }

        return back()->with('success', 'License updated.');
    }

    // ---------------- Plans ----------------

    public function plans()
    {
        $plans = SecureApiPlan::orderBy('price_monthly')->orderBy('id')->get();

        return view('admin.secure-api.plans', compact('plans'));
    }

    public function updatePlan(Request $request, int $id)
    {
        $plan = SecureApiPlan::findOrFail($id);

        $plan->update($request->validate([
            'name'             => 'required|string|max:255',
            'price_monthly'    => 'required|numeric|min:0',
            'quota_monthly'    => 'required|integer|min:-1',
            'rate_rpm'         => 'required|integer|min:1',
            'rpm_override'     => 'nullable|integer|min:1',
            'service_accounts' => 'required|integer|min:-1',
            'trial_days'       => 'required|integer|min:0',
            'grace_days'       => 'required|integer|min:0',
        ]));

        return back()->with('success', 'Plan updated.');
    }

    // ---------------- Agency ----------------

    public function agency(AgencyService $agency)
    {
        $agencies = $agency->agencyLicenses();

        return view('admin.secure-api.agency', compact('agencies'));
    }

    public function createAgency(Request $request, AgencyService $agency)
    {
        $data = $request->validate([
            'email'  => 'required|email',
            'plan'   => 'required|in:pro,enterprise',
            'seats'  => 'required|integer|min:1|max:500',
            'clients'=> 'nullable|integer|min:0|max:100',
        ]);

        $result = $agency->createAgency($data['email'], $data['plan'], (int) $data['seats']);

        $clientIds = [];
        if ((int) ($data['clients'] ?? 0) > 0) {
            $built = $agency->createClients($result['id'], (int) $data['clients'], $data['plan']);
            $clientIds = $built['ids'] ?? [];
        }

        session()->flash('issued_key', $result['key']);
        session()->flash('agency_id', $result['id']);
        session()->flash('client_ids', $clientIds);

        return back()->with('success', 'Agency license created.');
    }

    public function agencyAction(Request $request, AgencyService $agency)
    {
        $data = $request->validate([
            'action'     => 'required|in:assign,revoke',
            'license_id' => 'required|integer',
            'email'      => 'nullable|email',
        ]);

        if ($data['action'] === 'assign') {
            $agency->setClientEmail((int) $data['license_id'], (string) $data['email']);
        } else {
            $agency->revokeClient((int) $data['license_id']);
        }

        return back()->with('success', 'Client updated.');
    }

    // ---------------- Webhook ledger ----------------

    public function events()
    {
        $events = SecureApiWebhookEvent::orderByDesc('id')->paginate(30);

        return view('admin.secure-api.events', compact('events'));
    }
}
