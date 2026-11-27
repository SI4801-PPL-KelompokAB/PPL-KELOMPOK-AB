@extends('layouts.app')

@section('title', 'Profil Pasien')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-dark mb-1">Profil &amp; Identitas Pasien</h4>
                <p class="text-muted small mb-0">Lengkapi data identitas domisili dan kepesertaan BPJS untuk keperluan pendaftaran antrean (PBI-04)</p>
            </div>
            <a href="{{ route('pasien.dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>

        <div class="row g-4">
            {{-- Kolom Kiri: Form Identitas Pasien --}}
            <div class="col-md-7 col-lg-8">
                <div class="card-mockup">
                    <div class="card-mockup-header border-bottom pb-3">
                        <h5 class="fw-bold text-dark mb-0">Form Data Identitas Pasien</h5>
                        <small class="text-muted">Profil lengkap merupakan syarat wajib pendaftaran antrean online</small>
                    </div>
                    <div class="card-mockup-body">
                        <form method="POST" action="{{ route('pasien.profil.update') }}" dusk="form-profil">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Tanggal Lahir <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-calendar-event"></i></span>
                                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', optional($profil->tanggal_lahir)->format('Y-m-d')) }}" dusk="tanggal_lahir" required class="form-control @error('tanggal_lahir') is-invalid @enderror">
                                </div>
                                @error('tanggal_lahir')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Jenis Kelamin <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-gender-ambiguous"></i></span>
                                    <select name="jenis_kelamin" dusk="jenis_kelamin" required class="form-select @error('jenis_kelamin') is-invalid @enderror">
                                        <option value="">-- Pilih Jenis Kelamin --</option>
                                        <option value="L" @selected(old('jenis_kelamin', $profil->jenis_kelamin) === 'L')>Laki-laki (L)</option>
                                        <option value="P" @selected(old('jenis_kelamin', $profil->jenis_kelamin) === 'P')>Perempuan (P)</option>
                                    </select>
                                </div>
                                @error('jenis_kelamin')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Alamat Domisili <span class="text-danger">*</span></label>
                                <textarea name="alamat" dusk="alamat" rows="3" required class="form-control @error('alamat') is-invalid @enderror" placeholder="Masukkan alamat lengkap tempat tinggal">{{ old('alamat', $profil->alamat) }}</textarea>
                                @error('alamat')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Kategori Pasien <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-card-heading"></i></span>
                                    <select name="jenis_pasien" id="jenis_pasien" dusk="jenis_pasien" required class="form-select @error('jenis_pasien') is-invalid @enderror">
                                        <option value="">-- Pilih Kategori Pasien --</option>
                                        <option value="BPJS" @selected(old('jenis_pasien', $profil->jenis_pasien) === 'BPJS')>BPJS Kesehatan</option>
                                        <option value="UMUM" @selected(old('jenis_pasien', $profil->jenis_pasien) === 'UMUM')>Pasien Umum / Mandiri</option>
                                    </select>
                                </div>
                                @error('jenis_pasien')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-secondary">
                                    Nomor Kartu BPJS <small class="text-muted">(Wajib bila BPJS, kosong bila UMUM)</small>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-credit-card-2-front"></i></span>
                                    <input type="text" name="no_bpjs" value="{{ old('no_bpjs', $profil->no_bpjs) }}" dusk="no_bpjs" class="form-control @error('no_bpjs') is-invalid @enderror" placeholder="Contoh: 0001234567890">
                                </div>
                                @error('no_bpjs')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('pasien.dashboard') }}" class="btn btn-outline-secondary rounded-3 px-3">Batal</a>
                                <button type="submit" dusk="tombol-simpan-profil" class="btn btn-app-primary rounded-3 px-4">
                                    <i class="bi bi-save me-1"></i> Simpan Profil
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Ringkasan Akun & Status Profil --}}
            <div class="col-md-5 col-lg-4">
                <div class="card-mockup p-4 text-center mb-4">
                    <div class="rounded-circle mx-auto p-3 mb-3 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background-color: #d1f4ed; color: #005954;">
                        <i class="bi bi-person-heart fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">{{ auth()->user()->name }}</h5>
                    <span class="badge bg-primary text-white px-3 py-1 rounded-pill fw-bold text-uppercase" style="font-size: 0.7rem;">
                        PASIEN TERDAFTAR
                    </span>

                    <div class="border-top text-start pt-3 mt-4">
                        <div class="mb-2">
                            <small class="text-muted d-block">Nomor Telepon:</small>
                            <span class="small fw-semibold text-dark">{{ auth()->user()->no_hp }}</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Email Akun:</small>
                            <span class="small fw-semibold text-dark">{{ auth()->user()->email }}</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Kelengkapan Profil:</small>
                            @if($profil && $profil->isLengkap())
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-check-circle-fill me-1"></i> Lengkap
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Belum Lengkap
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Kotak Info PBI-04 --}}
                <div class="card-mockup p-3 bg-light border-0">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-info-circle-fill text-primary fs-5 mt-1"></i>
                        <small class="text-muted">
                            Data profil ini digunakan untuk mencetak identitas pada <strong>Tiket Antrean Pasien (PBI-09)</strong> dan rekam riwayat kunjungan ke klinik.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
