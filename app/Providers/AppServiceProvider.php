<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Logout;

use Spatie\Activitylog\Models\Activity;
use App\Models\Admin;
use App\Models\Notification;
use App\Models\User;
use App\Models\JobVacancy;
use Illuminate\Support\Facades\Mail;
class AppServiceProvider extends ServiceProvider
{
    /**
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Logout::class, function (Logout $event) {
            $user = $event->user;
            if ($user) {
                activity()
                    ->causedBy($user)
                    ->event('logout')
                    ->withProperties(['section' => 'Login', 'guard' => $event->guard])
                    ->log('logged out');
            } else {
                activity()
                    ->event('logout')
                    ->withProperties(['section' => 'Login', 'guard' => $event->guard])
                    ->log('logged out');
            }
        });

        Activity::created(function (Activity $activity) {
            $actor = $activity->causer;
            if (!$actor) {
                return;
            }

            $section = $activity->properties['section'] ?? ucfirst((string) $activity->event ?: 'Activity');
            $desc = trim((string) ($activity->description ?? 'performed an action'));
            $desc = rtrim($desc, ". \t\n\r\0\x0B");

            $actorName = $actor->name ?? ($actor->username ?? 'Unknown');
            $isAdmin = $activity->causer_type === Admin::class;
            $prefix = $isAdmin ? '' : '';
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
            } elseif ($vacancyId && in_array($section, ['Exam Management', 'Application List', 'Job Vacancy'])) {
                $link = route('admin.manage_exam', ['vacancy_id' => $vacancyId]);
            } elseif ($section === 'System Users Management') {
                $link = route('admin_account_management');
            }

            $admins = Admin::all();
            foreach ($admins as $admin) {
                Notification::create([
                    'notifiable_type' => 'App\Models\Admin',
                    'notifiable_id' => $admin->id,
                    'type' => 'info',
                    'data' => [
                        'title' => $section,
                        'message' => $message,
                        'link' => $link,
                    ]
                ]);

                if ($admin->email) {
                    Mail::send('emails.admin_event_notification', [
                        'actorName' => $actorName,
                        'recipientName' => $admin->name ?? $admin->username,
                        'applicantName' => $applicantName,
                        'positionTitle' => $positionTitle,
                        'vacancyId' => $vacancyId,
                        'title' => $section,
                        'body' => $message,
                        'link' => $link,
                        'occurredAt' => $activity->created_at,
                    ], function ($m) use ($admin) {
                        $m->to($admin->email)->subject('DILG-CAR Admin Notification');
                    });
                    $sentCount++;
                    // Mail::send('emails.admin_event_notification', [
                    //     'actorName' => $actorName,
                    //     'recipientName' => $admin->name ?? $admin->username,
                    //     'applicantName' => $applicantName,
                    //     'positionTitle' => $positionTitle,
                    //     'vacancyId' => $vacancy_id,
                    //     'title' => $section,
                    //     'body' => $message,
                    //     'link' => $link,
                    //     'occurredAt' => $activity->created_at,
                    // ], function ($m) use ($admin) {
                    //     $m->to($admin->email)->subject('DILG-CAR Admin Notification');
                    // });

                }
            }
        });
    }
}
