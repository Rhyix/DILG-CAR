Hello,

An automated backup for the {{ $databaseName }} database has been generated and attached to this email.

Attachment: {{ $fileName }}
Format: {{ $wasEncrypted ? 'Encrypted backup (.enc)' : 'SQL backup (.sql)' }}

@if ($wasEncrypted)
The backup attachment is password-protected. Use the configured scheduler password to decrypt it.

@endif
This message was sent automatically by the DILG-CAR backup scheduler.
