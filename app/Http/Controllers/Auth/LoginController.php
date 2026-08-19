<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

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

        $columns = Schema::getColumnListing('users');

        $user = null;

        if (in_array('username', $columns) && in_array('email', $columns)) {
            $user = User::where('username', $login)
                ->orWhere('email', $login)
                ->first();
        } elseif (in_array('username', $columns)) {
            $user = User::where('username', $login)->first();
        } elseif (in_array('email', $columns)) {
            $user = User::where('email', $login)->first();
        }

        if ($user && Hash::check($password, $user->password)) {
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

            return redirect()->intended($this->redirectPath());
        }

        throw ValidationException::withMessages([
            'login' => [trans('auth.failed')],
        ]);
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
    }
}
