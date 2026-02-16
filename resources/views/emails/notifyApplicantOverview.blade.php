<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Application Document Status Overview</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet" />
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Montserrat', sans-serif;
      background-color: #F3F8FF;
    }

    .container {
      max-width: 680px;
      margin: 30px auto;
      background: #FFFFFF;
      border: 1px solid #cfd9e0;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .header {
      padding: 20px 30px 10px;
      display: flex;
      align-items: center;
    }

    .logo {
      width: 60px;
      height: 60px;
      margin-right: 15px;
    }

    .title-text h2 {
      margin: 0;
      font-size: 18px;
      font-weight: 700;
      color: #002C63;
      line-height: 1.3;
    }

    .banner {
      background-color: #002C76;
      color: white;
      padding: 15px 30px;
      margin: 15px 15px 0px 15px;
      font-size: 18px;
      font-weight: 700;
      border-radius: 16px;
    }

    .content {
      padding: 0px 30px 15px 30px;
      color: #1a202c;
      font-size: 14px;
      line-height: 1.6;
    }

    .info-grid {
      display: table;
      width: 100%;
      margin: 15px 0;
      border-collapse: collapse;
    }

    .info-row {
      display: table-row;
    }

    .info-cell {
      display: table-cell;
      padding: 8px 12px;
      border: 1px solid #e2e8f0;
      vertical-align: top;
    }

    .info-label {
      font-size: 11px;
      font-weight: 600;
      color: #64748b;
      text-transform: uppercase;
      display: block;
      margin-bottom: 4px;
    }

    .info-value {
      font-size: 13px;
      font-weight: 600;
      color: #002C63;
    }

    .qs-section {
      margin: 20px 0;
      padding: 15px;
      background-color: #f8fafc;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
    }

    .qs-title {
      font-size: 13px;
      font-weight: 700;
      color: #002C63;
      margin-bottom: 10px;
    }

    .qs-items {
      display: table;
      width: 100%;
    }

    .qs-item {
      display: table-cell;
      padding: 5px;
      text-align: center;
      font-size: 11px;
    }

    .qs-indicator {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      display: inline-block;
      margin-right: 5px;
    }

    .qs-green {
      background-color: #10B981;
    }

    .qs-red {
      background-color: #EF4444;
    }

    .qs-gray {
      background-color: #9CA3AF;
    }

    .progress-section {
      margin: 20px 0;
      padding: 15px;
      background-color: #f8fafc;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
    }

    .progress-bar-container {
      width: 100%;
      height: 20px;
      background-color: #e2e8f0;
      border-radius: 10px;
      overflow: hidden;
      margin: 10px 0;
    }

    .progress-bar {
      height: 100%;
      background-color: #002C76;
      transition: width 0.3s ease;
    }

    .progress-text {
      font-size: 12px;
      color: #64748b;
      text-align: center;
      margin-top: 5px;
    }

    .doc-table {
      width: 100%;
      border-collapse: collapse;
      margin: 15px 0;
      font-size: 12px;
    }

    .doc-table th {
      background-color: #f1f5f9;
      padding: 10px 8px;
      text-align: left;
      font-weight: 700;
      color: #002C63;
      border-bottom: 2px solid #cbd5e1;
      font-size: 11px;
      text-transform: uppercase;
    }

    .doc-table td {
      padding: 10px 8px;
      border-bottom: 1px solid #e2e8f0;
    }

    .doc-name {
      font-weight: 600;
      color: #002C63;
    }

    .status-verified {
      color: #00730A;
      font-weight: 700;
    }

    .status-revision {
      color: #BC0000;
      font-weight: 700;
    }

    .status-pending {
      color: #E47E00;
      font-weight: 700;
    }

    .remarks-text {
      font-size: 11px;
      font-style: italic;
      color: #64748b;
      margin-top: 3px;
    }

    .admin-remarks-box {
      background-color: #e6f0ff;
      border: 2px solid #b3d1ff;
      border-radius: 8px;
      padding: 15px;
      margin: 20px 0;
    }

    .admin-remarks-box h3 {
      margin: 0 0 10px 0;
      font-size: 14px;
      font-weight: 700;
      color: #002C63;
    }

    .admin-remarks-box p {
      margin: 0;
      font-size: 13px;
      line-height: 1.5;
    }

    .button-container {
      margin: 25px 0;
      text-align: center;
    }

    .btn {
      display: inline-block;
      margin: 5px;
      padding: 14px 24px;
      text-decoration: none;
      font-weight: 600;
      border-radius: 8px;
      font-size: 14px;
      transition: all 0.2s;
    }

    .btn-primary {
      background-color: #002C76;
      color: white !important;
    }

    .btn-primary:hover {
      background-color: #003b9c;
    }

    .btn-secondary {
      background-color: #059669;
      color: white !important;
    }

    .btn-secondary:hover {
      background-color: #047857;
    }

    .note {
      font-size: 12px;
      color: #718096;
      margin-top: 15px;
      padding: 10px;
      background-color: #f8fafc;
      border-radius: 6px;
    }
  </style>
</head>

<body>
  <div class="container">
    <!-- Header -->
    <div class="header">
      <img
        src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c9/Department_of_the_Interior_and_Local_Government_%28DILG%29_Seal_-_Logo.svg/2048px-Department_of_the_Interior_and_Local_Government_%28DILG%29_Seal_-_Logo.svg.png"
        alt="DILG Logo" class="logo" />
      <div class="title-text">
        <h2>DILG - CAR<br>Recruitment Selection and Placement Portal</h2>
      </div>
    </div>

    <!-- Banner -->
    <div class="banner">
      Application Status Overview
    </div>

    <!-- Content -->
    <div class="content">
      <p style="margin-top: 20px;">Hello <strong>{{ $applicant_name ?? 'Applicant' }}</strong>,</p>

      <p>Your application for the position of <strong>{{ $position_title ?? '[Position Title]' }}</strong> has been
        reviewed. Please find the complete overview of your application status below.</p>

      <!-- Job Details Grid -->
      <div class="info-grid">
        <div class="info-row">
          <div class="info-cell" style="width: 50%;">
            <span class="info-label">Job Applied</span>
            <span class="info-value">{{ $position_title ?? 'N/A' }}</span>
          </div>
          <div class="info-cell" style="width: 50%;">
            <span class="info-label">Compensation</span>
            <span class="info-value">₱{{ number_format($compensation ?? 0, 2) }}</span>
          </div>
        </div>
        <div class="info-row">
          <div class="info-cell" style="width: 50%;">
            <span class="info-label">Place of Assignment</span>
            <span class="info-value">{{ $place_of_assignment ?? 'N/A' }}</span>
          </div>
          <div class="info-cell" style="width: 50%;">
            <span class="info-label">Deadline</span>
            <span class="info-value">{{ $deadline ?? 'No deadline set' }}</span>
          </div>
        </div>
      </div>

      <!-- Qualification Standards -->
      <div class="qs-section">
        <div class="qs-title">Qualification Standards</div>
        <div class="qs-items">
          @if(!isset($vacancy_type) || $vacancy_type === 'Plantilla')
            <div class="qs-item">
              <span class="qs-indicator {{ $qs_education === 'yes' ? 'qs-green' : 'qs-red' }}"></span>
              Education
            </div>
            <div class="qs-item">
              <span
                class="qs-indicator {{ $qs_eligibility === 'yes' ? 'qs-green' : ($qs_eligibility === 'na' ? 'qs-gray' : 'qs-red') }}"></span>
              Eligibility
            </div>
            <div class="qs-item">
              <span class="qs-indicator qs-gray"></span>
              Experience
            </div>
            <div class="qs-item">
              <span class="qs-indicator {{ $qs_training === 'yes' ? 'qs-green' : 'qs-red' }}"></span>
              Training
            </div>
          @else
            <div class="qs-item">
              <span class="qs-indicator {{ $qs_education === 'yes' ? 'qs-green' : 'qs-red' }}"></span>
              Education
            </div>
            <div class="qs-item">
              <span class="qs-indicator qs-gray"></span>
              Eligibility
            </div>
            <div class="qs-item">
              <span class="qs-indicator {{ $qs_experience === 'yes' ? 'qs-green' : 'qs-red' }}"></span>
              Experience
            </div>
            <div class="qs-item">
              <span class="qs-indicator {{ $qs_training === 'yes' ? 'qs-green' : 'qs-red' }}"></span>
              Training
            </div>
          @endif
        </div>
        <p style="text-align: center; margin: 10px 0 0 0; font-size: 13px;">
          <strong>Overall Result:
            <span style="color: {{ $qs_result === 'Qualified' ? '#10B981' : '#EF4444' }};">
              {{ $qs_result ?? 'Not Qualified' }}
            </span>
          </strong>
        </p>
      </div>

      <!-- Application Progress -->
      <div class="progress-section">
        <div class="qs-title">Application Progress</div>
        <div class="progress-bar-container">
          <div class="progress-bar" style="width: {{ $progress_percentage ?? 0 }}%;"></div>
        </div>
        <div class="progress-text">
          <strong>{{ $progress_percentage ?? 0 }}%</strong> Complete ({{ $progress_count ?? '0/17' }} Documents)
        </div>
      </div>

      <!-- Admin Remarks -->
      @if(!empty($application_remarks))
        <div class="admin-remarks-box">
          <h3>📋 Important Instructions</h3>
          <p>{{ $application_remarks }}</p>
        </div>
      @endif

      <!-- Required Documents Table -->
      <h3 style="color: #002C63; font-size: 14px; margin: 25px 0 10px 0;">Required Documents</h3>
      <table class="doc-table">
        <thead>
          <tr>
            <th style="width: 45%;">Document</th>
            <th style="width: 20%;">Status</th>
            <th style="width: 35%;">Remarks</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($documents as $doc)
            <tr>
              <td class="doc-name">{{ $doc['name'] }}</td>
              <td>
                @if($doc['status'] == 'Verified' || $doc['status'] == 'Okay/Confirmed')
                  <span class="status-verified">✓ VERIFIED</span>
                @elseif($doc['status'] == 'Needs Revision' || $doc['status'] == 'Disapproved With Deficiency')
                  <span class="status-revision">✗ NEEDS REVISION</span>
                @elseif($doc['status'] == 'Not Submitted')
                  <span class="status-pending">NOT SUBMITTED</span>
                @else
                  <span class="status-pending">{{ strtoupper($doc['status']) }}</span>
                @endif
              </td>
              <td>
                @if(!empty($doc['remarks']) && $doc['remarks'] != 'No remarks provided.' && !empty(trim($doc['remarks'])))
                  {{ $doc['remarks'] }}
                @else
                  <span style="color: #cbd5e1;">—</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

      <!-- Action Buttons -->
      <div class="button-container">
        <a href="{{ route('login.form', ['redirect' => 'application_status', 'user' => $user_id, 'vacancy' => $vacancy_id]) }}"
          class="btn btn-secondary">
          📄 Comply & Upload Documents
        </a>
        <br>
        <a href="{{ route('application_status', ['user' => $user_id, 'vacancy' => $vacancy_id]) }}"
          class="btn btn-primary">
          👁️ View Full Application
        </a>
      </div>

      <div class="note">
        <strong>Note:</strong> If you need to update or submit documents, click the "Comply & Upload Documents" button
        above. This will take you directly to your application page where you can upload the required files.
      </div>

      <p style="margin-top: 25px; font-size: 13px;">
        If you have any questions, feel free to reach out via email at
        <a href="mailto:dilgcarcloud@gmail.com" style="color: #002C76;">dilgcarcloud@gmail.com</a>.
      </p>

      <p style="font-size: 13px;">
        Thank you for your patience.<br>
        <strong>– DILG-CAR Human Resources</strong>
      </p>
    </div>
  </div>
</body>

</html>