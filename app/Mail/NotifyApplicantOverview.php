<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\JobVacancy;
use App\Models\User;

class NotifyApplicantOverview extends Mailable
{
    use Queueable, SerializesModels;

    public $user_id;
    public $vacancy_id;
    public $applicant_name;
    public $position_title;
    public $documents;
    public $application_remarks;

    public function __construct($user_id, $vacancy_id, $documents, $application_remarks)
    {
        $this->user_id = $user_id;
        $this->vacancy_id = $vacancy_id;
        $this->documents = $documents;
        $this->application_remarks = $application_remarks;
        
        $this->applicant_name = User::where('id', $user_id)->value('name');
        $this->position_title = JobVacancy::where('vacancy_id', $vacancy_id)->value('position_title');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'DILG-CAR Application Document Status Overview',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notifyApplicantOverview',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
