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
                $context = [
                    'user_id' => $this->userId,
                    'vacancy_id' => $this->vacancyId,
                    'exam_id' => $this->examId
                ];
                Log::error("SendExamNotification: Missing data", $context);
                throw new \RuntimeException('Unable to send exam notification due to missing data.');
            }

            // Generate exam link with token
            $application = \App\Models\Applications::where('user_id', $this->userId)
                ->where('vacancy_id', $this->vacancyId)
                ->first();

            $token = $application ? $application->exam_token : null;
            if (empty($token)) {
                throw new \RuntimeException('Exam token not found for applicant.');
            }
            $examLink = route('user.exam_lobby', ['vacancy_id' => $this->vacancyId, 'token' => $token]);

            // Send email using the exam schedule link template
            Mail::send('emails.exam_sched_link', [
                'user' => $user,
                'vacancy' => $vacancy,
                'exam' => $examDetail,
                'join_link' => $examLink,
            ], function ($message) use ($user, $vacancy) {
                $message->to($user->email, $user->name)
                    ->subject('Examination Schedule - ' . $vacancy->position_title)
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
