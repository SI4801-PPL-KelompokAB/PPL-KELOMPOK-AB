@extends('layouts.app')

@section('title', 'Ruang Konsultasi')

@section('content')
@php
    $sayaDokter = auth()->user()->role === 'dokter';
    $lawan = $sayaDokter ? $konsultasi->pasien : $konsultasi->dokter;
    $kembali = $sayaDokter ? route('dokter.konsultasi.index') : route('konsultasi.show', $konsultasi);
@endphp

<div class="row justify-content-center">
    <div class="col-lg-8">
        {{-- Kepala ruang konsultasi --}}
        <div class="card-mockup mb-3">
            <div class="card-mockup-body">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                    <div>
                        <span class="badge-kode">{{ $konsultasi->nomor_konsultasi }}</span>
                        <h2 class="h5 fw-bold text-dark mt-2 mb-1">{{ $lawan?->name ?? '-' }}</h2>
                        <small class="text-muted">{{ $konsultasi->poli?->nama_poli ?? '-' }} · {{ $konsultasi->tanggal_konsultasi->locale('id')->translatedFormat('l, j F Y') }}</small>
                    </div>
                    <x-badge-konsultasi :status="$konsultasi->status" dusk="status-konsultasi" />
                </div>

                <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap border-top mt-3 pt-3">
                    <a href="{{ $kembali }}" class="btn btn-outline-secondary btn-sm">Kembali</a>

                    @if ($sayaDokter && $konsultasi->status === 'pending')
                        <form method="POST" action="{{ route('dokter.konsultasi.mulai', $konsultasi) }}" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-app-primary btn-sm" dusk="tombol-mulai-sesi">Mulai Sesi</button>
                        </form>
                    @elseif ($sayaDokter && $konsultasi->status === 'ongoing')
                        <form method="POST" action="{{ route('dokter.konsultasi.selesai', $konsultasi) }}" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm" dusk="tombol-akhiri-sesi">Akhiri Sesi</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Riwayat chat --}}
        <div class="card-mockup">
            <div id="riwayat" class="card-mockup-body d-flex flex-column gap-3" style="max-height: 420px; overflow-y: auto;" dusk="riwayat-chat">
                {{-- Keluhan awal --}}
                <div class="border rounded-3 p-3 bg-light">
                    <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.7rem;">Keluhan awal · {{ $konsultasi->pasien?->name }}</small>
                    <p class="mb-0" style="white-space: pre-line;">{{ $konsultasi->keluhan }}</p>
                </div>

                @forelse ($konsultasi->messages as $p)
                    @php $saya = (int) $p->sender_user_id === (int) auth()->id(); @endphp
                    <div class="d-flex {{ $saya ? 'justify-content-end' : 'justify-content-start' }}">
                        <div class="rounded-3 px-3 py-2 {{ $saya ? 'text-white' : 'bg-light border' }}"
                            style="max-width: 80%; {{ $saya ? 'background-color: var(--app-primary);' : '' }}">
                            <small class="d-block mb-1 {{ $saya ? 'text-white-50' : 'text-muted' }}">{{ $saya ? 'Anda' : $p->sender->name }} · {{ $p->created_at->format('H:i') }}</small>
                            <div style="white-space: pre-line;">{{ $p->message }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-muted small my-3">Belum ada pesan.</p>
                @endforelse
            </div>

            {{-- Form kirim pesan --}}
            <div class="border-top p-3">
                @if ($konsultasi->status === 'ongoing')
                    <form method="POST" action="{{ route('konsultasi.pesan', $konsultasi) }}" class="d-flex align-items-start gap-2">
                        @csrf
                        <div class="flex-grow-1">
                            <textarea id="pesan" name="pesan" rows="2"
                                class="form-control @error('pesan') is-invalid @enderror"
                                placeholder="Tulis pesan (maks. 1000 karakter)" dusk="input-pesan">{{ old('pesan') }}</textarea>
                            @error('pesan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-app-primary" dusk="tombol-kirim-pesan">
                            Kirim <i class="bi bi-send ms-1"></i>
                        </button>
                    </form>
                @elseif ($konsultasi->status === 'pending')
                    <p class="text-center text-muted small mb-0" dusk="info-sesi">Menunggu dokter memulai sesi.</p>
                @else
                    <p class="text-center text-muted small mb-0" dusk="info-sesi">Sesi telah berakhir.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const riwayat = document.getElementById('riwayat');
        if (riwayat) riwayat.scrollTop = riwayat.scrollHeight;

        @if (in_array($konsultasi->status, ['pending', 'ongoing']))
        // Muat ulang tiap 10 detik supaya pesan baru muncul, kecuali saat sedang mengetik
        setInterval(() => {
            const pesan = document.getElementById('pesan');
            if (!pesan || (pesan.value === '' && document.activeElement !== pesan)) location.reload();
        }, 10000);
        @endif
    });
</script>
@endpush