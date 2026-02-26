<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show()
    {
        return redirect()->route('account.settings');
    }

    public function edit()
    {
        return redirect()->route('account.settings');
    }

    public function accountSettings()
    {
        return view('profile.account_settings', ['user' => Auth::user()]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();
        $user->fill($validated);
        $user->save();
        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);
        if (array_key_exists('bio', $validated)) $profile->bio = $validated['bio'];
        if (array_key_exists('phone', $validated)) $profile->phone = $validated['phone'];
        if (array_key_exists('address', $validated)) $profile->address = $validated['address'];
        if (array_key_exists('preferences', $validated)) $profile->preferences = $validated['preferences'];
        $profile->save();
        return redirect()->route('account.settings')->with('settings_success', 'Profile updated successfully.');
    }

    public function avatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);
        $file = $request->file('avatar');
        $path = 'avatars/'.Auth::id().'-'.time().'.png';
        $imageData = file_get_contents($file->getPathname());
        // Attempt simple square resize via GD if available
        if (function_exists('imagecreatefromstring')) {
            $src = imagecreatefromstring($imageData);
            if ($src) {
                $w = imagesx($src); $h = imagesy($src);
                $size = 256;
                $dst = imagecreatetruecolor($size, $size);
                $min = min($w, $h);
                $sx = (int)(($w - $min) / 2);
                $sy = (int)(($h - $min) / 2);
                imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $size, $size, $min, $min);
                ob_start(); imagepng($dst); $imageData = ob_get_clean();
                imagedestroy($dst); imagedestroy($src);
            }
        }
        Storage::disk('public')->put($path, $imageData);
        $user = Auth::user();
        $user->avatar_path = $path;
        $user->save();
        return back()->with('status', 'Avatar updated.');
    }

    public function password(ChangePasswordRequest $request)
    {
        $user = Auth::user();
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }
        $user->password = Hash::make($request->input('password'));
        $user->save();
        return redirect()->route('account.settings')->with('password_success', 'Password updated successfully.');
    }
}
