@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="py-2">
    {{-- Banner Selamat Datang --}}
    <div class="card-mockup mb-4" style="background: linear-gradient(135deg, #005954 0%, #003633 100%); color: white;">
        <div class="p-4 p-md-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold text-uppercase mb-2" style="font-size: 0.7rem;">
                    <i class="bi bi-shield-lock-fill me-1"></i> Administrator Panel
                </span>
                <h3 class="fw-bold mb-1">Selamat Datang, {{ auth()->user()->name }}</h3>
                <p class="text-white-50 small mb-0">
                    Kelola data poli, jadwal dokter, kapasitas kuota antrean, dan staf klinik di satu tempat.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.antrean.index') }}" class="btn btn-warning btn-sm fw-bold text-dark rounded-pill px-3 py-2">
                    <i class="bi bi-ticket-detailed-fill me-1"></i> Kelola Antrean
                </a>
                <a href="{{ route('admin.layanan.index') }}" class="btn btn-outline-light btn-sm fw-semibold rounded-pill px-3 py-2">
                    <i class="bi bi-tag me-1"></i> Layanan &amp; Tarif
                </a>
                <a href="{{ route('admin.poli.create') }}" class="btn btn-light btn-sm fw-semibold rounded-pill px-3 py-2">
                    <i class="bi bi-plus-circle-fill text-success me-1"></i> Tambah Poli
                </a>
                <a href="{{ route('admin.jadwal.create') }}" class="btn btn-outline-light btn-sm fw-semibold rounded-pill px-3 py-2">
                    <i class="bi bi-calendar-plus me-1"></i> Tambah Jadwal
                </a>
                <a href="{{ route('admin.profil.edit') }}" class="btn btn-outline-light btn-sm fw-semibold rounded-pill px-3 py-2">
                    <i class="bi bi-person-circle me-1"></i> Profil Admin
                </a>
            </div>
        </div>
    </div>

    {{-- Kartu Metrik Statistik --}}
    <div class="row g-3 mb-4">
        {{-- Layanan & Tarif --}}
        <div class="col-sm-6 col-xl">
            <div class="card-mockup h-100 p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Layanan &amp; Tarif</span>
                    <div class="rounded-3 p-2 bg-info-subtle text-info">
                        <i class="bi bi-tag fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <h2 class="fw-bold text-dark mb-0">{{ $totalLayanan }}</h2>
                    <small class="text-muted">Daftar Tarif</small>
                </div>
                <div class="mt-3 pt-2 border-top">
                    <a href="{{ route('admin.layanan.index') }}" class="small text-decoration-none fw-semibold text-info">
                        Kelola Tarif <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Poli Aktif --}}
        <div class="col-sm-6 col-xl">
            <div class="card-mockup h-100 p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Poli Aktif</span>
                    <div class="rounded-3 p-2" style="background-color: #d1f4ed; color: #005954;">
                        <i class="bi bi-hospital fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <h2 class="fw-bold text-dark mb-0">{{ $totalPoli }}</h2>
                    <small class="text-muted">Poli Layanan</small>
                </div>
                <div class="mt-3 pt-2 border-top">
                    <a href="{{ route('admin.poli.index') }}" class="small text-decoration-none fw-semibold" style="color: #005954;">
                        Kelola Data Poli <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Dokter Terdaftar --}}
        <div class="col-sm-6 col-xl">
            <div class="card-mockup h-100 p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Dokter Terdaftar</span>
                    <div class="rounded-3 p-2 bg-primary-subtle text-primary">
                        <i class="bi bi-person-badge fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <h2 class="fw-bold text-dark mb-0">{{ $totalDokter }}</h2>
                    <small class="text-muted">Tenaga Medis</small>
                </div>
                <div class="mt-3 pt-2 border-top">
                    <a href="{{ route('admin.jadwal.index') }}" class="small text-decoration-none fw-semibold text-primary">
                        Lihat Jadwal Praktik <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Jadwal Praktik Aktif --}}
        <div class="col-sm-6 col-xl">
            <div class="card-mockup h-100 p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Jadwal Praktik</span>
                    <div class="rounded-3 p-2 bg-warning-subtle text-warning">
                        <i class="bi bi-calendar-check fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <h2 class="fw-bold text-dark mb-0">{{ $totalJadwal }}</h2>
                    <small class="text-muted">Sesi Mingguan</small>
                </div>
                <div class="mt-3 pt-2 border-top">
                    <a href="{{ route('admin.jadwal.create') }}" class="small text-decoration-none fw-semibold text-warning">
                        Atur Sesi Kuota <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Antrean Pasien Hari Ini --}}
        <div class="col-sm-6 col-xl">
            <div class="card-mockup h-100 p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Antrean Hari Ini</span>
                    <div class="rounded-3 p-2 bg-success-subtle text-success">
                        <i class="bi bi-ticket-detailed fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <h2 class="fw-bold text-dark mb-0">{{ $antreanHariIni }}</h2>
                    <small class="text-muted">Pasien Terdaftar ({{ $totalPasien }} Total)</small>
                </div>
                <div class="mt-3 pt-2 border-top">
                    <a href="{{ route('admin.antrean.index') }}" class="small text-decoration-none fw-semibold text-success">
                        Kelola Antrean Hari Ini <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Konten Utama: 2 Kolom --}}
    <div class="row g-4">
        {{-- Kolom Kiri: Jadwal Praktik Hari Ini --}}
        <div class="col-lg-7">
            <div class="card-mockup h-100">
                <div class="card-mockup-header d-flex justify-content-between align-items-center border-bottom pb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Jadwal Dokter Hari Ini</h5>
                        <small class="text-muted">Hari {{ ucfirst($hariIni) }}, {{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}</small>
                    </div>
                    <a href="{{ route('admin.jadwal.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        Semua Jadwal
                    </a>
                </div>
                <div class="card-mockup-body p-0">
                    <div class="table-responsive">
                        <table class="table table-mockup align-middle">
                            <thead>
                                <tr>
                                    <th>Dokter</th>
                                    <th>Poli</th>
                                    <th>Jam Kerja</th>
                                    <th>Kuota</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jadwalHariIni as $j)
                                    <tr>
                                        <td class="fw-semibold text-dark">
                                            <i class="bi bi-person-fill text-muted me-1"></i>
                                            {{ $j->doctor->user->name ?? 'Dokter #'.$j->doctor_id }}
                                        </td>
                                        <td>
                                            <span class="badge-kode me-1">{{ $j->poli->kode_poli ?? '-' }}</span>
                                            {{ $j->poli->nama_poli ?? '-' }}
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
                                        <td>
                                            @if($j->status)
                                                <span class="status-dot-active">&bull; Aktif</span>
                                            @else
                                                <span class="status-dot-inactive">&bull; Nonaktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="bi bi-calendar-x d-block fs-4 mb-2"></i>
                                            Belum ada jadwal dokter praktik yang terdaftar untuk hari {{ ucfirst($hariIni) }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Data Poli & Shortcut Akun --}}
        <div class="col-lg-5">
            <div class="card-mockup mb-4">
                <div class="card-mockup-header d-flex justify-content-between align-items-center border-bottom pb-3">
                    <h5 class="fw-bold text-dark mb-0">Master Data Poli</h5>
                    <a href="{{ route('admin.poli.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        Kelola
                    </a>
                </div>
                <div class="card-mockup-body p-0">
                    <div class="table-responsive">
                        <table class="table table-mockup align-middle">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Poli</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($polis as $poli)
                                    <tr>
                                        <td>
                                            <span class="badge-kode">{{ $poli->kode_poli }}</span>
                                        </td>
                                        <td class="fw-semibold text-dark">
                                            {{ $poli->nama_poli }}
                                        </td>
                                        <td class="text-end">
                                            @if($poli->status)
                                                <span class="status-dot-active">&bull; Aktif</span>
                                            @else
                                                <span class="status-dot-inactive">&bull; Nonaktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Kartu Akses Cepat Akun Staf --}}
            <div class="card-mockup p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Manajemen Akun Staf (PBI-03)</h6>
                        <small class="text-muted d-block">Tambah &amp; kelola akses dokter dan admin baru.</small>
                    </div>
                    <a href="{{ route('admin.akun.create') }}" class="btn btn-sm btn-app-primary rounded-pill px-3">
                        <i class="bi bi-person-plus me-1"></i> Buat Akun
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection