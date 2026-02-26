<?php

namespace App\Providers;

use App\Jobs\ProcessAdminActivityNotification;
use App\Models\Applications;
use App\Models\UploadedDocument;
use App\Observers\ApplicationObserver;
use App\Observers\UploadedDocumentObserver;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

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

        Activity::created(function (Activity $activity) {
            ProcessAdminActivityNotification::dispatch($activity->id);
        });
    }
}
