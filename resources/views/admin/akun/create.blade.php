@extends('layouts.app')

@section('title', 'Buat Akun Staf Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-dark mb-1">Buat Akun Staf Baru</h4>
                <p class="text-muted small mb-0">Hanya administrator yang memiliki wewenang untuk mendaftarkan akun dokter dan admin baru (PBI-03)</p>
            </div>
            <a href="{{ route('admin.akun.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>

        <div class="card-mockup">
            <div class="card-mockup-header border-bottom pb-3">
                <h5 class="fw-bold text-dark mb-0">Form Pembuatan Akun</h5>
                <small class="text-muted">Lengkapi data akun staf di bawah ini dengan benar</small>
            </div>
            <div class="card-mockup-body">
                <form method="POST" action="{{ route('admin.akun.store') }}" dusk="form-buat-akun">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Role Akses <span class="text-danger">*</span></label>
                        <select name="role" dusk="role" required class="form-select @error('role') is-invalid @enderror">
                            <option value="dokter" @selected(old('role') === 'dokter')>Dokter Praktik</option>
                            <option value="admin" @selected(old('role') === 'admin')>Administrator Puskesmas</option>
                        </select>
                        @error('role')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nama Lengkap <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                            <input type="text" name="name" value="{{ old('name') }}" dusk="nama" required class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: dr. Andi Wijaya">
                        </div>
                        @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Alamat Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" value="{{ old('email') }}" dusk="email" required class="form-control @error('email') is-invalid @enderror" placeholder="Contoh: dokter.andi@bonamu.com">
                        </div>
                        @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-telephone"></i></span>
                            <input type="text" name="no_hp" value="{{ old('no_hp') }}" dusk="no_hp" required class="form-control @error('no_hp') is-invalid @enderror" placeholder="Contoh: 081234567890">
                        </div>
                        @error('no_hp')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-key"></i></span>
                                <input type="password" name="password" dusk="password" required class="form-control @error('password') is-invalid @enderror" placeholder="Min. 8 karakter">
                            </div>
                            @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Konfirmasi Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-check2-circle"></i></span>
                                <input type="password" name="password_confirmation" dusk="password_confirmation" required class="form-control" placeholder="Ulangi password">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.akun.index') }}" class="btn btn-outline-secondary rounded-3 px-3">Batal</a>
                        <button type="submit" dusk="tombol-submit" class="btn btn-app-primary rounded-3 px-4">
                            <i class="bi bi-check-circle me-1"></i> Buat Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
