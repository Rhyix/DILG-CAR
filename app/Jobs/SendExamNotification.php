<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\JobVacancy;
use App\Models\ExamDetail;

class SendExamNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $vacancyId;
    protected $userId;
    protected $examId;
    protected $senderEmail;

    /**
     * Create a new job instance.
     */
    public function __construct($vacancyId, $userId, $examId, $senderEmail)
    {
        $this->vacancyId = $vacancyId;
        $this->userId = $userId;
        $this->examId = $examId;
        $this->senderEmail = $senderEmail;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = User::find($this->userId);
            $vacancy = JobVacancy::where('vacancy_id', $this->vacancyId)->first();
            $examDetail = ExamDetail::find($this->examId);

            if (!$user || !$vacancy || !$examDetail) {
                Log::error("SendExamNotification: Missing data", [
                    'user_id' => $this->userId,
                    'vacancy_id' => $this->vacancyId,
                    'exam_id' => $this->examId
                ]);
                return;
            }

            // Generate exam link with token
            $application = \App\Models\Applications::where('user_id', $this->userId)
                ->where('vacancy_id', $this->vacancyId)
                ->first();

            $token = $application ? $application->exam_token : null;
            $examLink = route('user.exam_lobby', ['vacancy_id' => $this->vacancyId, 'token' => $token]);

            // Send email
            Mail::send('emails.exam_notification', [
                'userName' => $user->name,
                'positionTitle' => $vacancy->position_title,
                'vacancyId' => $this->vacancyId,
                'examDate' => $examDetail->date,
                'examTime' => $examDetail->time,
                'examVenue' => $examDetail->place,
                'examDuration' => $examDetail->duration,
                'examLink' => $examLink,
                'confirmationLink' => route('exam.confirm_notification', ['token' => $token]),
            ], function ($message) use ($user, $vacancy) {
                $message->to($user->email, $user->name)
                    ->subject('Exam Notification - ' . $vacancy->position_title)
                    ->from($this->senderEmail, 'DILG-CAR Recruitment');
            });

            Log::info("Exam notification sent successfully", [
                'user_id' => $this->userId,
                'vacancy_id' => $this->vacancyId,
                'email' => $user->email
            ]);

        } catch (\Exception $e) {
            Log::error("SendExamNotification failed: " . $e->getMessage(), [
                'user_id' => $this->userId,
                'vacancy_id' => $this->vacancyId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
