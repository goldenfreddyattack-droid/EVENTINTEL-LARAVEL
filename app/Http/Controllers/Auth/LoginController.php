<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function username()
    {
        return 'login';
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        $login = trim((string) $request->input('login'));
        $password = (string) $request->input('password');

        $user = User::where('email', $login)->first();

        if ($user && Hash::check($password, $user->password)) {
            if (is_null($user->email_verified_at)) {
                return back()
                    ->withInput($request->only('login', 'password'))
                    ->withErrors(['login' => 'Please verify your email address before logging in.']);
            }

            if (($user->status ?? null) === 'pending' && ($user->role ?? null) !== 'client') {
                return back()
                    ->withInput($request->only('login', 'password'))
                    ->withErrors(['login' => 'Your account is still waiting for admin approval.']);
            }

            if (($user->status ?? null) === 'rejected') {
                return back()
                    ->withInput($request->only('login', 'password'))
                    ->withErrors(['login' => 'Your profile has been rejected and cannot be used to log in.']);
            }

            Auth::login($user, $request->boolean('remember'));

            $request->session()->regenerate();

            if (($user->role ?? null) === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            if (($user->role ?? null) === 'supplier') {
                return redirect('/supplier/DASHBOARD');
            }

            if (($user->role ?? null) === 'coordinator') {
                return redirect()->route('coordinator.dashboard');
            }

            if (($user->role ?? null) === 'client') {
                return redirect()->route('home');
            }

            return redirect()->intended($this->redirectPath());
        }

        return back()
            ->withInput($request->only('login', 'password'))
            ->withErrors(['login' => trans('auth.failed')]);
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
    }
}
