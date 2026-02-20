<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->redirectUrl(config('services.google.redirect'))
            ->with([
                'response_type' => 'code',
                'access_type' => 'offline',
                'prompt' => 'consent',
            ])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            ['name' => $googleUser->getName(), 'password' => bcrypt('google-oauth')]
        );

        Auth::login($user);

        activity()
            ->withProperties(['ip' => request()->ip(), 'section' => 'Google Login'])
            ->causedBy(auth()->user())
            ->event('login')
            ->log('login through google');

        return redirect()->route('dashboard');
    }
}
