<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        // Bandingkan password sebagai teks biasa (tanpa hash)
        if ($user && hash_equals((string) $user->password, $credentials['password'])) {

            if ($user->role !== 'pengelola') {
                return back()->withErrors(['email' => 'Akun ini bukan akun pengelola mitra.']);
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended(route('merchant.dashboard'));
        }

        return back()->withErrors(['email' => 'Email atau kata sandi salah.'])
                    ->onlyInput('email');
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.home');
    }
}