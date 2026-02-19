<div style="font-family: Montserrat, Arial, sans-serif; background:#f7fafc; padding:24px;">
  <div style="max-width:720px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:16px; box-shadow:0 8px 20px rgba(0,0,0,0.06); overflow:hidden;">
    <div style="background:#0D2B70; color:#fff; padding:20px 24px;">
      <h1 style="margin:0; font-size:20px;">Admin Notification</h1>
      <p style="margin:4px 0 0 0; font-size:13px; opacity:.9;">Applicant Notification Triggered</p>
    </div>
    <div style="padding:24px;">
      <h2 style="margin:0 0 10px 0; font-size:18px; color:#0D2B70;">Action Details</h2>
      <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
        <tr>
          <td style="padding:6px 0; font-weight:700; color:#374151; width:180px;">Timestamp</td>
          <td style="padding:6px 0; color:#111827;">{{ $timestamp }} ({{ $timezone }})</td>
        </tr>
        <tr>
          <td style="padding:6px 0; font-weight:700; color:#374151;">Initiated By</td>
          <td style="padding:6px 0; color:#111827;">{{ $actorName }}</td>
        </tr>
        <tr>
          <td style="padding:6px 0; font-weight:700; color:#374151;">Applicant Name</td>
          <td style="padding:6px 0; color:#111827;">{{ $applicantName }}</td>
        </tr>
        <tr>
          <td style="padding:6px 0; font-weight:700; color:#374151;">Vacancy</td>
          <td style="padding:6px 0; color:#111827;">{{ $positionTitle }} ({{ $vacancyId }})</td>
        </tr>
      </table>

      <h2 style="margin:18px 0 8px 0; font-size:18px; color:#0D2B70;">Verified Documents</h2>

      @if(empty($documents))
        <p style="margin:0; color:#6b7280;">No documents were marked Verified or Needs Revision.</p>
      @else
        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; margin-top:8px;">
          <thead>
            <tr style="background:#f3f4f6; color:#111827;">
              <th align="left" style="padding:10px; font-size:12px; text-transform:uppercase; letter-spacing:.03em;">Document Type</th>
              <th align="left" style="padding:10px; font-size:12px; text-transform:uppercase; letter-spacing:.03em;">Document ID</th>
              <th align="left" style="padding:10px; font-size:12px; text-transform:uppercase; letter-spacing:.03em;">Status</th>
              <th align="left" style="padding:10px; font-size:12px; text-transform:uppercase; letter-spacing:.03em;">Remarks</th>
            </tr>
          </thead>
          <tbody>
            @foreach($documents as $doc)
              <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:10px; color:#111827;">{{ $doc['name'] ?? $doc['text'] ?? $doc['id'] ?? 'N/A' }}</td>
                <td style="padding:10px; color:#111827;">{{ $doc['doc_id'] ?? 'N/A' }}</td>
                <td style="padding:10px;">
                  <span style="display:inline-block; padding:4px 8px; border-radius:9999px; font-size:12px; font-weight:700; background:#eef2ff; color:#1f2937;">
                    {{ $doc['status'] ?? 'N/A' }}
                  </span>
                </td>
                <td style="padding:10px; color:#6b7280;">{{ $doc['remarks'] ?? '' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif

      <div style="margin-top:24px; padding:16px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px; color:#374151; font-size:12px;">
        This notification was generated automatically by DILG-CAR. If you believe you received this in error, please contact the system administrator.
      </div>
    </div>
  </div>
</div>
