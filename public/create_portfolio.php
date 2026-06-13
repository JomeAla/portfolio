<?php
$host = 'localhost';
$dbname = 'joala_portfolio';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

echo "=== Creating 3 Portfolio Projects ===\n\n";

// Project 1: JoAla E-commerce Store
$p1 = $pdo->prepare("INSERT INTO projects (title, slug, description, short_description, content, client, category, year, image, github_url, live_url, is_featured, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

$projects = [
    [
        'JoAla E-commerce Store',
        'joala-store',
        'Full-featured e-commerce platform with 17 digital products, email marketing automation, and lead generation system.',
        'Complete e-commerce solution for digital products in Nigeria',
        '## JoAla E-commerce Store

A full-featured Laravel-based e-commerce platform built for selling digital products in Nigeria.

### Features
- Product management (17 digital products)
- Email marketing automation
- Lead generation & nurturing sequences
- Payment integration (Paystack, Flutterwave)
- Customer management
- Order processing
- Download delivery system
- Affiliate tracking

### Tech Stack
- Laravel 10
- MySQL
- Tailwind CSS
- Mailgun
- Paystack/Flutterwave API

### What I Built
- Custom product management system
- Automated email sequences (43 sequences)
- Lead magnet delivery system
- Post-purchase upsell flow
- Admin dashboard

### Live Site
https://joala.com.ng',
        'JoAla Ventures',
        'E-commerce',
        '2024',
        '',
        'https://github.com/jomeala/joala-portfolio',
        'https://joala.com.ng',
        1
    ],
    [
        'Portfolio Website',
        'portfolio-website',
        'Professional portfolio website showcasing development services and projects.',
        'Custom portfolio with admin panel',
        '## Portfolio Website

A professional portfolio website for a Nigerian developer.

### Features
- Project showcase
- Service listing
- Contact forms
- Admin dashboard
- Blog integration
- SEO optimization

### Tech Stack
- Laravel
- MySQL
- Tailwind CSS

### Live Site
https://joala.com.ng/portfolio',
        'Personal',
        'Portfolio',
        '2024',
        '',
        'https://github.com/jomeala/portfolio',
        'https://joala.com.ng/portfolio',
        1
    ],
    [
        'Email Marketing Automation',
        'email-automation',
        'Complete email marketing system with automated sequences and lead nurturing.',
        'Automated email sequences for lead conversion',
        '## Email Marketing Automation System

Complete email marketing automation built with Laravel.

### Features
- 43 automated email sequences
- Lead magnet delivery
- Post-purchase follow-ups
- Cart abandonment sequences
- Re-engagement campaigns
- Upsell/cross-sell automation

### Sequences Created
- 17 lead magnet nurturing sequences
- 21 post-purchase sequences
- Cart abandonment flow
- Re-engagement flow

### Tech Stack
- Laravel
- MySQL
- Mailgun API
- Custom email queue system

### Results
- Automated lead nurturing
- Increased conversions
- 43 total sequences',
        'JoAla Ventures',
        'Automation',
        '2024',
        '',
        '',
        '',
        1
    ]
];

foreach ($projects as $p) {
    $p1->execute($p);
    echo "Created: {$p[0]}\n";
}

echo "\n=== DONE! 3 Projects Created ===\n";