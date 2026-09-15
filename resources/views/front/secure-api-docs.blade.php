@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-12">
    <h1 class="text-3xl font-bold text-slate-800 mb-2">WP Secure API Gateway — Documentation</h1>
    <p class="text-slate-500 mb-10">Everything you need to connect AI agents, n8n and Make.com to your WordPress site. Brought to you by <strong>JoAla Ventures</strong>.</p>

    <div class="space-y-8">
        <section class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-slate-800 mb-3">Quick start</h2>
            <ol class="list-decimal list-inside space-y-2 text-slate-700">
                <li>Install the plugin from WordPress.org and add a JWT secret to <code>wp-config.php</code>.</li>
                <li>Create a service account under <strong>Secure API → Service Accounts</strong> and copy its key/secret.</li>
                <li>Exchange them for a token: <code>POST /wp-json/secure-api/v1/service-token</code>.</li>
                <li>Call protected endpoints with <code>Authorization: Bearer &lt;token&gt;</code>.</li>
            </ol>
        </section>

        <section class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-slate-800 mb-3">Endpoints</h2>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr><th class="text-left px-3 py-2">Endpoint</th><th class="text-left px-3 py-2">Purpose</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr><td class="px-3 py-2"><code>/token</code></td><td class="px-3 py-2">User login → access + refresh tokens</td></tr>
                    <tr><td class="px-3 py-2"><code>/service-token</code></td><td class="px-3 py-2">Service-account login</td></tr>
                    <tr><td class="px-3 py-2"><code>/refresh</code></td><td class="px-3 py-2">Rotate a refresh token</td></tr>
                    <tr><td class="px-3 py-2"><code>/revoke</code></td><td class="px-3 py-2">Revoke tokens</td></tr>
                    <tr><td class="px-3 py-2"><code>/generate</code></td><td class="px-3 py-2">Receive a payload → forward it to your automation webhook</td></tr>
                    <tr><td class="px-3 py-2"><code>/proxy</code></td><td class="px-3 py-2">Send any http(s) request out from WordPress (Full plans)</td></tr>
                    <tr><td class="px-3 py-2"><code>/custom/&lt;route&gt;</code></td><td class="px-3 py-2">Your own endpoints via <code>SecureApi\Gateway::add_route()</code></td></tr>
                    <tr><td class="px-3 py-2"><code>/license/activate</code></td><td class="px-3 py-2">Activate a license key</td></tr>
                </tbody>
            </table>
        </section>

        <section class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-slate-800 mb-3">Plans (₦)</h2>
            <p class="text-slate-700">Free ₦0 · <strong>Pro ₦15,000/mo</strong> · <strong>Enterprise ₦50,000/mo</strong> · <strong>Agency ₦40,000/mo</strong> (10 seats) · <strong>Lifetime ₦75,000</strong> one-time. Licenses are issued here after payment and delivered by email.</p>
        </section>

        <section class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-slate-800 mb-3">Activation</h2>
            <p class="text-slate-700">After purchase you receive a license key. Open <strong>Secure API → Licenses</strong> in your WordPress admin, paste the key and activate. The key is validated against this site (6-hour cache, 7-day grace).</p>
        </section>

        <section class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-slate-800 mb-3">Request signatures (optional)</h2>
            <p class="text-slate-700">Enable HMAC signing under <strong>Settings → API Security</strong>. Sign each request as <code>HMAC-SHA256("{timestamp}.{METHOD}.{route}.{sha256(body)}")</code> and send <code>X-SecureApi-Timestamp</code> + <code>X-SecureApi-Signature</code> headers (±300 s window).</p>
        </section>

        <section class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-slate-800 mb-3">Troubleshooting &amp; support</h2>
            <ul class="list-disc list-inside space-y-2 text-slate-700">
                <li>401 — your server must forward the <code>Authorization</code> header to PHP.</li>
                <li>429 — respect the <code>Retry-After</code> header; quotas reset monthly.</li>
                <li>502 — the upstream webhook/target did not respond.</li>
                <li>Need help? Contact <a class="text-blue-700 hover:underline" href="/contact">JoAla Ventures support</a>.</li>
            </ul>
        </section>

        <p class="text-slate-500">
            <a class="text-blue-700 hover:underline" href="/store">Buy a plan →</a>
            &nbsp;·&nbsp;
            <a class="text-blue-700 hover:underline" href="/affiliate/register">Become an affiliate →</a>
        </p>
    </div>
</div>
@endsection
