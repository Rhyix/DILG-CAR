<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Notification</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            background-color: #0D2B70;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }

        .email-header h1 {
            margin: 0;
            font-size: 24px;
        }

        .email-body {
            padding: 30px;
            color: #333333;
            line-height: 1.6;
        }

        .email-body h2 {
            color: #0D2B70;
            font-size: 20px;
            margin-top: 0;
        }

        .exam-details {
            background-color: #f8f9fa;
            border-left: 4px solid #0D2B70;
            padding: 15px;
            margin: 20px 0;
        }

        .exam-details p {
            margin: 8px 0;
        }

        .exam-details strong {
            color: #0D2B70;
        }

        .cta-button {
            display: inline-block;
            background-color: #0D2B70;
            color: #ffffff;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }

        .cta-button:hover {
            background-color: #0a1f4d;
        }

        .email-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666666;
        }

        .divider {
            border-top: 1px solid #e0e0e0;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            <h1>📝 Exam Notification</h1>
        </div>

        <div class="email-body">
            <h2>Dear {{ $user->name ?? 'Applicant' }},</h2>

            <p>We are pleased to inform you that the exam for the position of <strong>{{ $vacancy->position_title ?? 'Position' }}</strong>
                (Vacancy ID: {{ $vacancy->vacancy_id ?? 'N/A' }}) has been scheduled.</p>

            <div class="exam-details">
                <p><strong>📅 Date:</strong>
                    {{ isset($exam->date) && $exam->date ? \Carbon\Carbon::parse($exam->date)->format('F d, Y') : 'TBD' }}
                </p>
                <p><strong>🕐 Time:</strong>
                    {{ isset($exam->time) && $exam->time ? \Carbon\Carbon::parse($exam->time)->format('h:i A') : 'TBD' }}
                </p>
                <p><strong>📍 Venue:</strong> {{ $exam->place ?? 'TBD' }}</p>
                @if(isset($exam->message) && $exam->message)
                    <p><strong>✉️ Message:</strong> {{ $exam->message }}</p>
                @endif
                <!-- <p><strong>⏱️ Duration:</strong> {{ $exam->duration ?? 'TBD' }} minutes</p> -->
            </div>

            @isset($examLink)
            <p>Please click the button below to access the exam lobby when it's time to take your exam:</p>

            <!-- lalagay lang ito kapag exam day na -->
            <div style="text-align: center;">
                <a href="{{ $examLink }}" class="cta-button">Go to Exam Lobby</a>
            </div>
            @endisset

            @isset($confirmationLink)
                <p style="text-align: center; margin-top: 20px;">Please confirm your attendance by clicking the button
                    below:</p>
                <div style="text-align: center;">
                    <a href="{{ $confirmationLink }}" class="cta-button" style="background-color: #28a745;">Confirm
                        Attendance</a>
                </div>
            @endisset

            <div class="divider"></div>

            <p><strong>Important Reminders:</strong></p>
            <ul>
                <li>Please arrive at the exam venue at least 15 minutes before the scheduled time.</li>
                <li>Bring a valid ID for verification purposes.</li>
                <li>Make sure you have a stable internet connection if taking the exam online.</li>
                <li>The exam will be available only during the scheduled time.</li>
            </ul>

            <p>If you have any questions or concerns, please don't hesitate to contact us.</p>

            <p>Good luck with your exam!</p>

            <p>Best regards,<br>
                <strong>DILG-CAR Recruitment Team</strong>
            </p>
        </div>

        <div class="email-footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} DILG-CAR. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
