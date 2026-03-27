<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Ticket Response</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #f8f9fa; border-radius: 10px; padding: 30px;">
        <h2 style="color: #1a202c; margin-top: 0;">Support Ticket Response</h2>
        
        <p>Hello {{ $ticket->name }},</p>
        
        <p>Thank you for contacting us. Here is our response to your ticket:</p>
        
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <p style="margin-top: 0;"><strong>Your Message:</strong></p>
            <p style="color: #4a5568;">{{ $ticket->message }}</p>
            
            <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
            
            <p style="margin-top: 0;"><strong>Our Response:</strong></p>
            <p style="color: #2d3748;">{{ $ticket->admin_response }}</p>
        </div>
        
        <p><strong>Ticket Status:</strong> {{ ucfirst($ticket->status) }}</p>
        <p><strong>Ticket ID:</strong> {{ $ticket->ticket_number }}</p>
        
        <p>If you have any further questions, please don't hesitate to contact us.</p>
        
        <p>Best regards,<br>JoAla Support Team</p>
    </div>
    
    <p style="text-align: center; color: #718096; font-size: 12px; margin-top: 20px;">
        © {{ date('Y') }} JoAla. All rights reserved.
    </p>
</body>
</html>
