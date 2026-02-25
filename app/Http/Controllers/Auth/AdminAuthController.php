<?php

namespace App\Http\Controllers\Auth; // Assuming your controllers are in this namespace

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller; // Make sure to import the base Controller class

class AdminAuthController extends Controller
{
    private function clearPdsSessionCache(Request $request): void
    {
        $request->session()->forget([
            'form',
            'data_learning',
            'data_voluntary',
            'data_otherInfo',
            'vacancy_doc_uploads',
            'pds_form_owner',
            'redirect_after_login',
            'pending_registration',
        ]);
    }

    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
        return redirect('/admin/dashboard');
        }

        if(Auth::user()){
            return redirect()->route('dashboard_user');
        }

        return view('login_register.admin_login');
    }

public function login(Request $request)
{
    $attempts = session()->get('login_attempts', 0);

    // Enforce reCAPTCHA only in production environment
    if (app()->environment('production')) {
        $captcha = $request->input('g-recaptcha-response');

        if (!$captcha || !$this->verifyRecaptcha($captcha, $request->ip())) {
            return back()->withErrors([
                'captcha' => 'Please confirm you are not a robot.'
            ]);
        }
    }

    if (Auth::check()) {
        $this->clearPdsSessionCache($request);
        Auth::logout();
    }

    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::guard('admin')->attempt($credentials)) {
        $request->session()->regenerate();

        $user = Auth::guard('admin')->user();

        // 🔒 Check if the account is deactivated
        if ($user->is_active != 1) {
            Auth::guard('admin')->logout(); // Prevent login

            activity()
                ->causedBy($user)
                ->withProperties(['section' => 'Login'])
                ->event('login')
                ->log('Admin login blocked. Account is deactivated.');

            return back()->withErrors([
                'email' => 'Your account has been deactivated.',
            ]);
        }

        activity()
            ->causedBy($user)
            ->withProperties(['section' => 'Login'])
            ->event('login')
            ->log('Admin logged in successfully.');

        // 🎯 Redirect based on role
        return $user->role === 'viewer'
            ? redirect()->route('viewer')
            : redirect()->intended('/admin/dashboard');
    }

    activity()
        ->withProperties(['ip' => request()->ip(), 'email' => $request->email])
        ->withProperties(['section' => 'Login'])
        ->event('login')
        ->log('Admin logged in unsuccessfully.');

        // session()->increment('login_attempts');
        // return back()->withErrors([
        //     'email' => 'Invalid credentials.'
        // ])->withInput();

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ]);
}

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

    public function logout(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $this->clearPdsSessionCache($request);
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        activity()
            ->withProperties(['section' => 'Login'])
            ->causedBy($admin)
            ->log('Admin logged out.');

        return redirect('/admin/login')
            ->header('Clear-Site-Data', '"cache","storage"');
    }
}
