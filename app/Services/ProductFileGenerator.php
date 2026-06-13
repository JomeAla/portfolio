<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProductFileGenerator
{
    protected string $baseDir;
    protected string $storageDir;

    public function __construct()
    {
        $this->baseDir = storage_path('app/public/uploads/products/files');
        if (!File::exists($this->baseDir)) {
            File::makeDirectory($this->baseDir, 0755, true);
        }
        $this->storageDir = $this->baseDir;
        $publicDir = public_path('uploads/products/files');
        if (!File::exists($publicDir)) {
            File::makeDirectory($publicDir, 0755, true);
        }
    }

    public function createAndGenerate(array $data): array
    {
        $slug = $data['slug'];
        $existing = DB::table('products')->where('slug', $slug)->first();
        if ($existing) {
            return $this->generate($slug);
        }

        $id = DB::table('products')->insertGetId([
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? '',
            'short_description' => $data['short_description'] ?? '',
            'price' => $data['price'],
            'sale_price' => $data['sale_price'] ?? null,
            'file_path' => 'uploads/products/files/' . $slug . '.html',
            'type' => $data['type'] ?? 'digital',
            'is_active' => 1,
            'order' => $data['order'] ?? 99,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->generate($slug);
    }

    public function generate(string $slug): array
    {
        $product = DB::table('products')->where('slug', $slug)->first();
        if (!$product) {
            return ['success' => false, 'message' => "Product '$slug' not found"];
        }

        $method = $this->methodForSlug($slug);
        if (!method_exists($this, $method)) {
            return ['success' => false, 'message' => "No generator for '$slug' (looked for $method)"];
        }

        $html = $this->$method($product);
        $filename = $slug . '.html';

        File::put($this->baseDir . '/' . $filename, $html);
        File::put(public_path('uploads/products/files/' . $filename), $html);

        DB::table('products')->where('id', $product->id)->update([
            'file_path' => 'uploads/products/files/' . $filename
        ]);

        return [
            'success' => true,
            'message' => "Generated: $filename",
            'file_path' => 'uploads/products/files/' . $filename
        ];
    }

    protected function methodForSlug(string $slug): string
    {
        $map = [
            'e-commerce-starter-kit' => 'buildEcommerceStarterKit',
        ];
        if (isset($map[$slug])) {
            return $map[$slug];
        }
        return 'build' . Str::studly(str_replace('-', '_', $slug));
    }

    protected function buildWebsiteAuditKit($product): string
    {
        return $this->layout('Website Audit Kit', $product, '
<h1 class="title">Website Audit Kit</h1>
<p class="subtitle">A comprehensive 20-point audit to identify issues and opportunities</p>

<div class="section">
<h2>How to Use This Kit</h2>
<p>Go through each category below and score your website on a scale of 1–5 for each item. Total your scores and follow the priority action plan at the end.</p>
</div>

<div class="section">
<h2>1. Speed & Performance</h2>
<table>
<tr><th>#</th><th>Check</th><th>1–5</th><th>Notes</th></tr>
<tr><td>1</td><td>Page load time under 3 seconds (test with GTmetrix / PageSpeed)</td><td></td><td></td></tr>
<tr><td>2</td><td>Images are compressed and using next-gen formats (WebP)</td><td></td><td></td></tr>
<tr><td>3</td><td>CSS/JS are minified and combined where possible</td><td></td><td></td></tr>
<tr><td>4</td><td>Browser caching and CDN are configured</td><td></td><td></td></tr>
<tr><td>5</td><td>Server response time (TTFB) under 500ms</td><td></td><td></td></tr>
</table>
</div>

<div class="section">
<h2>2. Mobile Experience</h2>
<table>
<tr><th>#</th><th>Check</th><th>1–5</th><th>Notes</th></tr>
<tr><td>6</td><td>Fully responsive across all screen sizes</td><td></td><td></td></tr>
<tr><td>7</td><td>Touch targets are large enough (min 48px)</td><td></td><td></td></tr>
<tr><td>8</td><td>No horizontal scrolling or cut-off content</td><td></td><td></td></tr>
<tr><td>9</td><td>Font sizes are readable without zooming</td><td></td><td></td></tr>
</table>
</div>

<div class="section">
<h2>3. SEO Fundamentals</h2>
<table>
<tr><th>#</th><th>Check</th><th>1–5</th><th>Notes</th></tr>
<tr><td>10</td><td>Meta titles and descriptions set on every page</td><td></td><td></td></tr>
<tr><td>11</td><td>Heading structure is logical (H1→H2→H3)</td><td></td><td></td></tr>
<tr><td>12</td><td>XML sitemap submitted to Google Search Console</td><td></td><td></td></tr>
<tr><td>13</td><td>Alt text on all images</td><td></td><td></td></tr>
<tr><td>14</td><td>URLs are clean and keyword-rich</td><td></td><td></td></tr>
</table>
</div>

<div class="section">
<h2>4. Conversion Elements</h2>
<table>
<tr><th>#</th><th>Check</th><th>1–5</th><th>Notes</th></tr>
<tr><td>15</td><td>Clear primary CTA above the fold</td><td></td><td></td></tr>
<tr><td>16</td><td>Contact info is easy to find</td><td></td><td></td></tr>
<tr><td>17</td><td>Social proof (testimonials, reviews) visible</td><td></td><td></td></tr>
</table>
</div>

<div class="section">
<h2>5. Security Check</h2>
<table>
<tr><th>#</th><th>Check</th><th>1–5</th><th>Notes</th></tr>
<tr><td>18</td><td>SSL certificate active and forcing HTTPS</td><td></td><td></td></tr>
<tr><td>19</td><td>Forms use proper validation and CSRF protection</td><td></td><td></td></tr>
<tr><td>20</td><td>Software/CMS/plugins are up to date</td><td></td><td></td></tr>
</table>
</div>

<div class="section highlight">
<h2>Scoring Guide</h2>
<p><strong>80–100:</strong> Excellent — minor tweaks only</p>
<p><strong>60–79:</strong> Good — several areas need attention</p>
<p><strong>40–59:</strong> Fair — significant improvements needed</p>
<p><strong>Below 40:</strong> Critical — major overhaul recommended</p>
</div>

<div class="section">
<h2>Priority Action Plan</h2>
<p>Items scored 1–2 are your top priorities. List them below:</p>
<ol>
<li>_________________________________</li>
<li>_________________________________</li>
<li>_________________________________</li>
<li>_________________________________</li>
<li>_________________________________</li>
</ol>
</div>

<div class="footer">
<p>Generated for: ___________________ | Date: ___________________</p>
</div>
');
    }

    protected function buildEmailSequenceTemplatesPack($product): string
    {
        $sequences = [
            'Welcome Sequence' => [
                'Subject: Welcome to the family!',
                'Subject: Here\'s your free gift',
                'Subject: How to get the most out of [Product]',
                'Subject: Meet the team behind [Brand]',
                'Subject: We\'d love your feedback',
            ],
            'Launch Sequence' => [
                'Subject: Something exciting is coming...',
                'Subject: Sneak peek inside [Product]',
                'Subject: 24 hours to go!',
                'Subject: It\'s here! 🎉',
            ],
            'Abandoned Cart Recovery' => [
                'Subject: Did you forget something?',
                'Subject: Your cart is expiring soon',
                'Subject: Here\'s 10% off to complete your order',
            ],
            'Post-Purchase Thank You' => [
                'Subject: Thank you for your order!',
                'Subject: How to set up your new [Product]',
                'Subject: You\'re invited to our community',
            ],
            'Re-engagement Campaign' => [
                'Subject: We miss you!',
                'Subject: Is [Product] still working for you?',
                'Subject: Last chance — don\'t miss out',
                'Subject: We\'ve updated [Product] — check it out',
            ],
            'Weekly Newsletter' => [
                'Subject: [Weekly digest] — Top tips & resources',
                'Subject: [Brand] Update — what\'s new this week',
                'Subject: Industry insights you need to know',
            ],
        ];

        $html = '<h1 class="title">Email Sequence Templates Pack</h1>';
        $html .= '<p class="subtitle">6 complete sequences — 24 tested templates — ready to copy & send</p>';
        $html .= '<div class="section"><h2>How to Use These Templates</h2>';
        $html .= '<p>Copy each email into your email marketing platform (Mailchimp, ConvertKit, MailerLite, etc.). Customise the bracketed [Placeholders] with your brand details. Each template includes subject line, preview text, body copy, and CTA placement.</p></div>';

        foreach ($sequences as $name => $emails) {
            $html .= '<div class="section"><h2>' . $name . '</h2>';
            foreach ($emails as $i => $subject) {
                $html .= '<div class="email-card"><h3>Email ' . ($i + 1) . '</h3>';
                $html .= '<p><strong>' . $subject . '</strong></p>';
                $html .= '<p><em>Preview text:</em> ' . $this->previewForSubject($subject) . '</p>';
                $html .= '<div class="placeholder">[Insert email body here — follow the <a href="#guide">writing guide below</a>]</div>';
                $html .= '<p><strong>CTA:</strong> <a href="#" style="background:#2563eb;color:#fff;padding:8px 20px;border-radius:6px;text-decoration:none;display:inline-block">[Button Text]</a></p>';
                $html .= '<p><small>Send timing: ' . $this->timingForSequence($name, $i) . '</small></p>';
                $html .= '</div>';
            }
            $html .= '</div>';
        }

        $html .= '<div class="section highlight" id="guide"><h2>Email Writing Guide</h2>';
        $html .= '<ul><li><strong>Subject lines:</strong> Keep under 50 characters, create curiosity</li>';
        $html .= '<li><strong>Preview text:</strong> Use as second subject line — complement, don\'t repeat</li>';
        $html .= '<li><strong>Opening line:</strong> Personalise or reference past behaviour</li>';
        $html .= '<li><strong>Body:</strong> One idea per email. Short paragraphs. Bullet points.</li>';
        $html .= '<li><strong>CTA:</strong> One clear button per email. High contrast colour.</li>';
        $html .= '<li><strong>Signature:</strong> Real person name + photo. Builds trust.</li></ul></div>';

        $html .= '<div class="footer"><p>© ' . date('Y') . ' — Copy, customise, and send. Simple as that.</p></div>';

        return $this->layout('Email Sequence Templates Pack', $product, $html);
    }

    protected function buildFreelancerToolkit($product): string
    {
        return $this->layout('Freelancer Toolkit', $product, '
<h1 class="title">Freelancer Toolkit</h1>
<p class="subtitle">The exact system to go from ₦50k/mo to ₦500k+ consistently</p>

<div class="section">
<h2>What\'s Inside</h2>
<ul>
<li><strong>Finding Premium Clients:</strong> Where to find clients who pay well — LinkedIn, Upwork tips, local business outreach scripts</li>
<li><strong>5 Proven Proposal Templates:</strong> Web design, branding, content writing, social media management, and software development</li>
<li><strong>3 Contract Templates:</strong> Fixed-price, hourly retainer, and milestone-based payment</li>
<li><strong>Pricing Strategies:</strong> Value-based pricing, package pricing, and how to raise rates without losing clients</li>
<li><strong>Communication Scripts:</strong> Scope creep handling, late payment follow-ups, revision request boundaries</li>
<li><strong>Client Onboarding System:</strong> Welcome packet template, project kickoff checklist, progress reporting template</li>
</ul>
</div>

<div class="section highlight">
<h2>Premium Client Outreach Script</h2>
<p><em>"Hi [Name], I noticed [specific observation about their business/website]. I help businesses like yours [specific result]. I\'d love to share a quick 5-minute audit of your current [service area] — no strings attached. Would that be helpful this week?"</em></p>
</div>

<div class="section">
<h2>Proposal Structure That Converts</h2>
<ol>
<li><strong>Their Problem:</strong> Show you understand their pain</li>
<li><strong>Your Solution:</strong> Specific approach with timeline</li>
<li><strong>Deliverables:</strong> Exactly what they get</li>
<li><strong>Investment:</strong> Price presented as value</li>
<li><strong>Next Steps:</strong> Clear call to action</li>
</ol>
</div>

<div class="section">
<h2>Contract Essentials Checklist</h2>
<table>
<tr><th>Item</th><th>Included?</th></tr>
<tr><td>Scope of work (detailed)</td><td>☐</td></tr>
<tr><td>Timeline with milestones</td><td>☐</td></tr>
<tr><td>Payment terms & schedule</td><td>☐</td></tr>
<tr><td>Revision & change order policy</td><td>☐</td></tr>
<tr><td>Late payment penalties</td><td>☐</td></tr>
<tr><td>Confidentiality clause</td><td>☐</td></tr>
<tr><td>Termination conditions</td><td>☐</td></tr>
<tr><td>Signature lines</td><td>☐</td></tr>
</table>
</div>

<div class="footer"><p>© ' . date('Y') . ' Freelancer Toolkit — build your freedom business</p></div>
');
    }

    protected function buildInstagramGrowthSystem($product): string
    {
        return $this->layout('Instagram Growth System', $product, '
<h1 class="title">Instagram Growth System</h1>
<p class="subtitle">Build an engaged following of real people — organically</p>

<div class="section">
<h2>The 5-Pillar Growth Framework</h2>
<ul>
<li><strong>1. Profile Optimisation:</strong> Bio formula, highlight covers, link-in-bio strategy</li>
<li><strong>2. Content Strategy:</strong> The 4:1 content ratio (value : engagement : sales : personality)</li>
<li><strong>3. Hashtag Research:</strong> 30 high-traffic, low-competition tags per niche</li>
<li><strong>4. Engagement System:</strong> 30 minutes/day protocol that doubles reach</li>
<li><strong>5. Conversion Path:</strong> From follower to customer in 5 DMs</li>
</ul>
</div>

<div class="section">
<h2>Content Pillars Matrix</h2>
<table>
<tr><th>Content Type</th><th>Frequency</th><th>Purpose</th></tr>
<tr><td>Educational posts</td><td>3x/week</td><td>Establish authority</td></tr>
<tr><td>Behind-the-scenes</td><td>1x/week</td><td>Build connection</td></tr>
<tr><td>User-generated content</td><td>1x/week</td><td>Social proof</td></tr>
<tr><td>Promotional</td><td>1x/week</td><td>Drive sales</td></tr>
<tr><td>Interactive (polls, Q&A)</td><td>1x/week</td><td>Boost algorithm</td></tr>
</table>
</div>

<div class="section highlight">
<h2>30-Day Instagram Growth Calendar</h2>
<p><strong>Week 1:</strong> Optimise profile + post 7 value posts + engage 30 min/day</p>
<p><strong>Week 2:</strong> Add Stories daily + collaborate with 3 accounts in your niche</p>
<p><strong>Week 3:</strong> Launch a giveaway or challenge + use all 5 content types</p>
<p><strong>Week 4:</strong> Analyse insights + double down on top-performing content + DM outreach</p>
</div>

<div class="section">
<h2>10 Story Templates That Drive Engagement</h2>
<ol>
<li>This or That (poll)</li>
<li>Ask Me Anything (question box)</li>
<li>Behind the Scenes (multi-slide)</li>
<li>Quick Tip (text overlay)</li>
<li>Customer Win (testimonial reshare) </li>
<li>Day in the Life (time-lapse)</li>
<li>Quiz (knowledge test)</li>
<li>Countdown (launch/product)</li>
<li>Before & After (transformation)</li>
<li>Quote of the Day (aesthetic)</li>
</ol>
</div>

<div class="footer"><p>Consistency beats intensity. Do this daily.</p></div>
');
    }

    protected function buildCourseCreatorKit($product): string
    {
        return $this->layout('Course Creator Kit', $product, '
<h1 class="title">Course Creator Kit</h1>
<p class="subtitle">Turn your expertise into a profitable online course</p>

<div class="section">
<h2>Phase 1: Find Your Profitable Course Idea</h2>
<ul>
<li><strong>Skill → Course Matrix:</strong> List your skills. Which ones solve a painful problem? Which ones people actively search for?</li>
<li><strong>Market Validation Checklist:</strong> Search volume, competitor analysis, audience willingness to pay</li>
<li><strong>Idea Scorecard:</strong> Rate each idea on demand, expertise, passion, and profit potential</li>
</ul>
</div>

<div class="section">
<h2>Phase 2: Curriculum Building</h2>
<table>
<tr><th>Module</th><th>Lessons</th><th>Format</th></tr>
<tr><td>Introduction & overview</td><td>2–3 lessons</td><td>Video + PDF</td></tr>
<tr><td>Core concepts</td><td>5–7 lessons</td><td>Video + workbook</td></tr>
<tr><td>Practical application</td><td>3–5 lessons</td><td>Screencast + template</td></tr>
<tr><td>Advanced strategies</td><td>3–5 lessons</td><td>Video + case study</td></tr>
<tr><td>Conclusion & next steps</td><td>1–2 lessons</td><td>Video + resource list</td></tr>
</table>
</div>

<div class="section highlight">
<h2>Recording Setup on Any Budget</h2>
<p><strong>Basic (₦0–₦50k):</strong> Smartphone (1080p), window light, USB microphone, free editing app (CapCut)</p>
<p><strong>Intermediate (₦50k–₦200k):</strong> DSLR/mirrorless, ring light, lapel mic, OBS + DaVinci Resolve</p>
<p><strong>Pro (₦200k+):</strong> Multi-camera setup, studio lighting, condenser mic, professional editor</p>
</div>

<div class="section">
<h2>Landing Page Conversion Checklist</h2>
<table>
<tr><td>☐</td><td>Headline states the transformation clearly</td></tr>
<tr><td>☐</td><td>Subheadline expands on the promise</td></tr>
<tr><td>☐</td><td>Curriculum overview (module-by-module)</td></tr>
<tr><td>☐</td><td>Instructor bio with credibility markers</td></tr>
<tr><td>☐</td><td>Testimonials (minimum 3)</td></tr>
<tr><td>☐</td><td>Pricing with value breakdown</td></tr>
<tr><td>☐</td><td>Money-back guarantee</td></tr>
<tr><td>☐</td><td>FAQ section addressing objections</td></tr>
<tr><td>☐</td><td>Countdown timer or scarcity element</td></tr>
<tr><td>☐</td><td>Mobile-optimised checkout</td></tr>
</table>
</div>

<div class="footer"><p>Your expertise is worth packaging. Start today.</p></div>
');
    }

    protected function buildLocalBusinessDigitalKit($product): string
    {
        return $this->layout('Local Business Digital Kit', $product, '
<h1 class="title">Local Business Digital Kit</h1>
<p class="subtitle">Get more customers walking through your door</p>

<div class="section">
<h2>1. Google Business Profile Optimisation</h2>
<ul>
<li>Complete every field (name, address, phone, hours, services)</li>
<li>Add 20+ high-quality photos (exterior, interior, products, team)</li>
<li>Choose the right primary and secondary categories</li>
<li>Write a keyword-rich business description</li>
<li>Post updates at least weekly (offers, events, new products)</li>
<li>Respond to every review within 24 hours</li>
</ul>
</div>

<div class="section">
<h2>2. Facebook Page Setup for Local Discovery</h2>
<ul>
<li>Use local city/neighbourhood in your page name</li>
<li>Enable direct messaging and quick replies</li>
<li>Set up Facebook Shop if applicable</li>
<li>Create events for promotions and activations</li>
<li>Join local community groups (don\'t spam — add value)</li>
</ul>
</div>

<div class="section">
<h2>3. WhatsApp Business Setup</h2>
<ul>
<li>Create a business profile with logo, description, hours</li>
<li>Set up quick replies for common questions</li>
<li>Create catalogues of your products/services</li>
<li>Use broadcast lists for promotions (opt-in only)</li>
<li>Add WhatsApp button to your website and social profiles</li>
</ul>
</div>

<div class="section highlight">
<h2>30-Day Local Growth Plan</h2>
<table>
<tr><th>Week</th><th>Action</th></tr>
<tr><td>1</td><td>Set up / optimise GBP and Facebook pages</td></tr>
<tr><td>2</td><td>Collect 10 reviews (offer small incentive)</td></tr>
<tr><td>3</td><td>Launch WhatsApp Business + run first broadcast</td></tr>
<tr><td>4</td><td>Post consistently + analyse results + adjust</td></tr>
</table>
</div>

<div class="footer"><p>Local customers are searching for you. Make sure they find you.</p></div>
');
    }

    protected function buildNigerianBusinessDigitalKit($product): string
    {
        return $this->layout('Nigerian Business Digital Kit', $product, '
<h1 class="title">Nigerian Business Digital Kit</h1>
<p class="subtitle">Digital tools and strategies tailored for Nigerian businesses</p>

<div class="section">
<h2>Website Launch Checklist</h2>
<table>
<tr><td>☐</td><td>Register domain (.com.ng or .ng for local trust)</td></tr>
<tr><td>☐</td><td>Hosting (Nigerian hosts or reliable international)</td></tr>
<tr><td>☐</td><td>SSL certificate active</td></tr>
<tr><td>☐</td><td>Paystack or Flutterwave payment integration</td></tr>
<tr><td>☐</td><td>Mobile-responsive design</td></tr>
<tr><td>☐</td><td>Contact page with phone, email, WhatsApp, address</td></tr>
<tr><td>☐</td><td>Privacy policy & terms of service</td></tr>
<tr><td>☐</td><td>Google Analytics / tracking installed</td></tr>
</table>
</div>

<div class="section">
<h2>WhatsApp Business Setup (Nigeria-Specific)</h2>
<ul>
<li>Register with a dedicated Nigeria SIM (MTN/Glo/Airtel/9mobile)</li>
<li>Enable two-step verification for security</li>
<li>Set up catalogue with prices in ₦</li>
<li>Create quick replies for: pricing, location, hours, delivery info</li>
<li>Use broadcast lists for promotions (comply with Data Protection Act)</li>
</ul>
</div>

<div class="section">
<h2>Social Media Content Calendar (30 Days)</h2>
<table>
<tr><th>Day</th><th>Platform</th><th>Content</th></tr>
<tr><td>Mon</td><td>Instagram</td><td>Product showcase (photo + short description)</td></tr>
<tr><td>Tue</td><td>Facebook</td><td>Customer testimonial / review</td></tr>
<tr><td>Wed</td><td>WhatsApp</td><td>Tip or value-add broadcast</td></tr>
<tr><td>Thu</td><td>Instagram</td><td>Behind-the-scenes / team photo</td></tr>
<tr><td>Fri</td><td>All</td><td>Promotion or weekend offer</td></tr>
<tr><td>Sat</td><td>Instagram</td><td>User-generated content reshare</td></tr>
<tr><td>Sun</td><td>Rest</td><td>Plan next week\'s content</td></tr>
</table>
</div>

<div class="section highlight">
<h2>Nigerian Payment Gateway Comparison</h2>
<table>
<tr><th>Gateway</th><th>Setup Fee</th><th>Transaction Fee</th><th>Settlement</th></tr>
<tr><td>Paystack</td><td>Free</td><td>1.5% + ₦100</td><td>T+1</td></tr>
<tr><td>Flutterwave</td><td>Free</td><td>1.4% + ₦0</td><td>T+1</td></tr>
<tr><td>Remita</td><td>₦10k–₦25k</td><td>0.5%–1.5%</td><td>T+2</td></tr>
<tr><td>Interswitch</td><td>₦50k–₦100k</td><td>0.5%–1%</td><td>T+2</td></tr>
</table>
</div>

<div class="footer"><p>Built for Nigerian businesses with local budgets and global ambitions.</p></div>
');
    }

    protected function buildWhatsappMarketingBundle($product): string
    {
        return $this->layout('WhatsApp Marketing Bundle', $product, '
<h1 class="title">WhatsApp Marketing Bundle</h1>
<p class="subtitle">Turn WhatsApp into your #1 sales channel</p>

<div class="section">
<h2>WhatsApp Business Setup Guide</h2>
<ol>
<li>Download WhatsApp Business from Play Store/App Store</li>
<li>Register with your business phone number</li>
<li>Create your business profile (name, description, email, address, hours)</li>
<li>Set up product catalogue with images and prices</li>
<li>Configure quick replies for 5 most common questions</li>
<li>Set away messages and greeting messages</li>
<li>Add WhatsApp link to your website and Instagram bio</li>
</ol>
</div>

<div class="section">
<h2>10 Broadcast Templates</h2>
<div class="email-card"><h3>New Product Launch</h3><p>"Hello [Name]! We just launched [product]. Check it out here: [link]. Special price for our first 20 customers!"</p></div>
<div class="email-card"><h3>Flash Sale</h3><p>"Hi! Flash sale alert: [discount]% off [product] for the next [time]. Use code: [code]. Shop now: [link]"</p></div>
<div class="email-card"><h3>Event Reminder</h3><p>"Reminder: Our [event] is on [date] at [time]! See you at [location]. Confirm attendance: yes/no"</p></div>
<div class="email-card"><h3>Order Update</h3><p>"Your order #[id] is [status]. Track it here: [link]. Thank you for choosing [business]!"</p></div>
<div class="email-card"><h3>Feedback Request</h3><p>"Hi [Name]! How was your experience with [product/service]? We\'d love your feedback. Reply with a rating 1-5 ⭐"</p></div>
<div class="email-card"><h3>Re-engagement</h3><p>"Hi! It\'s been a while. We have new [products/services] you might like. Check them out: [link]"</p></div>
<div class="email-card"><h3>Referral Request</h3><p>"Enjoying our service? Refer a friend and both get [reward]! Share this link: [referral link]"</p></div>
<div class="email-card"><h3>Seasonal Greeting</h3><p>"Wishing you and your family a wonderful [festive season]! Here\'s a special gift for you: [link]"</p></div>
<div class="email-card"><h3>Appointment Reminder</h3><p>"Reminder: Your appointment is on [date] at [time] at [location]. Reply R to reschedule or C to confirm."</p></div>
<div class="email-card"><h3>Abandoned Cart</h3><p>"You left items in your cart! Complete your order now and get free delivery: [link]"</p></div>
</div>

<div class="section highlight">
<h2>Best Practices</h2>
<ul>
<li>Always get opt-in consent before broadcasting</li>
<li>Keep messages under 200 characters</li>
<li>Send during business hours (9am–6pm)</li>
<li>Use multimedia (images, videos, voice notes) — they get 3x more responses</li>
<li>Personalise with customer\'s name</li>
<li>Track message open rates using broadcast stats</li>
</ul>
</div>

<div class="footer"><p>WhatsApp has 90%+ open rates. Use it wisely.</p></div>
');
    }

    protected function buildEmailMarketingPremiumBundle($product): string
    {
        return $this->layout('Email Marketing Premium Bundle', $product, '
<h1 class="title">Email Marketing Premium Bundle</h1>
<p class="subtitle">Advanced email marketing strategy for serious businesses</p>

<div class="section">
<h2>Strategy Framework</h2>
<ul>
<li><strong>Audience Segmentation:</strong> How to segment by behaviour, demographics, purchase history, and engagement level</li>
<li><strong>Lead Scoring System:</strong> Identify your hottest leads with a simple point-based system</li>
<li><strong>Automation Workflows:</strong> Welcome series, birthday flows, re-engagement, post-purchase upsell</li>
<li><strong>A/B Testing Protocol:</strong> Subject lines, CTAs, send times, and content formats</li>
<li><strong>Deliverability Guide:</strong> SPF, DKIM, DMARC setup — keep emails out of spam</li>
</ul>
</div>

<div class="section">
<h2>Segmentation Strategy</h2>
<table>
<tr><th>Segment</th><th>Criteria</th><th>Offer</th></tr>
<tr><td>New subscribers</td><td>Signed up < 30 days</td><td>Welcome discount + onboarding</td></tr>
<tr><td>Active buyers</td><td>Purchased in last 90 days</td><td>Loyalty rewards + cross-sell</td></tr>
<tr><td>Lapsed customers</td><td>No purchase in 6+ months</td><td>Win-back offer + "we miss you"</td></tr>
<tr><td>High spenders</td><td>Top 20% by total spend</td><td>VIP access + exclusive previews</td></tr>
<tr><td>Cart abandoners</td><td>Items in cart > 24 hours</td><td>Discount + free shipping</td></tr>
</table>
</div>

<div class="section highlight">
<h2>Email Deliverability Checklist</h2>
<table>
<tr><td>☐</td><td>Custom sending domain configured (SPF record)</td></tr>
<tr><td>☐</td><td>DKIM key published in DNS</td></tr>
<tr><td>☐</td><td>DMARC policy set to p=quarantine or p=reject</td></tr>
<tr><td>☐</td><td>Warm up new sending domains (send small volumes first)</td></tr>
<tr><td>☐</td><td>Remove bounces and unsubscribes promptly</td></tr>
<tr><td>☐</td><td>Monitor spam complaints (< 0.1% rate)</td></tr>
<tr><td>☐</td><td>Use double opt-in for new subscribers</td></tr>
<tr><td>☐</td><td>Clean list every 6 months (remove inactives)</td></tr>
</table>
</div>

<div class="footer"><p>Email marketing: ₦42 return for every ₦1 spent. Get it right.</p></div>
');
    }

    protected function buildDoneForYouEmailAutomation($product): string
    {
        return $this->layout('Done-For-You Email Automation', $product, '
<h1 class="title">Done-For-You Email Automation</h1>
<p class="subtitle">Full email automation setup — strategy, copy, and workflows</p>

<div class="section">
<h2>Complete Automation Workflows</h2>

<h3>Workflow 1: Welcome & Onboarding (7 emails)</h3>
<p>Day 0: Welcome + deliver lead magnet<br>
Day 1: How to use [resource]<br>
Day 3: Brand story & mission<br>
Day 5: Social proof / testimonials<br>
Day 7: First offer (low commitment)<br>
Day 10: Educational value<br>
Day 14: Main offer introduction</p>

<h3>Workflow 2: Abandoned Cart Recovery (3 emails)</h3>
<p>Hour 1: "Did you forget something?"<br>
Hour 24: "Your cart is expiring" + social proof<br>
Hour 48: "Last chance" + discount code</p>

<h3>Workflow 3: Post-Purchase Upsell (4 emails)</h3>
<p>Day 1: Thank you + setup guide<br>
Day 3: Related product recommendation<br>
Day 7: Usage tips to maximise value<br>
Day 14: Loyalty program invitation</p>

<h3>Workflow 4: Re-engagement (4 emails)</h3>
<p>Week 1: "We miss you"<br>
Week 2: "What\'s new?"<br>
Week 3: "Last chance 💔"<br>
Week 4: Final email + unsubscribe option</p>
</div>

<div class="section">
<h2>Segment & Trigger Matrix</h2>
<table>
<tr><th>Trigger</th><th>Segment</th><th>Workflow</th></tr>
<tr><td>New subscriber</td><td>All</td><td>Welcome Sequence</td></tr>
<tr><td>Cart abandoned</td><td>Has items in cart</td><td>Cart Recovery</td></tr>
<tr><td>First purchase</td><td>New customer</td><td>Post-Purchase</td></tr>
<tr><td>No purchase (60 days)</td><td>Active subscriber</td><td>Educational Nurture</td></tr>
<tr><td>No open (90 days)</td><td>Inactive subscriber</td><td>Re-engagement</td></tr>
<tr><td>Birthday recorded</td><td>All customers</td><td>Birthday Offer</td></tr>
</table>
</div>

<div class="footer"><p>Set it up once. Let it run on autopilot.</p></div>
');
    }

    protected function buildChurchOrganizationWebsiteKit($product): string
    {
        return $this->layout('Church & Organization Website Kit', $product, '
<h1 class="title">Church & Organization Website Kit</h1>
<p class="subtitle">Connect your congregation online — sermons, events, giving & more</p>

<div class="section">
<h2>Website Structure</h2>
<table>
<tr><th>Page</th><th>Content</th></tr>
<tr><td>Home</td><td>Hero with service times, latest sermon, upcoming events, welcome message</td></tr>
<tr><td>About</td><td>Mission, vision, history, leadership team with photos</td></tr>
<tr><td>Sermons</td><td>Series-based sermon library with audio/video player and notes</td></tr>
<tr><td>Events</td><td>Calendar with monthly view, registration for special programs</td></tr>
<tr><td>Ministries</td><td>Children, youth, women, men, outreach — each with leader and info</td></tr>
<tr><td>Give</td><td>Online tithes & offerings via Paystack, giving history tracker</td></tr>
<tr><td>Contact</td><td>Form, map, phone, email, social media links</td></tr>
<tr><td>Blog</td><td>Devotionals, announcements, testimonies</td></tr>
</table>
</div>

<div class="section">
<h2>Content Creation Guide</h2>
<ul>
<li><strong>Sermon Upload Workflow:</strong> Record → Edit audio → Upload → Add notes/discussion questions → Publish</li>
<li><strong>Event Promotion Checklist:</strong> Create flyer → Social media posts (3x) → Email blast → WhatsApp broadcast → Website event page</li>
<li><strong>Online Giving Setup:</strong> Paystack/Fluuterwave integration → QR code for service → Giving dashboard for accounting</li>
</ul>
</div>

<div class="section highlight">
<h2>Recommended Tech Stack</h2>
<p><strong>Website:</strong> WordPress with GeneratePress theme (lightweight, accessible)</p>
<p><strong>Sermon Plugin:</strong> Sermon Manager or Church Content</p>
<p><strong>Events:</strong> The Events Calendar</p>
<p><strong>Giving:</strong> GiveWP with Paystack add-on</p>
<p><strong>Email:</strong> Mailchimp (free up to 500 subscribers)</p>
<p><strong>Live Streaming:</strong> YouTube Live + embed on website</p>
</div>

<div class="footer"><p>Bring your ministry online and reach more souls.</p></div>
');
    }

    protected function buildRealEstatePropertyKit($product): string
    {
        return $this->layout('Real Estate Property Kit', $product, '
<h1 class="title">Real Estate Property Kit</h1>
<p class="subtitle">A complete property listing platform for agencies and agents</p>

<div class="section">
<h2>System Architecture</h2>
<ul>
<li><strong>Property Types:</strong> House, Apartment, Land, Commercial, Villa, Duplex</li>
<li><strong>Listing Status:</strong> For Sale, For Rent, Sold, Leased, Pending</li>
<li><strong>Property Features:</strong> Bedrooms, bathrooms, sqm, parking, furnished, etc.</li>
<li><strong>Media:</strong> Multiple images, floor plans, video walkthrough, 360° virtual tour</li>
<li><strong>Location:</strong> GPS coordinates, street address, nearby landmarks, map view</li>
</ul>
</div>

<div class="section">
<h2>Frontend Features</h2>
<ul>
<li>Advanced search with filters (price range, location, type, bedrooms)</li>
<li>Grid, list, and map view toggles</li>
<li>Property detail page with gallery lightbox and enquiry form</li>
<li>Agent profile pages with listed properties</li>
<li>Mortgage calculator</li>
<li>Compare properties (up to 4)</li>
<li>Favourite/saved listings for logged-in users</li>
</ul>
</div>

<div class="section">
<h2>Database Schema (Key Tables)</h2>
<table>
<tr><th>Table</th><th>Key Fields</th></tr>
<tr><td>properties</td><td>id, title, slug, description, price, status, type, bedrooms, bathrooms, area, address, city, state, latitude, longitude, featured, agent_id</td></tr>
<tr><td>property_images</td><td>id, property_id, image_path, is_primary, sort_order</td></tr>
<tr><td>property_features</td><td>id, property_id, feature_name, feature_value</td></tr>
<tr><td>agents</td><td>id, name, email, phone, photo, bio, social_links</td></tr>
<tr><td>enquiries</td><td>id, property_id, name, email, phone, message, created_at</td></tr>
<tr><td>saved_properties</td><td>id, user_id, property_id, created_at</td></tr>
</table>
</div>

<div class="footer"><p>Recommended stack: Laravel + MySQL + Google Maps API + Cloudinary for images</p></div>
');
    }

    protected function buildSchoolManagementSystem($product): string
    {
        return $this->layout('School Management System', $product, '
<h1 class="title">School Management System</h1>
<p class="subtitle">Complete school management — students, staff, academics, fees & results</p>

<div class="section">
<h2>Core Modules</h2>
<ul>
<li><strong>Student Management:</strong> Admissions, profiles, attendance, medical records, behaviour tracking</li>
<li><strong>Staff Management:</strong> Teacher profiles, payroll, attendance, performance reviews</li>
<li><strong>Academic Management:</strong> Class/subject assignment, timetable, lesson notes, curriculum mapping</li>
<li><strong>Result Processing:</strong> CA + exam scores, GPA calculation, termly reports, transcript generation</li>
<li><strong>Fees Management:</strong> Fee structure, invoice generation, payment tracking, receipt printing</li>
<li><strong>Parent Portal:</strong> View grades, attendance, fee balance, school announcements, communication with teachers</li>
</ul>
</div>

<div class="section">
<h2>Academic Term Structure (Nigeria)</h2>
<table>
<tr><th>Term</th><th>Duration</th><th>Key Activities</th></tr>
<tr><td>1st Term</td><td>Sept–Dec</td><td>Admissions, CA tests, mid-term break, exams</td></tr>
<tr><td>2nd Term</td><td>Jan–April</td><td>Continuing classes, sports day, CA tests, exams</td></tr>
<tr><td>3rd Term</td><td>April–July</td><td>Final exams, promotions, graduations, summer</td></tr>
</table>
</div>

<div class="section">
<h2>Result Grading System</h2>
<table>
<tr><th>Score</th><th>Grade</th><th>Remark</th></tr>
<tr><td>75–100</td><td>A</td><td>Excellent</td></tr>
<tr><td>65–74</td><td>B</td><td>Very Good</td></tr>
<tr><td>55–64</td><td>C</td><td>Good</td></tr>
<tr><td>45–54</td><td>D</td><td>Pass</td></tr>
<tr><td>40–44</td><td>E</td><td>Poor</td></tr>
<tr><td>0–39</td><td>F</td><td>Fail</td></tr>
</table>
</div>

<div class="footer"><p>Recommended stack: Laravel + MySQL + Tailwind CSS + DomPDF for result printing</p></div>
');
    }

    protected function buildRestaurantPosKit($product): string
    {
        return $this->layout('Restaurant POS Kit', $product, '
<h1 class="title">Restaurant POS Kit</h1>
<p class="subtitle">Complete POS and ordering system for restaurants and food businesses</p>

<div class="section">
<h2>System Features</h2>
<ul>
<li><strong>Order Management:</strong> Dine-in, takeaway, delivery — with table/order tracking</li>
<li><strong>Menu Management:</strong> Categories, items, modifiers (sides, extras), combo deals</li>
<li><strong>Table Reservation:</strong> Visual floor plan, booking calendar, availability check</li>
<li><strong>Kitchen Display:</strong> Real-time order relay to kitchen, course-based preparation</li>
<li><strong>Payment:</strong> Cash, card, POS terminal, transfer — split bills by item/person</li>
<li><strong>Inventory:</strong> Stock tracking, low-stock alerts, cost calculation</li>
<li><strong>Reports:</strong> Daily sales, popular items, profit margins, staff performance</li>
</ul>
</div>

<div class="section">
<h2>Order Flow</h2>
<ol>
<li>Customer arrives → server creates order → assigns table</li>
<li>Items added with modifiers → sent to kitchen display</li>
<li>Kitchen prepares → marks items ready → server serves</li>
<li>Bill requested → payment processed → receipt printed</li>
<li>Table cleared → ready for next customer</li>
</ol>
</div>

<div class="section highlight">
<h2>Tech Requirements</h2>
<p><strong>Frontend:</strong> Vue.js or React (responsive, works on tablets)</p>
<p><strong>Backend:</strong> Laravel API</p>
<p><strong>Database:</strong> MySQL / PostgreSQL</p>
<p><strong>Offline:</strong> Service worker + IndexedDB for offline order-taking</p>
<p><strong>Printing:</strong> Web-based ESC/POS thermal printer integration</p>
</div>

<div class="footer"><p>Keep your restaurant running smoothly, even during network downtime.</p></div>
');
    }

    protected function buildSaaSStarterKit($product): string
    {
        return $this->layout('SaaS Starter Kit', $product, '
<h1 class="title">SaaS Starter Kit</h1>
<p class="subtitle">Launch your subscription-based SaaS product in weeks</p>

<div class="section">
<h2>Architecture Overview</h2>
<ul>
<li><strong>Auth:</strong> Registration, login, email verification, password reset, social login (Google/GitHub)</li>
<li><strong>Subscription Billing:</strong> Paystack recurring charges, plan management, invoice generation</li>
<li><strong>Plan Tiers:</strong> Free, Pro (₦X/mo), Business (₦Y/mo), Enterprise (custom)</li>
<li><strong>User Dashboard:</strong> Usage stats, billing history, account settings</li>
<li><strong>Admin Panel:</strong> User management, subscription oversight, revenue reports</li>
<li><strong>API:</strong> RESTful API with tokens for third-party integration</li>
<li><strong>Webhooks:</strong> Stripe/Paystack event handling, external service integration</li>
</ul>
</div>

<div class="section">
<h2>Database Schema (Core Tables)</h2>
<table>
<tr><th>Table</th><th>Purpose</th></tr>
<tr><td>users</td><td>id, name, email, password, email_verified_at, trial_ends_at</td></tr>
<tr><td>plans</td><td>id, name, slug, description, price, interval, features (JSON)</td></tr>
<tr><td>subscriptions</td><td>id, user_id, plan_id, status, starts_at, ends_at, cancelled_at</td></tr>
<tr><td>invoices</td><td>id, user_id, subscription_id, amount, status, paid_at, paystack_reference</td></tr>
<tr><td>usage_logs</td><td>id, user_id, feature, count, recorded_at</td></tr>
</table>
</div>

<div class="section">
<h2>Subscription Flow</h2>
<ol>
<li>User selects plan → redirected to Paystack checkout</li>
<li>Paystack sends webhook (subscription.create, charge.success)</li>
<li>System activates subscription + creates invoice</li>
<li>Cron job checks for expiring/cancelled subscriptions daily</li>
<li>User downgraded to free tier on cancellation</li>
</ol>
</div>

<div class="section highlight">
<h2>Recommended Stack</h2>
<p>Laravel 11 + Laravel Cashier + Paystack + MySQL + Tailwind CSS + Alpine.js</p>
</div>

<div class="footer"><p>Your SaaS idea deserves a solid foundation. Build on this.</p></div>
');
    }

    protected function buildEcommerceStarterKit($product): string
    {
        return $this->layout('E-commerce Starter Kit', $product, '
<h1 class="title">E-commerce Starter Kit</h1>
<p class="subtitle">Launch your online store with a fully-functional Laravel e-commerce platform</p>

<div class="section">
<h2>Core Features</h2>
<ul>
<li><strong>Product Management:</strong> Unlimited products, categories, tags, variations (size, colour, etc.)</li>
<li><strong>Shopping Cart:</strong> Persistent cart (guest + logged-in), wishlist, save-for-later</li>
<li><strong>Checkout:</strong> Paystack payment integration, Pay-on-Delivery option, multi-address shipping</li>
<li><strong>Order Tracking:</strong> Order status (pending, processing, shipped, delivered, cancelled)</li>
<li><strong>Coupon System:</strong> Percentage and fixed discounts, expiry dates, usage limits</li>
<li><strong>Email Notifications:</strong> Order confirmation, shipping update, delivery confirmation</li>
<li><strong>Reviews & Ratings:</strong> Product reviews with photo upload, verified purchase badge</li>
<li><strong>Dashboard:</strong> Sales analytics, order management, inventory tracking</li>
</ul>
</div>

<div class="section">
<h2>Database Schema (Key Tables)</h2>
<table>
<tr><th>Table</th><th>Key Fields</th></tr>
<tr><td>products</td><td>id, name, slug, description, price, sale_price, sku, stock, category_id, images, is_active</td></tr>
<tr><td>product_variations</td><td>id, product_id, name, price, stock, sku</td></tr>
<tr><td>categories</td><td>id, name, slug, parent_id, image</td></tr>
<tr><td>orders</td><td>id, user_id, order_number, status, total, shipping_address, payment_method</td></tr>
<tr><td>order_items</td><td>id, order_id, product_id, quantity, price, variation</td></tr>
<tr><td>carts</td><td>id, user_id/session_id, product_id, quantity, variation</td></tr>
<tr><td>coupons</td><td>id, code, type, value, min_amount, usage_limit, expires_at</td></tr>
</table>
</div>

<div class="footer"><p>Start selling online today. Built with Laravel, Tailwind, and Paystack.</p></div>
');
    }

    protected function buildFinancialLiteracyEBook($product): string
    {
        return $this->layout('Financial Literacy E-Book', $product, '
<h1 class="title">Smart Money for Nigerian Entrepreneurs</h1>
<p class="subtitle">A practical guide to managing, saving, and growing your money</p>

<div class="badge" style="background:#f59e0b">₦8,000 — Instant Download</div>

<div class="section">
<h2>What You\'ll Learn</h2>
<ul>
<li><strong>Chapter 1: The Nigerian Money Mindset</strong> — Break free from scarcity thinking. Understand how your upbringing shapes your financial decisions.</li>
<li><strong>Chapter 2: Budgeting That Actually Works</strong> — The 50-30-20 rule adapted for Nigerian incomes. Zero-based budgeting for entrepreneurs with irregular income.</li>
<li><strong>Chapter 3: Saving & Emergency Funds</strong> — Where to save in Nigeria (fixed deposits, mutual funds, Treasury bills). How to build a 6-month emergency fund on any income.</li>
<li><strong>Chapter 4: Debt Management</strong> — Good debt vs bad debt. Strategies to pay off loans, credit cards, and POS debts faster.</li>
<li><strong>Chapter 5: Investing for Beginners</strong> — Stocks, bonds, real estate, and agriculture investments in Nigeria. How to start with as little as ₦5,000.</li>
<li><strong>Chapter 6: Business vs Personal Finance</strong> — Separate your finances. Pay yourself first. Tax essentials for Nigerian business owners.</li>
<li><strong>Chapter 7: Retirement Planning</strong> — RSA, pension schemes, and alternative retirement strategies for entrepreneurs without employers.</li>
<li><strong>Chapter 8: Building Wealth</strong> — Multiple income streams, passive income ideas for Nigerians, and the mindset of the wealthy.</li>
</ul>
</div>

<div class="section highlight">
<h2>Bonuses Included</h2>
<ul>
<li>Monthly budget template (Excel)</li>
<li>Net worth tracker spreadsheet</li>
<li>Nigerian investment platforms comparison table</li>
<li>Debt payoff calculator</li>
</ul>
</div>

<div class="section">
<h2>Who This Is For</h2>
<ul>
<li>Nigerian entrepreneurs with irregular income</li>
<li>Salary earners looking to start investing</li>
<li>Anyone tired of living paycheck to paycheck</li>
<li>Business owners who mix personal and business finances</li>
</ul>
</div>

<div class="footer"><p>Financial freedom starts with one decision. Make it today.</p></div>
');
    }

    protected function buildAutomationPlaybook($product): string
    {
        return $this->layout('Automation Playbook', $product, '
<h1 class="title">Business Automation Playbook</h1>
<p class="subtitle">Stop doing manual work — automate your Nigerian business</p>

<div class="section">
<h2>What\'s Inside</h2>
<ul>
<li><strong>Chapter 1: Automation Mindset</strong> — What to automate vs what to keep manual. The 80/20 rule of automation.</li>
<li><strong>Chapter 2: Email Automation</strong> — Set up welcome sequences, abandoned cart recovery, and post-purchase follow-ups using free tools.</li>
<li><strong>Chapter 3: WhatsApp Automation</strong> — Broadcast scheduling, quick replies, chatbot flows, and catalogue management.</li>
<li><strong>Chapter 4: Social Media Scheduling</strong> — Tools and workflows to schedule 30 days of content in one sitting.</li>
<li><strong>Chapter 5: CRM & Lead Management</strong> — Track leads automatically, score prospects, and trigger follow-ups.</li>
<li><strong>Chapter 6: Invoicing & Payments</strong> — Automated invoicing, payment reminders, and reconciliation with Paystack.</li>
<li><strong>Chapter 7: Order Fulfillment</strong> — Auto-send digital products, delivery notifications, and customer feedback requests.</li>
<li><strong>Chapter 8: Analytics & Reporting</strong> — Automated reports for sales, traffic, and customer behaviour.</li>
</ul>
</div>

<div class="section">
<h2>Automation Tools Comparison</h2>
<table>
<tr><th>Tool</th><th>Cost</th><th>Best For</th></tr>
<tr><td>Zapier</td><td>Free — $30/mo</td><td>Connecting 2,000+ apps</td></tr>
<tr><td>Make (Integromat)</td><td>Free — $15/mo</td><td>Complex workflows, cheaper than Zapier</td></tr>
<tr><td>n8n (self-hosted)</td><td>Free (own server)</td><td>Advanced users, unlimited operations</td></tr>
<tr><td>WhatsApp Business API</td><td>Variable</td><td>Broadcast + chatbot at scale</td></tr>
<tr><td>Mailchimp Automation</td><td>Free — $30/mo</td><td>Email sequences + segments</td></tr>
</table>
</div>

<div class="section highlight">
<h2>Quick Wins (Implement in 1 Day)</h2>
<ol>
<li>Set up email welcome sequence (30 min)</li>
<li>Schedule 7 days of social media posts (1 hour)</li>
<li>Create 5 WhatsApp quick replies (20 min)</li>
<li>Connect Paystack to Google Sheets (15 min)</li>
<li>Set up auto-reply for FAQs (30 min)</li>
</ol>
</div>

<div class="footer"><p>Automate the boring stuff. Focus on what matters.</p></div>
');
    }

    protected function buildWpSetupGuide($product): string
    {
        return $this->layout('WordPress Setup Guide', $product, '
<h1 class="title">WordPress Setup Guide</h1>
<p class="subtitle">Launch your WordPress site in under 2 hours — no technical skills needed</p>

<div class="section">
<h2>Step-by-Step Guide</h2>
<ol>
<li><strong>Domain & Hosting</strong> — Where to buy domains in Nigeria (Whogohost, DomainKing, Truehost). Recommended hosting plans for beginners.</li>
<li><strong>Install WordPress</strong> — One-click install via cPanel. Manual installation if your host doesn\'t offer it.</li>
<li><strong>Choose a Theme</strong> — Free vs premium themes. Best lightweight themes for speed (Astra, GeneratePress, Kadence).</li>
<li><strong>Essential Plugins</strong> — Must-have plugins for SEO, security, speed, and backups. Free options for each category.</li>
<li><strong>Create Pages</strong> — Home, About, Services, Portfolio, Blog, Contact. Page structure and content tips for each.</li>
<li><strong>Set Up Navigation</strong> — Menu structure, mega menus, mobile menu optimisation.</li>
<li><strong>SEO Setup</strong> — Install Rank Math or Yoast. Configure meta titles, sitemaps, and Google Search Console.</li>
<li><strong>Speed Optimisation</strong> — Caching, image optimisation, CDN setup, and performance checklist.</li>
<li><strong>Security Hardening</strong> — Login protection, firewall, regular backups, and security plugins.</li>
<li><strong>Launch Checklist</strong> — Final checks before going live. Testing, forms, analytics, and social media integration.</li>
</ol>
</div>

<div class="section">
<h2>Recommended Plugins (Free)</h2>
<table>
<tr><th>Category</th><th>Plugin</th></tr>
<tr><td>SEO</td><td>Rank Math SEO</td></tr>
<tr><td>Security</td><td>Wordfence Security</td></tr>
<tr><td>Speed</td><td>WP Rocket (paid) or WP Super Minify (free)</td></tr>
<tr><td>Backup</td><td>UpdraftPlus</td></tr>
<tr><td>Forms</td><td>Fluent Forms or Contact Form 7</td></tr>
<tr><td>Page Builder</td><td>Elementor (free version)</td></tr>
<tr><td>Caching</td><td>W3 Total Cache</td></tr>
</table>
</div>

<div class="footer"><p>Get your site online today. No experience required.</p></div>
');
    }

    protected function buildShopifyLaunchChecklist($product): string
    {
        return $this->layout('Shopify Launch Checklist', $product, '
<h1 class="title">Shopify Launch Checklist</h1>
<p class="subtitle">Everything you need to launch your Shopify store the right way</p>

<div class="section">
<h2>Pre-Launch Setup</h2>
<table>
<tr><th>#</th><th>Task</th><th>Status</th></tr>
<tr><td>1</td><td>Choose a store name and register domain</td><td>☐</td></tr>
<tr><td>2</td><td>Select Shopify plan (Basic $39/mo or Starter $5/mo)</td><td>☐</td></tr>
<tr><td>3</td><td>Choose and customise a free/paid theme</td><td>☐</td></tr>
<tr><td>4</td><td>Set up branding: logo, colours, fonts, favicon</td><td>☐</td></tr>
<tr><td>5</td><td>Configure shipping zones and rates (local + international)</td><td>☐</td></tr>
<tr><td>6</td><td>Set up tax settings (Nigerian VAT: 7.5%)</td><td>☐</td></tr>
<tr><td>7</td><td>Connect payment gateway (Paystack or Flutterwave)</td><td>☐</td></tr>
<tr><td>8</td><td>Configure email notifications (Shopify Email or Mailchimp)</td><td>☐</td></tr>
<tr><td>9</td><td>Set up Google Analytics + Facebook Pixel</td><td>☐</td></tr>
<tr><td>10</td><td>Create essential pages: About, Contact, FAQ, Privacy, Refund</td><td>☐</td></tr>
</table>
</div>

<div class="section">
<h2>Product Setup</h2>
<table>
<tr><th>#</th><th>Task</th><th>Status</th></tr>
<tr><td>11</td><td>Add products with high-quality images (minimum 4 per product)</td><td>☐</td></tr>
<tr><td>12</td><td>Write compelling product descriptions (features + benefits)</td><td>☐</td></tr>
<tr><td>13</td><td>Set up product variants (size, colour, etc.)</td><td>☐</td></tr>
<tr><td>14</td><td>Organise into collections/ categories</td><td>☐</td></tr>
<tr><td>15</td><td>Set up inventory tracking</td><td>☐</td></tr>
<tr><td>16</td><td>Configure digital download delivery (for digital products)</td><td>☐</td></tr>
</table>
</div>

<div class="section highlight">
<h2>Launch Day Checklist</h2>
<ol>
<li>Test complete purchase flow (add to cart → checkout → payment → confirmation)</li>
<li>Test on mobile phone and desktop</li>
<li>Place a test order and verify email notification</li>
<li>Submit sitemap to Google Search Console</li>
<li>Announce launch on social media (3 posts minimum)</li>
<li>Send launch email to your list</li>
<li>Monitor first 24 hours closely</li>
</ol>
</div>

<div class="section">
<h2>Post-Launch (First 30 Days)</h2>
<ul>
<li>Week 1: Collect feedback from first customers</li>
<li>Week 2: Run first Facebook/Instagram ad campaign</li>
<li>Week 3: Analyse analytics and adjust product mix</li>
<li>Week 4: Plan next month\'s marketing strategy</li>
</ul>
</div>

<div class="footer"><p>Launch with confidence. Your Shopify store is ready to sell.</p></div>
');
    }

    protected function layout(string $title, $product, string $content): string
    {
        $price = $product->sale_price ?? $product->price;
        return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . $title . ' — Instant Download</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #1e293b; line-height: 1.6; padding: 40px 20px; }
.container { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 48px; }
.title { font-size: 28px; font-weight: 800; margin-bottom: 8px; color: #0f172a; }
.subtitle { font-size: 16px; color: #64748b; margin-bottom: 32px; }
.section { margin-bottom: 32px; }
.section h2 { font-size: 20px; font-weight: 700; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #e2e8f0; color: #0f172a; }
.section h3 { font-size: 16px; font-weight: 600; margin-bottom: 8px; margin-top: 16px; color: #334155; }
.section ul, .section ol { padding-left: 24px; margin-bottom: 16px; }
.section li { margin-bottom: 8px; }
.section p { margin-bottom: 12px; }
table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 14px; }
th, td { border: 1px solid #e2e8f0; padding: 10px 12px; text-align: left; }
th { background: #f1f5f9; font-weight: 600; color: #475569; }
.highlight { background: #f0f9ff; border-left: 4px solid #3b82f6; padding: 20px 24px; border-radius: 8px; }
.email-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 12px; }
.email-card h3 { margin-top: 0; color: #3b82f6; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
.placeholder { background: #fef9c3; border: 1px dashed #eab308; border-radius: 4px; padding: 8px 12px; margin: 8px 0; font-size: 13px; color: #854d0e; }
.footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center; color: #94a3b8; font-size: 13px; }
@media print { body { padding: 0; background: #fff; } .container { box-shadow: none; padding: 24px; } }
.badge { display: inline-block; background: #059669; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-bottom: 16px; }
@media (max-width: 640px) { .container { padding: 24px 16px; } table { font-size: 12px; } th, td { padding: 6px 8px; } }
</style>
</head>
<body>
<div class="container">
<div class="badge">₦' . number_format($price) . ' — Instant Download</div>
' . $content . '
</div>
</body>
</html>';
    }

    protected function previewForSubject(string $subject): string
    {
        $previews = [
            'Welcome to the family!' => 'We\'re so glad you joined us',
            'Here\'s your free gift' => 'Your download link is inside',
            'How to get the most out of' => 'A quick start guide for new users',
            'Meet the team behind' => 'The people making it all happen',
            'We\'d love your feedback' => 'Your opinion shapes our future',
            'Something exciting is coming' => 'Get ready for something new',
            'Sneak peek inside' => 'A first look at what\'s coming',
            '24 hours to go!' => 'The wait is almost over',
            'Did you forget something' => 'Your items are still waiting',
            'Your cart is expiring soon' => 'Don\'t lose what you\'ve picked',
            'Here\'s 10% off to complete your order' => 'A little nudge to seal the deal',
            'Thank you for your order!' => 'What happens next',
            'How to set up your new' => 'Get started in minutes',
            'You\'re invited to our community' => 'Join fellow [product] users',
            'We miss you!' => 'It\'s been too long',
            'Last chance' => 'Don\'t miss out on this',
            'We\'ve updated' => 'See what\'s new and improved',
        ];

        foreach ($previews as $key => $preview) {
            if (str_contains($subject, trim(substr($key, 0, 20)))) {
                return $preview;
            }
        }
        return 'Open this email for valuable content';
    }

    protected function timingForSequence(string $sequence, int $emailIndex): string
    {
        $timings = [
            'Welcome Sequence' => ['Day 0 (immediate)', 'Day 1', 'Day 3', 'Day 5', 'Day 7'],
            'Launch Sequence' => ['Day -7', 'Day -3', 'Day -1', 'Day 0'],
            'Abandoned Cart Recovery' => ['1 hour after', '24 hours after', '48 hours after'],
            'Post-Purchase Thank You' => ['Immediate', 'Day 1', 'Day 3'],
            'Re-engagement Campaign' => ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            'Weekly Newsletter' => ['Every Tuesday 10am', 'Every Thursday 10am', 'Every Tuesday 10am'],
        ];

        return $timings[$sequence][$emailIndex] ?? 'Send immediately';
    }
}
