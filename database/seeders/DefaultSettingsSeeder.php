<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class DefaultSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'site_name' => 'JoAla Portfolio',
            'site_description' => 'Professional developer specializing in custom web and mobile applications.',
            'primary_color' => '#0f172a',
            'accent_color' => '#3b82f6',
            'phone' => '+2349065257784',
            'whatsapp' => '+2349065257784',
            'contact_email' => 'jomealawuru@hotmail.com',
            'twitter' => '@jomswoks',
            'github_username' => 'JomeAla',
            'paystack_test_public_key' => '',
            'paystack_test_secret_key' => '',
            'paystack_live_public_key' => '',
            'paystack_live_secret_key' => '',
            'payment_mode' => 'test',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
