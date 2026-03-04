<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\JobVacancy;
use App\Models\User;
use App\Models\ExamDetail;

class NotifyApplicantMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $vacancy_id;
    public $user_id;
    public $exam_id;

    public $vacancy;
    public $user;
    public $exam;

    public function __construct($vacancy_id, $user_id, $exam_id)
    {
        $this->vacancy_id = $vacancy_id;
        $this->user_id    = $user_id;
        $this->exam_id    = $exam_id;

        $this->vacancy = JobVacancy::where('vacancy_id', $this->vacancy_id)->firstOrFail();
        $this->user    = User::findOrFail($this->user_id);
        $this->exam    = ExamDetail::findOrFail($this->exam_id);
    }

    public function build(): static
    {
        return $this
            ->subject('DILG-CAR Examination')
            ->view('emails.exam_notification')
            ->with([
                'vacancy' => $this->vacancy,
                'user'    => $this->user,
                'exam'    => $this->exam,
            ]);
    }
}
