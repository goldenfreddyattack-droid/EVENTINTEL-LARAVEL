<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless(auth()->user()->role === 'admin', 403, 'Administrator access only.');

            return $next($request);
        });
    }

    public function dashboard()
    {
        return view('admin.dashboard', ['stats' => $this->stats()]);
    }

    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:100'],
            'middle_initial' => ['nullable', 'string', 'max:10'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'same:confirm_password'],
            'confirm_password' => ['required', 'string'],
            'role' => ['required', Rule::in(['client', 'supplier', 'coordinator', 'admin'])],
            'status' => ['required', Rule::in(['approved', 'pending', 'rejected'])],
            'phone' => ['nullable', 'string', 'max:30'],
            'age' => ['nullable', 'integer', 'min:13'],
            'gender' => ['nullable', 'string', 'max:50'],
            'province' => ['nullable', 'string', 'max:100'],
            'municipality' => ['nullable', 'string', 'max:100'],
            'barangay' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'business_name' => ['nullable', 'string', 'max:150'],
            'business_address' => ['nullable', 'string', 'max:255'],
        ]);

        $firstName = trim($validated['first_name'] ?? '');
        $middleInitial = trim($validated['middle_initial'] ?? '');
        $lastName = trim($validated['last_name'] ?? '');
        $fullName = trim("{$firstName} {$middleInitial} {$lastName}") ?: $validated['username'];

        DB::table('users')->insert([
            'username' => $validated['username'],
            'full_name' => $fullName,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status' => $validated['status'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'middle_initial' => $middleInitial,
            'age' => $validated['age'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'province' => $validated['province'] ?? null,
            'municipality' => $validated['municipality'] ?? null,
            'barangay' => $validated['barangay'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'business_name' => $validated['business_name'] ?? null,
            'business_address' => $validated['business_address'] ?? null,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Account created successfully.');
    }

    public function requests()
    {
        $requests = DB::table('users')
            ->whereIn('role', ['supplier', 'coordinator'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.requests', [
            'requests' => $requests,
            'stats' => $this->stats(),
        ]);
    }

    public function updateRequest(Request $request, $userId)
    {
        $status = $request->validate(['status' => ['required', Rule::in(['approved', 'rejected'])]])['status'];

        DB::table('users')
            ->where('user_id', $userId)
            ->whereIn('role', ['supplier', 'coordinator'])
            ->update(['status' => $status]);

        return redirect()->route('admin.requests')->with('success', "User {$status} successfully.");
    }

    public function legacyDashboard()
    {
        return view('admin.legacy-dashboard', ['stats' => $this->stats()]);
    }

    private function stats(): array
    {
        return [
            'users' => DB::table('users')->count(),
            'pending' => DB::table('users')->where('status', 'pending')->count(),
            'events' => DB::table('events')->count(),
        ];
    }
}
