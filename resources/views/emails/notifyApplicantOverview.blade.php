<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Application Document Status</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      background-color: #f9fafb;
      -webkit-font-smoothing: antialiased;
    }

    .container {
      max-width: 600px;
      margin: 40px auto;
      background: #ffffff;
      border-radius: 12px;
      overflow: hidden;
      border: 1px solid #e5e7eb;
    }

    .header {
      padding: 30px;
      text-align: center;
      border-bottom: 1px solid #f3f4f6;
    }

    .logo {
      width: 64px;
      height: 64px;
      margin-bottom: 16px;
    }

    h1 {
      font-size: 20px;
      font-weight: 700;
      color: #111827;
      margin: 0 0 4px 0;
      letter-spacing: -0.025em;
    }

    .subtitle {
      font-size: 12px;
      color: #6b7280;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      font-weight: 600;
      margin: 0;
    }

    .content {
      padding: 32px;
      color: #374151;
      font-size: 14px;
      line-height: 1.6;
    }

    .greeting {
      font-size: 16px;
      font-weight: 600;
      color: #111827;
      margin-bottom: 16px;
    }

    .section {
      margin-top: 32px;
    }

    .section-title {
      font-size: 11px;
      font-weight: 700;
      color: #9ca3af;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      margin-bottom: 12px;
    }

    .grid {
      display: table;
      width: 100%;
    }

    .grid-row {
      display: table-row;
    }

    .grid-cell {
      display: table-cell;
      padding-bottom: 16px;
      width: 50%;
    }

    .label {
      font-size: 11px;
      color: #6b7280;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 4px;
      font-weight: 600;
    }

    .value {
      font-size: 14px;
      font-weight: 600;
      color: #111827;
    }

    .card {
      background-color: #f9fafb;
      border: 1px solid #f3f4f6;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 16px;
    }

    .qs-items {
      display: table;
      width: 100%;
      margin-bottom: 16px;
    }

    .qs-item {
      display: table-cell;
      text-align: center;
      font-size: 12px;
      font-weight: 500;
      color: #4b5563;
    }

    .indicator {
      display: inline-block;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      margin-right: 6px;
    }

    .bg-green {
      background-color: #10B981;
    }

    .bg-red {
      background-color: #EF4444;
    }

    .bg-gray {
      background-color: #9CA3AF;
    }

    .result {
      text-align: center;
      font-size: 14px;
      font-weight: 700;
      padding-top: 16px;
      border-top: 1px solid #e5e7eb;
    }

    .text-green {
      color: #10B981;
    }

    .text-red {
      color: #EF4444;
    }

    .progress-bar-bg {
      background-color: #e5e7eb;
      border-radius: 9999px;
      height: 8px;
      width: 100%;
      overflow: hidden;
      margin: 12px 0;
    }

    .progress-bar-fill {
      background-color: #002C76;
      height: 100%;
    }

    .progress-stats {
      display: table;
      width: 100%;
    }

    .progress-stats-left {
      display: table-cell;
      font-size: 18px;
      font-weight: 700;
      color: #002C76;
    }

    .progress-stats-right {
      display: table-cell;
      text-align: right;
      font-size: 12px;
      color: #6b7280;
      font-weight: 500;
    }

    .remarks-box {
      background-color: #eff6ff;
      border: 1px solid #bfdbfe;
      border-radius: 8px;
      padding: 20px;
      margin-top: 16px;
    }

    .remarks-text {
      font-size: 14px;
      color: #1e3a8a;
      margin: 0;
      white-space: pre-wrap;
    }

    .docs-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 8px;
      font-size: 13px;
    }

    .docs-table th {
      text-align: left;
      padding: 12px 16px;
      background-color: #f9fafb;
      border-bottom: 1px solid #e5e7eb;
      font-size: 11px;
      font-weight: 600;
      color: #6b7280;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .docs-table td {
      padding: 16px;
      border-bottom: 1px solid #f3f4f6;
      vertical-align: top;
    }

    .status-badge {
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 0.05em;
      text-transform: uppercase;
    }

    .badge-verified {
      color: #059669;
    }

    .badge-revision {
      color: #dc2626;
    }

    .badge-pending {
      color: #d97706;
    }

    .badge-gray {
      color: #9ca3af;
    }

    .doc-name {
      font-weight: 600;
      color: #111827;
      margin-bottom: 4px;
    }

    .doc-remarks {
      font-size: 12px;
      color: #6b7280;
      font-style: italic;
    }

    .footer {
      background-color: #f9fafb;
      padding: 32px;
      text-align: center;
      border-top: 1px solid #e5e7eb;
    }

    .btn {
      display: inline-block;
      background-color: #002C76;
      color: #ffffff !important;
      font-weight: 600;
      font-size: 14px;
      text-decoration: none;
      padding: 12px 24px;
      border-radius: 6px;
      margin: 8px;
    }

    .btn-outline {
      background-color: #ffffff;
      color: #374151 !important;
      border: 1px solid #d1d5db;
    }

    .footer-text {
      font-size: 12px;
      color: #6b7280;
      margin-top: 24px;
      line-height: 1.5;
    }

    .footer-link {
      color: #002C76;
      text-decoration: none;
    }

    td {
      white-space: normal !important;
      word-wrap: break-word;
    }
  </style>
</head>

<body>

  @php
    $hasRevisions = collect($documents)->contains(function ($doc) {
      return $doc['status'] == 'Needs Revision' || $doc['status'] == 'Disapproved With Deficiency';
    });
    $isQualified = ($qs_result === 'Qualified');
    $showActionRequirements = (!$isQualified || $hasRevisions);
  @endphp

  <div class="container">
    <div class="header">
      <h1>DILG - CAR</h1>
      <p class="subtitle">Recruitment Selection & Placement</p>
    </div>

    <div class="content">
      <div class="greeting">Hello {{ $applicant_name ?? 'Applicant' }},</div>
      <p>Your application for the position of <strong>{{ $position_title ?? '[Position Title]' }}</strong> has been
        reviewed. Here is the latest status overview of your application.</p>

      <div class="section">
        <div class="section-title">Job Details</div>
        <div class="grid">
          <div class="grid-row">
            <div class="grid-cell">
              <div class="label">Job Applied</div>
              <div class="value">{{ $position_title ?? 'N/A' }}</div>
            </div>
            <div class="grid-cell">
              <div class="label">Compensation</div>
              <div class="value">₱{{ number_format($compensation ?? 0, 2) }}</div>
            </div>
          </div>
          <div class="grid-row">
            <div class="grid-cell">
              <div class="label">Place of Assignment</div>
              <div class="value">{{ $place_of_assignment ?? 'N/A' }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="section">
        <div class="section-title">Qualification Standards</div>
        <div class="card">
          <div class="qs-items">
            <div class="qs-item">
              <span
                class="indicator {{ strtolower($qs_education) === 'qualified' || strtolower($qs_education) === 'yes' ? 'bg-green' : 'bg-red' }}"></span>
              Education
            </div>
            <div class="qs-item">
              <span
                class="indicator {{ strtolower($qs_eligibility) === 'qualified' || strtolower($qs_eligibility) === 'yes' ? 'bg-green' : (strtolower($qs_eligibility) === 'na' ? 'bg-gray' : 'bg-red') }}"></span>
              Eligibility
            </div>
            <div class="qs-item">
              <span
                class="indicator {{ strtolower($qs_experience) === 'qualified' || strtolower($qs_experience) === 'yes' ? 'bg-green' : (strtolower($qs_experience) === 'na' ? 'bg-gray' : 'bg-red') }}"></span>
              Experience
            </div>
            <div class="qs-item">
              <span
                class="indicator {{ strtolower($qs_training) === 'qualified' || strtolower($qs_training) === 'yes' ? 'bg-green' : (strtolower($qs_training) === 'na' ? 'bg-gray' : 'bg-red') }}"></span>
              Training
            </div>
          </div>
          <div class="result">
            Overall Result: <span
              class="{{ $isQualified ? 'text-green' : 'text-red' }}">{{ $qs_result ?? 'Not Qualified' }}</span>
          </div>
        </div>
      </div>

      <div class="section">
        <div class="section-title">Application Progress</div>
        <div class="card">
          <div class="progress-stats">
            <div class="progress-stats-left">{{ $progress_percentage ?? 0 }}%</div>
            <div class="progress-stats-right">{{ $progress_count ?? '0/17' }} Documents Verified</div>
          </div>
          <div class="progress-bar-bg">
            <div class="progress-bar-fill" style="width: {{ $progress_percentage ?? 0 }}%;"></div>
          </div>
        </div>
      </div>

      @if($showActionRequirements)
        <div class="section">
          <div class="section-title">Action Requirements</div>

          @if(!empty($deadline))
            <div class="card" style="margin-bottom: 0; padding: 16px 20px;">
              <div class="label" style="margin-bottom: 2px;">Submit compliance by</div>
              <div class="value" style="color: #dc2626;">{{ \Carbon\Carbon::parse($deadline)->format('F d, Y h:i A') }}
              </div>
            </div>
          @endif

          @if(!empty($application_remarks))
            <div class="remarks-box">
              <div class="label" style="color: #3b82f6; margin-bottom: 8px;">Applicant Remarks</div>
              <div class="remarks-text">{{ $application_remarks }}</div>
            </div>
          @endif
        </div>
      @endif

      <div class="section">
        <div class="section-title">Required Documents Overview</div>
        <div style="border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; margin-top: 12px;">
          <table class="docs-table">
            <thead>
              <tr>
                <th style="width: 50%;">Document</th>
                <th style="width: 50%;">Status & Remarks</th>
              </tr>
            </thead>
            <tbody>
              @php
                // Sort document rows alphabetically by document name.
                $sortedDocuments = collect($documents)->sortBy(function ($doc) {
                    return strtolower($doc['text'] ?? $doc['name'] ?? '');
                });
              @endphp

              @foreach ($sortedDocuments as $doc)
                @php
                  $statusClass = 'badge-pending';
                  $statusLabel = strtoupper($doc['status']);
                  if ($doc['status'] == 'Verified' || $doc['status'] == 'Okay/Confirmed') {
                    $statusClass = 'badge-verified';
                    $statusLabel = '✓';
                  } elseif ($doc['status'] == 'Needs Revision' || $doc['status'] == 'Disapproved With Deficiency') {
                    $statusClass = 'badge-revision';
                    $statusLabel = '✗';
                  } elseif ($doc['status'] == 'Not Submitted') {
                    $statusClass = 'badge-gray';
                  }
                @endphp
                <tr>
                  <td>
                    <div class="doc-name">{{ $doc['name'] }}</div>
                  </td>
                  <td>
                    <div class="status-badge {{ $statusClass }}">{{ $statusLabel }}</div>
                    @if(!empty($doc['remarks']) && $doc['remarks'] != 'No remarks provided.' && !empty(trim($doc['remarks'])))
                      <div class="doc-remarks" style="margin-top: 4px;">{{ $doc['remarks'] }}</div>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="footer">
      <div style="margin-bottom: 24px;">
        @if($showActionRequirements)
          <a href="{{ route('login.form', ['redirect' => 'application_status', 'user' => $user_id, 'vacancy' => $vacancy_id]) }}"
            class="btn">Login to Comply</a>
        @endif
        <a href="{{ route('application_status', ['user' => $user_id, 'vacancy' => $vacancy_id]) }}"
          class="btn btn-outline">View Full Status</a>
      </div>
      <p class="footer-text">
        If you have any questions, please contact us at <a href="mailto:dilgcarcloud@gmail.com"
          class="footer-link">dilgcarcloud@gmail.com</a>.<br>
        Thank you, <strong>DILG-CAR Human Resources</strong>
      </p>
    </div>
  </div>

</body>

</html>
