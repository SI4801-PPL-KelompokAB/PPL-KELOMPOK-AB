@extends('layouts.app')

@section('title', 'Tiket Antrean Online')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="d-flex justify-content-between align-items-center mb-3 d-print-none">
            <a href="{{ route('antrean.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Antrean Saya
            </a>
            <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-printer me-1"></i> Cetak Tiket
            </button>
        </div>

        <!-- Tiket Antrean Card -->
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden" id="areaTiket">
            <!-- Header Tiket -->
            <div class="bg-primary text-white p-4 text-center position-relative">
                <div class="d-flex justify-content-center align-items-center gap-2 mb-1">
                    <i class="bi bi-hospital fs-3"></i>
                    <h5 class="fw-bold mb-0">KLINIK BONA MITRA KELUARGA</h5>
                </div>
                <p class="small text-white-50 mb-0">Bukti Pendaftaran Antrean Online</p>
            </div>

            <!-- Body Tiket -->
            <div class="card-body p-4 text-center">
                <div class="py-3">
                    <span class="text-uppercase text-muted fw-bold small letter-spacing-1">Nomor Antrean Anda</span>
                    <div class="display-1 fw-bold text-primary my-2" dusk="nomor-antrean-display">
                        {{ $queue->formatted_nomor }}
                    </div>
                    <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-1 rounded-pill fw-semibold">
                        <i class="bi bi-hourglass-split me-1"></i> Status: {{ $queue->status_label }}
                    </span>
                </div>

                <div class="alert alert-light border text-start my-3">
                    <div class="row g-2 small">
                        <div class="col-5 text-muted">Poli Klinik</div>
                        <div class="col-7 fw-semibold text-dark">: {{ $queue->doctorSchedule->poli->nama_poli ?? '-' }}</div>

                        <div class="col-5 text-muted">Dokter Praktik</div>
                        <div class="col-7 fw-semibold text-dark">: {{ $queue->doctorSchedule->doctor->user->name ?? '-' }}</div>

                        <div class="col-5 text-muted">Tanggal Kunjungan</div>
                        <div class="col-7 fw-semibold text-dark">: {{ \Carbon\Carbon::parse($queue->tanggal_antrean)->translatedFormat('l, d F Y') }}</div>

                        <div class="col-5 text-muted">Jam Praktik</div>
                        <div class="col-7 fw-semibold text-dark">
                            : {{ \Carbon\Carbon::parse($queue->doctorSchedule->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($queue->doctorSchedule->jam_selesai)->format('H:i') }} WIB
                        </div>

                        <div class="col-5 text-muted">Nama Pasien</div>
                        <div class="col-7 fw-semibold text-dark">: {{ $queue->patientProfile->user->name ?? auth()->user()->name }}</div>

                        <div class="col-5 text-muted">No. Handphone</div>
                        <div class="col-7 fw-semibold text-dark">: {{ $queue->patientProfile->user->no_hp ?? auth()->user()->no_hp }}</div>
                    </div>
                </div>

                @if($antreanDiDepan > 0)
                    <div class="p-3 bg-info-subtle text-info-emphasis rounded-3 small text-center mb-3">
                        <i class="bi bi-people-fill me-1"></i> Terdapat <strong>{{ $antreanDiDepan }} antrean</strong> yang masih menunggu di depan Anda.
                    </div>
                @else
                    <div class="p-3 bg-success-subtle text-success rounded-3 small text-center mb-3">
                        <i class="bi bi-check-circle-fill me-1"></i> Anda adalah nomor urut pertama untuk sesi praktik ini.
                    </div>
                @endif

                <div class="text-muted small text-start">
                    <p class="fw-semibold mb-1 text-dark"><i class="bi bi-info-circle me-1 text-primary"></i> Catatan Pasien:</p>
                    <ul class="mb-0 ps-3">
                        <li>Harap hadir di klinik paling lambat 15 menit sebelum jam praktik dimulai.</li>
                        <li>Tunjukkan bukti nomor antrean ini kepada petugas resepsionis/loket saat tiba.</li>
                        <li>Bawa kartu identitas (KTP/KK) atau kartu BPJS (bila peserta BPJS).</li>
                    </ul>
                </div>
            </div>

            <!-- Footer Tiket -->
            <div class="card-footer bg-light p-3 text-center text-muted small border-top">
                Dibuat pada: {{ $queue->created_at->format('d/m/Y H:i') }} WIB &bull; Kode: Q-{{ $queue->id }}
            </div>
        </div>

        <div class="text-center mt-3 d-print-none">
            <a href="{{ route('antrean.index') }}" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i> Selesai
            </a>
        </div>
    </div>
</div>
@endsection

