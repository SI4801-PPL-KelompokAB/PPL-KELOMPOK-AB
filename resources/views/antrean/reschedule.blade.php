@extends('layouts.app')

@section('title', 'Penjadwalan Ulang Antrean')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1 text-dark fw-bold">Penjadwalan Ulang (Reschedule) Antrean</h2>
                <p class="text-muted small mb-0">Pilih tanggal kunjungan atau jadwal praktik dokter tujuan yang baru</p>
            </div>
            <a href="{{ route('antrean.show', $queue) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Tiket
            </a>
        </div>

        {{-- ringkasan jadwal antrean saat ini --}}
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center justify-content-between mb-4">
            <div>
                <h6 class="alert-heading fw-bold mb-1">
                    <i class="bi bi-ticket-detailed me-1"></i> Jadwal Antrean Saat Ini: Nomor {{ $queue->formatted_nomor }}
                </h6>
                <p class="small mb-0">
                    <strong>{{ $queue->doctorSchedule->poli->nama_poli ?? 'Poli' }}</strong> &mdash;
                    {{ $queue->doctorSchedule->doctor->user->name ?? 'Dokter' }} &bull;
                    {{ \Carbon\Carbon::parse($queue->tanggal_antrean)->translatedFormat('l, d F Y') }}
                    ({{ \Carbon\Carbon::parse($queue->doctorSchedule->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($queue->doctorSchedule->jam_selesai)->format('H:i') }} WIB)
                </p>
            </div>
            <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill fw-semibold">
                Status: {{ $queue->status_label }}
            </span>
        </div>

        @if($queue->status !== 'waiting')
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <i class="bi bi-exclamation-octagon-fill me-2"></i>
                Hanya antrean yang masih berstatus <strong>Menunggu</strong> yang dapat dijadwalkan ulang.
            </div>
        @endif

        {{-- form filter tanggal baru dan poli --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('antrean.reschedule.form', $queue) }}" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold text-dark">Pilih Tanggal Kunjungan Baru</label>
                        <input type="date" name="tanggal" class="form-control" value="{{ $tanggalString }}" min="{{ date('Y-m-d') }}" onchange="this.form.submit()" dusk="input-tanggal-reschedule">
                        <div class="form-text small">Hari terpilih: <strong>{{ ucfirst($namaHari) }}</strong>, {{ \Carbon\Carbon::parse($tanggalString)->translatedFormat('d F Y') }}</div>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label fw-semibold text-dark">Pilih Poli Klinik</label>
                        <select name="poli" class="form-select" onchange="this.form.submit()" dusk="select-poli-reschedule">
                            @foreach($polis as $poli)
                                <option value="{{ $poli->id }}" {{ $poliId == $poli->id ? 'selected' : '' }}>
                                    {{ $poli->nama_poli }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small">Anda juga dapat berpindah ke jadwal dokter di poli yang dipilih.</div>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100" dusk="btn-filter-reschedule">
                            <i class="bi bi-arrow-repeat me-1"></i> Terapkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- daftar jadwal dokter tujuan --}}
        <h5 class="fw-bold text-dark mb-3">
            <i class="bi bi-calendar2-check text-primary me-2"></i>
            Pilihan Jadwal Dokter Hari {{ ucfirst($namaHari) }}
        </h5>

        <div class="row g-3">
            @forelse($schedules as $item)
                @php
                    $jadwal = $item['schedule'];
                    $sisa = $item['sisa_kuota'];
                    $penuh = $item['is_penuh'];
                    $sama = $item['is_jadwal_saat_ini'];
                @endphp
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100 {{ ($penuh || $sama) ? 'opacity-75 bg-light' : '' }}">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="fw-bold text-dark mb-1">{{ $jadwal->doctor->user->name ?? 'Dokter' }}</h5>
                                        <span class="badge bg-light text-secondary border">
                                            <i class="bi bi-hospital me-1"></i> {{ $jadwal->poli->nama_poli ?? 'Poli' }}
                                        </span>
                                    </div>
                                    @if($sama)
                                        <span class="badge bg-info-subtle text-info-emphasis px-3 py-2 fw-semibold">
                                            Jadwal Saat Ini
                                        </span>
                                    @elseif($penuh)
                                        <span class="badge bg-danger-subtle text-danger px-3 py-2 fw-semibold">
                                            <i class="bi bi-x-circle me-1"></i> Jadwal Penuh
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success px-3 py-2 fw-semibold">
                                            <i class="bi bi-check-circle me-1"></i> Tersedia
                                        </span>
                                    @endif
                                </div>

                                <div class="p-3 bg-light rounded-3 mb-3 mt-3">
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

                            <form action="{{ route('antrean.reschedule', $queue) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="doctor_schedule_id" value="{{ $jadwal->id }}">
                                <input type="hidden" name="tanggal_antrean" value="{{ $tanggalString }}">

                                @if($queue->status !== 'waiting')
                                    <button type="button" class="btn btn-secondary w-100" disabled>
                                        Tidak Dapat Dijadwalkan Ulang
                                    </button>
                                @elseif($sama)
                                    <button type="submit" class="btn btn-outline-secondary w-100" dusk="btn-pilih-reschedule-{{ $jadwal->id }}">
                                        <i class="bi bi-check2-circle me-1"></i> Jadwal Anda Saat Ini
                                    </button>
                                @elseif($penuh)
                                    <button type="button" class="btn btn-secondary w-100" disabled>
                                        <i class="bi bi-slash-circle me-1"></i> Jadwal Penuh
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-warning w-100 fw-semibold shadow-sm" dusk="btn-pilih-reschedule-{{ $jadwal->id }}" onclick="return confirm('Pindahkan jadwal antrean Anda ke {{ $jadwal->doctor->user->name ?? 'dokter' }} pada {{ \Carbon\Carbon::parse($tanggalString)->translatedFormat('d F Y') }}?')">
                                        <i class="bi bi-calendar2-range me-1"></i> Pilih Jadwal Baru Ini
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
