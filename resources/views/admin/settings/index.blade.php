@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Settings</h1>
    <p class="text-gray-600 mt-2">Manage your website configuration and preferences.</p>
</div>

<!-- Tabs -->
<div class="border-b border-gray-200 mb-6">
    <nav class="flex gap-8">
        <button onclick="switchTab('general')" class="pb-4 border-b-2 border-blue-600 text-blue-600 font-medium tab-btn" data-tab="general">
            General
        </button>
        <button onclick="switchTab('appearance')" class="pb-4 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium tab-btn" data-tab="appearance">
            Appearance
        </button>
        <button onclick="switchTab('payment')" class="pb-4 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium tab-btn" data-tab="payment">
            Payment (Paystack)
        </button>
        <button onclick="switchTab('github')" class="pb-4 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium tab-btn" data-tab="github">
            GitHub
        </button>
        <button onclick="switchTab('email')" class="pb-4 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium tab-btn" data-tab="email">
            Email (SMTP)
        </button>
    </nav>
</div>

<!-- General Settings -->
<div id="tab-general" class="tab-content">
    <form method="POST" action="/admin/settings/general" class="space-y-6">
        @csrf
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">General Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Site Name</label>
                    <input type="text" name="site_name" value="{{ $settings['site_name'] ?? '' }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                    <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input type="text" name="phone" value="{{ $settings['phone'] ?? '' }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">WhatsApp Number</label>
                    <input type="text" name="whatsapp" value="{{ $settings['whatsapp'] ?? '' }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                    <input type="text" name="address" value="{{ $settings['address'] ?? '' }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Site Description</label>
                    <textarea name="site_description" rows="3" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">{{ $settings['site_description'] ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Homepage Settings</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hero Title</label>
                    <input type="text" name="hero_title" value="{{ $settings['hero_title'] ?? '' }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hero Subtitle</label>
                    <textarea name="hero_subtitle" rows="2" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">{{ $settings['hero_subtitle'] ?? '' }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">CTA Button Text</label>
                    <input type="text" name="cta_text" value="{{ $settings['cta_text'] ?? '' }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">CTA Button Link</label>
                    <input type="text" name="cta_link" value="{{ $settings['cta_link'] ?? '' }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                </div>
            </div>
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
            Save General Settings
        </button>
    </form>
</div>

<!-- Appearance Settings -->
<div id="tab-appearance" class="tab-content hidden">
    <form method="POST" action="/admin/settings/appearance" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Branding</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Logo</label>
                    <input type="file" name="logo" accept="image/*" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    @if(isset($settings['logo']) && $settings['logo'])
                        <p class="mt-2 text-sm text-gray-500">Current: {{ $settings['logo'] }}</p>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Favicon</label>
                    <input type="file" name="favicon" accept="image/*" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">About Page Photo</label>
                    <input type="file" name="about_photo" accept="image/*" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    @if(isset($settings['about_photo']) && $settings['about_photo'])
                        <p class="mt-2 text-sm text-gray-500">Current: {{ $settings['about_photo'] }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Colors</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Primary Color</label>
                    <div class="flex gap-3">
                        <input type="color" name="primary_color" value="{{ $settings['primary_color'] ?? '#0f172a' }}" 
                            class="w-16 h-12 rounded-lg cursor-pointer border border-gray-300">
                        <input type="text" name="primary_color_text" value="{{ $settings['primary_color'] ?? '#0f172a' }}" 
                            class="flex-1 px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Accent Color</label>
                    <div class="flex gap-3">
                        <input type="color" name="accent_color" value="{{ $settings['accent_color'] ?? '#3b82f6' }}" 
                            class="w-16 h-12 rounded-lg cursor-pointer border border-gray-300">
                        <input type="text" name="accent_color_text" value="{{ $settings['accent_color'] ?? '#3b82f6' }}" 
                            class="flex-1 px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Typography</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Heading Font</label>
                    <select name="font_heading" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                        <option value="Geist" {{ ($settings['font_heading'] ?? '') == 'Geist' ? 'selected' : '' }}>Geist</option>
                        <option value="Satoshi" {{ ($settings['font_heading'] ?? '') == 'Satoshi' ? 'selected' : '' }}>Satoshi</option>
                        <option value="Cabinet Grotesk" {{ ($settings['font_heading'] ?? '') == 'Cabinet Grotesk' ? 'selected' : '' }}>Cabinet Grotesk</option>
                        <option value="Outfit" {{ ($settings['font_heading'] ?? '') == 'Outfit' ? 'selected' : '' }}>Outfit</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Body Font</label>
                    <select name="font_body" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                        <option value="Geist" {{ ($settings['font_body'] ?? '') == 'Geist' ? 'selected' : '' }}>Geist</option>
                        <option value="Satoshi" {{ ($settings['font_body'] ?? '') == 'Satoshi' ? 'selected' : '' }}>Satoshi</option>
                        <option value="Inter" {{ ($settings['font_body'] ?? '') == 'Inter' ? 'selected' : '' }}>Inter</option>
                        <option value="system-ui" {{ ($settings['font_body'] ?? '') == 'system-ui' ? 'selected' : '' }}>System UI</option>
                    </select>
                </div>
            </div>
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
            Save Appearance Settings
        </button>
    </form>
</div>

<!-- Payment Settings -->
<div id="tab-payment" class="tab-content hidden">
    <form method="POST" action="/admin/settings/payment" class="space-y-6">
        @csrf
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Paystack Configuration</h2>
            <p class="text-sm text-gray-500 mb-6">Configure your Paystack API keys. Use test keys during development and switch to live keys when ready.</p>
            
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Public Key</label>
                    <input type="text" name="paystack_public_key" value="{{ $settings['paystack_public_key'] ?? '' }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        placeholder="pk_test_...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Secret Key</label>
                    <input type="password" name="paystack_secret_key" value="{{ $settings['paystack_secret_key'] ?? '' }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        placeholder="sk_test_...">
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="paystack_test_mode" id="test_mode" value="1" 
                        {{ ($settings['paystack_test_mode'] ?? 'true') == 'true' ? 'checked' : '' }}
                        class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-200">
                    <label for="test_mode" class="text-sm font-medium text-gray-700">Enable Test Mode</label>
                </div>
            </div>
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
            Save Payment Settings
        </button>
    </form>
</div>

<!-- GitHub Settings -->
<div id="tab-github" class="tab-content hidden">
    <form method="POST" action="/admin/settings/github" class="space-y-6">
        @csrf
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">GitHub Integration</h2>
            <p class="text-sm text-gray-500 mb-6">Connect your GitHub account to display your repositories on your portfolio.</p>
            
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">GitHub Username</label>
                    <input type="text" name="github_username" value="{{ $settings['github_username'] ?? '' }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        placeholder="yourusername">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Personal Access Token</label>
                    <input type="password" name="github_token" value="{{ $settings['github_token'] ?? '' }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        placeholder="ghp_...">
                    <p class="mt-2 text-sm text-gray-500">Generate a token at GitHub Settings > Developer settings > Personal access tokens</p>
                </div>
            </div>
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
            Save GitHub Settings
        </button>
    </form>
</div>

<!-- Email/SMTP Settings -->
<div id="tab-email" class="tab-content hidden">
    <form method="POST" action="/admin/settings/email" class="space-y-6">
        @csrf
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">SMTP Email Configuration</h2>
            <p class="text-sm text-gray-500 mb-6">Configure your email settings to send support responses and product download links to customers.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mail Mailer</label>
                    <select name="mail_mailer" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                        <option value="smtp" {{ ($settings['mail_mailer'] ?? '') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                        <option value="sendmail" {{ ($settings['mail_mailer'] ?? '') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                        <option value="log" {{ ($settings['mail_mailer'] ?? '') == 'log' ? 'selected' : '' }}>Log (Development)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mail Host</label>
                    <input type="text" name="mail_host" value="{{ $settings['mail_host'] ?? '' }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        placeholder="smtp.mailprovider.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mail Port</label>
                    <input type="number" name="mail_port" value="{{ $settings['mail_port'] ?? '587' }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        placeholder="587">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mail Username</label>
                    <input type="text" name="mail_username" value="{{ $settings['mail_username'] ?? '' }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        placeholder="username">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mail Password</label>
                    <input type="password" name="mail_password" value="{{ $settings['mail_password'] ?? '' }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        placeholder="password">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mail Encryption</label>
                    <select name="mail_encryption" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                        <option value="tls" {{ ($settings['mail_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ ($settings['mail_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="" {{ ($settings['mail_encryption'] ?? '') == '' ? 'selected' : '' }}>None</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Address</label>
                    <input type="email" name="mail_from_address" value="{{ $settings['mail_from_address'] ?? 'support@joala.com.ng' }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        placeholder="support@joala.com.ng">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Name</label>
                    <input type="text" name="mail_from_name" value="{{ $settings['mail_from_name'] ?? 'JoAla Support' }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        placeholder="JoAla Support">
                </div>
            </div>
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
            Save Email Settings
        </button>
    </form>
</div>

<script>
function switchTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.getElementById('tab-' + tab).classList.remove('hidden');
    
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('border-blue-600', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    document.querySelector('[data-tab="' + tab + '"]').classList.remove('border-transparent', 'text-gray-500');
    document.querySelector('[data-tab="' + tab + '"]').classList.add('border-blue-600', 'text-blue-600');
}
</script>
@endsection