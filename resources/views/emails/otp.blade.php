<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>OTP Verification</title>
  <!-- Google Fonts: Montserrat -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
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

    .title-text {
      color: #002c63;
    }

    .title-text h2 {
      margin: 0;
      font-size: 18px;
      font-weight: 700;
      line-height: 1.3;
    }

    .banner {
      background-color: #002C76;
      color: white;
      padding: 15px 30px;
      margin: 15px 15px 0px 15px;
      font-size: 18px;
      font-weight: 700;
      border-radius: 16px 16px 16px 16px;
      display: flex;
      align-items: center;
    }

    .banner img {
      width: 20px;
      margin-right: 10px;
    }

    .content {
      padding: 0px 30px 15px 30px;
      color: #1a202c;
      font-size: 15px;
      line-height: 1.6;
    }

    .otp-box {
      margin: 20px 0;
      background-color: #f2f2f2;
      border: 2px dashed #002c63;
      text-align: center;
      font-size: 28px;
      font-weight: 700;
      padding: 20px;
      color: #002c63;
      letter-spacing: 4px;
      border-radius: 8px;
    }

    .footer {
      padding: 0 30px 30px;
      font-size: 14px;
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
      <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c9/Department_of_the_Interior_and_Local_Government_%28DILG%29_Seal_-_Logo.svg/2048px-Department_of_the_Interior_and_Local_Government_%28DILG%29_Seal_-_Logo.svg.png" alt="DILG Logo" class="logo">
      <div class="title-text">
        <h2>DILG - CAR<br>Recruitment Selection and Placement Portal</h2>
      </div>
    </div>

    <!-- Banner -->
    <div class="banner">
      <img src="https://img.icons8.com/ios-filled/50/ffffff/key-security.png" alt="Key Icon" />
      One-Time Password (OTP)
    </div>

    <!-- Content -->
    <div class="content">
      <p>Hello!</p>
      <p>
        You are registering a new account on <strong>DILG - CAR Recruitment Selection and Placement Portal</strong>.
        To verify your account, here is your OTP to be entered on the verification page:
      </p>

      <div class="otp-box">
        {{ $otp }}
      </div>

      <p>This code will expire in <strong>5 minutes</strong>.</p>
      <p>Do not share this code with anyone.</p>
      <p>If you didn’t request this code, just ignore this email. Thank you!</p>
      <p><br><strong>– DILG – CAR</strong></p>
    </div>
  </div>
  @include('partials.loader')
</body>
</html>
