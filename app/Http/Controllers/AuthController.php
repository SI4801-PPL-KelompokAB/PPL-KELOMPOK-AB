<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Registrasi akun pasien.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'no_hp' => 'required|string|max:20|unique:users,no_hp',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'no_hp.required' => 'Nomor telepon wajib diisi.',
            'no_hp.unique' => 'Nomor telepon sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except([
                    'password',
                    'password_confirmation'
                ]));
        }

        $validated = $validator->validated();

        DB::transaction(function () use ($validated) {

            // Membuat akun pasien
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'no_hp' => $validated['no_hp'],
                'password' => $validated['password'],
                'role' => 'pasien',
                'is_active' => true,
            ]);

            // Membuat profil pasien kosong
            $user->patientProfile()->create([]);
        });

        return redirect('/login')
            ->with('success', 'Registrasi berhasil! Silakan login.');
    }

    /**
     * Menampilkan halaman registrasi pasien.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Menampilkan halaman login pengguna.
     */
    public function showLogin()
    {
        return view('auth.login');
    }



    /**
     * Login pasien, petugas, dan admin.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_hp' => 'required|string',
            'password' => 'required|string',
        ], [
            'no_hp.required' => 'Nomor telepon wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->only('no_hp'));
        }

        // Mencari akun berdasarkan nomor telepon
        $user = User::where('no_hp', $request->no_hp)->first();

        // Memeriksa akun dan password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return redirect()->back()
                ->withErrors([
                    'no_hp' => 'Nomor telepon atau password salah.'
                ])
                ->withInput($request->only('no_hp'));
        }

        // Memeriksa status akun
        if (!$user->is_active) {
            return redirect()->back()
                ->withErrors([
                    'no_hp' => 'Akun Anda sedang dinonaktifkan.'
                ])
                ->withInput($request->only('no_hp'));
        }

        // Membuat session login
        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended('/')
            ->with('success', 'Login berhasil. Selamat datang!');
    }

    /**
     * Logout pengguna.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')
            ->with('success', 'Anda berhasil logout.');
    }
}
