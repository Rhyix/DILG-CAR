<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;

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
        // Support both field naming styles:
        // first_name/middle_name/last_name and fname/mname/lname.
        $firstName = trim((string) ($request->input('first_name') ?? $request->input('fname') ?? ''));
        $middleName = trim((string) ($request->input('middle_name') ?? $request->input('middle_initial') ?? $request->input('mname') ?? ''));
        $lastName = trim((string) ($request->input('last_name') ?? $request->input('lname') ?? ''));
        $phoneNumber = preg_replace('/\D+/', '', (string) $request->input('phone_number', ''));

        $request->merge([
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'phone_number' => $phoneNumber,
        ]);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'phone_number' => ['required', 'regex:/^09\d{9}$/'],
            'sex' => 'nullable|string|max:20',
        ], [
            'phone_number.regex' => 'Contact number must follow the format 09XX XXX XXXX.',
        ]);

        $fullName = trim(implode(' ', array_filter([
            $firstName,
            $middleName !== '' ? strtoupper(mb_substr($middleName, 0, 1)) . '.' : '',
            $lastName,
        ], fn ($value) => $value !== '')));

        // Generate OTP
        $otp = rand(100000, 999999);



        // Store registration data temporarily in session
        session([
            'pending_registration' => [
                'name' => $fullName,
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'phone_number' => $request->input('phone_number'),
                'sex' => $request->input('sex'),
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'otp' => $otp,
                'expires_at' => now()->addMinutes(5), //
                'resend_available_at_ts' => now()->addSeconds(30)->timestamp,
            ]
        ]);

        //info(now());
        //info(now()->addMinutes(5));
        try {
            Mail::to($request->email)->send(new OTPmail($otp));
        } catch (\Throwable $e) {
            Log::error('Failed to send registration OTP mail.', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);
            activity()
                ->withProperties(['ip' => request()->ip(), 'email' => $request->email, 'section' => 'Register'])
                ->event('send')
                ->log('Failed to send OTP via mail.');

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'Unable to send OTP right now. Please try again after a moment.']);
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


    $resendAvailableAtTs = (int) ($data['resend_available_at_ts'] ?? 0);
    if ($resendAvailableAtTs <= 0 && isset($data['resend_available_at'])) {
        $resendAvailableAtTs = Carbon::parse($data['resend_available_at'])->timestamp;
    }
    if ($resendAvailableAtTs <= 0) {
        $resendAvailableAtTs = now()->timestamp;
    }

    return view('login_register.otp', [
        'email' => $data['email'],
        'status' => 'otp_waiting',
        'resendAvailableAtTs' => $resendAvailableAtTs,
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
            'name' => $data['name'],
            'first_name' => $data['first_name'] ?? null,
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'sex' => $data['sex'] ?? null,
            'email' => $data['email'],
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
        $wantsJson = $request->expectsJson() || $request->ajax() || $request->wantsJson() || $request->isJson();

        if (!$data) {
            return $wantsJson
                ? response()->json(['message' => 'Session expired.'], 419)
                : redirect()->route('register')->withErrors(['expired' => 'Session expired. Please register again.']);
        }

        $resendAvailableAtTs = (int) ($data['resend_available_at_ts'] ?? 0);
        if ($resendAvailableAtTs <= 0 && isset($data['resend_available_at'])) {
            $resendAvailableAtTs = Carbon::parse($data['resend_available_at'])->timestamp;
        }

        $nowTs = now()->timestamp;
        if ($resendAvailableAtTs > $nowTs) {
            $retryAfter = $resendAvailableAtTs - $nowTs;
            $message = "Please wait {$retryAfter} seconds before requesting another OTP.";

            return $wantsJson
                ? response()->json(['message' => $message, 'retry_after' => $retryAfter], 429)
                : back()->withErrors(['otp' => $message]);
        }

        $newOtp = rand(100000, 999999);
        $data['otp'] = $newOtp;
        $data['expires_at'] = now()->addMinutes(5);
        $data['resend_available_at_ts'] = now()->addSeconds(30)->timestamp;
        session(['pending_registration' => $data]);

        try {
            Mail::to($data['email'])->send(new OTPmail($newOtp));
        } catch (\Throwable $e) {
            Log::error('Failed to resend registration OTP mail.', [
                'email' => $data['email'] ?? null,
                'error' => $e->getMessage(),
            ]);
            activity()
                ->withProperties(['ip' => request()->ip(), 'email' => $data['email'] ?? 'Unknown', 'section' => 'Register'])
                ->event('send')
                ->log('Failed to resend OTP via mail.');

            return $wantsJson
                ? response()->json(['message' => 'Unable to send OTP right now. Please try again.'], 500)
                : back()->withErrors(['otp' => 'Unable to send OTP right now. Please try again.']);
        }

        activity()
            ->withProperties(['ip' => request()->ip(), 'email' => $data['email'] ?? 'Unknown', 'section' => 'Register'])
            ->event('send')
            ->log('Resent OTP for registration.');

        return $wantsJson
            ? response()->json([
                'message' => 'OTP resent.',
                'retry_after' => 30,
                'resend_available_at' => (int) $data['resend_available_at_ts'],
            ])
            : back()->with('status', 'A new OTP has been sent to your email.');
    }



}
