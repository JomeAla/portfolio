<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PagesSeeder extends Seeder
{
    public function run()
    {
        $pages = [
            [
                'slug' => 'home',
                'title' => 'Home',
                'content' => json_encode([
                    'hero' => [
                        'title' => 'Custom Applications That Drive Results',
                        'subtitle' => 'I build scalable web and mobile applications using AI-powered development workflows.',
                    ],
                    'cta' => [
                        'text' => 'Start Your Project',
                        'link' => '/brief',
                    ],
                ]),
                'meta_title' => 'Home',
                'meta_description' => 'Custom application development services',
            ],
            [
                'slug' => 'about',
                'title' => 'About',
                'content' => json_encode([
                    'bio' => 'Passionate developer with expertise in building custom applications.',
                    'skills' => ['Laravel', 'React', 'Node.js', 'Mobile Apps', 'API Development'],
                ]),
                'meta_title' => 'About Me',
                'meta_description' => 'Learn more about my background and skills',
            ],
            [
                'slug' => 'contact',
                'title' => 'Contact',
                'content' => json_encode([
                    'email' => 'jomealawuru@hotmail.com',
                    'phone' => '+2349065257784',
                    'whatsapp' => '+2349065257784',
                ]),
                'meta_title' => 'Contact',
                'meta_description' => 'Get in touch for your project needs',
            ],
            [
                'slug' => 'services',
                'title' => 'Services',
                'content' => json_encode([
                    'description' => 'Professional development services tailored to your needs.',
                ]),
                'meta_title' => 'Services',
                'meta_description' => 'Web development, mobile apps, and more',
            ],
        ];

        foreach ($pages as $page) {
            Page::create($page);
        }
    }
}
