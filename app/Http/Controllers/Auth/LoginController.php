<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Http;


class LoginController extends Controller
{
    public function showLoginForm()
    {
        if(Auth::user()){
            return redirect()->route('dashboard');
        }

        if (Auth::guard('admin')->check()) {
        return redirect('/admin/dashboard');
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
            'email'    => 'required|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if(Auth::attempt($credentials)){
            $request->session()->regenerate();

            activity()->log('login');

            activity()
                ->causedBy(auth()->user())
                ->event('login')
                ->log('User logged in successfully.');

            return redirect()->route('dashboard')->with('status','welcome');
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
