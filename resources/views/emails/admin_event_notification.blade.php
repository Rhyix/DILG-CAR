<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>DILG-CAR Admin Notification</title>
    <style>
        body { font-family: Arial, sans-serif; color: #0D2B70; }
        .container { max-width: 640px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 12px; }
        .title { font-weight: 700; font-size: 18px; }
        .muted { color: #6b7280; font-size: 12px; }
        .btn { display: inline-block; padding: 8px 14px; background: #0D2B70; color: #fff; border-radius: 9999px; text-decoration: none; font-weight: 700; }
    </style>
</head>
<body>
    <div class="container">
        <p class="muted">Timestamp: {{ \Carbon\Carbon::parse($occurredAt)->format('M d, Y h:i A') }}</p>
        <img class="logo" src="{{ asset('images/dilg_logo.png') }}" alt="DILG-CAR">
        <p class="title">{{ $title }}</p>
        <p>{{ $body }}</p>
        @if($positionTitle)
        <p><strong>Position:</strong> {{ $positionTitle }} ({{ $vacancyId }})</p>
        @endif
        @endif
        @if($applicantName)
        <p><strong>Applicant:</strong> {{ $applicantName }}</p>
        @endif
        <p class="muted">By: {{ $actorName }}</p>
        @if($link)
        <p><a class="btn" href="{{ $link }}">View Details</a></p>
        @endif
        <p class="muted">This is an automated email from DILG-CAR RHRMSPB.</p>
    </div>
</body>
</html>
