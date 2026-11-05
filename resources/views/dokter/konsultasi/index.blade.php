@extends('layouts.konsultasi')

@section('title', 'Konsultasi Masuk')

@section('content')
@php
    $pill = [
        'pending'   => ['bg-[#E5E7EB] text-[#374151]', 'bg-[#6B7280]'],
        'accepted'  => ['bg-[#E5E7EB] text-[#374151]', 'bg-[#6B7280]'],
        'ongoing'   => ['bg-[#FEF3C7] text-[#92400E]', 'bg-[#D97706]'],
        'completed' => ['bg-[#DCFCE7] text-[#15803D]', 'bg-[#16A34A]'],
    ];
@endphp

<div class="space-y-6">

    @if (session('success'))
        <div class="bg-[#DCFCE7] text-[#15803D] text-body-default rounded-xl px-4 py-3">{{ session('success') }}</div>
    @endif
    @if ($errors->has('status'))
        <div class="bg-error-container text-on-error-container text-body-default rounded-xl px-4 py-3">{{ $errors->first('status') }}</div>
    @endif

    {{-- STAT --}}
    <div class="grid grid-cols-3 gap-3">
        @foreach (['pending' => 'Menunggu', 'ongoing' => 'Berlangsung', 'completed' => 'Selesai'] as $key => $nama)
            <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/40 p-4 shadow-sm text-center">
                <span class="text-[10px] uppercase tracking-wider font-label-uppercase text-outline block mb-1">{{ strtoupper($nama) }}</span>
                <div class="text-headline-md font-headline-md text-primary-container leading-none my-1">{{ $hitung[$key] ?? 0 }}</div>
                <p class="text-[11px] text-on-surface-variant leading-tight">Konsultasi</p>
            </div>
        @endforeach
    </div>

    {{-- DAFTAR --}}
    <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/40 shadow-sm p-6" style="border-top: 4px solid #0F5C5A;">
        <span class="text-label-uppercase font-label-uppercase text-secondary uppercase tracking-wider block mb-1">KONSULTASI MASUK</span>
        <h1 class="text-headline-md font-headline-md text-on-surface mb-5">Permintaan konsultasi</h1>

        <div class="space-y-3">
            @forelse ($konsultasis as $k)
                <div class="p-4 rounded-xl border border-outline-variant/60 bg-surface-container-lowest flex items-center justify-between gap-4 flex-wrap">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-mono-code text-sm font-bold text-primary-container">{{ $k->nomor_konsultasi }}</span>
                            <span class="{{ $pill[$k->status][0] }} font-badge-status text-badge-status px-2.5 py-0.5 rounded-full inline-flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ $pill[$k->status][1] }}"></span>{{ $k->status_label }}
                            </span>
                        </div>
                        <p class="text-body-semibold font-body-semibold text-on-surface mt-1">{{ $k->pasien?->name }} · {{ $k->poli?->nama_poli ?? '-' }}</p>
                        <p class="text-body-sm text-outline">{{ $k->tanggal_konsultasi->locale('id')->translatedFormat('l, j F Y') }}</p>
                        <p class="text-body-default text-on-surface-variant mt-1">{{ \Illuminate\Support\Str::limit($k->keluhan, 90) }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        @if ($k->status === 'pending')
                            <form method="POST" action="{{ route('dokter.konsultasi.mulai', $k) }}">
                                @csrf
                                <button type="submit" class="bg-primary-container hover:bg-primary text-on-primary px-4 py-2 rounded-xl font-body-semibold text-body-default shadow-sm transition-all">Mulai Sesi</button>
                            </form>
                        @endif
                        <a href="{{ route('konsultasi.ruang', $k) }}" class="border border-outline-variant text-on-surface hover:bg-surface-container-low px-4 py-2 rounded-xl font-body-semibold text-body-default transition-all">
                            {{ $k->status === 'completed' ? 'Lihat' : 'Buka Ruang' }}
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-body-default text-outline">Belum ada konsultasi masuk.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection