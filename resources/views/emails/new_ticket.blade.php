<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Contact Form Submission</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #3b82f6; color: white; padding: 30px; border-radius: 10px 10px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">New Contact Form Submission</h1>
    </div>
    
    <div style="background: #f8fafc; padding: 30px; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 10px 10px;">
        <p>You have received a new contact form submission:</p>
        
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;"><strong>Name:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">{{ $ticket->name }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;"><strong>Email:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">{{ $ticket->email }}</td>
            </tr>
            @if($ticket->phone)
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;"><strong>Phone:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">{{ $ticket->phone }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;"><strong>Subject:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">{{ $ticket->subject }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0; vertical-align: top;"><strong>Message:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">{{ $ticket->message }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 0;"><strong>Ticket #:</strong></td>
                <td style="padding: 10px 0;">{{ $ticket->ticket_number }}</td>
            </tr>
        </table>
        
        <div style="margin-top: 20px;">
            <a href="{{ url('/admin/support') }}" style="display: inline-block; background: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;">
                View in Admin Panel
            </a>
        </div>
    </div>
</body>
</html>
