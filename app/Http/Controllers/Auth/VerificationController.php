<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function verify(Request $request, User $user)
    {
        abort_unless($request->hasValidSignature(), 403, 'This verification link is invalid or expired.');
        abort_unless(hash_equals(sha1($user->getEmailForVerification()), (string) $request->query('hash')), 403, 'This verification link is invalid.');

        if (is_null($user->email_verified_at)) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return redirect()->route('login')->with('status', 'Email verified successfully. Please wait for admin approval.');
    }
}
