<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ProfileUpdateRequest;

class ProfileController extends Controller
{
    public function show()
    {
        return view('userui.profile', ['user' => auth()->user()]);
    }

    public function update(ProfileUpdateRequest $request)
    {
        if ($request->password) {
            auth()->user()->update(['password' => Hash::make($request->password)]);
        }

        auth()->user()->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->back()->with('success', 'Profile updated.');
    }

    public function applyRole(Request $request)
    {
        $data = $request->validate([
            'apply_role' => ['required', 'in:supplier,coordinator'],
            'business_name' => ['required', 'string', 'max:150'],
            'business_address' => ['required', 'string', 'max:255'],
            'valid_id' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'business_permit' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'face_capture' => ['nullable', 'string'],
        ]);

        $user = auth()->user();
        $values = [
            'role' => $data['apply_role'],
            'status' => 'pending',
            'business_name' => trim($data['business_name']),
            'business_address' => trim($data['business_address']),
        ];

        foreach (['valid_id' => 'ids', 'business_permit' => 'permits'] as $field => $folder) {
            if ($request->hasFile($field)) {
                $values[$field] = $this->storePublicUpload($request->file($field), $folder, $field);
            }
        }

        $facePath = $this->storeFaceCapture($data['face_capture'] ?? null);
        if ($facePath) {
            $values['face_capture'] = $facePath;
        }

        DB::table('users')->where('user_id', $user->user_id)->update($values);

        return redirect()->route('profile.show')->with('success', 'Application submitted as ' . ucfirst($data['apply_role']) . '. Admin will review it shortly.');
    }

    private function storePublicUpload($file, string $folder, string $prefix): string
    {
        $directory = public_path('uploads/' . $folder);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $name = $prefix . '_' . uniqid('', true) . '.' . $file->extension();
        $file->move($directory, $name);

        return 'uploads/' . $folder . '/' . $name;
    }

    private function storeFaceCapture(?string $faceData): ?string
    {
        if (! $faceData || ! preg_match('#^data:image/[^;]+;base64,(.*)$#s', $faceData, $matches)) {
            return null;
        }

        $raw = base64_decode($matches[1], true);
        if ($raw === false || strlen($raw) > 5 * 1024 * 1024) {
            return null;
        }

        $directory = public_path('uploads/faces');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $name = 'face_' . uniqid('', true) . '.png';
        file_put_contents($directory . DIRECTORY_SEPARATOR . $name, $raw);

        return 'uploads/faces/' . $name;
    }
}
