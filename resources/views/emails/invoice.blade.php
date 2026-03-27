<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->invoice_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #0f172a; color: white; padding: 30px; border-radius: 10px 10px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">INVOICE</h1>
        <p style="margin: 5px 0 0 0; opacity: 0.8;">{{ $invoice->invoice_number }}</p>
    </div>
    
    <div style="background: #f8fafc; padding: 30px; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 10px 10px;">
        <p>Dear {{ $invoice->client_name }},</p>
        
        <p>Thank you for your business. Please find below your invoice details:</p>
        
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">
                    <strong>Invoice Number:</strong>
                </td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0; text-align: right;">
                    {{ $invoice->invoice_number }}
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">
                    <strong>Amount:</strong>
                </td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 18px;">
                    <strong>₦{{ number_format($invoice->amount, 2) }}</strong>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">
                    <strong>Expires:</strong>
                </td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0; text-align: right;">
                    {{ $invoice->expires_at->format('M d, Y H:i') }}
                </td>
            </tr>
        </table>
        
        @if($invoice->description)
        <p><strong>Description:</strong></p>
        <p>{{ $invoice->description }}</p>
        @endif
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $paymentUrl }}" style="display: inline-block; background: #3b82f6; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;">
                Pay Now
            </a>
        </div>
        
        <p style="font-size: 14px; color: #64748b; text-align: center;">
            This invoice expires in 24 hours. Please complete your payment before expiration.
        </p>
        
        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 30px 0;">
        
        <p style="font-size: 14px; color: #64748b;">
            If you have any questions, please contact us at support@joala.com.ng
        </p>
        
        <p style="font-size: 14px; color: #64748b;">
            Best regards,<br>
            JoAla Team
        </p>
    </div>
</body>
</html>
