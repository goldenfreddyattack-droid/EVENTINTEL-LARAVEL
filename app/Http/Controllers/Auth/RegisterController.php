<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'age' => ['nullable', 'integer', 'min:18'],
            'gender' => ['nullable', 'string'],
            'phone' => ['required', 'string', 'max:20'],
            'province' => ['required', 'string', 'max:100'],
            'municipality' => ['required', 'string', 'max:100'],
            'barangay' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', 'in:client,supplier,coordinator,admin'],
            'data_privacy_consent' => ['accepted'],
        ]);
    }

    protected function create(array $data)
    {
        $fullName = trim(($data['first_name'] ?? '') . ' ' . ($data['middle_initial'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        $fullName = preg_replace('/\s+/', ' ', trim($fullName));

        return User::create([
            'username' => $data['username'],
            'full_name' => $fullName,
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'middle_initial' => $data['middle_initial'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'client',
            'status' => 'pending',
            'age' => $data['age'] ?? null,
            'gender' => $data['gender'] ?? null,
            'phone' => $data['phone'] ?? null,
            'province' => $data['province'] ?? null,
            'municipality' => $data['municipality'] ?? null,
            'barangay' => $data['barangay'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
        ]);
    }

    protected function registered(Request $request, $user)
    {
        return redirect()->route('login')->with('status', 'Account created. Please wait for admin approval.');
    }
}
