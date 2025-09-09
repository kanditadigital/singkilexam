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

    /**
     * Sign out the user.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function signOut(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        toast('Anda berhasil logout', 'success');
        return redirect('/');
    }
}
