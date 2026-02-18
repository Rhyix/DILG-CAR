<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Http;


class LoginController extends Controller
{
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
            return redirect()->route('dashboard');
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

        return view('login_register.login');
    }

    public function login(Request $request)
    {
        $attempts = session()->get('login_attempts', 0);

        if (!env('APP_DEBUG')) {
            $captcha = $request->input('g-recaptcha-response');

            if (!$captcha || !$this->verifyRecaptcha($captcha, $request->ip())) {
                return back()->withErrors([
                    'captcha' => 'Please confirm you are not a robot.'
                ]);
            }
        }

        // Logout admin if logged in
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }

        $credentials = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

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
            }

            return redirect()->route('dashboard')->with('status', 'welcome');
        }

        activity()
            ->event('login')
            ->withProperties(['ip' => request()->ip(), 'email' => $request->email, 'section' => 'Login'])
            ->log('Failed login attempt.');

        // // Increment on failure
        // session()->increment('login_attempts');
        // return back()->withErrors([
        //     'email' => 'Invalid credentials.'
        // ])->withInput();

        return back()->withErrors(['email' => 'Invalid credentials provided.'])
            ->withInput($request->only('email'));
    }


    // reCAPTCHA verifier
    protected function verifyRecaptcha($token, $ip)
    {
        $secret = env('RECAPTCHA_SECRET');

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $ip,
        ]);

        return $response->json('success');
    }

}
