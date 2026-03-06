<?php

namespace App\Http\Controllers;

use App\Models\DocumentGalleryItem;
use App\Models\UploadedDocument;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $user = Auth::user();
        $user->loadMissing(['profile', 'personalInformation']);
        $isGoogleSignup = Hash::check('google-oauth', (string) $user->password);
        $galleryItems = DocumentGalleryItem::where('user_id', $user->id)
            ->latest('updated_at')
            ->get();
        $documentTypeOptions = array_values(array_filter(
            UploadedDocument::DOCUMENTS,
            fn ($docType) => $docType !== 'isApproved'
        ));

        return view('profile.account_settings', [
            'user' => $user,
            'personalInfo' => $user->personalInformation,
            'isGoogleSignup' => $isGoogleSignup,
            'galleryItems' => $galleryItems,
            'documentTypeOptions' => $documentTypeOptions,
        ]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();
        $middleInitial = filled($validated['middle_name'] ?? null)
            ? strtoupper(mb_substr(trim((string) $validated['middle_name']), 0, 1)) . '.'
            : '';
        $validated['name'] = trim(implode(' ', array_filter([
            trim((string) ($validated['first_name'] ?? '')),
            $middleInitial,
            trim((string) ($validated['last_name'] ?? '')),
        ])));
        if (array_key_exists('phone', $validated)) {
            $validated['phone_number'] = preg_replace('/\D+/', '', (string) $validated['phone']);
        }
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $path = 'avatars/' . Auth::id() . '-' . time() . '.png';
            $imageData = file_get_contents($file->getPathname());
            // Attempt simple square resize via GD if available.
            if (function_exists('imagecreatefromstring')) {
                $src = imagecreatefromstring($imageData);
                if ($src) {
                    $w = imagesx($src);
                    $h = imagesy($src);
                    $size = 256;
                    $dst = imagecreatetruecolor($size, $size);
                    $min = min($w, $h);
                    $sx = (int) (($w - $min) / 2);
                    $sy = (int) (($h - $min) / 2);
                    imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $size, $size, $min, $min);
                    ob_start();
                    imagepng($dst);
                    $imageData = ob_get_clean();
                    imagedestroy($dst);
                    imagedestroy($src);
                }
            }
            Storage::disk('public')->put($path, $imageData);
            $validated['avatar_path'] = $path;
        }
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

    public function storeGalleryDocument(Request $request)
    {
        $documentTypeOptions = array_values(array_filter(
            UploadedDocument::DOCUMENTS,
            fn ($docType) => $docType !== 'isApproved'
        ));

        $validated = $request->validate([
            'gallery_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'document_type' => ['nullable', 'string', 'in:' . implode(',', $documentTypeOptions)],
        ]);

        $user = Auth::user();
        $file = $request->file('gallery_document');
        $originalName = $file->getClientOriginalName();
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $safeBaseName = Str::of($baseName)->replaceMatches('/[^A-Za-z0-9\-_]+/', '_')->trim('_')->limit(50, '');
        $safeBaseName = $safeBaseName === '' ? 'document' : (string) $safeBaseName;
        $storedName = now()->format('YmdHis') . '_' . Str::random(8) . '_' . $safeBaseName . ($extension !== '' ? ".{$extension}" : '');
        $storagePath = $file->storeAs("document_gallery/{$user->id}", $storedName, 'public');

        DocumentGalleryItem::create([
            'user_id' => $user->id,
            'document_type' => $validated['document_type'] ?? null,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'storage_path' => $storagePath,
            'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
            'file_size_8b' => (int) $file->getSize(),
        ]);

        return redirect()
            ->route('account.settings')
            ->with('document_gallery_success', 'Document uploaded to your gallery.');
    }

    public function previewGalleryDocument(DocumentGalleryItem $item)
    {
        if ((int) $item->user_id !== (int) Auth::id()) {
            abort(403);
        }

        if (!Storage::disk('public')->exists($item->storage_path)) {
            return redirect()
                ->route('account.settings')
                ->withErrors(['gallery_document' => 'The selected file is missing from storage.']);
        }

        $mimeType = (string) ($item->mime_type ?: Storage::disk('public')->mimeType($item->storage_path) ?: 'application/octet-stream');
        $contentDisposition = in_array($mimeType, ['application/pdf', 'image/jpeg', 'image/png'], true)
            ? 'inline'
            : 'attachment';

        return response(Storage::disk('public')->get($item->storage_path), 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => $contentDisposition . '; filename="' . addslashes($item->original_name) . '"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public',
        ]);
    }

    public function downloadGalleryDocument(DocumentGalleryItem $item)
    {
        if ((int) $item->user_id !== (int) Auth::id()) {
            abort(403);
        }

        if (!Storage::disk('public')->exists($item->storage_path)) {
            return redirect()
                ->route('account.settings')
                ->withErrors(['gallery_document' => 'The selected file is missing from storage.']);
        }

        return Storage::disk('public')->download($item->storage_path, $item->original_name);
    }

    public function deleteGalleryDocument(DocumentGalleryItem $item)
    {
        if ((int) $item->user_id !== (int) Auth::id()) {
            abort(403);
        }

        if (!empty($item->storage_path) && Storage::disk('public')->exists($item->storage_path)) {
            Storage::disk('public')->delete($item->storage_path);
        }

        $item->delete();

        return redirect()
            ->route('account.settings')
            ->with('document_gallery_success', 'Document removed from your gallery.');
    }
}
