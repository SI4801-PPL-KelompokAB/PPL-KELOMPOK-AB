@extends('layouts.app')

@section('title', 'Konsultasi Online')

@section('content')
@php
    $pertama = $jadwals->first(fn ($x) => $x->sisa_kuota > 0 && ! $x->sudah_tutup)?->id;
    $terbuka = $jadwals->where('sudah_tutup', false);
@endphp

<div class="mb-4">
    <h2 class="h4 mb-1 text-dark fw-bold">Buat Konsultasi Online</h2>
    <p class="text-muted small mb-0">Pilih tanggal, poli, dan jadwal dokter, lalu tuliskan keluhan Anda</p>
</div>

<div class="row g-4">
    {{-- Kiri: form --}}
    <div class="col-lg-7">
        <div class="card-mockup">
            <div class="card-mockup-body">
                {{-- Filter tanggal dan poli (GET). Dokter dipilih lewat jadwal di bawah --}}
                <form method="GET" action="{{ route('konsultasi.create') }}" class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark" for="input-tanggal">Tanggal</label>
                        <input id="input-tanggal" type="date" name="tanggal" class="form-control"
                            value="{{ $tanggal }}" min="{{ today()->toDateString() }}"
                            onchange="this.form.submit()" dusk="input-tanggal">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark" for="select-poli">Poli</label>
                        <select id="select-poli" name="poli" class="form-select" onchange="this.form.submit()" dusk="select-poli">
                            @foreach ($polis as $p)
                                <option value="{{ $p->id }}" @selected((int) $poliId === (int) $p->id)>{{ $p->nama_poli }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>

                {{-- Form kirim konsultasi --}}
                <form method="POST" action="{{ route('konsultasi.store') }}" dusk="form-konsultasi">
                    @csrf
                    <input type="hidden" name="tanggal_konsultasi" value="{{ $tanggal }}">

                    <h6 class="fw-bold text-dark mb-3">Jadwal tersedia</h6>

                    <div class="d-flex flex-column gap-2 mb-4">
                        @forelse ($jadwals as $j)
                            @if ($j->sisa_kuota > 0 && ! $j->sudah_tutup)
                                <label class="border rounded-3 p-3 d-flex align-items-center justify-content-between gap-3 mb-0" style="cursor: pointer;" dusk="jadwal-{{ $j->id }}">
                                    <span class="d-flex align-items-center gap-3">
                                        <input type="radio" name="doctor_schedule_id" value="{{ $j->id }}" class="form-check-input mt-0"
                                            @checked((string) old('doctor_schedule_id', $pertama) === (string) $j->id)>
                                        <span>
                                            <span class="d-block fw-semibold text-dark">{{ $j->doctor->user->name }}</span>
                                            <small class="text-muted">{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}</small>
                                        </span>
                                    </span>
                                    <span class="badge rounded-pill {{ $j->sisa_kuota <= 2 ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-success-subtle text-success-emphasis border border-success-subtle' }}">
                                        Sisa kuota: {{ $j->sisa_kuota }}
                                    </span>
                                </label>
                            @else
                                <div class="border rounded-3 p-3 d-flex align-items-center justify-content-between gap-3 bg-light opacity-75">
                                    <span>
                                        <span class="d-block text-muted">{{ $j->doctor->user->name }}</span>
                                        <small class="text-muted">{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}</small>
                                    </span>
                                    <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis border border-danger-subtle">{{ $j->sudah_tutup ? 'Tutup' : 'Penuh' }}</span>
                                </div>
                            @endif
                        @empty
                            <p class="text-muted mb-0">Tidak ada dokter yang praktik di poli ini pada tanggal tersebut</p>
                        @endforelse
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark" for="keluhan">Keluhan / Gejala <span class="text-danger">*</span></label>
                        <textarea id="keluhan" name="keluhan" rows="4"
                            class="form-control @error('keluhan') is-invalid @enderror"
                            placeholder="Ceritakan keluhan Anda (min. 10, maks. 1000 karakter)" dusk="input-keluhan">{{ old('keluhan') }}</textarea>
                        <div class="d-flex justify-content-between mt-1">
                            @error('keluhan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @else
                                <span></span>
                            @enderror
                            <small id="hitung" class="text-muted font-monospace">0 / 1000</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-top pt-3">
                        <a href="{{ route('pasien.dashboard') }}" class="btn btn-outline-secondary">Kembali</a>
                        <button type="submit" class="btn btn-app-primary" dusk="tombol-buat-konsultasi">
                            Buat Konsultasi <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Kanan: ringkasan dan langkah --}}
    <div class="col-lg-5">
        <div class="row g-3 mb-3">
            <div class="col-4">
                <div class="card-mockup p-3 text-center h-100">
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Dokter</small>
                    <div class="fs-3 fw-bold" style="color: var(--app-primary);">{{ $dokters->count() }}</div>
                    <small class="text-muted">Dokter tersedia</small>
                </div>
            </div>
            <div class="col-4">
                <div class="card-mockup p-3 text-center h-100">
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Kuota</small>
                    <div class="fs-3 fw-bold" style="color: var(--app-primary);">{{ $terbuka->sum('sisa_kuota') }}</div>
                    <small class="text-muted">Sisa kuota</small>
                </div>
            </div>
            <div class="col-4">
                <div class="card-mockup p-3 text-center h-100">
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Aktif</small>
                    <div class="fs-3 fw-bold" style="color: var(--app-primary);">{{ $aktif }}</div>
                    <small class="text-muted">Konsultasi aktif</small>
                </div>
            </div>
        </div>

        <div class="card-mockup mb-3">
            <div class="card-mockup-body">
                <h6 class="fw-bold text-dark mb-3">Langkah konsultasi</h6>
                <ol class="list-group list-group-numbered list-group-flush">
                    <li class="list-group-item px-0"><strong>Pilih jadwal</strong> <small class="text-muted d-block">Poli, dokter &amp; jam</small></li>
                    <li class="list-group-item px-0"><strong>Isi keluhan</strong> <small class="text-muted d-block">Gejala saat ini</small></li>
                    <li class="list-group-item px-0"><strong>Konfirmasi</strong> <small class="text-muted d-block">Cek data</small></li>
                    <li class="list-group-item px-0"><strong>Mulai konsultasi</strong> <small class="text-muted d-block">Lewat chat</small></li>
                </ol>
            </div>
        </div>

        <div class="card-mockup">
            <div class="card-mockup-body">
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle me-1" style="color: var(--app-primary);"></i> Ketentuan</h6>
                <ul class="small text-muted mb-0 ps-3">
                    <li>Satu konsultasi aktif per jadwal.</li>
                    <li>Jadwal penuh atau tutup tidak bisa dipilih.</li>
                    <li>Keluhan 10 sampai 1000 karakter.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Penghitung jumlah karakter keluhan
    document.addEventListener('DOMContentLoaded', () => {
        const keluhan = document.getElementById('keluhan');
        const hitung = document.getElementById('hitung');
        if (!keluhan) return;
        const perbarui = () => { hitung.textContent = `${keluhan.value.length} / 1000`; };
        keluhan.addEventListener('input', perbarui);
        perbarui();
    });
</script>
@endpush