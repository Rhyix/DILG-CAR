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
      margin-bottom: 10px;
    }
    .status-item {
        margin-bottom: 8px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 8px;
    }
    .status-item:last-child {
        border-bottom: none;
    }
    .status-label {
        font-weight: 600;
        color: #002C63;
        display: block;
    }
    .status-value {
        font-weight: bold;
    }
    .status-verified { color: #00730A; }
    .status-revision { color: #BC0000; }
    .status-pending { color: #E47E00; }
    
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
    .remarks-text {
        font-size: 13px;
        font-style: italic;
        color: #555;
        margin-top: 2px;
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Header -->
    <div class="header">
      <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c9/Department_of_the_Interior_and_Local_Government_%28DILG%29_Seal_-_Logo.svg/2048px-Department_of_the_Interior_and_Local_Government_%28DILG%29_Seal_-_Logo.svg.png" alt="DILG Logo" class="logo" />
      <div class="title-text">
        <h2>DILG - CAR<br>Recruitment Selection and Placement Portal</h2>
      </div>
    </div>

    <!-- Banner -->
    <div class="banner">
      <img src="https://cdn-icons-png.flaticon.com/512/1827/1827392.png" alt="Notification Icon" />
      Document Status Overview
    </div>

    <!-- Content -->
    <div class="content">
      <p>Hello {{ $applicant_name ?? 'Applicant' }},</p>

      <p>
        Here is the latest status overview of your submitted documents for the position of 
        <strong>{{ $position_title ?? '[Position Title]' }}</strong>.
      </p>
      
      @if(!empty($application_remarks))
      <div class="status-box" style="background-color: #e6f0ff; border-color: #b3d1ff;">
          <h3>Admin Remarks</h3>
          <p>{{ $application_remarks }}</p>
      </div>
      @endif

      <div class="status-box">
        <h3>Document Statuses</h3>
        @foreach ($documents as $doc)
            <div class="status-item">
                <span class="status-label">{{ $doc['name'] }}</span>
                Status: 
                @if($doc['status'] == 'Verified' || $doc['status'] == 'Okay/Confirmed')
                    <span class="status-value status-verified">VERIFIED</span>
                @elseif($doc['status'] == 'Needs Revision' || $doc['status'] == 'Disapproved With Deficiency')
                    <span class="status-value status-revision">NEEDS REVISION</span>
                @else
                    <span class="status-value status-pending">{{ strtoupper($doc['status']) }}</span>
                @endif
                
                @if(!empty($doc['remarks']) && $doc['remarks'] != 'No remarks provided.' && $doc['remarks'] != $doc['original_name'])
                    <div class="remarks-text">Remark: {{ $doc['remarks'] }}</div>
                @endif
            </div>
        @endforeach
      </div>

      <p>
        To view the full details and update your documents if needed, please click the button below:
      </p>

      <a href="{{ route('application_status', ['user' => $user_id, 'vacancy' => $vacancy_id]) }}" class="status-link">View My Application</a>

      <p class="note">
        If the button above does not work, copy and paste this link into your browser:<br>
        <span style="word-break: break-all;">{{ route('application_status', ['user' => $user_id, 'vacancy' => $vacancy_id]) }}</span>
      </p>

      <p>
        If you have any questions, feel free to reach out via email at 
        <a href="mailto:dilgcarcloud@gmail.com">dilgcarcloud@gmail.com</a>.
      </p>

      <p>
        Thank you.<br>
        <strong>– DILG-CAR HR</strong>
      </p>
    </div>
  </div>
</body>
</html>
