<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Display the sign in view.
     *
     * @return \Illuminate\View\View
     */
    public function formSignIn()
    {
        return view('auth.signin',[
            'title'     => 'Selamat Datang di Examdita'
        ]);
    }

    public function formSignParticipate()
    {
        return view('auth.login',[
            'title'     => 'Selamat Datang di Examdita'
        ]);
    }


    /**
     * Sign in the user.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function signIn(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if(Auth::attempt($credentials)){
            $request->session()->regenerate();
            return redirect()->intended('/disdik/home');
        }else{
            toast('Anda gagal login', 'error');
            return redirect()->back();
        }
    }

    /**
     * Site Login
     * Sekolah
     * Cabdin
     */
    public function myAuth(Request $request)
    {
        $request->validate([
            'email'     => 'required|email',
            'password'  => 'required',
            'captcha'   => 'required|captcha'
        ],[
            'captcha.captcha'   => 'Kode keamanan salah, coba lagi.'
        ]);

        // Login Cabdin
        if (Auth::guard('branches')->attempt(
            $request->only('email', 'password'),
        )) {
            $request->session()->regenerate();
            return redirect()->intended(route('cabdin.dashboard'));
        }

        // Login Sekolah
        if (Auth::guard('schools')->attempt(
            array_merge($request->only('email', 'password'), ['is_active' => 1]),
        )) {
            $request->session()->regenerate();
            return redirect()->intended('/sch/dashboard');
        }

        // Jika gagal
        return back()->withErrors([
            'email' => 'Email atau password salah, atau akun belum aktif.',
        ]);
    }

    public function signParticipate(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('students')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/std/confirmation');
        }

        if (Auth::guard('employees')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/std/confirmation');
        }

        toast('Anda gagal login', 'error');
        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    /**
     * Sign out the user.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        if (Auth::guard('branches')->check()) {
            Auth::guard('branches')->logout();
        } elseif (Auth::guard('schools')->check()) {
            Auth::guard('schools')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Anda berhasil logout.');
    }


    public function stdOut(Request $request)
    {
        // Check which guard is currently authenticated and logout from that guard
        if (Auth::guard('students')->check()) {
            Auth::guard('students')->logout();
        }else {
            Auth::logout(); // fallback for default guard
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        toast('Anda berhasil logout', 'success');
        return redirect('/bro-login');
    }

    /**
     * Get the current authenticated user type and details
     *
     * @return array
     */
    public function getCurrentUser()
    {
        if (Auth::guard('students')->check()) {
            return [
                'type' => 'student',
                'user' => Auth::guard('students')->user(),
                'guard' => 'students'
            ];
        } elseif (Auth::guard('employees')->check()) {
            return [
                'type' => 'employee',
                'user' => Auth::guard('employees')->user(),
                'guard' => 'employees'
            ];
        } elseif (Auth::check()) {
            return [
                'type' => 'admin',
                'user' => Auth::user(),
                'guard' => 'web'
            ];
        }

        return [
            'type' => null,
            'user' => null,
            'guard' => null
        ];
    }
}
