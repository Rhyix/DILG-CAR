<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\JobVacancy;
use App\Models\User;
use App\Models\ExamDetail;

class NotifyApplicantMail extends Mailable
{
    use Queueable, SerializesModels;

    public $vacancy_id;
    public $user_id;
    public $exam_id;

    public $vacancy;
    public $user;
    public $exam;

    /**
     * Create a new message instance.
     */
    public function __construct($vacancy_id, $user_id, $exam_id)
    {
        $this->vacancy_id = $vacancy_id;
        $this->user_id = $user_id;
        $this->exam_id = $exam_id;

        $this->vacancy = JobVacancy::where('vacancy_id', $this->vacancy_id)->firstOrFail();
        $this->user = User::findOrFail($this->user_id);
        $this->exam = ExamDetail::findOrFail($this->exam_id);

        //info('content');
        //info($this->vacancy);
        //info($this->user);
        //info($this->exam);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'DILG-CAR Examination',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {

        return new Content(
            view: 'emails.exam_notification',
            with: [
                'vacancy' => $this->vacancy,
                'user' => $this->user,
                'exam' => $this->exam,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
