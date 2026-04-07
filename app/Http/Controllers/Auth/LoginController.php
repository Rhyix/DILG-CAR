<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ApplicantOnboarding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;


class LoginController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOGIN_DECAY_SECONDS = 60;

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

    private function throttleKey(Request $request): string
    {
        return Str::lower(trim((string) $request->input('email'))) . '|' . $request->ip();
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        $key = $this->throttleKey($request);

        if (! RateLimiter::tooManyAttempts($key, self::MAX_LOGIN_ATTEMPTS)) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'email' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.',
        ]);
    }

    private function recordFailedAttempt(Request $request): void
    {
        RateLimiter::hit($this->throttleKey($request), self::LOGIN_DECAY_SECONDS);
    }

    private function clearFailedAttempts(Request $request): void
    {
        RateLimiter::clear($this->throttleKey($request));
    }

    public function showLoginForm(Request $request)
    {
        if (Auth::user()) {
            // If user is already logged in and there's a redirect parameter, handle it
            if ($request->has('redirect') && $request->redirect === 'application_status') {
                $userId = $request->get('user');
                $vacancyId = $request->get('vacancy');

                if ($userId && $vacancyId) {
                    return redirect()->route('application_status', [
                        'user' => $userId,
                        'vacancy' => $vacancyId
                    ])->with('comply_redirect', true);
                }
            }
            // If already logged in and redirecting to exam lobby, forward immediately
            if ($request->has('redirect') && $request->redirect === 'exam_lobby') {
                $vacancyId = $request->get('vacancy');
                $token = $request->get('token');
                if ($vacancyId) {
                    return redirect()->route('user.exam_lobby', [
                        'vacancy_id' => $vacancyId,
                        'token' => $token
                    ]);
                }
            }
            if ($request->has('redirect') && $request->redirect === 'exam_attendance') {
                $vacancyId = $request->get('vacancy');
                if ($vacancyId) {
                    return redirect()->route('exam.attendance.prompt', [
                        'vacancy_id' => $vacancyId,
                    ]);
                }
            }
            return redirect()->route('dashboard_user');
        }

        if (Auth::guard('admin')->check()) {
            return redirect('/admin/dashboard');
        }

        // Store redirect information in session if present
        if ($request->has('redirect') && $request->redirect === 'application_status') {
            $request->session()->put('redirect_after_login', [
                'target' => 'application_status',
                'user' => $request->get('user'),
                'vacancy' => $request->get('vacancy'),
            ]);
        }
        if ($request->has('redirect') && $request->redirect === 'exam_lobby') {
            $request->session()->put('redirect_after_login', [
                'target' => 'exam_lobby',
                'vacancy' => $request->get('vacancy'),
                'token' => $request->get('token'),
            ]);
        }
        if ($request->has('redirect') && $request->redirect === 'exam_attendance') {
            $request->session()->put('redirect_after_login', [
                'target' => 'exam_attendance',
                'vacancy' => $request->get('vacancy'),
            ]);
        }

        return view('login_register.login');
    }

    public function login(Request $request)
    {
        // Logout admin if logged in
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }

        $credentials = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6|max:255',
            'remember' => 'nullable|boolean',
        ]);

        $this->ensureIsNotRateLimited($request);

        $email = trim((string) ($credentials['email'] ?? ''));
        $password = (string) ($credentials['password'] ?? '');
        $remember = (bool) ($credentials['remember'] ?? false);

        $user = User::query()
            ->where('email', $email)
            ->first();

        $emailMatchesCase = $user && hash_equals((string) $user->email, $email);
        $passwordMatches = $user && Hash::check($password, (string) $user->password);

        if ($emailMatchesCase && $passwordMatches) {
            Auth::login($user, $remember);
            $request->session()->regenerate();
            $this->clearPdsSessionCache($request);
            $this->clearFailedAttempts($request);

            activity()->log('login');

            activity()
                ->causedBy(auth()->user())
                ->event('login')
                ->log('User logged in successfully.');

            // Check for redirect after login
            if ($request->session()->has('redirect_after_login')) {
                $redirectData = $request->session()->get('redirect_after_login');
                $request->session()->forget('redirect_after_login');

                if ($redirectData['target'] === 'application_status') {
                    return redirect()->route('application_status', [
                        'user' => $redirectData['user'],
                        'vacancy' => $redirectData['vacancy']
                    ])->with('comply_redirect', true);
                }
                if ($redirectData['target'] === 'exam_lobby') {
                    return redirect()->route('user.exam_lobby', [
                        'vacancy_id' => $redirectData['vacancy'],
                        'token' => $redirectData['token'] ?? null
                    ]);
                }
                if ($redirectData['target'] === 'exam_attendance') {
                    return redirect()->route('exam.attendance.prompt', [
                        'vacancy_id' => $redirectData['vacancy'],
                    ]);
                }
            }

            if (ApplicantOnboarding::shouldRequire($user)) {
                return redirect()
                    ->route('dashboard_user')
                    ->with('open_onboarding_modal', true)
                    ->with('status', 'Please complete your onboarding before submitting applications.');
            }

            return redirect()->route('dashboard_user')->with('status', 'welcome');
        }

        activity()
            ->event('login')
            ->withProperties(['ip' => request()->ip(), 'email' => $request->email, 'section' => 'Login'])
            ->log('Failed login attempt.');
        $this->recordFailedAttempt($request);

        return back()->withErrors(['email' => 'Invalid credentials provided.'])
            ->withInput($request->only('email'));
    }


}
