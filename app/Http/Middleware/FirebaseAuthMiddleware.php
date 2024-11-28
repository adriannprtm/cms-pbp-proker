<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FirebaseAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Periksa apakah user memiliki token di session
        $idToken = session('firebase_id_token');

        if (!$idToken) {
            return redirect('/login')->withErrors(['error' => 'Silakan login terlebih dahulu.']);
        }

        try {
            // Validasi token menggunakan Firebase Auth
            $verifiedIdToken = Firebase::auth()->verifyIdToken($idToken);

            // Jika berhasil, lanjutkan ke request berikutnya
            return $next($request);
        } catch (\Exception $e) {
            // Jika token tidak valid atau gagal diverifikasi
            return redirect('/login')->withErrors(['error' => 'Sesi Anda telah berakhir. Silakan login kembali.']);
        }
    }
}
