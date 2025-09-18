<?php

namespace App\Http\Controllers;

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
            'title'     => 'Selamat Datang di Singkil Exam'
        ]);
    }

    public function formSignParticipate()
    {
        return view('auth.login',[
            'title'     => 'Selamat Datang di Singkil Exam'
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
            return redirect()->intended('/disdik/dashboard');
        }else{
            toast('Anda gagal login', 'error');
            return redirect()->back();
        }
    }

    public function signParticipate(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('students')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('std/confirmation');
        }else{
            toast('Anda gagal login', 'error');
            return redirect()->back();
        }

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
    public function signOut(Request $request)
    {
        // Check which guard is currently authenticated and logout from that guard
        if (Auth::guard('students')->check()) {
            Auth::guard('students')->logout();
        } elseif (Auth::guard('employees')->check()) {
            Auth::guard('employees')->logout();
        } else {
            Auth::logout(); // fallback for default guard
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        toast('Anda berhasil logout', 'success');
        return redirect('/');
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
