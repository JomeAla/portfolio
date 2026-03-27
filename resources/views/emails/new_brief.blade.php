<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Project Brief</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #10b981; color: white; padding: 30px; border-radius: 10px 10px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">New Project Brief</h1>
    </div>
    
    <div style="background: #f8fafc; padding: 30px; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 10px 10px;">
        <p>You have received a new project brief:</p>
        
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;"><strong>Name:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">{{ $brief->name }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;"><strong>Email:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">{{ $brief->email }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;"><strong>Phone:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">{{ $brief->phone }}</td>
            </tr>
            @if($brief->company)
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;"><strong>Company:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">{{ $brief->company }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;"><strong>Project Type:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">{{ $brief->project_type }}</td>
            </tr>
            @if($brief->budget_range)
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;"><strong>Budget:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">{{ $brief->budget_range }}</td>
            </tr>
            @endif
            @if($brief->timeline)
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;"><strong>Timeline:</strong></td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">{{ $brief->timeline }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 10px 0; vertical-align: top;"><strong>Description:</strong></td>
                <td style="padding: 10px 0;">{{ $brief->description }}</td>
            </tr>
        </table>
        
        <div style="margin-top: 20px;">
            <a href="{{ url('/admin/briefs') }}" style="display: inline-block; background: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;">
                View in Admin Panel
            </a>
        </div>
    </div>
</body>
</html>
