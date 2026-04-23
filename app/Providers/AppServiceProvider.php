<?php

namespace App\Providers;

use App\Jobs\ProcessAdminActivityNotification;
use App\Models\Applications;
use App\Models\EmailLog;
use App\Models\UploadedDocument;
use App\Observers\ApplicationObserver;
use App\Observers\UploadedDocumentObserver;
use Illuminate\Auth\Events\Logout;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
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
        $this->ensureFrameworkRuntimeDirectories();

        date_default_timezone_set(config('app.timezone', 'Asia/Manila'));

        Applications::observe(ApplicationObserver::class);
        UploadedDocument::observe(UploadedDocumentObserver::class);

        Gate::define('admin.exam.monitor', function ($admin): bool {
            return in_array((string) ($admin->role ?? ''), ['superadmin', 'admin', 'viewer'], true);
        });

        Gate::define('admin.exam.manage', function ($admin): bool {
            return in_array((string) ($admin->role ?? ''), ['superadmin', 'admin'], true);
        });

        Gate::define('admin.applicants.monitor', function ($admin): bool {
            return in_array((string) ($admin->role ?? ''), ['superadmin', 'admin', 'hr_division'], true);
        });

        Gate::define('admin.system.manage', function ($admin): bool {
            return (string) ($admin->role ?? '') === 'superadmin';
        });

        Gate::define('admin.backoffice.full', function ($admin): bool {
            return in_array((string) ($admin->role ?? ''), ['superadmin', 'admin'], true);
        });

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

            Event::listen(MessageSent::class, function (MessageSent $event) {
                $message = $event->message;
                $recipients = collect($message->getTo() ?? [])
                    ->map(fn($address, $email) => is_string($email) ? $email : (string) ($address->getAddress() ?? ''))
                    ->filter()
                    ->values()
                    ->all();

                if ($recipients === []) {
                    $recipients = collect($message->getTo() ?? [])
                        ->map(fn($address) => method_exists($address, 'getAddress') ? (string) $address->getAddress() : '')
                        ->filter()
                        ->values()
                        ->all();
                }

                $subject = (string) ($message->getSubject() ?? '(no subject)');
                $causer = auth('admin')->user() ?? auth()->user();

                activity()
                    ->causedBy($causer)
                    ->event('email_sent')
                    ->withProperties([
                        'section' => 'Email Logs',
                        'recipients' => $recipients,
                        'subject' => $subject,
                        'mailer' => config('mail.default'),
                    ])
                    ->log('Sent email to ' . implode(', ', $recipients));

                foreach ($recipients as $recipientEmail) {
                    EmailLog::create([
                        'vacancy_id' => 'system',
                        'user_id' => 0,
                        'recipient_email' => $recipientEmail,
                        'status' => 'sent',
                        'error_message' => null,
                    ]);
                }
            });

        Activity::created(function (Activity $activity) {
            ProcessAdminActivityNotification::dispatch($activity->id)
                ->onConnection('database');
        });
    }

    private function ensureFrameworkRuntimeDirectories(): void
    {
        $directories = [
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
        ];

        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                @mkdir($directory, 0755, true);
            }
        }
    }
}
