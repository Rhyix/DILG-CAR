<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Mail;
use App\Mail\OTPmail;

use Spatie\Activitylog\Models\Activity;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
    /*
        activity()
            ->log('Viewed registration form.');
    */
        return view('login_register.register');
    }

    public function register(Request $request)
    {
        //info("mail");
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Generate OTP
        $otp = rand(100000, 999999);



        // Store registration data temporarily in session
        session([
            'pending_registration' => [
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'otp'       => $otp,
                'expires_at'=> now()->addMinutes(5), //
            ]
        ]);

        //info(now());
        //info(now()->addMinutes(5));
        try {
            Mail::to($request->email)->send(new OTPmail($otp));
        } catch (\Throwable $e) {
            activity()
                ->withProperties(['ip' => request()->ip(), 'email' => $request->email, 'section' => 'Register'])
                ->event('send')
                ->log('Failed to send OTP via mail.');
        }
        //info("mail");

        activity()
            ->withProperties(['ip' => request()->ip(), 'email' => $request->email, 'section' => 'Register'])
            ->event('register')
            ->log('Started registration and sent OTP.');

        return redirect()->route('otp')->with('status', 'Enter the OTP sent to your email.');
    }

    public function OTPForm()
{
    $data = session('pending_registration');

    if (!$data) {
        return redirect()->route('register.form')->withErrors(['expired' => 'Session expired. Please register again.']);
    }

    activity()
        ->withProperties(['ip' => request()->ip(), 'email' => $data['email'] ?? 'Unknown', 'section' => 'Register'])
        ->event('view')
        ->log('Viewed OTP input form.');


    return view('login_register.otp', [
        'email' => $data['email'],
        'status' => 'otp_waiting'
    ]);
}


    public function OTPCheck(Request $request)
    {
        //info("otp_check");
        $request->validate(['otp' => 'required']);


        $data = session('pending_registration');

        //info("data_check");
        if (!$data) {
            activity()
                ->withProperties(['ip' => request()->ip(), 'email' => $data['email'] ?? 'Unknown', 'section' => 'Register'])
                ->event('verify')
                    ->log('OTP expired during registration.');

            return redirect()->route('register')->withErrors(['expired' => 'Session expired. Please register again.']);
        }

        //info("exp_check");
        if (now()->gt($data['expires_at'])) {
            session()->forget('pending_registration');

            activity()
                ->withProperties(['ip' => request()->ip(), 'email' => $data['email'] ?? 'Unknown', 'section' => 'Register'])
                    ->event('verify')
                ->log('OTP expired during registration.');

            return redirect()->route('register')->withErrors(['expired' => 'OTP expired. Please register again.']);
        }

        //info($data['otp']);
        if ($request->otp != $data['otp']) {
            activity()
                ->withProperties(['ip' => request()->ip(), 'email' => $data['email'] ?? 'Unknown', 'section' => 'Register'])
                    ->event('verify')
                ->log('Entered invalid OTP.');

            return back()->withErrors(['otp' => 'Invalid OTP.']);
        }

        // OTP correct and not expired, create user
        $user = \App\Models\User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'],
            'email_verified_at' => now(),
        ]);

        session()->forget('pending_registration'); // Clean up session

        // auth()->login($user);

        activity()
            ->causedBy($user)
            ->event('verify')
            ->withProperties(['ip' => request()->ip(), 'email' => $user->email, 'section' => 'Register'])
            ->log('Completed registration and verified email.');

        return redirect()->route('login.form')->with('success', 'Account verified successfully! You may now log in.');
    }

    public function resendOTP(Request $request)
    {
        $data = session('pending_registration');

        if (!$data) {
            return $request->ajax()
                ? response()->json(['message' => 'Session expired.'], 419)
                : redirect()->route('register')->withErrors(['expired' => 'Session expired. Please register again.']);
        }

        $newOtp = rand(100000, 999999);
        $data['otp'] = $newOtp;
        $data['expires_at'] = now()->addMinutes(5);
        session(['pending_registration' => $data]);

        try {
            Mail::to($data['email'])->send(new OTPmail($newOtp));
        } catch (\Throwable $e) {
            activity()
                ->withProperties(['ip' => request()->ip(), 'email' => $data['email'] ?? 'Unknown', 'section' => 'Register'])
                ->event('send')
                ->log('Failed to resend OTP via mail.');
        }

        activity()
            ->withProperties(['ip' => request()->ip(), 'email' => $data['email'] ?? 'Unknown', 'section' => 'Register'])
            ->event('send')
            ->log('Resent OTP for registration.');

        return $request->ajax()
            ? response()->json(['message' => 'OTP resent.'])
            : back()->with('status', 'A new OTP has been sent to your email.');
    }



}
