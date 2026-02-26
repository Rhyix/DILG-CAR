<?php

namespace App\Jobs;

use App\Models\Admin;
use App\Models\JobVacancy;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;

class ProcessAdminActivityNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(public int $activityId)
    {
    }

    public function handle(): void
    {
        $activity = Activity::with('causer')->find($this->activityId);
        if (!$activity) {
            return;
        }

        $actor = $activity->causer;
        if (!$actor) {
            return;
        }

        $section = $activity->properties['section'] ?? ucfirst((string) $activity->event ?: 'Activity');
        $desc = trim((string) ($activity->description ?? 'performed an action'));
        $desc = rtrim($desc, ". \t\n\r\0\x0B");

        $actorName = $actor->name ?? ($actor->username ?? 'Unknown');
        $message = $actorName . ' ' . $desc . '.';

        $userId = $activity->properties['user_id'] ?? null;
        $vacancyId = $activity->properties['vacancy_id'] ?? null;
        $applicantName = null;
        $positionTitle = null;
        $link = null;

        if ($userId) {
            $applicantName = User::where('id', $userId)->value('name');
        }
        if ($vacancyId) {
            $positionTitle = JobVacancy::where('vacancy_id', $vacancyId)->value('position_title');
        }

        if ($userId && $vacancyId) {
            $link = route('admin.applicant_status', ['user_id' => $userId, 'vacancy_id' => $vacancyId]);
        } elseif ($vacancyId && in_array($section, ['Exam Management', 'Application List', 'Job Vacancy'], true)) {
            $link = route('admin.manage_exam', ['vacancy_id' => $vacancyId]);
        } elseif ($section === 'System Users Management') {
            $link = route('admin_account_management');
        }

        $category = $this->resolveCategory($activity, (string) $section);
        if (!$category) {
            Log::info('Filtered admin notification', ['event' => $activity->event, 'section' => $section]);
            return;
        }

        $admins = Admin::all();
        $sentCount = 0;

        foreach ($admins as $admin) {
            $notificationLink = $link;
            if ($section === 'System Users Management' && ($admin->role ?? null) !== 'superadmin') {
                $notificationLink = match ($admin->role ?? null) {
                    'hr_division' => route('applications_list'),
                    'viewer' => route('viewer'),
                    default => route('dashboard_admin'),
                };
            }

            Notification::create([
                'notifiable_type' => Admin::class,
                'notifiable_id' => $admin->id,
                'type' => 'info',
                'created_at' => now(),
                'updated_at' => now(),
                'data' => [
                    'title' => $section,
                    'message' => $message,
                    'link' => $notificationLink,
                    'category' => $category,
                ],
            ]);

            if (!$admin->email) {
                continue;
            }

            Mail::send('emails.admin_event_notification', [
                'actorName' => $actorName,
                'recipientName' => $admin->name ?? $admin->username,
                'applicantName' => $applicantName,
                'positionTitle' => $positionTitle,
                'vacancyId' => $vacancyId,
                'title' => $section,
                'body' => $message,
                'link' => $notificationLink,
                'occurredAt' => $activity->created_at,
            ], function ($m) use ($admin) {
                $m->to($admin->email)->subject('DILG-CAR Admin Notification');
            });

            $sentCount++;
        }

        Log::info('Sent admin notifications', [
            'category' => $category,
            'count' => $sentCount,
            'section' => $section,
            'event' => $activity->event,
        ]);
    }

    private function resolveCategory(Activity $activity, string $section): ?string
    {
        $eventName = (string) ($activity->event ?? '');
        if (in_array(strtolower($eventName), ['login', 'logout'], true)) {
            return null;
        }

        $changes = $activity->properties['changes'] ?? null;
        if ($section === 'Application List' && is_array($changes)) {
            foreach ($changes as $key => $change) {
                if (!str_starts_with((string) $key, 'document_') || !is_array($change)) {
                    continue;
                }
                if (!isset($change['status']['new'])) {
                    continue;
                }

                $newStatus = (string) $change['status']['new'];
                if (in_array($newStatus, ['Verified', 'Needs Revision'], true)) {
                    return 'document_verification';
                }
            }
        }

        if ($section === 'Exam Management' && in_array($eventName, ['start', 'notify_schedule'], true)) {
            return 'exam_lifecycle';
        }

        return null;
    }
}

