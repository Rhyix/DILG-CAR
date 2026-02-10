<?php

namespace App\Jobs;

<<<<<<< Updated upstream
=======
use App\Mail\NotifyApplicantMail;
use App\Models\User;
use App\Models\ExamDetail;
use App\Models\Applications;
use App\Models\EmailLog;
>>>>>>> Stashed changes
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
<<<<<<< Updated upstream
use App\Models\User;
use App\Models\JobVacancy;
use App\Models\ExamDetail;
=======
>>>>>>> Stashed changes

class SendExamNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

<<<<<<< Updated upstream
    protected $vacancyId;
    protected $userId;
    protected $examId;
    protected $senderEmail;
=======
    public $vacancy_id;
    public $user_id;
    public $exam_id;
    public $sender_email;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @return array
     */
    public function backoff()
    {
        return [1, 5, 10, 20, 60];
    }
>>>>>>> Stashed changes

    /**
     * Create a new job instance.
     */
<<<<<<< Updated upstream
    public function __construct($vacancyId, $userId, $examId, $senderEmail)
    {
        $this->vacancyId = $vacancyId;
        $this->userId = $userId;
        $this->examId = $examId;
        $this->senderEmail = $senderEmail;
=======
    public function __construct($vacancy_id, $user_id, $exam_id, $sender_email = null)
    {
        $this->vacancy_id = $vacancy_id;
        $this->user_id = $user_id;
        $this->exam_id = $exam_id;
        $this->sender_email = $sender_email;
>>>>>>> Stashed changes
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
<<<<<<< Updated upstream
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
=======
        $user = User::find($this->user_id);
        
        if (!$user) {
            Log::error("SendExamNotification: User not found for ID {$this->user_id}");
            return;
        }

        // Log start
        $log = EmailLog::create([
            'vacancy_id' => $this->vacancy_id,
            'user_id' => $this->user_id,
            'recipient_email' => $user->email,
            'status' => 'processing'
        ]);

        try {
            $mail = new NotifyApplicantMail($this->vacancy_id, $this->user_id, $this->exam_id);
            
            if ($this->sender_email) {
                $mail->from($this->sender_email, 'DILG-CAR Admin');
            }

            Mail::to($user->email)->send($mail);

            $log->update(['status' => 'sent']);
            Log::info("Email sent successfully to {$user->email} for vacancy {$this->vacancy_id}");

        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            Log::error("Failed to send email to {$user->email}: " . $e->getMessage());
            
            // Release the job back to the queue to retry
            $this->release(10); 
>>>>>>> Stashed changes
        }
    }
}
