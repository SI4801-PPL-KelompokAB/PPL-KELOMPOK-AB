@extends('layouts.konsultasi')

@section('title', 'Ruang Konsultasi')

@section('content')
@php
    $sayaDokter = auth()->user()->role === 'dokter';
    $lawan = $sayaDokter ? $konsultasi->pasien : $konsultasi->dokter;
    $pill = [
        'pending'   => ['bg-[#E5E7EB] text-[#374151]', 'bg-[#6B7280]'],
        'accepted'  => ['bg-[#E5E7EB] text-[#374151]', 'bg-[#6B7280]'],
        'ongoing'   => ['bg-[#FEF3C7] text-[#92400E]', 'bg-[#D97706]'],
        'completed' => ['bg-[#DCFCE7] text-[#15803D]', 'bg-[#16A34A]'],
        'rejected'  => ['bg-[#FEE2E2] text-[#B91C1C]', 'bg-[#DC2626]'],
    ][$konsultasi->status];
    $kembali = $sayaDokter ? route('dokter.konsultasi.index') : route('konsultasi.show', $konsultasi);
@endphp

<div class="max-w-[760px] mx-auto space-y-4">

    @if (session('success'))
        <div class="bg-[#DCFCE7] text-[#15803D] text-body-default rounded-xl px-4 py-3">{{ session('success') }}</div>
    @endif

    {{-- KEPALA --}}
    <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/40 p-5 shadow-sm" style="border-top: 4px solid #0F5C5A;">
        <div class="flex items-start justify-between gap-3 flex-wrap">
            <div>
                <p class="font-label-uppercase text-label-uppercase text-outline tracking-widest uppercase">{{ $konsultasi->nomor_konsultasi }}</p>
                <h1 class="font-headline-sm text-headline-sm text-on-surface mt-1">{{ $lawan?->name ?? '-' }}</h1>
                <p class="text-body-sm text-outline mt-0.5">{{ $konsultasi->poli?->nama_poli ?? '-' }} · {{ $konsultasi->tanggal_konsultasi->locale('id')->translatedFormat('l, j F Y') }}</p>
            </div>
            <span class="{{ $pill[0] }} font-badge-status text-badge-status px-3 py-1 rounded-full inline-flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full {{ $pill[1] }}"></span>{{ $konsultasi->status_label }}
            </span>
        </div>

        <div class="mt-4 pt-3 border-t border-outline-variant/30 flex items-center justify-between gap-3 flex-wrap">
            <a href="{{ $kembali }}" class="border border-outline-variant text-on-surface hover:bg-surface-container-low px-4 py-2 rounded-xl font-body-semibold text-body-default transition-all">Kembali</a>

            @if ($sayaDokter && $konsultasi->status === 'pending')
                <form method="POST" action="{{ route('dokter.konsultasi.mulai', $konsultasi) }}">
                    @csrf
                    <button type="submit" class="bg-primary-container hover:bg-primary text-on-primary px-5 py-2 rounded-xl font-body-semibold text-body-default shadow-sm transition-all">Mulai Sesi</button>
                </form>
            @elseif ($sayaDokter && $konsultasi->status === 'ongoing')
                <form method="POST" action="{{ route('dokter.konsultasi.selesai', $konsultasi) }}">
                    @csrf
                    <button type="submit" class="border border-primary-container text-primary-container hover:bg-surface-container-low px-5 py-2 rounded-xl font-body-semibold text-body-default transition-all">Akhiri Sesi</button>
                </form>
            @endif
        </div>
    </section>

    {{-- RIWAYAT CHAT --}}
    <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/40 shadow-sm">
        <div id="riwayat" class="p-5 space-y-3 max-h-[420px] overflow-y-auto">
            {{-- Keluhan awal --}}
            <div class="bg-surface rounded-xl border border-outline-variant/40 p-4">
                <p class="font-label-uppercase text-label-uppercase text-outline uppercase tracking-wider mb-1">Keluhan awal · {{ $konsultasi->pasien?->name }}</p>
                <p class="text-body-default text-on-surface whitespace-pre-line">{{ $konsultasi->keluhan }}</p>
            </div>

            @forelse ($konsultasi->messages as $p)
                @php $saya = (int) $p->sender_user_id === (int) auth()->id(); @endphp
                <div class="flex {{ $saya ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] rounded-2xl px-4 py-2.5 {{ $saya ? 'bg-primary-container text-on-primary' : 'bg-surface-container-low text-on-surface border border-outline-variant/40' }}">
                        <p class="text-[11px] {{ $saya ? 'text-on-primary/70' : 'text-outline' }} mb-0.5">{{ $saya ? 'Anda' : $p->sender->name }} · {{ $p->created_at->format('H:i') }}</p>
                        <p class="text-body-default whitespace-pre-line">{{ $p->message }}</p>
                    </div>
                </div>
            @empty
                <p class="text-center text-body-sm text-outline py-4">Belum ada pesan.</p>
            @endforelse
        </div>

        {{-- FORM KIRIM --}}
        <div class="border-t border-outline-variant/30 p-4">
            @if ($errors->has('pesan') || $errors->has('status'))
                <div class="flex items-center gap-1 text-xs text-error mb-2">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    <span>{{ $errors->first('pesan') ?: $errors->first('status') }}</span>
                </div>
            @endif

            @if ($konsultasi->status === 'ongoing')
                <form method="POST" action="{{ route('konsultasi.pesan', $konsultasi) }}" class="flex items-end gap-3">
                    @csrf
                    <textarea id="pesan" name="pesan" rows="2" placeholder="Tulis pesan (maks. 1000 karakter)"
                        class="flex-1 p-3 text-body-default text-on-surface placeholder:text-outline-variant border border-outline-variant rounded-lg focus:border-primary-container focus:ring-2 focus:ring-secondary-fixed/40 transition-all resize-none">{{ old('pesan') }}</textarea>
                    <button type="submit" class="bg-primary-container hover:bg-primary text-on-primary px-5 py-2.5 rounded-xl font-body-semibold text-body-default shadow-sm transition-all flex items-center gap-2">
                        <span>Kirim</span>
                        <span class="material-symbols-outlined text-[18px]">send</span>
                    </button>
                </form>
            @elseif ($konsultasi->status === 'pending')
                <p class="text-body-sm text-outline text-center">Menunggu dokter memulai sesi.</p>
            @else
                <p class="text-body-sm text-outline text-center">Sesi telah berakhir.</p>
            @endif
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const r = document.getElementById('riwayat');
        if (r) r.scrollTop = r.scrollHeight;

        @if (in_array($konsultasi->status, ['pending', 'ongoing']))
        // Muat ulang tiap 10 detik supaya pesan baru muncul (tanpa mengganggu saat mengetik)
        setInterval(() => {
            const t = document.getElementById('pesan');
            if (!t || (t.value === '' && document.activeElement !== t)) location.reload();
        }, 10000);
        @endif
    });
</script>
@endpush