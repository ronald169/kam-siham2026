<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Redirection basée sur le rôle
            $user = Auth::user();

            return redirect()->intended($this->redirectTo());
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email');
    }

    protected function redirectTo()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return route('admin.dashboard');
        } elseif ($user->isMedecin()) {
            return route('medecin.dashboard');
        } elseif ($user->isConsultant()) {
            return route('consultant.dashboard');
        }

        return route('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
