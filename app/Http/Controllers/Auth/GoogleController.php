<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    private function resolveGoogleRedirectUrl(Request $request): string
    {
        $configuredRedirect = (string) config('services.google.redirect', '');
        $defaultPath = '/auth/google/callback';

        if ($configuredRedirect !== '') {
            $parsed = parse_url($configuredRedirect);

            // If redirect URI in .env is absolute, use it as canonical callback URL.
            if (($parsed['scheme'] ?? null) && ($parsed['host'] ?? null)) {
                $host = $parsed['host'] === '0.0.0.0' ? '127.0.0.1' : $parsed['host'];
                $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
                $path = $parsed['path'] ?? $defaultPath;
                $query = isset($parsed['query']) ? ('?' . $parsed['query']) : '';

                return "{$parsed['scheme']}://{$host}{$port}{$path}{$query}";
            }

            $path = $parsed['path'] ?? $defaultPath;
            $query = isset($parsed['query']) ? ('?' . $parsed['query']) : '';
            return url($path . $query);
        }

        $host = $request->getHost() === '0.0.0.0' ? '127.0.0.1' : $request->getHost();
        $port = (int) $request->getPort();
        $scheme = $request->getScheme();
        $isDefaultPort = ($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443);
        $portSuffix = $isDefaultPort ? '' : ':' . $port;

        return "{$scheme}://{$host}{$portSuffix}{$defaultPath}";
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

    public function redirectToGoogle(Request $request)
    {
        $redirectUrl = $this->resolveGoogleRedirectUrl($request);

        return Socialite::driver('google')
            ->redirectUrl($redirectUrl)
            ->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))
            ->with([
                'response_type' => 'code',
                'access_type' => 'offline',
                'prompt' => 'consent',
            ])
            ->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        $redirectUrl = $this->resolveGoogleRedirectUrl($request);
        $googleUser = Socialite::driver('google')
            ->redirectUrl($redirectUrl)
            ->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))
            ->stateless()
            ->user();

        $googleFullName = trim((string) $googleUser->getName());
        $nameParts = preg_split('/\s+/', $googleFullName) ?: [];
        $firstName = (string) ($nameParts[0] ?? '');
        $lastName = count($nameParts) > 1 ? (string) end($nameParts) : '';
        $middleName = count($nameParts) > 2
            ? trim(implode(' ', array_slice($nameParts, 1, -1)))
            : '';
        $middleInitial = $middleName !== '' ? strtoupper(mb_substr($middleName, 0, 1)) . '.' : '';
        $fullName = trim(implode(' ', array_filter([$firstName, $middleInitial, $lastName])));

        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $fullName !== '' ? $fullName : $googleFullName,
                'first_name' => $firstName !== '' ? $firstName : null,
                'middle_name' => $middleName !== '' ? $middleName : null,
                'last_name' => $lastName !== '' ? $lastName : null,
                'password' => bcrypt('google-oauth'),
            ]
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
