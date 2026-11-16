@extends('layouts.app')

@section('title', 'Ambil Antrean Online')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="mb-4">
            <h2 class="h4 mb-1 text-dark fw-bold">Pendaftaran Antrean Online</h2>
            <p class="text-muted small mb-0">Pilih poli, tanggal kunjungan, dan dokter praktik untuk mendapatkan nomor antrean</p>
        </div>

        {{-- Cek Profil Lengkap --}}
        @if(!$profil || !$profil->isLengkap())
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4">
                <i class="bi bi-person-exclamation fs-3 text-warning me-3"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">Identitas Profil Belum Lengkap</h6>
                    <p class="small mb-0">
                        Untuk mengambil nomor antrean online, Anda wajib melengkapi data profil pasien (tanggal lahir, jenis kelamin, dan alamat).
                    </p>
                </div>
            </div>
        @endif

        {{-- Cek Antrean Aktif Ganda --}}
        @if($antreanAktif)
            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle-fill fs-3 text-info me-3"></i>
                    <div>
                        <h6 class="alert-heading fw-bold mb-1">Anda Sudah Memiliki Antrean Aktif</h6>
                        <p class="small mb-0">
                            Nomor Antrean Anda: <strong class="badge bg-primary fs-6">{{ $antreanAktif->formatted_nomor }}</strong> 
                            ({{ $antreanAktif->doctorSchedule->poli->nama_poli ?? 'Poli' }} - {{ $antreanAktif->doctorSchedule->doctor->user->name ?? 'Dokter' }})
                            pada tanggal {{ \Carbon\Carbon::parse($antreanAktif->tanggal_antrean)->translatedFormat('d F Y') }}.
                        </p>
                    </div>
                </div>
                <a href="{{ route('antrean.show', $antreanAktif) }}" class="btn btn-sm btn-outline-primary text-nowrap ms-3">
                    <i class="bi bi-ticket-detailed me-1"></i> Lihat Tiket
                </a>
            </div>
        @endif

        <!-- Card Pemilihan Tanggal dan Poli -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('antrean.create') }}" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold text-dark">Rencana Tanggal Kunjungan</label>
                        <input type="date" name="tanggal" class="form-control" value="{{ $tanggalString }}" min="{{ date('Y-m-d') }}" onchange="this.form.submit()" dusk="input-pilih-tanggal">
                        <div class="form-text small">Hari terpilih: <strong>{{ ucfirst($namaHari) }}</strong>, {{ \Carbon\Carbon::parse($tanggalString)->translatedFormat('d F Y') }}</div>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label fw-semibold text-dark">Pilih Poli Klinik</label>
                        <select name="poli" class="form-select" onchange="this.form.submit()" dusk="select-pilih-poli">
                            @foreach($polis as $poli)
                                <option value="{{ $poli->id }}" {{ $poliId == $poli->id ? 'selected' : '' }}>
                                    {{ $poli->nama_poli }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small">Daftar dokter yang berpraktik akan menyesuaikan poli yang dipilih.</div>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-arrow-repeat me-1"></i> Terapkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Daftar Jadwal Praktik Dokter -->
        <h5 class="fw-bold text-dark mb-3">
            <i class="bi bi-calendar2-check text-primary me-2"></i>
            Dokter Berpraktik Hari {{ ucfirst($namaHari) }}
        </h5>

        <div class="row g-3">
            @forelse($schedules as $item)
                @php
                    $jadwal = $item['schedule'];
                    $sisa = $item['sisa_kuota'];
                    $penuh = $item['is_penuh'];
                    $bisaAmbil = $profil && $profil->isLengkap() && !$antreanAktif && !$penuh;
                @endphp
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100 {{ $penuh ? 'opacity-75 bg-light' : '' }}">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="fw-bold text-dark mb-1">{{ $jadwal->doctor->user->name ?? 'Dokter' }}</h5>
                                        <span class="badge bg-light text-secondary border">
                                            <i class="bi bi-hospital me-1"></i> {{ $jadwal->poli->nama_poli ?? 'Poli' }}
                                        </span>
                                    </div>
                                    @if($penuh)
                                        <span class="badge bg-danger-subtle text-danger px-3 py-2 fw-semibold">
                                            <i class="bi bi-x-circle me-1"></i> Jadwal Penuh
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success px-3 py-2 fw-semibold">
                                            <i class="bi bi-check-circle me-1"></i> Tersedia
                                        </span>
                                    @endif
                                </div>

                                <p class="text-muted small mb-3">
                                    SIP: {{ $jadwal->doctor->nomor_sip ?? 'SIP Belum Tercatat' }}
                                </p>

                                <div class="p-3 bg-light rounded-3 mb-3">
                                    <div class="d-flex justify-content-between small text-muted mb-2">
                                        <span><i class="bi bi-clock me-1"></i> Jam Praktik:</span>
                                        <strong class="text-dark">
                                            {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }} WIB
                                        </strong>
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span><i class="bi bi-people me-1"></i> Kuota Antrean:</span>
                                        <span>{{ $jadwal->kuota }} pasien (Terisi: {{ $item['terpakai'] }})</span>
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted">
                                        <span><i class="bi bi-ticket me-1"></i> Sisa Kuota:</span>
                                        <strong class="{{ $penuh ? 'text-danger' : 'text-primary' }}">{{ $sisa }} nomor lagi</strong>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('antrean.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="doctor_schedule_id" value="{{ $jadwal->id }}">
                                <input type="hidden" name="tanggal_antrean" value="{{ $tanggalString }}">

                                @if($penuh)
                                    <button type="button" class="btn btn-secondary w-100" disabled>
                                        <i class="bi bi-slash-circle me-1"></i> Jadwal Penuh
                                    </button>
                                @elseif(!$profil || !$profil->isLengkap())
                                    <button type="button" class="btn btn-outline-warning w-100" disabled title="Lengkapi profil dahulu">
                                        <i class="bi bi-exclamation-triangle me-1"></i> Lengkapi Profil Pasien
                                    </button>
                                @elseif($antreanAktif)
                                    <button type="button" class="btn btn-outline-secondary w-100" disabled title="Selesaikan antrean aktif Anda terlebih dahulu">
                                        <i class="bi bi-lock me-1"></i> Ada Antrean Aktif
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-primary w-100 shadow-sm" dusk="btn-ambil-antrean-{{ $jadwal->id }}" onclick="return confirm('Ambil nomor antrean untuk {{ $jadwal->doctor->user->name ?? 'dokter' }} pada {{ \Carbon\Carbon::parse($tanggalString)->translatedFormat('d F Y') }}?')">
                                        <i class="bi bi-ticket-perforated me-1"></i> Ambil Nomor Antrean
                                    </button>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm text-center py-5">
                        <div class="card-body text-muted">
                            <i class="bi bi-calendar-x fs-1 text-secondary d-block mb-2"></i>
                            <h6 class="fw-semibold text-dark">Tidak Ada Praktik Dokter</h6>
                            <p class="small mb-0">Pada hari {{ ucfirst($namaHari) }}, tidak ada dokter yang berpraktik di poli ini. Silakan pilih tanggal atau poli lainnya.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

