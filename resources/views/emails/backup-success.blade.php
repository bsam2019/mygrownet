@extends('emails.layout')

@section('content')
    <h2 class="email-title">✅ Backup Successful</h2>
    
    <p class="email-text">
        Great news, a new backup of <strong>{{ $applicationName }}</strong> was successfully created on the disk named <strong>{{ $diskName }}</strong>.
    </p>

    <table class="details-table">
        <tr>
            <td class="details-label">Application name</td>
            <td class="details-value">{{ $applicationName }}</td>
        </tr>
        <tr>
            <td class="details-label">Backup name</td>
            <td class="details-value">MyGrowNet</td>
        </tr>
        <tr>
            <td class="details-label">Disk</td>
            <td class="details-value">{{ $diskName }}</td>
        </tr>
        <tr>
            <td class="details-label">Newest backup size</td>
            <td class="details-value">{{ number_format($newestBackupSize / 1024 / 1024, 2) }} MB</td>
        </tr>
        <tr>
            <td class="details-label">Number of backups</td>
            <td class="details-value">{{ $numberOfBackups }}</td>
        </tr>
        <tr>
            <td class="details-label">Total storage used</td>
            <td class="details-value">{{ number_format($totalStorageUsed / 1024 / 1024, 2) }} MB</td>
        </tr>
        <tr>
            <td class="details-label">Newest backup date</td>
            <td class="details-value">{{ $newestBackupDate?->format('Y/m/d H:i:s') ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="details-label">Oldest backup date</td>
            <td class="details-value">{{ $oldestBackupDate?->format('Y/m/d H:i:s') ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="info-box info-box-success">
        <p><strong>All systems operational.</strong> Your data is safely backed up and secure.</p>
    </div>

    <p class="email-text" style="margin-top: 32px; color: #6b7280; font-size: 14px;">
        Regards,<br>
        <strong>MyGrowNet</strong>
    </p>
@endsection
