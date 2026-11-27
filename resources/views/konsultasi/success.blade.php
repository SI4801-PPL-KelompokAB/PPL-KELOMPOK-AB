@extends('layouts.app')

@section('title', 'Konsultasi Berhasil')

@section('content')
@php
    $tahap = [
        'pending'   => ['Menunggu', 'Menunggu dokter memulai sesi'],
        'ongoing'   => ['Berlangsung', 'Sesi sedang berjalan'],
        'completed' => ['Selesai', 'Konsultasi telah selesai'],
    ];
    $posisi = array_search($konsultasi->status, array_keys($tahap));
@endphp

<div class="row g-4">
    {{-- Kiri: kartu nomor konsultasi --}}
    <div class="col-lg-7">
        <div class="card-mockup text-center">
            <div class="card-mockup-body p-4">
                <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px;">
                    <i class="bi bi-check-lg fs-2"></i>
                </div>
                <h2 class="h4 fw-bold text-dark mb-4">Konsultasi berhasil dibuat</h2>

                <div class="border rounded-3 p-4 mb-4 bg-light">
                    <small class="text-muted text-uppercase fw-bold" style="letter-spacing: 0.08em;">Nomor Konsultasi</small>
                    <div class="display-6 fw-bold font-monospace my-2" style="color: var(--app-primary);" dusk="nomor-konsultasi">{{ $konsultasi->nomor_konsultasi }}</div>

                    <hr style="border-top-style: dashed;">

                    <h5 class="fw-semibold text-dark mb-1">{{ $konsultasi->dokter?->name ?? '-' }}</h5>
                    <p class="text-muted mb-1">{{ $konsultasi->poli?->nama_poli ?? '-' }}</p>
                    <p class="text-muted mb-3">
                        <i class="bi bi-calendar3 me-1"></i>
                        {{ $konsultasi->tanggal_konsultasi->locale('id')->translatedFormat('l, j F Y') }}@if ($jadwal), {{ substr($jadwal->jam_mulai, 0, 5) }}@endif
                    </p>

                    <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap">
                        <x-badge-konsultasi :status="$konsultasi->status" dusk="status-konsultasi" />
                        <small class="text-muted"><i class="bi bi-clock me-1"></i>Diperbarui {{ $konsultasi->updated_at->format('H:i') }}</small>
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="{{ route('konsultasi.ruang', $konsultasi) }}" class="btn btn-app-primary" dusk="tombol-buka-ruang">
                        <i class="bi bi-chat-dots me-1"></i> Buka Ruang Konsultasi
                    </a>
                    <a href="{{ route('pasien.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-house me-1"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Kanan: ringkasan dan tahapan --}}
    <div class="col-lg-5">
        <div class="row g-3 mb-3">
            <div class="col-4">
                <div class="card-mockup p-3 h-100">
                    <div class="fs-3 fw-bold" style="color: var(--app-primary);">{{ $antreanDiDepan }}</div>
                    <small class="fw-semibold text-dark d-block">Antrean di depan</small>
                    <small class="text-muted">pasien menunggu</small>
                </div>
            </div>
            <div class="col-4">
                <div class="card-mockup p-3 h-100">
                    <div class="fs-3 fw-bold" style="color: var(--app-primary);">{{ $sedangDilayani ? substr($sedangDilayani, -4) : '-' }}</div>
                    <small class="fw-semibold text-dark d-block">Sedang dilayani</small>
                    <small class="text-muted">{{ $sedangDilayani ? 'nomor sesi' : 'belum ada sesi' }}</small>
                </div>
            </div>
            <div class="col-4">
                <div class="card-mockup p-3 h-100">
                    <div class="fs-3 fw-bold" style="color: var(--app-primary);">{{ $sisaKuota }}</div>
                    <small class="fw-semibold text-dark d-block">Sisa kuota</small>
                    <small class="text-muted">dari {{ $kuotaMaksimal }} kuota</small>
                </div>
            </div>
        </div>

        <div class="card-mockup">
            <div class="card-mockup-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark mb-0">Tahapan Anda</h6>
                    @if ($posisi !== false)
                        <span class="badge rounded-pill bg-light text-secondary border">Langkah {{ $posisi + 1 }} dari 3</span>
                    @endif
                </div>

                <ul class="list-group">
                    @foreach ($tahap as $key => [$nama, $ket])
                        @php
                            $sekarang = $konsultasi->status === $key;
                            $lewat = $posisi !== false && $loop->index < $posisi;
                        @endphp
                        <li class="list-group-item d-flex align-items-start gap-2 {{ $sekarang ? 'list-group-item-success' : '' }}">
                            <i class="bi {{ $sekarang ? 'bi-record-circle-fill' : ($lewat ? 'bi-check-circle-fill' : 'bi-circle') }} mt-1" style="color: var(--app-primary);"></i>
                            <div class="flex-grow-1">
                                <div class="fw-semibold {{ $sekarang || $lewat ? 'text-dark' : 'text-muted' }}">
                                    {{ $nama }}
                                    @if ($sekarang)
                                        <span class="badge bg-white text-success border ms-1">Saat ini</span>
                                    @endif
                                </div>
                                <small class="text-muted">{{ $ket }}</small>
                            </div>
                        </li>
                    @endforeach
                </ul>

                @if ($jadwal)
                    <p class="small text-muted mt-3 mb-0">
                        <i class="bi bi-info-circle me-1" style="color: var(--app-primary);"></i>
                        Jam praktik: <strong>{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB</strong>
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Keterangan status --}}
<div class="card-mockup mt-4">
    <div class="card-mockup-body">
        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-question-circle me-1" style="color: var(--app-primary);"></i> Keterangan Status</h6>
        <div class="row g-3 small">
            <div class="col-md-6 d-flex align-items-center gap-2"><x-badge-konsultasi status="pending" /> <span class="text-muted">menunggu dokter memulai sesi</span></div>
            <div class="col-md-6 d-flex align-items-center gap-2"><x-badge-konsultasi status="ongoing" /> <span class="text-muted">sesi sedang berjalan</span></div>
            <div class="col-md-6 d-flex align-items-center gap-2"><x-badge-konsultasi status="completed" /> <span class="text-muted">konsultasi telah selesai</span></div>
            <div class="col-md-6 d-flex align-items-center gap-2"><x-badge-konsultasi status="rejected" /> <span class="text-muted">silakan buat konsultasi baru</span></div>
        </div>
    </div>
</div>
@endsection