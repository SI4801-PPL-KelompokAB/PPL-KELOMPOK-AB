@extends('layouts.app')

@section('title', 'Profil Administrator')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-dark mb-1">Pengaturan Akun &amp; Profil Admin</h4>
                <p class="text-muted small mb-0">Kelola informasi data diri, kontak, dan kredensial akses administrator Bonamu</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>

        <div class="row g-4">
            {{-- Kolom Kiri: Form Edit Profil --}}
            <div class="col-md-7 col-lg-8">
                <div class="card-mockup">
                    <div class="card-mockup-header border-bottom pb-3">
                        <h5 class="fw-bold text-dark mb-0">Data Akun Administrator</h5>
                        <small class="text-muted">Perbarui data nama, email, nomor kontak, atau ganti kata sandi</small>
                    </div>
                    <div class="card-mockup-body">
                        <form method="POST" action="{{ route('admin.profil.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Nama Lengkap <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $admin->name) }}" required>
                                </div>
                                @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Alamat Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $admin->email) }}" required>
                                </div>
                                @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-telephone"></i></span>
                                    <input type="text" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror" value="{{ old('no_hp', $admin->no_hp) }}" required>
                                </div>
                                @error('no_hp')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <hr class="my-4 text-muted opacity-25">

                            <h6 class="fw-bold text-dark mb-1">Ganti Password (Opsional)</h6>
                            <p class="text-muted small mb-3">Kosongkan jika tidak ingin mengubah kata sandi.</p>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Password Baru (Minimal 8 Karakter)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-key"></i></span>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Masukkan password baru jika ingin diganti">
                                </div>
                                @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-secondary">Konfirmasi Password Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-check2-circle"></i></span>
                                    <input type="password" name="password_confirmation" class="form-control" placeholder="Ketik ulang password baru">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-3 px-3">Batal</a>
                                <button type="submit" class="btn btn-app-primary rounded-3 px-4">
                                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Info Ringkas Akun --}}
            <div class="col-md-5 col-lg-4">
                <div class="card-mockup p-4 text-center mb-4">
                    <div class="rounded-circle mx-auto p-3 mb-3 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background-color: #d1f4ed; color: #005954;">
                        <i class="bi bi-person-fill-gear fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">{{ $admin->name }}</h5>
                    <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold text-uppercase" style="font-size: 0.7rem;">
                        {{ strtoupper($admin->role) }}
                    </span>

                    <div class="border-top text-start pt-3 mt-4">
                        <div class="mb-2">
                            <small class="text-muted d-block">Cabang Klinik:</small>
                            <span class="small fw-semibold text-dark">
                                {{ $admin->cabang->nama_cabang ?? 'Klinik Bonamu Pusat' }}
                            </span>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Status Akun:</small>
                            <span class="status-dot-active">&bull; Aktif</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Terdaftar Sejak:</small>
                            <span class="small text-muted">
                                {{ $admin->created_at ? $admin->created_at->translatedFormat('d F Y') : '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
