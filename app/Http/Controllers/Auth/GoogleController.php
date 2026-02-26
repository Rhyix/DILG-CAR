<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    private function resolveGoogleRedirectUrl(): string
    {
        $configuredRedirect = (string) config('services.google.redirect', '');
        $defaultPath = '/auth/google/callback';

        if ($configuredRedirect === '') {
            return url($defaultPath);
        }

        $parsed = parse_url($configuredRedirect);
        $path = $parsed['path'] ?? $defaultPath;
        $query = isset($parsed['query']) ? ('?' . $parsed['query']) : '';

        // Build callback URL using current host to avoid localhost/127.0.0.1 mismatch.
        return url($path . $query);
    }

    private function clearPdsSessionCache(Request $request): void
    {
        $request->session()->forget([
            'form',
            'data_learning',
            'data_voluntary',
            'data_otherInfo',
            'vacancy_doc_uploads',
            'pds_form_owner',
        ]);
    }

    public function redirectToGoogle()
    {
        $redirectUrl = $this->resolveGoogleRedirectUrl();

        return Socialite::driver('google')
            ->redirectUrl($redirectUrl)
            ->with([
                'response_type' => 'code',
                'access_type' => 'offline',
                'prompt' => 'consent',
            ])
            ->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        $redirectUrl = $this->resolveGoogleRedirectUrl();
        $googleUser = Socialite::driver('google')
            ->redirectUrl($redirectUrl)
            ->stateless()
            ->user();

        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            ['name' => $googleUser->getName(), 'password' => bcrypt('google-oauth')]
        );

        Auth::login($user);
        $request->session()->regenerate();
        $this->clearPdsSessionCache($request);

        activity()
            ->withProperties(['ip' => request()->ip(), 'section' => 'Google Login'])
            ->causedBy(auth()->user())
            ->event('login')
            ->log('login through google');

        return redirect()->route('dashboard_user');
    }
}
