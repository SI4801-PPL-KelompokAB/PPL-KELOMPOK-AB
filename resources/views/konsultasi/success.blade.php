@extends('layouts.konsultasi')

@section('title', 'Konsultasi Berhasil')

@section('content')
@php
    $tahap = [
        'pending'   => ['Menunggu', 'menunggu dokter'],
        'ongoing'   => ['Berlangsung', 'sesi berjalan'],
        'completed' => ['Selesai', 'konsultasi selesai'],
    ];
    $posisi = array_search($konsultasi->status, array_keys($tahap));
    $pill = [
        'pending'   => ['bg-[#E5E7EB] text-[#374151]', 'bg-[#6B7280]'],
        'accepted'  => ['bg-[#E5E7EB] text-[#374151]', 'bg-[#6B7280]'],
        'ongoing'   => ['bg-[#FEF3C7] text-[#92400E]', 'bg-[#D97706]'],
        'completed' => ['bg-[#DCFCE7] text-[#15803D]', 'bg-[#16A34A]'],
        'rejected'  => ['bg-[#FEE2E2] text-[#B91C1C]', 'bg-[#DC2626]'],
    ][$konsultasi->status];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

    {{-- KIRI: KARTU NOMOR --}}
    <section class="lg:col-span-7 flex flex-col gap-6">
        <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/40 border-t-4 border-t-primary-container p-8 text-center shadow-[0_4px_6px_-1px_rgba(15,92,90,0.06),0_10px_20px_-3px_rgba(15,23,42,0.05)]">
            <div class="w-14 h-14 rounded-full bg-[#DCFCE7] text-[#15803D] flex items-center justify-center mx-auto mb-3 shadow-inner">
                <span class="material-symbols-outlined text-[32px]" style="font-variation-settings: 'FILL' 1;">check</span>
            </div>
            <h1 class="font-headline-md text-headline-md text-on-surface mb-6">Konsultasi berhasil dibuat</h1>

            <div class="bg-surface rounded-2xl border border-outline-variant/40 p-6 mb-6 text-center">
                <p class="font-label-uppercase text-label-uppercase text-outline tracking-widest mb-2 uppercase">NOMOR KONSULTASI</p>
                <div class="font-mono-code text-3xl sm:text-4xl font-extrabold text-primary-container tracking-tight mb-4">{{ $konsultasi->nomor_konsultasi }}</div>

                <div class="border-t border-dashed border-outline-variant/60 my-5"></div>

                <div class="flex flex-col items-center justify-center gap-1">
                    <h2 class="font-headline-sm text-headline-sm text-on-surface">{{ $konsultasi->dokter?->name ?? '-' }}</h2>
                    <p class="font-body-default text-body-default text-outline">{{ $konsultasi->poli?->nama_poli ?? '-' }}</p>
                    <p class="font-body-default text-body-default text-outline flex items-center justify-center gap-1.5 mt-1">
                        <span class="material-symbols-outlined text-[16px] text-outline">calendar_today</span>
                        <span>{{ $konsultasi->tanggal_konsultasi->locale('id')->translatedFormat('l, j F Y') }}@if ($jadwal), {{ substr($jadwal->jam_mulai, 0, 5) }}@endif</span>
                    </p>
                </div>

                <div class="mt-5 pt-4 border-t border-outline-variant/20 flex items-center justify-center flex-wrap gap-2">
                    <span class="{{ $pill[0] }} font-badge-status text-badge-status px-3 py-1 rounded-full inline-flex items-center gap-1.5 shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full {{ $pill[1] }}"></span>
                        <span>{{ $konsultasi->status_label }}</span>
                    </span>
                    <span class="font-body-sm text-body-sm text-outline flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">schedule</span>
                        Diperbarui {{ $konsultasi->updated_at->format('H:i') }}
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-center flex-wrap gap-3 mt-4">
                <a href="{{ route('konsultasi.ruang', $konsultasi) }}" class="bg-primary-container hover:bg-primary text-on-primary px-6 py-2.5 rounded-xl font-body-semibold text-body-default active:scale-[0.98] transition-all shadow-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">chat</span>
                    Buka Ruang Konsultasi
                </a>
                <a href="{{ url('/') }}" class="bg-primary-container hover:bg-primary text-on-primary px-6 py-2.5 rounded-xl font-body-semibold text-body-default active:scale-[0.98] transition-all shadow-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">home</span>
                    Kembali ke Beranda
                </a>
                <a href="#" class="border border-outline-variant hover:bg-surface-container-low text-on-surface px-6 py-2.5 rounded-xl font-body-semibold text-body-default active:scale-[0.98] transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">history</span>
                    Lihat Riwayat
                </a>
            </div>
        </div>
    </section>

    {{-- KANAN: STAT + TAHAPAN --}}
    <section class="lg:col-span-5 flex flex-col gap-4">
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/40 p-4 shadow-sm flex flex-col justify-between">
                <div class="text-2xl font-extrabold text-primary-container tracking-tight leading-none mb-2">{{ $antreanDiDepan }}</div>
                <div>
                    <p class="font-body-semibold text-xs text-on-surface leading-snug">Antrean di depan</p>
                    <p class="font-body-sm text-[11px] text-outline mt-1 leading-tight">pasien menunggu</p>
                </div>
            </div>
            <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/40 p-4 shadow-sm flex flex-col justify-between">
                <div class="text-2xl font-extrabold text-primary-container tracking-tight leading-none mb-2">{{ $sedangDilayani ? substr($sedangDilayani, -4) : '-' }}</div>
                <div>
                    <p class="font-body-semibold text-xs text-on-surface leading-snug">Sedang dilayani</p>
                    <p class="font-body-sm text-[11px] text-outline mt-1 leading-tight">{{ $sedangDilayani ? 'nomor sesi' : 'belum ada sesi' }}</p>
                </div>
            </div>
            <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/40 p-4 shadow-sm flex flex-col justify-between">
                <div class="text-2xl font-extrabold text-primary-container tracking-tight leading-none mb-2">{{ $sisaKuota }}</div>
                <div>
                    <p class="font-body-semibold text-xs text-on-surface leading-snug">Sisa kuota</p>
                    <p class="font-body-sm text-[11px] text-outline mt-1 leading-tight">dari {{ $kuotaMaksimal }} kuota</p>
                </div>
            </div>
        </div>

        <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/40 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-headline-sm text-headline-sm text-on-surface">Tahapan Anda</h3>
                @if ($posisi !== false)
                    <span class="font-badge-status text-[11px] text-primary bg-secondary-fixed/30 px-2 py-0.5 rounded-full">Langkah {{ $posisi + 1 }} dari 3</span>
                @endif
            </div>

            <div class="space-y-2 relative">
                @foreach ($tahap as $key => [$nama, $ket])
                    @php
                        $sekarang = $konsultasi->status === $key;
                        $lewat    = $posisi !== false && $loop->index < $posisi;
                    @endphp

                    @if ($sekarang)
                        <div class="relative bg-secondary-fixed/20 border border-secondary/30 rounded-xl p-3.5 flex items-start gap-3 shadow-xs">
                            <div class="relative flex items-center justify-center mt-0.5">
                                <span class="w-3.5 h-3.5 rounded-full bg-primary-container flex items-center justify-center ring-4 ring-secondary-container">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                </span>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="font-body-semibold text-body-default text-primary-container">{{ $nama }}</p>
                                    <span class="font-badge-status text-[10px] text-primary font-bold uppercase tracking-wider bg-white/80 px-2 py-0.5 rounded">Saat Ini</span>
                                </div>
                                <p class="font-body-default text-body-sm text-on-surface-variant mt-0.5">{{ $ket }}</p>
                            </div>
                        </div>
                    @else
                        <div class="relative rounded-xl p-3.5 flex items-start gap-3 {{ $lewat ? '' : 'opacity-70' }}">
                            <div class="w-3.5 h-3.5 rounded-full mt-0.5 flex-shrink-0 {{ $lewat ? 'bg-primary-container' : 'border-2 border-outline-variant bg-surface-container-lowest' }}"></div>
                            <div class="flex-1">
                                <p class="font-body-semibold text-body-default {{ $lewat ? 'text-on-surface' : 'text-on-surface-variant' }}">{{ $nama }}</p>
                                <p class="font-body-default text-body-sm text-outline mt-0.5">{{ $ket }}</p>
                            </div>
                        </div>
                    @endif

                    @unless ($loop->last)
                        <div class="w-0.5 h-4 bg-outline-variant/50 ml-5 my-0.5"></div>
                    @endunless
                @endforeach
            </div>

            @if ($jadwal)
                <div class="mt-4 pt-3 border-t border-outline-variant/20 flex items-center gap-2 text-outline font-body-sm text-body-sm">
                    <span class="material-symbols-outlined text-[16px] text-primary">info</span>
                    <span>Jam praktik: <strong>{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB</strong></span>
                </div>
            @endif
        </div>
    </section>
</div>

{{-- KETERANGAN STATUS --}}
<section class="mt-6">
    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/40 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-headline-sm text-headline-sm text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-primary-container">help_outline</span>
                Keterangan Status
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-start gap-3 p-3 rounded-xl bg-surface hover:bg-surface-container-low transition-colors">
                <span class="bg-[#E5E7EB] text-[#374151] font-badge-status text-badge-status px-2.5 py-1 rounded-full inline-flex items-center gap-1.5 flex-shrink-0 mt-0.5"><span class="w-1.5 h-1.5 rounded-full bg-[#6B7280]"></span>Menunggu</span>
                <span class="font-body-default text-body-default text-on-surface-variant">menunggu dokter memulai sesi</span>
            </div>
            <div class="flex items-start gap-3 p-3 rounded-xl bg-surface hover:bg-surface-container-low transition-colors">
                <span class="bg-[#FEF3C7] text-[#92400E] font-badge-status text-badge-status px-2.5 py-1 rounded-full inline-flex items-center gap-1.5 flex-shrink-0 mt-0.5"><span class="w-1.5 h-1.5 rounded-full bg-[#D97706]"></span>Berlangsung</span>
                <span class="font-body-default text-body-default text-on-surface-variant">sesi sedang berjalan</span>
            </div>
            <div class="flex items-start gap-3 p-3 rounded-xl bg-surface hover:bg-surface-container-low transition-colors">
                <span class="bg-[#DCFCE7] text-[#15803D] font-badge-status text-badge-status px-2.5 py-1 rounded-full inline-flex items-center gap-1.5 flex-shrink-0 mt-0.5"><span class="w-1.5 h-1.5 rounded-full bg-[#16A34A]"></span>Selesai</span>
                <span class="font-body-default text-body-default text-on-surface-variant">konsultasi telah selesai</span>
            </div>
            <div class="flex items-start gap-3 p-3 rounded-xl bg-surface hover:bg-surface-container-low transition-colors">
                <span class="bg-[#FEE2E2] text-[#B91C1C] font-badge-status text-badge-status px-2.5 py-1 rounded-full inline-flex items-center gap-1.5 flex-shrink-0 mt-0.5"><span class="w-1.5 h-1.5 rounded-full bg-[#DC2626]"></span>Ditolak</span>
                <span class="font-body-default text-body-default text-on-surface-variant">silakan buat konsultasi baru</span>
            </div>
        </div>

        <div class="border-t border-outline-variant/30 my-4"></div>

        <div class="flex items-center gap-2 font-body-sm text-body-sm text-outline italic">
            <span class="material-symbols-outlined text-[16px] text-secondary not-italic">notifications_active</span>
            <span>Notifikasi dikirim saat sesi dimulai.</span>
        </div>
    </div>
</section>
@endsection