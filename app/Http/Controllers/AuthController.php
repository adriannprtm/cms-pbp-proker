<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kreait\Laravel\Firebase\Facades\Firebase;

class AuthController extends Controller
{
    public function index(){
        return view('Auth.login');
    }

    public function login(Request $request)
    {
        $validator = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $firebaseAuth = Firebase::auth();
            $signInResult = $firebaseAuth->signInWithEmailAndPassword($request->email, $request->password);

            $idToken = $signInResult->idToken();
            session(['firebase_id_token' => $idToken]); // Simpan token ke session

            return redirect('/dashboard');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Login gagal, silakan coba lagi.']);
        }
    }


    public function logout()
    {
        session()->forget('firebase_id_token'); // Hapus token dari session
        Auth::logout();
        return redirect('/login');
    }
}

