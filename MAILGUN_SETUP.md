# Mailgun Setup Guide

## Step 1: Sign Up for Mailgun
1. Go to https://www.mailgun.com
2. Sign up for free account
3. Verify your email

## Step 2: Add Your Domain
1. After login, go to **Domains** in the left sidebar
2. Click **Add Domain**
3. Enter your domain: `mg.joala.com` (or any subdomain)
4. Follow the DNS instructions from Mailgun:
   - Add MX records
   - Add TXT records
   - Add CNAME records (for tracking)
5. Wait 1-48 hours for domain verification

**Note**: For quick testing, you can use Mailgun's sandbox domain (limited to authorized recipients).

## Step 3: Get Your Credentials
From Mailgun Dashboard:
- **API Key**: Found in Account > API Security > Your API keys
- **Domain**: The domain you added (e.g., `mg.joala.com`)

## Step 4: Configure process_emails.php
Open `process_emails.php` and update these values:

```php
$mailgunConfig = [
    'api_key' => 'YOUR_MAILGUN_API_KEY',    // e.g., key-1234567890abcdef
    'domain' => 'YOUR_MAILGUN_DOMAIN',     // e.g., mg.joala.com
    'from_email' => 'support@joala.com',
    'from_name' => 'JoAla Ventures'
];
```

## Alternative: Laravel Mailgun Package
If you prefer using Laravel's native mail functionality, install:
```bash
composer require mailgun/mailgun-php symfony/http-client
```

Then configure in `.env`:
```
MAILGUN_DOMAIN=mg.joala.com
MAILGUN_SECRET_KEY=key-xxx
```

## Testing
After setup, test by visiting:
```
https://www.joala.com.ng/process-emails
```

This should send any pending emails via Mailgun.

## Free Tier Limits
- **Sandbox**: 300 emails/day (only to authorized emails)
- **Verified Domain**: 5,000 emails/month free