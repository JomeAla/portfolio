<!--
Email Sequence Templates Pack
A Comprehensive Email Marketing System for busy entrepreneurs and business owners
Created by JoAla Ventures
Copyright 2026 - All Rights Reserved

TEMPLATE LEGEND:
[Text in brackets] = Replace with your information
{BText in curly braces} = Actionable instruction
-->

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email Sequence Templates Pack - JoAla Ventures</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.7;
            color: #1a1a2e;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            background: #fafbfc;
        }
        
        .header {
            text-align: center;
            padding: 60px 0;
            border-bottom: 3px solid #2563eb;
            margin-bottom: 40px;
        }
        
        .logo {
            font-size: 28px;
            font-weight: 800;
            color: #2563eb;
            letter-spacing: -0.5px;
            margin-bottom: 10px;
        }
        
        h1 {
            font-size: 42px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 16px;
            line-height: 1.2;
        }
        
        .subtitle {
            font-size: 20px;
            color: #64748b;
            font-weight: 500;
        }
        
        h2 {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            margin: 50px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        h3 {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            margin: 30px 0 15px;
        }
        
        h4 {
            font-size: 16px;
            font-weight: 600;
            color: #475569;
            margin: 20px 0 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .sequence {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        
        .sequence-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .sequence-number {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 20px;
        }
        
        .email-block {
            background: #f8fafc;
            border-left: 4px solid #2563eb;
            padding: 25px;
            margin: 20px 0;
            border-radius: 0 12px 12px 0;
        }
        
        .email-day {
            display: inline-block;
            background: #dbeafe;
            color: #2563eb;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        
        .subject-line {
            background: #fef3c7;
            padding: 15px 20px;
            border-radius: 8px;
            margin: 15px 0;
            font-family: monospace;
            font-size: 14px;
        }
        
        .subject-label {
            font-size: 12px;
            color: #b45309;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 5px;
        }
        
        .email-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 15px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .placeholder {
            background: #fef3c7;
            padding: 2px 6px;
            border-radius: 4px;
            color: #b45309;
            font-weight: 500;
        }
        
        .tip-box {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border: 1px solid #10b981;
            padding: 20px;
            border-radius: 12px;
            margin: 25px 0;
        }
        
        .tip-box h4 {
            color: #065f46;
            margin-bottom: 10px;
        }
        
        .tip-box p {
            color: #064e3b;
            font-size: 14px;
        }
        
        .warning-box {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            border: 1px solid #ef4444;
            padding: 20px;
            border-radius: 12px;
            margin: 25px 0;
        }
        
        .warning-box h4 {
            color: #991b1b;
            margin-bottom: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: 800;
            color: #2563eb;
        }
        
        .stat-label {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }
        
        .toc {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin: 30px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .toc ul {
            list-style: none;
        }
        
        .toc li {
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .toc li:last-child {
            border-bottom: none;
        }
        
        .toc-number {
            background: #2563eb;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            margin-right: 12px;
        }
        
        .quick-ref {
            background: #1e293b;
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin: 40px 0;
        }
        
        .quick-ref h3 {
            color: white;
            border-bottom: 1px solid #334155;
            padding-bottom: 15px;
        }
        
        .quick-ref ul {
            list-style: none;
            margin-top: 20px;
        }
        
        .quick-ref li {
            padding: 10px 0;
            color: #cbd5e1;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        
        .checkmark {
            color: #22c55e;
            font-weight: bold;
        }
        
        .footer {
            text-align: center;
            padding: 40px;
            margin-top: 50px;
            border-top: 2px solid #e2e8f0;
        }
        
        .footer p {
            color: #94a3b8;
            font-size: 14px;
        }
        
        @media print {
            body {
                background: white;
                padding: 20px;
            }
            .sequence {
                break-inside: avoid;
                box-shadow: none;
                border: 1px solid #e2e8f0;
            }
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo">JoAla Ventures</div>
        <h1>Email Sequence Templates Pack</h1>
        <p class="subtitle">6 Ready-to-Use Sequences • 24 Tested Templates • Maximum Results</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">6</div>
            <div class="stat-label">Email Sequences</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">24</div>
            <div class="stat-label">Email Templates</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">12</div>
            <div class="stat-label">Industry-Best Open Rates</div>
        </div>
    </div>

    <div class="sequence" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white;">
        <h2 style="color: white; border-bottom-color: rgba(255,255,255,0.2);">How to Use These Templates</h2>
        <p style="opacity: 0.95; margin-bottom: 15px;">Each template is ready to copy and customize. Look for the <span class="placeholder" style="background: rgba(255,255,255,0.2); color: white;">yellow highlighted text</span> - these are your customization points.</p>
        <p style="opacity: 0.95;">Replace everything in brackets [like this] with your specific information. Do not remove the brackets.</p>
    </div>

    <h2>📋 Table of Contents</h2>
    <div class="toc">
        <ul>
            <li><span><span class="toc-number">1</span>Welcome Series</span><span>5 emails</span></li>
            <li><span><span class="toc-number">2</span>Abandoned Cart Recovery</span><span>3 emails</span></li>
            <li><span><span class="toc-number">3</span>Re-engagement</span><span>4 emails</span></li>
            <li><span><span class="toc-number">4</span>Webinar Follow-up</span><span>5 emails</span></li>
            <li><span><span class="toc-number">5</span>Product Launch</span><span>4 emails</span></li>
            <li><span><span class="toc-number">6</span>Thank You & Upsell</span><span>3 emails</span></li>
        </ul>
    </div>

<!-- SEQUENCE 1: WELCOME SERIES -->
    <h2>1️⃣ Welcome Series</h2>
    <p>Build relationships from day one. This is your best opportunity to make a lasting impression.</p>
    
    <div class="sequence">
        <div class="sequence-header">
            <div class="sequence-number">1</div>
            <div>
                <h3 style="margin: 0;">Welcome & Introduce Yourself</h3>
                <p style="color: #64748b; font-size: 14px; margin: 0;">Day 0 • Send within 5 minutes of signup</p>
            </div>
        </div>
        
        <div class="tip-box">
            <h4>💡 Pro Tip</h4>
            <p>This email sets the tone for your entire relationship. Be personal, be real, and deliver value immediately. Your goal is to make them feel they made the right choice joining you.</p>
        </div>
        
        <div class="email-block">
            <span class="email-day">📧 Email 1 of 5</span>
            <h4>Subject Line Options</h4>
            <div class="subject-line">
                <span class="subject-label">Choose one:</span>
                • Welcome to [Your Business Name]!<br>
                • Thank you for joining [Your Business Name]<br>
                • Welcome aboard! Here's what to expect
            </div>
            
            <h4>Email Body</h4>
            <div class="email-content">Hi [First Name],

Welcome to [Your Business Name]! 🎉

I'm [Your Name], founder of [Your Business], and I'm genuinely excited you joined us today.

You just became part of a community of [X]+ entrepreneurs who are serious about [your market/niche].

Over the next few days, I'll be sharing insights that most people never learn:
• [Specific insight #1 - something valuable]
• [Specific insight #2 - something valuable]  
• [Specific insight #3 - something valuable]

But first, let me tell you why I started this business...

[Your Origin Story - 2-3 sentences about what motivated you and the problem you solve]

I know you deal with [common challenge your audience faces]. That's exactly why I created [Your Business] - to help people like you [specific outcome].

Tomorrow, I'll share the #1 strategy that's helped my customers [specific result].

Until then, feel free to reply to this email. I read every single one personally.

To your success,

[Your Name]
[Founder at Your Business Name]
[P.S. Want a quick win? Check out: Link to free resource]</div>
        </div>
    </div>

    <div class="sequence">
        <div class="sequence-header">
            <div class="sequence-number">2</div>
            <div>
                <h3 style="margin: 0;">Share Your Story</h3>
                <p style="color: #64748b; font-size: 14px; margin: 0;">Day 1 • 24 hours after welcome</p>
            </div>
        </div>
        
        <div class="email-block">
            <span class="email-day">📧 Email 2 of 5</span>
            <h4>Subject Line Options</h4>
            <div class="subject-line">
                <span class="subject-label">Choose one:</span>
                • The story behind [Your Business Name]<br>
                • How I went from [challenge] to [success]<br>
                • Why I do what I do
            </div>
            
            <div class="email-content">Hi [First Name],

Yesterday I mentioned my story. Today, I want to tell you the complete picture...

[Share a compelling narrative: What struggle did you face? What did you try that failed? What finally worked? Keep it authentic - vulnerability builds trust]

The turning point came when [key moment or realization].

I know you might be facing [common challenge]. That's precisely why I'm here.

Since then, we've helped [X]+ customers achieve [specific result]. Here's what they're saying:

"[A specific, credible testimonial about their transformation]"

"[Another testimonial focusing on results]"

Tomorrow, I'm going to share the single strategy that's made the biggest difference for my customers. It's simpler than you'd expect.

Talk soon,

[Your Name]
[P.S. Got questions? Reply directly. I respond to every email personally.]</div>
        </div>
    </div>

    <div class="sequence">
        <div class="sequence-header">
            <div class="sequence-number">3</div>
            <div>
                <h3 style="margin: 0;">Deliver First Value</h3>
                <p style="color: #64748b; font-size: 14px; margin: 0;">Day 2 • 48 hours after welcome</p>
            </div>
        </div>
        
        <div class="warning-box">
            <h4>⚠️ Critical Success Factor</h4>
            <p>This is THE most important email in your welcome sequence. Deliver REAL value here. Don't ask for anything - just give freely. This builds trust and positions you as an authority.</p>
        </div>
        
        <div class="email-block">
            <span class="email-day">📧 Email 3 of 5</span>
            <h4>Subject Line Options</h4>
            <div class="subject-line">
                <span class="subject-label">Choose one:</span>
                • My #1 strategy for [desired outcome]<br>
                • [First Name], here is your first gift<br>
                • The strategy that changed everything
            </div>
            
            <div class="email-content">Hi [First Name],

Here it is - the strategy that transformed [specific area] for my business...

[MAIN STRATEGY - Break this down into clear steps]

1. [Step One - specific action]
2. [Step Two - specific action]
3. [Step Three - specific action]

The key principle behind this: [Explain the why behind the strategy]

I've seen this produce [specific results] for [type of customers]. Don't just take my word for it:

"[Brief case study or testimonial showing results]"

The best part? This is just the beginning. There's much more where that came from.

Would you like me to go deeper on any of these points? Just reply and let me know.

[Your Name]
[P.S. Want more? I share these strategies weekly. Join us here: Link]</div>
        </div>
    </div>

    <div class="sequence">
        <div class="sequence-header">
            <div class="sequence-number">4</div>
            <div>
                <h3 style="margin: 0;">Social Proof</h3>
                <p style="color: #64748b; font-size: 14px; margin: 0;">Day 3 • 72 hours after welcome</p>
            </div>
        </div>
        
        <div class="email-block">
            <span class="email-day">📧 Email 4 of 5</span>
            <h4>Subject Line Options</h4>
            <div class="subject-line">
                <span class="subject-label">Choose one:</span>
                • What [X] people are saying about us<br>
                • Real results from real people<br>
                • Join [X]+ others who've [specific benefit]
            </div>
            
            <div class="email-content">Hi [First Name],

I wanted to share something powerful with you...

[Your Business] has helped over [X] people achieve [specific outcome].

Here's what they're saying:

"[Testimonial #1 - specific, focused on transformation]"

"[Testimonial #2 - specific, credible result]"

"[Testimonial #3 - specific outcome achieved]"

These are real people. Real transformations. And you can be next.

If you're ready to [desired outcome], I'm here to help you get there.

[Call to Action Button: Get Started]

Talk soon,

[Your Name]
[P.S. Want to see more results? Just reply "STORIES" and I'll send you more.]</div>
        </div>
    </div>

    <div class="sequence">
        <div class="sequence-header">
            <div class="sequence-number">5</div>
            <div>
                <h3 style="margin: 0;">First Soft Ask</h3>
                <p style="color: #64748b; font-size: 14px; margin: 0;">Day 5 • Ask for engagement</p>
            </div>
        </div>
        
        <div class="email-block">
            <span class="email-day">📧 Email 5 of 5</span>
            <h4>Subject Line Options</h4>
            <div class="subject-line">
                <span class="subject-label">Choose one:</span>
                • Can I ask you a favor?<br>
                • A quick question for you<br>
                • I'd love to hear from you
            </div>
            
            <div class="email-content">Hi [First Name],

I've been sharing a lot with you over the past few days. Now I'd love to hear from YOU.

What's your biggest challenge when it comes to [your niche/market]?

Is it [common challenge #1]?
[common challenge #2]?
Or something else entirely?

Just hit reply and tell me. I read every single response personally.

And here's a little something for your time - [mention a free resource if you have one, or skip this line]:

[Free Resource if applicable]

To your success,

[Your Name]
[P.S. If you're ready to get started, we have a special offer waiting for you - just ask!]</div>
        </div>
    </div>

<!-- SEQUENCE 2: ABANDONED CART -->
    <h2>2️⃣ Abandoned Cart Recovery</h2>
    <p>Recover lost sales with these proven sequences. Timing is everything.</p>
    
    <div class="sequence">
        <div class="sequence-header">
            <div class="sequence-number">1</div>
            <div>
                <h3 style="margin: 0;">Quick Reminder</h3>
                <p style="color: #64748b; font-size: 14px; margin: 0;">1 hour after cart abandonment</p>
            </div>
        </div>
        
        <div class="tip-box">
            <h4>💡 Timing is Everything</h4>
            <p>Send within 1 hour of cart abandonment while the intent is still hot. Keep it short, friendly, and low-pressure.</p>
        </div>
        
        <div class="email-block">
            <span class="email-day">📧 Email 1 of 3</span>
            <h4>Subject Line Options</h4>
            <div class="subject-line">
                <span class="subject-label">Choose one:</span>
                • You forgot something :)<br>
                • Did you mean to leave this behind?<br>
                • Hi [First Name], you almost forgot!
            </div>
            
            <div class="email-content">Hi [First Name],

I noticed you were checking out [Product Name] but didn't complete your order.

No worries - life happens! But I didn't want you to miss out on [key benefit].

Here's what's included in your order:
• [Benefit #1]
• [Benefit #2]
• [Benefit #3]

[Complete Order Button: Link to cart]

Questions? Just reply - I'm here to help!

[Your Name]

P.S. This offer will expire in [X] days.</div>
        </div>
    </div>

    <div class="sequence">
        <div class="sequence-header">
            <div class="sequence-number">2</div>
            <div>
                <h3 style="margin: 0;">Urgency + Benefit Recap</h3>
                <p style="color: #64748b; font-size: 14px; margin: 0;">24 hours later</p>
            </div>
        </div>
        
        <div class="email-block">
            <span class="email-day">📧 Email 2 of 3</span>
            <h4>Subject Line Options</h4>
            <div class="subject-line">
                <span class="subject-label">Choose one:</span>
                • Your cart expires in [X] hours<br>
                • Last chance to lock in [Product Name]<br>
                • Don't miss out - offer expires tonight
            </div>
            
            <div class="email-content">Hi [First Name],

Quick reminder: Your cart for [Product Name] expires in [X] hours.

Here's what you're getting:
[Brief bullet of key benefits]

Don't let [fear of missing out] stop you.

Here's what others are saying:
"[Testimonial quote]"

[Complete Order Button]

Questions? Call me: [phone number]

[Your Name]</div>
        </div>
    </div>

    <div class="sequence">
        <div class="sequence-header">
            <div class="sequence-number">3</div>
            <div>
                <h3 style="margin: 0;">Final Chance + Small Incentive</h3>
                <p style="color: #64748b; font-size: 14px; margin: 0;">72 hours later</p>
            </div>
        </div>
        
        <div class="email-block">
            <span class="email-day">📧 Email 3 of 3</span>
            <h4>Subject Line Options</h4>
            <div class="subject-line">
                <span class="subject-label">Choose one:</span>
                • One last thing...<br>
                • Final decision time<br>
                • Your cart is about to expire
            </div>
            
            <div class="email-content">Hi [First Name],

This is the FINAL email about your cart. After today, we'll need to start fresh.

I'll make this easy for you:

Use code [DISCOUNTCODE] for [X]% OFF - my way of saying thanks for being interested.

[Link to cart with discount]

This is my final offer. No follow-ups after this.

To your success,

[Your Name]

P.S. Still not ready? No problem. But at least reply "NOT NOW" so I stop emailing - I respect your time.</div>
        </div>
    </div>

<!-- SEQUENCE 3: RE-ENGAGEMENT -->
    <h2>3️⃣ Re-engagement Sequence</h2>
    <p>Win back inactive subscribers who haven't engaged in weeks.</p>
    
    <div class="sequence">
        <div class="sequence-header">
            <div class="sequence-number">1</div>
            <div>
                <h3 style="margin: 0;">"We Miss You"</h3>
                <p style="color: #64748b; font-size: 14px; margin: 0;">Week 1 • No activity for 21+ days</p>
            </div>
        </div>
        
        <div class="email-block">
            <span class="email-day">📧 Email 1 of 4</span>
            <h4>Subject Line Options</h4>
            <div class="subject-line">
                <span class="subject-label">Choose one:</span>
                • We miss you, [First Name]!<br>
                • It's been a while...<br>
                • Checking in on you
            </div>
            
            <div class="email-content">Hi [First Name],

It's been a while since we've connected. How are you doing?

I was thinking about you and wanted to check in.

Since we last talked, here's what's been happening at [Business]:
• [News/Update #1]
• [News/Update #2]

No pressure to buy anything. I just wanted to share [valuable content] with you:

[Link to valuable content]

Would love to hear what's going on with you. Just hit reply!

[Your Name]</div>
        </div>
    </div>

    <div class="sequence">
        <div class="sequence-header">
            <div class="sequence-number">4</div>
            <div>
                <h3 style="margin: 0;">Final Goodbye</h3>
                <p style="color: #64748b; font-size: 14px; margin: 0;">Week 4 • After 28 days</p>
            </div>
        </div>
        
        <div class="email-block">
            <span class="email-day">📧 Email 4 of 4</span>
            <h4>Subject Line Options</h4>
            <div class="subject-line">
                <span class="subject-label">Choose one:</span>
                • Goodbye (for now)<br>
                • Our last email<br>
                • Time to say goodbye
            </div>
            
            <div class="email-content">Hi [First Name],

I've enjoyed our conversations, but I don't want to take up any more of your time.

This is my final email in this series. I'll move you to [another list/newsletter] where you'll get [different content type] instead.

Before I go, here's one last gift - my best tips in one place:

[Link to free resource]

If you ever need anything, you know where to find me.

To your success,

[Your Name]

P.S. It was great connecting with you.</div>
        </div>
    </div>

<!-- SEQUENCE 4: WEBINAR FOLLOW-UP -->
    <h2>4️⃣ Webinar Follow-up</h2>
    <p>Maximize webinar attendance and convert attendees to customers.</p>
    
    <div class="sequence">
        <div class="sequence-header">
            <div class="sequence-number">1</div>
            <div>
                <h3 style="margin: 0;">Thank You + Replay Link</h3>
                <p style="color: #64748b; font-size: 14px; margin: 0;">Same day, immediately after webinar</p>
            </div>
        </div>
        
        <div class="email-block">
            <span class="email-day">📧 Email 1 of 5</span>
            <h4>Subject Line Options</h4>
            <div class="subject-line">
                <span class="subject-label">Choose one:</span>
                • Thank you for attending!<br>
                • Here is your replay link<br>
                • Thanks for joining [Webinar Name]!
            </div>
            
            <div class="email-content">Hi [First Name],

WOW! What an amazing response to [Webinar Name]!

[Number] people registered, [Number] showed up live. Incredible energy!

YOUR REPLAY LINK:
[Link to replay - expires in 48-72 hours]

Here's what we covered:
• [Key point #1]
• [Key point #2]
• [Key point #3]

[If you have slides: Download the slides here]

Looking forward to seeing you at the next one!

[Your Name]

P.S. Have questions? Reply directly - I answer personally.</div>
        </div>
    </div>

    <div class="sequence">
        <div class="sequence-header">
            <div class="sequence-number">5</div>
            <div>
                <h3 style="margin: 0;">Limited Time Offer</h3>
                <p style="color: #64748b; font-size: 14px; margin: 0;">Day 5 • Create urgency</p>
            </div>
        </div>
        
        <div class="email-block">
            <span class="email-day">📧 Email 5 of 5</span>
            <h4>Subject Line Options</h4>
            <div class="subject-line">
                <span class="subject-label">Choose one:</span>
                • Final reminder: Offer ends tonight<br>
                • Last chance: [Webinar] offer expires<br>
                • Today's your last day
            </div>
            
            <div class="email-content">Hi [First Name],

This is it - your FINAL DAY to get [Special Offer] from [Webinar].

[X] people have already taken advantage.

What's included:
[Brief list of what's in the offer]

Only [X] spots left at this price.

[Get Access Now Button]

After tonight, offer disappears.

One more thing: I'm personally selecting [X] people for 1-on-1 work. First come, first served.

[Your Name]

P.S. Questions? Call [number] - I answer personally.</div>
        </div>
    </div>

<!-- SEQUENCE 5: NEW PRODUCT LAUNCH -->
    <h2>5️⃣ Product Launch Sequence</h2>
    <p>Launch new products with maximum conversions.</p>
    
    <div class="sequence">
        <div class="sequence-header">
            <div class="sequence-number">1</div>
            <div>
                <h3 style="margin: 0;">Teaser - Build Anticipation</h3>
                <p style="color: #64748b; font-size: 14px; margin: 0;">5-7 days before launch</p>
            </div>
        </div>
        
        <div class="tip-box">
            <h4>💡 Build anticipation</h4>
            <p>Don't reveal everything. Create curiosity. Make them hungry for what's coming.</p>
        </div>
        
        <div class="email-block">
            <span class="email-day">📧 Email 1 of 4</span>
            <h4>Subject Line Options</h4>
            <div class="subject-line">
                <span class="subject-label">Choose one:</span>
                • Something big is coming...<br>
                • Can I tell you a secret?<br>
                • I've been working on something new
            </div>
            
            <div class="email-content">Hi [First Name],

I've been working on something new. And I wanted to tell YOU first.

[Hint at what you're creating without giving it all away]

Here's what I CAN tell you:
• It's for people who want [specific outcome]
• It solves [specific pain point]
• I've tested it with [X] people with incredible results

The official launch is [Day/Date].

Want to be first in line when it goes live?

[Early Access Button]

More details soon...

[Your Name]

P.S. This is just between us for now ;)</div>
        </div>
    </div>

    <div class="sequence">
        <div class="sequence-header">
            <div class="sequence-number">4</div>
            <div>
                <h3 style="margin: 0;">Urgency: Last Chance</h3>
                <p style="color: #64748b; font-size: 14px; margin: 0;">Day 4 • Final call</p>
            </div>
        </div>
        
        <div class="email-block">
            <span class="email-day">📧 Email 4 of 4</span>
            <h4>Subject Line Options</h4>
            <div class="subject-line">
                <span class="subject-label">Choose one:</span>
                • Last chance for launch pricing<br>
                • Only [X] hours left<br>
                • Final call: Launch offer ends
            </div>
            
            <div class="email-content">Hi [First Name],

This is it. Last chance.

Launch pricing for [Product Name] ends tonight at midnight.

After tomorrow:
• Price goes from [current] to [regular]
• [Bonus] no longer included
• [Added scarcity]

Current price: [Amount]
Regular price: [Amount more]

[Get It Now Button]

I'm hand-selecting [X] people to work with 1-on-1 after this. First come, first served.

[Your Name]

P.S. No follow-up. Just the waitlist for next time.</div>
        </div>
    </div>

<!-- SEQUENCE 6: THANK YOU & UPSELL -->
    <h2>6️⃣ Thank You & Upsell</h2>
    <p>Post-purchase sequence to maximize customer value.</p>
    
    <div class="sequence">
        <div class="sequence-header">
            <div class="sequence-number">1</div>
            <div>
                <h3 style="margin: 0;">Purchase Confirmation</h3>
                <p style="color: #64748b; font-size: 14px; margin: 0;">Immediately after purchase</p>
            </div>
        </div>
        
        <div class="email-block">
            <span class="email-day">📧 Email 1 of 3</span>
            <h4>Subject Line Options</h4>
            <div class="subject-line">
                <span class="subject-label">Choose one:</span>
                • Order confirmed! What's next?<br>
                • Thank you for your order!<br>
                • You're in! Here's what happens next
            </div>
            
            <div class="email-content">Hi [First Name],

YES! Thank you for your purchase of [Product Name].

Here's your receipt:
• Order #: [Number]
• Product: [Product]
• Amount: [Amount]
• Date: [Date]

WHAT'S NEXT:

1. Check your email for the download link (sent separately)
2. Download and review your [Product]
3. Join our private community [if applicable]

Questions? Reply directly - I'm here to help.

[Your Name]

P.S. Want to accelerate your results? Let's schedule a call: [Calendar Link]</div>
        </div>
    </div>

    <div class="sequence">
        <div class="sequence-header">
            <div class="sequence-number">3</div>
            <div>
                <h3 style="margin: 0;">Upsell to Premium</h3>
                <p style="color: #64748b; font-size: 14px; margin: 0;">Day 7 • Post-purchase</p>
            </div>
        </div>
        
        <div class="email-block">
            <span class="email-day">📧 Email 3 of 3</span>
            <h4>Subject Line Options</h4>
            <div class="subject-line">
                <span class="subject-label">Choose one:</span>
                • A special offer for existing customers<br>
                • Want to accelerate your results?<br>
                • As a valued customer...
            </div>
            
            <div class="email-content">Hi [First Name],

You've already seen the power of [Product 1]. Now imagine [Product 2]...

We created [Product 2] specifically for people who've done [Product 1] and want more.

What's included:
• [Benefit #1]
• [Benefit #2]
• [Benefit #3]

As a valued customer, I'm offering you [special pricing] for the next 48 hours.

[Get Access Button]

Space is limited to [X] people. We're at [current number].

Talk soon,

[Your Name]

P.S. Not ready? No pressure. But the price goes up when this ends.</div>
        </div>
    </div>

<!-- QUICK REFERENCE -->
    <div class="quick-ref">
        <h3>⚡ Quick Reference Guide</h3>
        <ul>
            <li><span class="checkmark">✓</span> Best send times: Tuesday-Thursday, 10am-12pm</li>
            <li><span class="checkmark">✓</span> Healthy open rate: 20-30%</li>
            <li><span class="checkmark">✓</span> Healthy CTR: 2-5%</li>
            <li><span class="checkmark">✓</span> Reply rate goal: 1%+ (engagement)</li>
            <li><span class="checkmark">✓</span> Personalize with first name</li>
            <li><span class="checkmark">✓</span> Always include P.S.</li>
            <li><span class="checkmark">✓</span> Test subject lines A/B</li>
            <li><span class="checkmark">✓</span> Monitor and optimize weekly</li>
        </ul>
    </div>

    <div class="footer">
        <p>© 2026 JoAla Ventures. All Rights Reserved.</p>
        <p>This document is for personal use only. Do not resell or distribute.</p>
    </div>

</body>
</html>