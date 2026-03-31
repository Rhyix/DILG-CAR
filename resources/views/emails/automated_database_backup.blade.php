<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Automated Database Backup</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <p>Hello,</p>
    <p>An automated backup for the <strong>{{ $databaseName }}</strong> database has been generated and attached to this email.</p>
    <p>
        Attachment: <strong>{{ $fileName }}</strong><br>
        Format: <strong>{{ $wasEncrypted ? 'Encrypted backup (.enc)' : 'SQL backup (.sql)' }}</strong>
    </p>
    @if ($wasEncrypted)
        <p>The backup attachment is password-protected. Use the configured scheduler password to decrypt it.</p>
    @endif
    <p>This message was sent automatically by the DILG-CAR backup scheduler.</p>
</body>
</html>
