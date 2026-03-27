<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'title' => 'Web Development',
                'slug' => 'web-development',
                'description' => 'Custom web applications built with modern technologies like Laravel, React, and Vue.',
                'icon' => 'fas fa-laptop-code',
                'features' => 'Custom Web Apps,E-commerce Solutions,CMS Development',
                'pricing' => 'Starting from ₦50,000',
                'is_active' => true,
            ],
            [
                'title' => 'Mobile Development',
                'slug' => 'mobile-development',
                'description' => 'Native and cross-platform mobile applications for iOS and Android.',
                'icon' => 'fas fa-mobile-alt',
                'features' => 'iOS Apps,Android Apps,Cross-platform',
                'pricing' => 'Starting from ₦100,000',
                'is_active' => true,
            ],
            [
                'title' => 'UI/UX Design',
                'slug' => 'ui-ux-design',
                'description' => 'User-centered design that creates engaging and intuitive experiences.',
                'icon' => 'fas fa-paint-brush',
                'features' => 'Wireframes,Prototypes,UI Design',
                'pricing' => 'Starting from ₦30,000',
                'is_active' => true,
            ],
            [
                'title' => 'API Development',
                'slug' => 'api-development',
                'description' => 'RESTful APIs and third-party integrations for seamless connectivity.',
                'icon' => 'fas fa-plug',
                'features' => 'RESTful APIs,Third-party Integrations,API Documentation',
                'pricing' => 'Starting from ₦40,000',
                'is_active' => true,
            ],
            [
                'title' => 'Business Automation',
                'slug' => 'business-automation',
                'description' => 'Automate repetitive tasks and streamline business processes to save time and reduce errors.',
                'icon' => 'fas fa-robot',
                'features' => 'Workflow Automation,Data Processing,Integration',
                'pricing' => 'Starting from ₦60,000',
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
