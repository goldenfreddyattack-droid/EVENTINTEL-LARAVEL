<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierProfileController extends Controller
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
        $profile = Auth::user();
        return view('supplier.profile', compact('profile'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'business_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'business_address' => ['nullable', 'string'],
        ]);

        DB::table('users')
            ->where('user_id', Auth::id())
            ->update([
                'full_name' => $validated['full_name'],
                'business_name' => $validated['business_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'business_address' => $validated['business_address'] ?? null,
            ]);

        $request->session()->put('full_name', $validated['full_name']);

        return redirect()->route('supplier.profile')->with('success', 'Profile updated successfully.');
    }
}
