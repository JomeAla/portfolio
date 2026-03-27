<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Purchase - Download Link</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #f8f9fa; border-radius: 10px; padding: 30px;">
        <h2 style="color: #1a202c; margin-top: 0;">Thank You for Your Purchase!</h2>
        
        <p>Hello {{ $order->customer_name }},</p>
        
        <p>Your payment has been successfully processed. Here are your order details:</p>
        
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <p style="margin-top: 0;"><strong>Order Number:</strong> {{ $order->order_number }}</p>
            <p><strong>Product:</strong> {{ $order->product->title }}</p>
            <p><strong>Amount Paid:</strong> ₦{{ number_format($order->final_amount, 2) }}</p>
            <p><strong>Date:</strong> {{ $order->created_at->format('F j, Y, g:i A') }}</p>
        </div>
        
        <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #92400e;">⚠️ Important - Download Link</h3>
            <p style="margin-bottom: 0;">Your download link is valid for <strong>24 hours</strong>. Click the button below to download your product:</p>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $downloadUrl }}" style="display: inline-block; background: #3b82f6; color: #fff; text-decoration: none; padding: 15px 30px; border-radius: 8px; font-weight: bold;">
                Download Your Product
            </a>
        </div>
        
        <p style="color: #718096; font-size: 14px;">If the button doesn't work, copy and paste this link into your browser:</p>
        <p style="background: #f1f5f9; padding: 10px; border-radius: 5px; word-break: break-all; font-size: 12px;">{{ $downloadUrl }}</p>
        
        <p><strong>Note:</strong> This download link will expire on {{ $order->download_expires_at->format('F j, Y, g:i A') }}.</p>
        
        <p>If you have any issues or need assistance, please don't hesitate to contact us.</p>
        
        <p>Best regards,<br>JoAla Team</p>
    </div>
    
    <p style="text-align: center; color: #718096; font-size: 12px; margin-top: 20px;">
        © {{ date('Y') }} JoAla. All rights reserved.
    </p>
</body>
</html>
