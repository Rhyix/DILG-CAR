<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Application Status Update</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet" />
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Montserrat', sans-serif;
      background-color: #F3F8FF;
    }
    .container {
      max-width: 600px;
      margin: 30px auto;
      background: #FFFFFF;
      border: 1px solid #cfd9e0;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
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
      display: flex;
      align-items: center;
    }
    .banner img {
      width: 20px;
      margin-right: 10px;
      filter: brightness(0) invert(1);
    }
    .content {
      padding: 0px 30px 15px 30px;
      color: #1a202c;
      font-size: 15px;
      text-align: justify;
      line-height: 1.6;
    }
    .status-box {
      margin: 20px 0;
      background-color: #f2f2f2;
      border: 2px dashed #cfd9e0;
      border-radius: 8px;
      padding: 15px;
    }
    .status-box h3 {
      margin-top: 0;
      color: #002C63;
      font-weight: 700;
      font-size: 16px;
    }
    .status-box p {
      margin: 4px 0;
      line-height: 1.4;
    }
    .status-link {
      display: block;
      margin: 10px;
      text-align: center;
      text-decoration: none;
      padding: 12px;
      background-color: #002C76;
      color: white !important;
      font-weight: 600;
      border-radius: 8px;
      font-size: 15px;
    }
    .note {
      font-size: 13px;
      color: #718096;
      margin-top: 10px;
    }
    .footer {
      padding: 0 30px 30px;
      font-size: 13px;
      color: #2d3748;
    }
    .footer strong {
      font-weight: 700;
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Header -->
    <div class="header">
      <img class="logo" src="{{ asset('images/dilg_logo.png') }}" alt="DILG Logo" style="object-fit:contain;">
      <div class="title-text">
        <h2>DILG - CAR<br>Recruitment Selection and Placement Portal</h2>
      </div>
    </div>

    <!-- Banner -->
    <div class="banner">
      <img src="https://cdn-icons-png.flaticon.com/512/1827/1827392.png" alt="Notification Icon" />
      Application Status Update
    </div>

    <!-- Content -->
    <div class="content">
      <p>Hello {{ $applicant_name ?? 'Applicant' }},</p>

      <p>
        This is to notify you that changes have been made to your application for the position of 
        <strong>{{ $position_title ?? '[Position Title]' }}</strong>.
      </p>

      <div class="status-box">
        <h3>Summary of Changes</h3>
        <ul style="padding-left: 20px; margin: 0;">
          <li><strong>Date of Change:</strong> {{ $date ?? '[Date not provided]' }}</p>
          <li><strong>Status:<strong> {{ $status ?? '—' }}</strong></li>
          <li><strong>Changes:</strong>
              <ul>
                  @foreach ($changes as $field => $change)
                      <li>
                          <strong>{{ ucfirst(str_replace('_', ' ', $field)) }}</strong>:
                          {{ $change['old'] ?? 'N/A' }} → {{ $change['new'] ?? 'N/A' }}
                      </li>
                  @endforeach
              </ul>
          </li>
          <li><strong>Admin:</strong> {{ $admin_name ?? 'Admin' }}</strong></li>
        </ul>
      </div>

      <p>
        To view the full details of your application, please click the button below:
      </p>

      <a href="{{ route('application_status', ['user' => $user_id, 'vacancy' => $vacancy_id]) }}" class="status-link">View My Application</a>

      <p class="note">
        If the button above does not work, copy and paste this link into your browser:<br>
        <span style="word-break: break-all;">{{ route('application_status', ['user' => $user_id, 'vacancy' => $vacancy_id]) }}</span>
      </p>

      <p>
        If you have any questions or concerns, feel free to reach out via email at 
        <a href="mailto:dilgcarcloud@gmail.com">dilgcarcloud@gmail.com</a> or 
        <a href="mailto:dilgcar.hr@gmail.com">dilgcar.hr@gmail.com</a>.
      </p>

      <p>
        Thank you for your continued interest in joining our team.<br>
        <strong>– DILG-CAR</strong>
      </p>
    </div>
  </div>
</body>
</html>
