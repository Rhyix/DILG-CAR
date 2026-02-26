<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>DILG-CAR Acknowledgement Receipt</title>
  <style>
    body {
      margin: 0;
      padding: 24px 12px;
      background: #f2f2f2;
      font-family: "Times New Roman", Times, serif;
      color: #111111;
    }

    .page {
      max-width: 840px;
      margin: 0 auto;
      background: #ffffff;
      border: 1px solid #444444;
      padding: 18px 18px 22px;
    }

    .header-table,
    .receipt-table,
    .qs-table,
    .action-table,
    .sign-table,
    .details-table {
      width: 100%;
      border-collapse: collapse;
    }

    .header-table td {
      vertical-align: middle;
    }

    .logo-cell {
      width: 72px;
      padding-right: 10px;
    }

    .logo-box {
      width: 64px;
      height: 64px;
      border: 1px solid #777777;
      border-radius: 50%;
      text-align: center;
      margin: 0 auto;
      overflow: hidden;
      background: #ffffff;
    }

    .logo-img {
      display: block;
      width: 100%;
      height: 100%;
      border: 0;
      object-fit: contain;
    }

    .office-name {
      margin: 0;
      font-size: 12px;
      letter-spacing: 0.4px;
      text-transform: uppercase;
    }

    .receipt-title {
      margin: 2px 0 0;
      font-size: 24px;
      letter-spacing: 0.8px;
      text-transform: uppercase;
      font-weight: 700;
    }

    .intro {
      margin: 12px 0 12px;
      font-size: 13px;
      line-height: 1.35;
    }

    .line-field {
      display: inline-block;
      border-bottom: 1px solid #333333;
      min-width: 185px;
      padding: 0 3px;
      font-weight: 700;
    }

    .receipt-table,
    .qs-table,
    .action-table {
      border: 1px solid #444444;
      margin-top: 8px;
    }

    .receipt-table th,
    .receipt-table td,
    .qs-table th,
    .qs-table td,
    .action-table th,
    .action-table td {
      border: 1px solid #444444;
      padding: 6px 7px;
      font-size: 12px;
      vertical-align: top;
      white-space: normal !important;
      word-break: break-word;
      overflow-wrap: anywhere;
      height: auto !important;
      max-height: none !important;
      overflow: visible !important;
    }

    .receipt-table th,
    .qs-table th,
    .action-table th {
      font-weight: 700;
      text-align: left;
      background: #f3f3f3;
    }

    .mark-col {
      width: 74px;
      text-align: center;
      font-weight: 700;
    }

    .remarks-col {
      width: 35%;
    }

    .center {
      text-align: center;
    }

    .section-label {
      margin: 12px 0 6px;
      font-size: 12px;
    }

    .status-label {
      text-align: center;
      font-weight: 700;
      font-size: 13px;
    }

    .action-text {
      margin: 0;
      line-height: 1.35;
      font-size: 12px;
    }

    .action-text + .action-text {
      margin-top: 6px;
    }

    .details-table {
      margin-top: 8px;
    }

    .details-table td {
      font-size: 12px;
      padding: 2px 0;
      vertical-align: top;
    }

    .details-label {
      width: 165px;
      font-weight: 700;
    }

    .sign-table {
      margin-top: 14px;
    }

    .sign-table td {
      width: 50%;
      font-size: 12px;
      vertical-align: top;
      padding: 3px 6px;
    }

    .sign-line {
      border-bottom: 1px solid #333333;
      min-height: 18px;
      margin-top: 12px;
    }

    .sign-note {
      margin-top: 3px;
      font-size: 11px;
      color: #333333;
    }

    .email-actions {
      margin-top: 14px;
      border-top: 1px solid #dddddd;
      padding-top: 10px;
      font-size: 12px;
      line-height: 1.4;
    }

    .email-actions a {
      color: #0b3b87;
      text-decoration: underline;
    }

    .footnote {
      margin-top: 10px;
      font-size: 11px;
      color: #333333;
    }
  </style>
</head>

<body>
  @php
    $normalizedDocuments = collect($documents ?? [])->map(function ($doc) {
      return [
        'name' => $doc['name'] ?? $doc['text'] ?? $doc['id'] ?? 'N/A',
        'status' => strtolower(trim((string) ($doc['status'] ?? ''))),
        'remarks' => trim((string) ($doc['remarks'] ?? '')),
      ];
    });

    $verifiedStatuses = ['verified', 'okay/confirmed', 'confirmed', 'approved', 'ok', 'uni'];
    $revisionStatuses = ['needs revision', 'disapproved with deficiency', 'rejected', 'ggs'];

    $hasRevisions = $normalizedDocuments->contains(function ($doc) use ($revisionStatuses) {
      return in_array($doc['status'], $revisionStatuses, true);
    });

    $allDocumentsVerified = $normalizedDocuments->count() > 0 &&
      $normalizedDocuments->every(function ($doc) use ($verifiedStatuses) {
        return in_array($doc['status'], $verifiedStatuses, true);
      });

    $isQualified = strtolower(trim((string) ($qs_result ?? ''))) === 'qualified';
    $showActionRequirements = (!$isQualified || $hasRevisions);
    $documentSubmissionStatus = $showActionRequirements ? 'INCOMPLETE' : 'COMPLETE';

    $formatQsValue = function ($value) {
      $normalized = strtolower(trim((string) $value));
      if (in_array($normalized, ['yes', 'qualified', 'pass', 'passed'], true)) {
        return 'YES';
      }
      if (in_array($normalized, ['na', 'n/a', 'not applicable'], true)) {
        return 'N/A';
      }
      return 'NO';
    };

    $qsEducationValue = $formatQsValue($qs_education ?? 'no');
    $qsEligibilityValue = $formatQsValue($qs_eligibility ?? 'no');
    $qsExperienceValue = $formatQsValue($qs_experience ?? 'no');
    $qsTrainingValue = $formatQsValue($qs_training ?? 'no');
    $overallQsMark = $isQualified ? 'YES (✓)' : 'NO (✕)';

    $displayDeadline = !empty($deadline) && $deadline !== 'No deadline set' ? $deadline : null;
    $displayRemarks = trim((string) ($application_remarks ?? ''));

    $sortedDocuments = $normalizedDocuments->sortBy(function ($doc) {
      return strtolower($doc['name']);
    })->values();

    $logoPath = public_path('images/dilg_logo.png');
    $logoSrc = asset('images/dilg_logo.png');
    if (isset($message) && is_object($message) && file_exists($logoPath)) {
      try {
        $logoSrc = $message->embed($logoPath);
      } catch (\Throwable $e) {
        $logoSrc = asset('images/dilg_logo.png');
      }
    }
  @endphp

  <div class="page">
    <table class="header-table" role="presentation">
      <tr>
        <td class="logo-cell">
          <div class="logo-box">
            <img src="{{ $logoSrc }}" alt="DILG Logo" class="logo-img">
          </div>
        </td>
        <td>
          <p class="office-name">DILG - Cordillera Administrative Region</p>
          <p class="receipt-title">Acknowledgement Receipt</p>
        </td>
      </tr>
    </table>

    <p class="intro">
      This is to acknowledge receipt of the application documents of Mr./Ms.
      <span class="line-field">{{ $applicant_name ?? 'Applicant' }}</span>
      for the vacant
      <span class="line-field">{{ $position_title ?? 'N/A' }}</span>
      position.
    </p>

    <table class="receipt-table" role="presentation">
      <thead>
        <tr>
          <th>Required Documents</th>
          <th class="mark-col">(✓ or ✕)</th>
          <th class="remarks-col">Remarks</th>
        </tr>
      </thead>
      <tbody>
        @if($sortedDocuments->isEmpty())
          <tr>
            <td colspan="3" class="center">No document records available.</td>
          </tr>
        @else
          @foreach($sortedDocuments as $doc)
            @php
              $mark = '-';
              if (in_array($doc['status'], $verifiedStatuses, true)) {
                $mark = '✓';
              } elseif (in_array($doc['status'], $revisionStatuses, true)) {
                $mark = '✕';
              }

              $remarksText = $doc['remarks'] !== '' && strtolower($doc['remarks']) !== 'no remarks provided.'
                ? $doc['remarks']
                : '-';
            @endphp
            <tr>
              <td>{{ $doc['name'] }}</td>
              <td class="center"><strong>{{ $mark }}</strong></td>
              <td>{{ $remarksText }}</td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>

    <p class="section-label">
      Is the applicant qualified and has met the required Qualification Standard (QS) of position on:
    </p>

    <table class="qs-table" role="presentation">
      <thead>
        <tr>
          <th class="center">Education<br>(Yes/No)</th>
          <th class="center">Eligibility<br>(Yes/No)</th>
          <th class="center">Experience<br>(Yes/No)</th>
          <th class="center">Training<br>(Yes/No)</th>
          <th class="center">Result<br>(Qualified)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="center">{{ $qsEducationValue }}</td>
          <td class="center">{{ $qsEligibilityValue }}</td>
          <td class="center">{{ $qsExperienceValue }}</td>
          <td class="center">{{ $qsTrainingValue }}</td>
          <td class="center">{{ $overallQsMark }}</td>
        </tr>
      </tbody>
    </table>

    <table class="action-table" role="presentation">
      <thead>
        <tr>
          <th style="width: 30%;">Documents Submitted</th>
          <th>Action Required from the Applicant</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="status-label">{{ $documentSubmissionStatus }}</td>
          <td>
            @if($showActionRequirements)
              <p class="action-text">Please comply with all deficiencies noted in the checklist above.</p>
              @if($displayDeadline)
                <p class="action-text"><strong>Submission deadline:</strong> {{ $displayDeadline }}</p>
              @endif
              @if($displayRemarks !== '')
                <p class="action-text"><strong>Remarks:</strong> {{ $displayRemarks }}</p>
              @endif
            @else
              <p class="action-text">No further action required. Wait for further instruction on the next assessment phase.</p>
            @endif
          </td>
        </tr>
      </tbody>
    </table>

    <table class="details-table" role="presentation">
      <tr>
        <td class="details-label">Job Applied:</td>
        <td>{{ $position_title ?? 'N/A' }}</td>
      </tr>
      <tr>
        <td class="details-label">Place of Assignment:</td>
        <td>{{ $place_of_assignment ?? 'N/A' }}</td>
      </tr>
      <tr>
        <td class="details-label">Monthly Compensation:</td>
        <td>PHP {{ number_format($compensation ?? 0, 2) }}</td>
      </tr>
      <tr>
        <td class="details-label">Document Progress:</td>
        <td>{{ $progress_count ?? '0/0' }} verified ({{ $progress_percentage ?? 0 }}%)</td>
      </tr>
    </table>

    <table class="sign-table" role="presentation">
      <tr>
        <td>Reviewed by:</td>
        <td>Received by or emailed to:</td>
      </tr>
      <tr>
        <td>
          <div class="sign-line"></div>
          <div class="sign-note">Printed name and signature of HR personnel</div>
        </td>
        <td>
          <div class="sign-line">{{ $applicant_name ?? 'Applicant' }}</div>
          <div class="sign-note">Printed name and signature of applicant or email address</div>
        </td>
      </tr>
      <tr>
        <td>Date reviewed: {{ now()->format('F d, Y') }}</td>
        <td>Date received or emailed: {{ now()->format('F d, Y') }}</td>
      </tr>
    </table>

    <div class="email-actions">
      @if($showActionRequirements)
        Action link:
        <a href="{{ route('login.form', ['redirect' => 'application_status', 'user' => $user_id, 'vacancy' => $vacancy_id]) }}">Login to Comply</a>
        <br>
      @endif
      View full status:
      <a href="{{ route('application_status', ['user' => $user_id, 'vacancy' => $vacancy_id]) }}">Application Status Page</a>
    </div>

    <p class="footnote">
      This email serves as an electronic acknowledgement receipt from DILG-CAR Human Resources. For concerns, contact
      <a href="mailto:dilgcarcloud@gmail.com">dilgcarcloud@gmail.com</a>.
    </p>
  </div>
</body>

</html>
