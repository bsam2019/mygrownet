<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc2626; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 20px; border: 1px solid #e5e7eb; }
        .alert-box { background: #fef2f2; border-left: 4px solid #dc2626; padding: 15px; margin: 15px 0; }
        .details { background: white; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .detail-row { padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .detail-label { font-weight: bold; color: #6b7280; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
        .button { display: inline-block; padding: 12px 24px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">🚨 Security Alert</h1>
            <p style="margin: 5px 0 0 0;">Suspicious Activity Detected</p>
        </div>
        
        <div class="content">
            <div class="alert-box">
                <strong>⚠️ Action Required</strong>
                <p>Suspicious activity has been detected on your {{ $companyName }} account.</p>
            </div>

            <div class="details">
                <h3 style="margin-top: 0;">Activity Details</h3>
                
                <div class="detail-row">
                    <span class="detail-label">Activity Type:</span>
                    <span>{{ ucwords(str_replace('_', ' ', $activity['activity_type'])) }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Description:</span>
                    <span>{{ $activity['description'] }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">IP Address:</span>
                    <span>{{ $activity['ip_address'] }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Detected At:</span>
                    <span>{{ $activity['detected_at'] }}</span>
                </div>
                
                @if(isset($activity['user_name']))
                <div class="detail-row">
                    <span class="detail-label">User:</span>
                    <span>{{ $activity['user_name'] }}</span>
                </div>
                @endif
            </div>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/cms/security/suspicious-activity" class="button">
                    Review Activity
                </a>
            </div>

            <div style="margin-top: 20px; padding: 15px; background: #eff6ff; border-radius: 4px;">
                <strong>What should I do?</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Review the activity details in your security dashboard</li>
                    <li>Verify if this was a legitimate action</li>
                    <li>Mark as reviewed or take appropriate action</li>
                    <li>Consider changing passwords if unauthorized access is suspected</li>
                </ul>
            </div>
        </div>

        <div class="footer">
            <p>This is an automated security alert from {{ $companyName }}.</p>
            <p>If you did not perform this action, please contact your administrator immediately.</p>
        </div>
    </div>
</body>
</html>
