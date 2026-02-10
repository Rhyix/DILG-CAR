<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Logout;

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
    }
}
