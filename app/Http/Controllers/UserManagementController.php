<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAkunAdminRequest;
use App\Models\User;

class UserManagementController extends Controller
{
    public function index()
    {
        $this->pastikanAdmin();

        $akuns = User::whereIn('role', ['dokter', 'admin'])
            ->orderBy('name')
            ->get();

        return view('admin.akun.index', compact('akuns'));
    }

    public function create()
    {
        $this->pastikanAdmin();

        return view('admin.akun.create');
    }

    public function store(StoreAkunAdminRequest $request)
    {
        $this->pastikanAdmin();

        $data = $request->validated();

        $akun = User::create([...$data, 'is_active' => true]);

        return redirect()->route('admin.akun.index')
            ->with('success', "Akun {$akun->name} ({$akun->role}) berhasil dibuat.");
    }

    private function pastikanAdmin(): void
    {
        // Pola penegakan role di codebase saat ini: cek langsung di controller.
        abort_unless(auth()->user()?->role === 'admin', 403);
    }
}
