<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Received - Invoice {{ $invoice->invoice_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #10b981; color: white; padding: 30px; border-radius: 10px 10px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">Payment Received</h1>
        <p style="margin: 5px 0 0 0; opacity: 0.9;">Thank you for your payment!</p>
    </div>
    
    <div style="background: #f8fafc; padding: 30px; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 10px 10px;">
        <p>Dear {{ $invoice->client_name }},</p>
        
        <p>We have received your payment. Thank you for your business!</p>
        
        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 8px 0; color: #64748b;">Invoice Number</td>
                    <td style="padding: 8px 0; text-align: right; font-weight: bold;">
                        {{ $invoice->invoice_number }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b;">Amount Paid</td>
                    <td style="padding: 8px 0; text-align: right; font-weight: bold; color: #10b981;">
                        ₦{{ number_format($invoice->amount_paid, 2) }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b;">Date Paid</td>
                    <td style="padding: 8px 0; text-align: right;">
                        {{ $invoice->paid_at->format('M d, Y H:i') }}
                    </td>
                </tr>
            </table>
        </div>
        
        <p style="font-size: 14px; color: #64748b;">
            If you have any questions about this payment, please contact us at support@joala.com.ng
        </p>
        
        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 30px 0;">
        
        <p style="font-size: 14px; color: #64748b;">
            Best regards,<br>
            JoAla Team
        </p>
    </div>
</body>
</html>
