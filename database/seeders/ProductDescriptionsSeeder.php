<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductDescriptionsSeeder extends Seeder
{
    public function run(): void
    {
        $descriptions = [
            'wordpress-starter-kit' => [
                'description' => 'Everything you need for a professional WordPress site - from setup to launch.',
                'full_description' => '<h2 class="text-2xl font-bold text-slate-900 mb-4">Build Professional WordPress Sites Faster Than Ever</h2>
<p class="text-lg text-slate-600 mb-6">Stop struggling with WordPress setup. This complete starter kit gives you everything you need to launch stunning websites in hours, not days.</p>

<h3 class="text-xl font-semibold text-slate-800 mb-3">What\'s Included</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>15+ Premium Page Templates</strong> - Homepage, About, Services, Portfolio, Blog, Contact, and more</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Custom Theme Framework</strong> - Clean, lightweight code that\'s easy to customize</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Plugin Integration Guide</strong> - Recommended plugins for SEO, security, and performance</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Speed Optimization Checklist</strong> - Make your site load under 2 seconds</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Client Handoff Documentation</strong> - Professional docs to hand over to clients</span></li>
</ul>

<h3 class="text-xl font-semibold text-slate-800 mb-3">Perfect For</h3>
<ul class="space-y-2 mb-6">
<li>Freelancers building multiple client sites</li>
<li>Agencies needing fast turnaround times</li>
<li>Business owners managing their own websites</li>
<li>Developers creating MVP sites quickly</li>
</ul>

<div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg mb-6">
<p class="text-blue-800 font-medium"><i class="fas fa-lightbulb mr-2"></i>Pro Tip: This kit works perfectly with popular page builders like Elementor, Divi, and WPBakery.</p>
</div>'
            ],
            'e-commerce-starter-kit' => [
                'description' => 'Complete Laravel e-commerce template with products, cart, checkout, and order management.',
                'full_description' => '<h2 class="text-2xl font-bold text-slate-900 mb-4">Launch Your Online Store in Days, Not Months</h2>
<p class="text-lg text-slate-600 mb-6">A fully-featured Laravel e-commerce platform that handles everything from product management to payment processing. Built with modern best practices and ready to customize.</p>

<h3 class="text-xl font-semibold text-slate-800 mb-3">Core Features</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Product Management</strong> - Unlimited products, categories, tags, and variations</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Shopping Cart</strong> - Persistent cart with wishlist functionality</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Paystack Integration</strong> - Nigerian payment gateway ready out of the box</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Order Tracking</strong> - Complete order management for admin and customers</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Email Notifications</strong> - Automated emails for orders, shipping, and more</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Coupon System</strong> - Percentage and fixed amount discounts</span></li>
</ul>

<h3 class="text-xl font-semibold text-slate-800 mb-3">Technical Stack</h3>
<p class="text-slate-600 mb-4">Built with Laravel 10, Tailwind CSS, and MySQL. Mobile-responsive by default.</p>

<div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg">
<p class="text-emerald-800 font-medium"><i class="fas fa-rocket mr-2"></i>Get your online store running today with minimal configuration.</p>
</div>'
            ],
            'real-estate-property-kit' => [
                'description' => 'Complete system for property agents and real estate agencies to list and manage properties.',
                'full_description' => '<h2 class="text-2xl font-bold text-slate-900 mb-4">Transform Your Real Estate Business with a Powerful Property Platform</h2>
<p class="text-lg text-slate-600 mb-6">Give your agency a professional online presence. This complete kit handles property listings, agent profiles, enquiries, and more - all in one beautiful package.</p>

<h3 class="text-xl font-semibold text-slate-800 mb-3">Features That Close Deals</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Beautiful Property Listings</strong> - Grid and map views with filters</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Image Galleries</strong> - Multiple photos per property with lightbox</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Agent Profiles</strong> - Showcase your team with contact info</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Enquiry Forms</strong> - Capture leads from interested buyers</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Property Search</strong> - Filter by location, price, type, bedrooms</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Virtual Tours</strong> - Embed video walkthroughs</span></li>
</ul>

<div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg">
<p class="text-amber-800 font-medium"><i class="fas fa-home mr-2"></i>Perfect for agencies in Nigeria looking to go digital.</p>
</div>'
            ],
            'school-management-system' => [
                'description' => 'Complete software for schools to manage students, teachers, classes, and academic records.',
                'full_description' => '<h2 class="text-2xl font-bold text-slate-900 mb-4">Streamline Your School\'s Operations</h2>
<p class="text-lg text-slate-600 mb-6">From admissions to result processing, this comprehensive system handles every aspect of school management. Save hours of manual work and eliminate errors.</p>

<h3 class="text-xl font-semibold text-slate-800 mb-3">Complete Module Coverage</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Student Management</strong> - Admissions, profiles, attendance, medical records</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Staff Management</strong> - Teacher records, payroll, performance tracking</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Academic Management</strong> - Class scheduling, subjects, grading systems</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Result Processing</strong> - Automated grade calculation and report cards</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Fees Management</strong> - Tuition tracking, invoicing, payment history</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Parent Portal</strong> - View grades, attendance, and announcements</span></li>
</ul>

<div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
<p class="text-blue-800 font-medium"><i class="fas fa-graduation-cap mr-2"></i>Designed for Nigerian curriculum and grading systems.</p>
</div>'
            ],
            'restaurant-pos-kit' => [
                'description' => 'Complete point-of-sale and ordering system designed specifically for restaurants and food businesses.',
                'full_description' => '<h2 class="text-2xl font-bold text-slate-900 mb-4">Run Your Restaurant Like a Pro</h2>
<p class="text-lg text-slate-600 mb-6">A complete POS system built for restaurants. Handle orders, manage tables, process payments, and track sales - all from one intuitive interface.</p>

<h3 class="text-xl font-semibold text-slate-800 mb-3">Everything You Need</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Order Management</strong> - Take orders by table, course, or item</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Table Reservations</strong> - Manage bookings and table availability</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Menu Management</strong> - Categories, modifiers, combo deals</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Kitchen Display</strong> - Orders sent directly to kitchen printers</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Split Bills</strong> - Divide by item, person, or payment method</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Sales Reports</strong> - Daily, weekly, monthly insights</span></li>
</ul>

<div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
<p class="text-red-800 font-medium"><i class="fas fa-utensils mr-2"></i>Works offline - never lose an order during WiFi downtime.</p>
</div>'
            ],
            'freelancer-toolkit' => [
                'description' => 'Complete guide and templates to help freelancers land high-paying clients consistently.',
                'full_description' => '<h2 class="text-2xl font-bold text-slate-900 mb-4">Stop Chasing Low-Paying Clients</h2>
<p class="text-lg text-slate-600 mb-6">This comprehensive toolkit gives you the exact system I used to go from $500/month to $5,000+ consistently. No fluff, just actionable strategies that work.</p>

<h3 class="text-xl font-semibold text-slate-800 mb-3">What You\'ll Learn</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Finding Premium Clients</strong> - Where to find clients who pay well and value quality</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Proposal Templates</strong> - 5 proven proposals that close deals</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Contract Templates</strong> - Protect yourself with professional contracts</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Pricing Strategies</strong> - How to charge what you\'re worth</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Communication Scripts</strong> - Handle scope creep, late payments, revisions</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Client Onboarding</strong> - Systems to make every project smooth</span></li>
</ul>

<h3 class="text-xl font-semibold text-slate-800 mb-3">Bonus Materials</h3>
<p class="text-slate-600 mb-4">Email scripts, invoice templates, project management checklists, and more.</p>

<div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded-r-lg">
<p class="text-purple-800 font-medium"><i class="fas fa-star mr-2"></i>Used by 500+ freelancers across Nigeria to triple their income.</p>
</div>'
            ],
            'instagram-growth-system' => [
                'description' => 'Proven system to grow your Instagram following organically with engaged, targeted followers.',
                'full_description' => '<h2 class="text-2xl font-bold text-slate-900 mb-4">Build an Instagram That Actually Converts</h2>
<p class="text-lg text-slate-600 mb-6">Forget bots and follow-for-follow. This system teaches you how to build an engaged audience of real people who actually care about your content.</p>

<h3 class="text-xl font-semibold text-slate-800 mb-3">The System</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Content Strategy</strong> - What to post and when for maximum engagement</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Hashtag Research</strong> - Find tags that bring real followers</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Story Formulas</strong> - 10 story templates that drive engagement</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>DM Strategies</strong> - Convert followers to customers via DM</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Analytics Guide</strong> - Track what works and double down</span></li>
</ul>

<div class="bg-pink-50 border-l-4 border-pink-500 p-4 rounded-r-lg">
<p class="text-pink-800 font-medium"><i class="fas fa-heart mr-2"></i>Average results: 500+ new followers per week organically.</p>
</div>'
            ],
            'nigerian-business-digital-kit' => [
                'description' => 'Essential digital tools and strategies for Nigerian businesses to establish a strong online presence.',
                'full_description' => '<h2 class="text-2xl font-bold text-slate-900 mb-4">Go Digital Without Getting Overwhelmed</h2>
<p class="text-lg text-slate-600 mb-6">A practical kit designed specifically for Nigerian businesses. No unnecessary features - just the tools that actually help you get customers online.</p>

<h3 class="text-xl font-semibold text-slate-800 mb-3">What\'s Inside</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Website Launch Checklist</strong> - 20-point guide to launching fast</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>WhatsApp Business Setup</strong> - Turn WhatsApp into a sales machine</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Google My Business Guide</strong> - Get found by local customers</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Social Media Calendar</strong> - 30 days of ready-to-post content</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Email Marketing Setup</strong> - Collect leads and nurture them</span></li>
</ul>

<div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg">
<p class="text-green-800 font-medium"><i class="fas fa-naira-sign mr-2"></i>Designed for businesses with small budgets but big ambitions.</p>
</div>'
            ],
            'church-organization-website-kit' => [
                'description' => 'Complete WordPress theme for churches and religious organizations with sermon management, event calendar, and donation features.',
                'full_description' => '<h2 class="text-2xl font-bold text-slate-900 mb-4">Connect Your Congregation Online</h2>
<p class="text-lg text-slate-600 mb-6">A beautiful, easy-to-use website kit built specifically for churches. Share sermons, promote events, accept tithes, and engage your community.</p>

<h3 class="text-xl font-semibold text-slate-800 mb-3">Ministry Features</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Sermon Library</strong> - Upload and organize sermons by series</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Event Calendar</strong> - Services, programs, special events</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Online Giving</strong> - Accept tithes and offerings via Paystack</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Ministry Teams</strong> - Showcase departments and leaders</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Service Times</strong> - Clear schedule with directions</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Blog/News</strong> - Share updates and devotionals</span></li>
</ul>

<div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
<p class="text-blue-800 font-medium"><i class="fas fa-praying-hands mr-2"></i>Help your congregation stay connected whether they\'re in church or at home.</p>
</div>'
            ],
            'website-audit-kit' => [
                'description' => '20-point website audit checklist to identify issues and opportunities for improvement.',
                'full_description' => '<h2 class="text-2xl font-bold text-slate-900 mb-4">Find What\'s Holding Your Website Back</h2>
<p class="text-lg text-slate-600 mb-6">A comprehensive 20-point audit that reveals exactly what\'s wrong with your website and how to fix it. No technical jargon - just clear, actionable steps.</p>

<h3 class="text-xl font-semibold text-slate-800 mb-3">Audit Categories</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Speed & Performance</strong> - Is your site loading fast enough?</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Mobile Experience</strong> - Does it work great on phones?</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>SEO Fundamentals</strong> - Are you findable on Google?</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Conversion Elements</strong> - Are visitors taking action?</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Security Check</strong> - Is your site safe and trusted?</span></li>
</ul>

<h3 class="text-xl font-semibold text-slate-800 mb-3">Includes</h3>
<p class="text-slate-600 mb-4">PDF checklist, video walkthrough of common issues, recommended tools, and a priority action plan.</p>

<div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg">
<p class="text-amber-800 font-medium"><i class="fas fa-search mr-2"></i>Perfect before redesigns or when traffic isn\'t converting.</p>
</div>'
            ],
            'email-sequence-templates-pack' => [
                'description' => '6 ready-to-use email sequences with 24 tested templates for maximum conversions.',
                'full_description' => '<h2 class="text-2xl font-bold text-slate-900 mb-4">Turn Subscribers into Buyers on Autopilot</h2>
<p class="text-lg text-slate-600 mb-6">Stop wondering what to email your list. This pack gives you 6 complete sequences covering onboarding, sales, re-engagement, and more - all ready to copy and send.</p>

<h3 class="text-xl font-semibold text-slate-800 mb-3">The 6 Sequences</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Welcome Sequence</strong> - 5 emails to introduce new subscribers to your brand</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Launch Sequence</strong> - 4 emails to promote product launches</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Abandoned Cart Recovery</strong> - 3 emails to recover lost sales</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Post-Purchase Thank You</strong> - 3 emails to delight new customers</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Re-engagement Campaign</strong> - 4 emails to win back inactive subscribers</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Weekly Newsletter Template</strong> - Ongoing content framework</span></li>
</ul>

<h3 class="text-xl font-semibold text-slate-800 mb-3">Each Template Includes</h3>
<p class="text-slate-600 mb-4">Subject line, preview text, email body, CTA placement, and timing recommendations.</p>

<div class="bg-violet-50 border-l-4 border-violet-500 p-4 rounded-r-lg">
<p class="text-violet-800 font-medium"><i class="fas fa-envelope-open-text mr-2"></i>Copy, paste, customize, send. It\'s that simple.</p>
</div>'
            ],
            'saas-starter-kit' => [
                'description' => 'Complete Laravel template to launch your Software as a Service (SaaS) business.',
                'full_description' => '<h2 class="text-2xl font-bold text-slate-900 mb-4">Launch Your SaaS in Weeks, Not Months</h2>
<p class="text-lg text-slate-600 mb-6">Everything you need to build and launch a subscription-based SaaS product. Authentication, billing, user management, and more - all wired up and ready.</p>

<h3 class="text-xl font-semibold text-slate-800 mb-3">Built-In Features</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>User Authentication</strong> - Registration, login, password reset, email verification</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Subscription Billing</strong> - Paystack integration for Nigerian payments</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Pricing Plans</strong> - Multiple tiers, annual/monthly options</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Dashboard</strong> - User dashboard with usage stats</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Admin Panel</strong> - Manage users, subscriptions, invoices</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Webhooks Ready</strong> - Connect to external services</span></li>
</ul>

<div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded-r-lg">
<p class="text-indigo-800 font-medium"><i class="fas fa-layer-group mr-2"></i>Built with Laravel 10 + Cashier for sustainable billing.</p>
</div>'
            ],
            'course-creator-kit' => [
                'description' => 'Everything you need to package and sell your expertise as an online course.',
                'full_description' => '<h2 class="text-2xl font-bold text-slate-900 mb-4">Turn Your Knowledge Into a Revenue Stream</h2>
<p class="text-lg text-slate-600 mb-6">From content creation to course delivery, this kit walks you through every step of launching a profitable online course. No tech skills required.</p>

<h3 class="text-xl font-semibold text-slate-800 mb-3">Course Creation Framework</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Topic Selection</strong> - Find your profitable course idea</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Curriculum Builder</strong> - Structure your content for maximum learning</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Recording Guide</strong> - Create professional videos with basic equipment</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Landing Page Template</strong> - Convert visitors to students</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Pricing Strategy</strong> - How to price your course for profit</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Launch Checklist</strong> - Everything to do before going live</span></li>
</ul>

<div class="bg-teal-50 border-l-4 border-teal-500 p-4 rounded-r-lg">
<p class="text-teal-800 font-medium"><i class="fas fa-play mr-2"></i>Includes Notion template for organizing your course content.</p>
</div>'
            ],
            'local-business-digital-kit' => [
                'description' => 'Comprehensive digital toolkit for local businesses to attract more customers online.',
                'full_description' => '<h2 class="text-2xl font-bold text-slate-900 mb-4">Get More Customers Through Your Door</h2>
<p class="text-lg text-slate-600 mb-6">A practical, no-nonsense toolkit for local businesses ready to compete online. Focus on strategies that actually bring foot traffic and phone calls.</p>

<h3 class="text-xl font-semibold text-slate-800 mb-3">Local Growth Strategies</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Google Business Profile</strong> - Get listed and rank in local search</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Facebook Page Setup</strong> - Optimize for local discovery and engagement</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>WhatsApp Business</strong> - Turn messages into appointments</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Review Strategy</strong> - Get more 5-star reviews</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Local SEO</strong> - Target customers in your area</span></li>
</ul>

<div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded-r-lg">
<p class="text-orange-800 font-medium"><i class="fas fa-store mr-2"></i>Perfect for restaurants, salons, shops, service businesses.</p>
</div>'
            ],
        ];

        foreach ($descriptions as $slug => $data) {
            DB::table('products')->where('slug', $slug)->update([
                'description' => $data['description'],
                'full_description' => $data['full_description']
            ]);
        }
    }
}