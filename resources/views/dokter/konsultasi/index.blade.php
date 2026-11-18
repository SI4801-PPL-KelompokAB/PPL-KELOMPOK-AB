@extends('layouts.app')

@section('title', 'Konsultasi Masuk')

@section('content')
<div class="mb-4">
    <h2 class="h4 mb-1 text-dark fw-bold">Konsultasi Masuk</h2>
    <p class="text-muted small mb-0">Daftar permintaan konsultasi online dari pasien</p>
</div>

{{-- Ringkasan per status --}}
<div class="row g-3 mb-4">
    @foreach (['pending' => 'Menunggu', 'ongoing' => 'Berlangsung', 'completed' => 'Selesai'] as $key => $nama)
        <div class="col-4">
            <div class="card-mockup p-3 text-center h-100">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">{{ $nama }}</small>
                <div class="fs-3 fw-bold" style="color: var(--app-primary);" dusk="jumlah-{{ $key }}">{{ $hitung[$key] ?? 0 }}</div>
                <small class="text-muted">Konsultasi</small>
            </div>
        </div>
    @endforeach
</div>

{{-- Daftar konsultasi --}}
<div class="card-mockup">
    <div class="card-mockup-header">
        <h6 class="fw-bold text-dark mb-0">Permintaan konsultasi</h6>
    </div>
    <div class="card-mockup-body">
        <div class="d-flex flex-column gap-2">
            @forelse ($konsultasis as $k)
                <div class="border rounded-3 p-3 d-flex justify-content-between align-items-center gap-3 flex-wrap" dusk="konsultasi-{{ $k->id }}">
                    <div class="min-w-0">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge-kode">{{ $k->nomor_konsultasi }}</span>
                            <x-badge-konsultasi :status="$k->status" />
                        </div>
                        <div class="fw-semibold text-dark mt-2">{{ $k->pasien?->name }} · {{ $k->poli?->nama_poli ?? '-' }}</div>
                        <small class="text-muted d-block">{{ $k->tanggal_konsultasi->locale('id')->translatedFormat('l, j F Y') }}</small>
                        <div class="text-secondary small mt-1">{{ \Illuminate\Support\Str::limit($k->keluhan, 90) }}</div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        @if ($k->status === 'pending')
                            <form method="POST" action="{{ route('dokter.konsultasi.mulai', $k) }}" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-app-primary btn-sm" dusk="tombol-mulai-sesi-{{ $k->id }}">Mulai Sesi</button>
                            </form>
                        @endif
                        <a href="{{ route('konsultasi.ruang', $k) }}" class="btn-action-outline" dusk="tombol-buka-ruang-{{ $k->id }}">
                            {{ $k->status === 'completed' ? 'Lihat' : 'Buka Ruang' }}
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-muted mb-0">Belum ada konsultasi masuk.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection