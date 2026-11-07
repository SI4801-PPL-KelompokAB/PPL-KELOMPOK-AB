@extends('layouts.konsultasi')

@section('title', 'Buat Akun')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-headline-md font-headline-md text-on-surface">Buat akun dokter / admin</h1>
            <p class="text-body-sm text-outline">Hanya admin yang bisa membuat akun ini (PBI03).</p>
        </div>
        <a href="{{ route('admin.akun.index') }}" class="border border-outline-variant text-on-surface hover:bg-surface-container-low px-4 py-2 rounded-xl font-body-semibold text-body-default">Daftar akun</a>
    </div>

    @if ($errors->any())
        <div class="bg-error-container text-on-error-container text-body-default rounded-xl px-4 py-3">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.akun.store') }}" dusk="form-buat-akun" class="bg-surface-container-lowest rounded-2xl border border-outline-variant/40 shadow-sm p-6 space-y-4">
        @csrf
        <div>
            <label class="block font-body-semibold text-body-default mb-1">Nama lengkap</label>
            <input type="text" name="name" value="{{ old('name') }}" dusk="nama" required class="w-full border border-outline-variant rounded-xl px-3 py-2 text-body-default">
            @error('name')<p class="text-error text-body-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block font-body-semibold text-body-default mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" dusk="email" required class="w-full border border-outline-variant rounded-xl px-3 py-2 text-body-default">
            @error('email')<p class="text-error text-body-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block font-body-semibold text-body-default mb-1">Nomor telepon</label>
            <input type="text" name="no_hp" value="{{ old('no_hp') }}" dusk="no_hp" required class="w-full border border-outline-variant rounded-xl px-3 py-2 text-body-default">
            @error('no_hp')<p class="text-error text-body-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block font-body-semibold text-body-default mb-1">Password (min. 8 karakter)</label>
            <input type="password" name="password" dusk="password" required class="w-full border border-outline-variant rounded-xl px-3 py-2 text-body-default">
            @error('password')<p class="text-error text-body-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block font-body-semibold text-body-default mb-1">Konfirmasi password</label>
            <input type="password" name="password_confirmation" dusk="password_confirmation" required class="w-full border border-outline-variant rounded-xl px-3 py-2 text-body-default">
        </div>
        <div>
            <label class="block font-body-semibold text-body-default mb-1">Role</label>
            <select name="role" dusk="role" required class="w-full border border-outline-variant rounded-xl px-3 py-2 text-body-default bg-white">
                <option value="">Pilih role</option>
                <option value="dokter" @selected(old('role') === 'dokter')>Dokter</option>
                <option value="admin" @selected(old('role') === 'admin')>Admin</option>
            </select>
            @error('role')<p class="text-error text-body-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <button type="submit" dusk="tombol-simpan-akun" class="bg-primary-container hover:bg-primary text-on-primary px-5 py-2.5 rounded-xl font-body-semibold text-body-default">Simpan akun</button>
    </form>
</div>
@endsection
