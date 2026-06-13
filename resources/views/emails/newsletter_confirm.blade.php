<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #fff; border: 1px solid #e5e5e5; border-radius: 8px; padding: 30px;">
        <h1 style="font-size: 24px; margin-bottom: 20px;">Confirm Your Subscription</h1>
        
        <p>Thanks for subscribing to our newsletter! Please confirm your email address by clicking the button below:</p>
        
        <div style="margin: 30px 0; text-align: center;">
            <a href="{{ route('newsletter.confirm', $lead->confirmation_token) }}" 
               style="display: inline-block; background: #2563eb; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600;">
                Confirm Subscription
            </a>
        </div>
        
        <p style="color: #666; font-size: 14px;">Or copy and paste this link into your browser:</p>
        <p style="color: #2563eb; font-size: 14px; word-break: break-all;">{{ route('newsletter.confirm', $lead->confirmation_token) }}</p>
        
        <hr style="border: none; border-top: 1px solid #e5e5e5; margin: 30px 0;">
        
        <p style="color: #666; font-size: 12px;">If you didn't subscribe to this newsletter, you can safely ignore this email.</p>
    </div>
</body>
</html>