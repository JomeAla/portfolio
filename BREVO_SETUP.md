# Brevo Email Configuration

## IMPORTANT: Replace Credentials Before Use

The scripts in this project contain placeholder credentials. You MUST replace them with your actual Brevo credentials before running locally.

## Credentials to Replace

### 1. brevo_setup.php
```php
'smtp_username' => 'YOUR_BREVO_USERNAME',  // e.g., a93fba001@smtp-brevo.com
'smtp_password' => 'YOUR_BREVO_SMTP_KEY', // SMTP key from Brevo dashboard
```

### 2. save_api_key.php
```php
$apiKey = 'YOUR_BREVO_API_KEY';  // Brevo API key (starts with xkeysib-)
```

### 3. test_api.php
```php
$apiKey = 'YOUR_BREVO_API_KEY';  // Brevo API key
```

## Your Actual Brevo Credentials

```
SMTP Server: smtp-relay.brevo.com
Port: 587
Login: YOUR_BREVO_USERNAME
SMTP Key: YOUR_BREVO_SMTP_KEY

API Key: YOUR_BREVO_API_KEY

Sender Name: Joala
Sender Email: campaigns@joala.com.ng
```

## Quick Reference

| Setting | Value |
|---------|-------|
| SMTP Host | smtp-relay.brevo.com |
| SMTP Port | 587 |
| API Endpoint | https://api.brevo.com/v3/smtp/email |
| From Email | campaigns@joala.com.ng |
| From Name | Joala |

## Testing

After replacing credentials:
```bash
# Test API
curl https://joala.com.ng/test_api.php

# Test SMTP  
curl https://joala.com.ng/test_smtp.php
```

## Security Note

DO NOT commit actual credentials to GitHub! GitHub will block the push if secrets are detected.
Use the placeholder format and set credentials via:
- Environment variables
- Database settings table
- Admin panel (Settings > Email)