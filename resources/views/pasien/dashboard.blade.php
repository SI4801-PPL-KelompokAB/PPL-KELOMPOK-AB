@extends('layouts.app')

@section('title', 'Dashboard Pasien')

@section('content')
<div class="py-2">
    {{-- Banner Selamat Datang Pasien --}}
    <div class="card-mockup mb-4" style="background: linear-gradient(135deg, #005954 0%, #004743 100%); color: white;">
        <div class="p-4 p-md-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-light text-dark px-3 py-1 rounded-pill fw-bold text-uppercase mb-2" style="font-size: 0.7rem;">
                    <i class="bi bi-heart-pulse-fill text-danger me-1"></i> Layanan Pasien Bonamu
                </span>
                <h3 class="fw-bold mb-1">Halo, {{ $user->name }}!</h3>
                <p class="text-white-50 small mb-0">
                    Selamat datang di portal layanan antrean dan konsultasi kesehatan Bonamu.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('antrean.create') }}" class="btn btn-warning text-dark btn-sm fw-bold rounded-pill px-3 py-2 shadow-sm">
                    <i class="bi bi-ticket-perforated-fill me-1"></i> Ambil Antrean Sekarang
                </a>
                <a href="{{ route('pasien.profil.edit') }}" class="btn btn-outline-light btn-sm fw-semibold rounded-pill px-3 py-2">
                    <i class="bi bi-person-lines-fill me-1"></i> Data Profil
                </a>
            </div>
        </div>
    </div>

    {{-- Cek Profil Lengkap --}}
    @if(!$profil || !$profil->isLengkap())
        <div class="alert alert-warning border-0 shadow-sm rounded-4 p-3 mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-exclamation-triangle-fill fs-3 text-warning"></i>
                <div>
                    <strong class="d-block text-dark">Data Profil Pasien Belum Lengkap!</strong>
                    <small class="text-muted">Lengkapi tanggal lahir, jenis kelamin, dan alamat Anda agar dapat mendaftar antrean online (PBI-04).</small>
                </div>
            </div>
            <a href="{{ route('pasien.profil.edit') }}" class="btn btn-sm btn-dark rounded-pill px-3 py-1 text-nowrap">
                Lengkapi Profil Sekarang &rarr;
            </a>
        </div>
    @endif

    {{-- Card Tiket Antrean Aktif (Jika Pasien Punya Antrean Hari Ini) --}}
    @if($antreanAktif)
        <div class="card-mockup mb-4 p-4 border-2" style="border-color: #005954 !important; background-color: #f0fdf9;">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 p-3 text-white text-center" style="background-color: #005954; min-width: 90px;">
                        <span class="d-block small text-white-50 text-uppercase" style="font-size: 0.65rem;">Nomor</span>
                        <span class="fw-bold fs-4">{{ $antreanAktif->formatted_nomor }}</span>
                    </div>
                    <div>
                        <span class="badge bg-success px-2 py-1 rounded-pill mb-1">
                            <i class="bi bi-clock me-1"></i> Antrean Aktif
                        </span>
                        <h5 class="fw-bold mb-1 text-dark">
                            {{ $antreanAktif->doctorSchedule->poli->nama_poli ?? 'Poli' }}
                        </h5>
                        <p class="small text-muted mb-0">
                            Dokter: <strong>{{ $antreanAktif->doctorSchedule->doctor->user->name ?? '-' }}</strong> &bull;
                            Tanggal: {{ \Carbon\Carbon::parse($antreanAktif->tanggal_antrean)->translatedFormat('d F Y') }}
                        </p>
                    </div>
                </div>
                <div>
                    <a href="{{ route('antrean.show', $antreanAktif) }}" class="btn btn-app-primary rounded-pill px-4 py-2">
                        <i class="bi bi-qr-code me-1"></i> Lihat Tiket Digital
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- 4 Menu Akses Cepat Pasien --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('antrean.create') }}" class="text-decoration-none">
                <div class="card-mockup h-100 p-3 hover-shadow transition">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-3 p-3 bg-primary-subtle text-primary">
                            <i class="bi bi-ticket-perforated fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Ambil Antrean</h6>
                            <small class="text-muted">Pilih Poli &amp; Dokter</small>
                        </div>
                    </div>
                    <small class="text-secondary d-block mt-2">Daftar nomor urut antrean secara online tanpa antre fisik.</small>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('antrean.index') }}" class="text-decoration-none">
                <div class="card-mockup h-100 p-3 hover-shadow transition">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-3 p-3 bg-success-subtle text-success">
                            <i class="bi bi-card-checklist fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Antrean Saya</h6>
                            <small class="text-muted">Pantau Tiket &amp; Status</small>
                        </div>
                    </div>
                    <small class="text-secondary d-block mt-2">Lihat status panggilan aktif dan tiket antrean klinik.</small>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('konsultasi.create') }}" class="text-decoration-none">
                <div class="card-mockup h-100 p-3 hover-shadow transition">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-3 p-3 bg-warning-subtle text-warning">
                            <i class="bi bi-chat-heart fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Konsultasi</h6>
                            <small class="text-muted">Chat Dokter Online</small>
                        </div>
                    </div>
                    <small class="text-secondary d-block mt-2">Konsultasi gejala dan keluhan kesehatan langsung dengan dokter.</small>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('pasien.profil.edit') }}" class="text-decoration-none">
                <div class="card-mockup h-100 p-3 hover-shadow transition">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-3 p-3" style="background-color: #d1f4ed; color: #005954;">
                            <i class="bi bi-person-lines-fill fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Profil Pasien</h6>
                            <small class="text-muted">Data Diri &amp; BPJS</small>
                        </div>
                    </div>
                    <small class="text-secondary d-block mt-2">Kelola identitas domisili, tanggal lahir, dan kepesertaan BPJS.</small>
                </div>
            </a>
        </div>
    </div>

    {{-- Jadwal Praktik Dokter Buka Hari Ini --}}
    <div class="card-mockup mb-4">
        <div class="card-mockup-header d-flex justify-content-between align-items-center border-bottom pb-3">
            <div>
                <h5 class="fw-bold text-dark mb-0">Poli &amp; Dokter Praktik Hari Ini</h5>
                <small class="text-muted">Hari {{ ucfirst($hariIni) }}, {{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}</small>
            </div>
            <a href="{{ route('antrean.create') }}" class="btn btn-sm btn-app-primary rounded-pill px-3">
                <i class="bi bi-plus-circle me-1"></i> Ambil Antrean
            </a>
        </div>
        <div class="card-mockup-body p-0">
            <div class="table-responsive">
                <table class="table table-mockup align-middle">
                    <thead>
                        <tr>
                            <th>Poli Layanan</th>
                            <th>Dokter Praktik</th>
                            <th>Jam Buka</th>
                            <th>Kapasitas Kuota</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwalHariIni as $j)
                            <tr>
                                <td>
                                    <span class="badge-kode me-1">{{ $j->poli->kode_poli ?? '-' }}</span>
                                    <span class="fw-semibold text-dark">{{ $j->poli->nama_poli ?? '-' }}</span>
                                </td>
                                <td>
                                    <i class="bi bi-person-badge text-muted me-1"></i>
                                    {{ $j->doctor->user->name ?? '-' }}
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        {{ $j->kuota }} Pasien
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('antrean.create', ['poli_id' => $j->poli_id, 'tanggal_antrean' => date('Y-m-d')]) }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                        Daftar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-calendar-x d-block fs-4 mb-2"></i>
                                    Tidak ada jadwal dokter praktik pada hari {{ ucfirst($hariIni) }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
