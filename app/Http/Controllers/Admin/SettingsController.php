<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    public function updateGeneral(Request $request)
    {
        $fields = [
            'site_name', 'site_description', 'contact_email', 
            'phone', 'whatsapp', 'address', 'hero_title', 
            'hero_subtitle', 'cta_text', 'cta_link'
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                Setting::set($field, $request->$field);
            }
        }

        return back()->with('success', 'General settings updated.');
    }

    public function updateAppearance(Request $request)
    {
        $fields = ['primary_color', 'accent_color', 'font_heading', 'font_body'];
        
        foreach ($fields as $field) {
            if ($request->has($field)) {
                Setting::set($field, $request->$field);
            }
        }

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo')->store('logos', 'public');
            Setting::set('logo', $logo);
        }

        if ($request->hasFile('favicon')) {
            $favicon = $request->file('favicon')->store('logos', 'public');
            Setting::set('favicon', $favicon);
        }

        if ($request->hasFile('about_photo')) {
            $aboutPhoto = $request->file('about_photo')->store('about', 'public');
            Setting::set('about_photo', $aboutPhoto);
        }

        return back()->with('success', 'Appearance settings updated.');
    }

    public function updatePayment(Request $request)
    {
        $request->validate([
            'paystack_public_key' => 'nullable|string',
            'paystack_secret_key' => 'nullable|string',
            'paystack_test_mode' => 'boolean',
        ]);

        Setting::set('paystack_public_key', $request->paystack_public_key);
        Setting::set('paystack_secret_key', $request->paystack_secret_key);
        Setting::set('paystack_test_mode', $request->paystack_test_mode ? 'true' : 'false');

        return back()->with('success', 'Payment settings updated.');
    }

    public function updateGithub(Request $request)
    {
        $request->validate([
            'github_username' => 'nullable|string',
            'github_token' => 'nullable|string',
        ]);

        Setting::set('github_username', $request->github_username);
        Setting::set('github_token', $request->github_token);

        return back()->with('success', 'GitHub settings updated.');
    }

    public function updateEmail(Request $request)
    {
        $fields = [
            'mail_mailer', 'mail_host', 'mail_port', 
            'mail_username', 'mail_password', 'mail_encryption',
            'mail_from_address', 'mail_from_name'
        ];

        foreach ($fields as $field) {
            $value = $request->has($field) ? $request->$field : '';
            Setting::set($field, $value);
        }

        $this->updateEnvFile($request);

        return back()->with('success', 'Email settings updated.');
    }

    protected function updateEnvFile(Request $request)
    {
        $envPath = base_path('.env');
        $envContent = file_exists($envPath) ? file_get_contents($envPath) : '';

        $mappings = [
            'mail_mailer' => 'MAIL_MAILER',
            'mail_host' => 'MAIL_HOST',
            'mail_port' => 'MAIL_PORT',
            'mail_username' => 'MAIL_USERNAME',
            'mail_password' => 'MAIL_PASSWORD',
            'mail_encryption' => 'MAIL_ENCRYPTION',
            'mail_from_address' => 'MAIL_FROM_ADDRESS',
            'mail_from_name' => 'MAIL_FROM_NAME',
        ];

        foreach ($mappings as $settingKey => $envKey) {
            $value = $request->$settingKey ?? '';
            $value = $this->formatEnvValue($value);
            $pattern = '/^' . preg_quote($envKey, '/') . '=.*$/m';
            $envContent = preg_replace($pattern, "{$envKey}={$value}", $envContent);
        }

        file_put_contents($envPath, $envContent);
    }

    protected function formatEnvValue($value)
    {
        if (empty($value)) {
            return '';
        }
        if (preg_match('/[\s#=]/', $value) || strpos($value, '"') !== false) {
            return '"' . str_replace('"', '\\"', $value) . '"';
        }
        return $value;
    }
}