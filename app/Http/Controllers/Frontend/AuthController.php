<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }
    public function handleLogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Kirim request ke API login Laravel Passport
        $response = Http::timeout(10)->post('http://127.0.0.1:8000/api/auth/login', [
            'email' => $request->email,
            'password' => $request->password,
        ]);
        $data = $response->json();

        if ($response->status() == 200) {
            session(['token' => $data['data']['token']]); // Simpan token di session
            return redirect()->route('dashboard');
        } else {
            return back()->with('error', $data['message']);
        }
    }

}
