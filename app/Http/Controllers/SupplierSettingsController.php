<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->role !== 'supplier') {
                abort(403, 'Unauthorized. Supplier access only.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $user = Auth::user();
        $notificationSettings = session('notification_settings', [
            'booking_alerts' => true,
            'messages' => true,
            'promotions' => false,
        ]);

        return view('supplier.settings', compact('user', 'notificationSettings'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if ($request->has('update_profile')) {
            $validated = $request->validate([
                'full_name' => ['required', 'string', 'max:150'],
                'email' => ['required', 'email'],
                'phone' => ['nullable', 'string', 'max:20'],
            ]);

            DB::table('users')->where('user_id', $user->user_id)->update([
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
            ]);

            $request->session()->put('full_name', $validated['full_name']);
            return redirect()->route('supplier.settings')->with('success', 'Profile updated successfully.');
        }

        if ($request->has('change_password')) {
            $request->validate([
                'current_password' => ['required'],
                'new_password' => ['required', 'string', 'min:6'],
                'confirm_password' => ['required', 'same:new_password'],
            ]);

            if (!password_verify($request->input('current_password'), $user->password)) {
                return redirect()->route('supplier.settings')->with('error', 'Current password is incorrect.');
            }

            DB::table('users')->where('user_id', $user->user_id)->update([
                'password' => bcrypt($request->input('new_password')),
            ]);

            return redirect()->route('supplier.settings')->with('success', 'Password changed successfully.');
        }

        if ($request->has('save_notifications')) {
            $settings = [
                'booking_alerts' => $request->has('booking_alerts'),
                'messages' => $request->has('messages'),
                'promotions' => $request->has('promotions'),
            ];

            $request->session()->put('notification_settings', $settings);
            return redirect()->route('supplier.settings')->with('success', 'Notification preferences saved.');
        }

        return redirect()->route('supplier.settings');
    }
}
