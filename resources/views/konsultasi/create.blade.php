@extends('layouts.konsultasi')

@section('title', 'Konsultasi Online')

@section('content')
@php
    $pertama = $jadwals->first(fn ($x) => $x->sisa_kuota > 0 && ! $x->sudah_tutup)?->id;
    $terbuka = $jadwals->where('sudah_tutup', false);
@endphp

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

    {{-- KIRI: FORM --}}
    <section class="lg:col-span-7 bg-surface-container-lowest rounded-2xl border border-outline-variant/60 shadow-sm p-6" style="border-top: 4px solid #0F5C5A;">
        <div class="mb-6">
            <span class="text-label-uppercase font-label-uppercase text-secondary uppercase tracking-wider block mb-1">BUAT KONSULTASI ONLINE</span>
            <h1 class="text-headline-md md:text-headline-lg font-headline-lg text-on-surface">Pilih jadwal</h1>
        </div>

        {{-- Filter tanggal dan poli (GET). Dokter dipilih lewat jadwal di bawah --}}
        <form method="GET" action="{{ route('konsultasi.create') }}" class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
            <div>
                <label class="block text-xs font-body-semibold text-on-surface mb-1.5" for="input-date">Tanggal</label>
                <div class="relative">
                    <input id="input-date" type="date" name="tanggal" value="{{ $tanggal }}" min="{{ today()->toDateString() }}" onchange="this.form.submit()"
                        class="relative w-full h-[42px] pl-3 pr-9 text-body-default text-on-surface bg-surface-container-lowest border border-outline-variant rounded-lg focus:border-primary-container focus:ring-2 focus:ring-secondary-fixed/40 transition-all cursor-pointer [&::-webkit-calendar-picker-indicator]:absolute [&::-webkit-calendar-picker-indicator]:inset-0 [&::-webkit-calendar-picker-indicator]:w-full [&::-webkit-calendar-picker-indicator]:opacity-0 [&::-webkit-calendar-picker-indicator]:cursor-pointer">
                    <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-on-surface-variant text-[20px] pointer-events-none">calendar_today</span>
                </div>
            </div>
            <div>
                <label class="block text-xs font-body-semibold text-on-surface mb-1.5" for="select-poli">Poli</label>
                <div class="relative">
                    <select id="select-poli" name="poli" onchange="this.form.submit()"
                        class="w-full h-[42px] pl-3 pr-8 text-body-default text-on-surface bg-surface-container-lowest border border-outline-variant rounded-lg appearance-none focus:border-primary-container focus:ring-2 focus:ring-secondary-fixed/40 transition-all">
                        @foreach ($polis as $p)
                            <option value="{{ $p->id }}" @selected((int) $poliId === (int) $p->id)>{{ $p->nama_poli }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2 top-2.5 text-on-surface-variant text-[20px] pointer-events-none">expand_more</span>
                </div>
            </div>
        </form>

        {{-- Form kirim konsultasi --}}
        <form id="form-konsultasi" method="POST" action="{{ route('konsultasi.store') }}">
            @csrf
            <input type="hidden" name="tanggal_konsultasi" value="{{ $tanggal }}">

            <div class="mt-6 mb-3">
                <h2 class="text-body-semibold font-body-semibold text-on-surface">Jadwal tersedia</h2>
            </div>

            @foreach (array_merge($errors->get('doctor_schedule_id'), $errors->get('tanggal_konsultasi')) as $pesan)
                <div class="flex items-center gap-1 text-xs text-error mb-2">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    <span>{{ $pesan }}</span>
                </div>
            @endforeach

            <div class="space-y-3" role="radiogroup" aria-label="Jadwal tersedia">
                @forelse ($jadwals as $j)
                    @if ($j->sisa_kuota > 0 && ! $j->sudah_tutup)
                        <label class="block cursor-pointer">
                            <input type="radio" name="doctor_schedule_id" value="{{ $j->id }}" class="peer sr-only" @checked((string) old('doctor_schedule_id', $pertama) === (string) $j->id)>
                            <div class="p-4 rounded-xl border border-outline-variant bg-surface-container-lowest hover:border-primary-container transition-all flex items-center justify-between peer-checked:border-2 peer-checked:border-primary-container peer-checked:bg-surface-container-low peer-checked:[&_.rd]:border-primary-container peer-checked:[&_.dot]:scale-100">
                                <div class="flex items-center gap-3">
                                    <div class="rd w-5 h-5 rounded-full border-2 border-outline flex items-center justify-center bg-surface-container-lowest">
                                        <div class="dot w-2.5 h-2.5 rounded-full bg-primary-container scale-0 transition-transform"></div>
                                    </div>
                                    <div>
                                        <div class="text-body-semibold font-body-semibold text-on-surface">{{ $j->doctor->user->name }}</div>
                                        <div class="text-xs text-on-surface-variant mt-1">{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}</div>
                                    </div>
                                </div>
                                @if ($j->sisa_kuota <= 2)
                                    <span class="text-badge-status font-badge-status text-amber-800 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded">Sisa kuota: {{ $j->sisa_kuota }}</span>
                                @else
                                    <span class="text-badge-status font-badge-status text-primary bg-secondary-fixed/30 px-2.5 py-1 rounded-full border border-secondary-fixed">Sisa kuota: {{ $j->sisa_kuota }}</span>
                                @endif
                            </div>
                        </label>
                    @else
                        <div class="p-4 rounded-xl border border-outline-variant/60 bg-surface-bright opacity-60 cursor-not-allowed flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-5 h-5 rounded-full border-2 border-outline-variant bg-surface-container-highest"></div>
                                <div>
                                    <div class="text-body-default text-outline">{{ $j->doctor->user->name }}</div>
                                    <div class="text-xs text-outline mt-1">{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}</div>
                                </div>
                            </div>
                            <span class="text-badge-status font-badge-status bg-error-container text-on-error-container px-2.5 py-0.5 rounded-full">{{ $j->sudah_tutup ? 'Tutup' : 'Penuh' }}</span>
                        </div>
                    @endif
                @empty
                    <p class="text-body-default text-outline">Tidak ada dokter yang praktik di poli ini pada tanggal tersebut</p>
                @endforelse
            </div>

            <div class="mt-5">
                <label class="block text-body-semibold font-body-semibold text-on-surface mb-1.5" for="keluhan">Keluhan / Gejala <span class="text-error">*</span></label>
                <textarea id="keluhan" name="keluhan" rows="4" placeholder="Ceritakan keluhan Anda (min. 10, maks. 1000 karakter)"
                    class="w-full p-3 text-body-default text-on-surface placeholder:text-outline-variant border border-outline-variant rounded-lg focus:border-primary-container focus:ring-2 focus:ring-secondary-fixed/40 transition-all resize-none">{{ old('keluhan') }}</textarea>
                <div class="flex items-center justify-between mt-1.5">
                    <div id="err-keluhan" @if ($errors->has('keluhan')) data-server="1" @endif
                        class="flex items-center gap-1 text-xs text-error {{ $errors->has('keluhan') ? '' : 'opacity-0' }}">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        <span>{{ $errors->first('keluhan') ?: 'Keluhan minimal 10 karakter' }}</span>
                    </div>
                    <span id="hitung" class="text-xs text-outline font-mono">0 / 1000</span>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-outline-variant/40 flex items-center justify-between">
                <a href="{{ url('/') }}" class="border border-outline-variant text-on-surface hover:bg-surface-container px-5 py-2.5 rounded-xl font-body-semibold text-body-default transition-all">Kembali</a>
                <button type="submit" class="bg-primary-container hover:bg-primary text-on-primary px-7 py-2.5 rounded-xl font-body-semibold text-body-default shadow-sm transition-all flex items-center gap-2 active:scale-[0.98]">
                    <span>Buat Konsultasi</span>
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </button>
            </div>
        </form>
    </section>

    {{-- KANAN: STAT + STEPPER --}}
    <aside class="lg:col-span-5 space-y-4">
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/60 p-4 shadow-sm text-center">
                <span class="text-[10px] uppercase tracking-wider font-label-uppercase text-outline block mb-1">DOKTER</span>
                <div class="text-headline-md font-headline-md text-primary-container leading-none my-1">{{ $dokters->count() }}</div>
                <p class="text-[11px] text-on-surface-variant leading-tight">Dokter tersedia</p>
            </div>
            <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/60 p-4 shadow-sm text-center">
                <span class="text-[10px] uppercase tracking-wider font-label-uppercase text-outline block mb-1">KUOTA</span>
                <div class="text-headline-md font-headline-md text-primary-container leading-none my-1">{{ $terbuka->sum('sisa_kuota') }}</div>
                <p class="text-[11px] text-on-surface-variant leading-tight">Sisa kuota</p>
            </div>
            <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/60 p-4 shadow-sm text-center">
                <span class="text-[10px] uppercase tracking-wider font-label-uppercase text-outline block mb-1">AKTIF</span>
                <div class="text-headline-md font-headline-md text-primary-container leading-none my-1">{{ $aktif }}</div>
                <p class="text-[11px] text-on-surface-variant leading-tight">Konsultasi aktif</p>
            </div>
        </div>

        <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/60 p-5 shadow-sm">
            <h2 class="text-headline-sm font-headline-sm text-on-surface mb-4">Langkah konsultasi</h2>
            <div class="space-y-2">
                <div class="bg-surface-container-low border border-secondary-fixed rounded-xl p-3 flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full bg-primary-container text-on-primary flex items-center justify-center text-xs font-body-semibold shrink-0">1</div>
                    <div class="flex-grow">
                        <span class="text-body-semibold font-body-semibold text-primary">Pilih jadwal</span>
                        <p class="text-body-sm text-outline">Poli, dokter &amp; jam</p>
                    </div>
                    <span class="material-symbols-outlined text-primary text-[18px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                </div>
                <div class="ml-6 w-0.5 h-3 bg-outline-variant/60"></div>
                <div class="py-2 px-3 flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full border-2 border-outline-variant text-outline flex items-center justify-center text-xs font-body-semibold shrink-0">2</div>
                    <div>
                        <span class="text-body-default font-body-semibold text-on-surface">Isi keluhan</span>
                        <p class="text-body-sm text-outline">Gejala saat ini</p>
                    </div>
                </div>
                <div class="ml-6 w-0.5 h-3 bg-outline-variant/60"></div>
                <div class="py-2 px-3 flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full border-2 border-outline-variant/40 text-outline-variant flex items-center justify-center text-xs font-body-semibold shrink-0">3</div>
                    <div>
                        <span class="text-body-default text-outline">Konfirmasi</span>
                        <p class="text-body-sm text-outline/80">Cek data</p>
                    </div>
                </div>
                <div class="ml-6 w-0.5 h-3 bg-outline-variant/60"></div>
                <div class="py-2 px-3 flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full border-2 border-outline-variant/40 text-outline-variant flex items-center justify-center text-xs font-body-semibold shrink-0">4</div>
                    <div>
                        <span class="text-body-default text-outline">Mulai konsultasi</span>
                        <p class="text-body-sm text-outline/80">Lewat chat</p>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</div>

{{-- KETENTUAN --}}
<section class="mt-6 bg-surface-container-lowest rounded-2xl border border-outline-variant/60 p-5 shadow-sm">
    <div class="flex items-center gap-2 mb-3 text-primary-container">
        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">info</span>
        <h2 class="text-headline-sm font-headline-sm text-on-surface">Ketentuan</h2>
    </div>
    <ul class="space-y-2 pl-1">
        <li class="flex items-start gap-2.5 text-body-default text-on-surface-variant"><span class="w-1.5 h-1.5 rounded-full bg-primary-container mt-2 shrink-0"></span><span>Satu konsultasi aktif per jadwal.</span></li>
        <li class="flex items-start gap-2.5 text-body-default text-on-surface-variant"><span class="w-1.5 h-1.5 rounded-full bg-primary-container mt-2 shrink-0"></span><span>Jadwal penuh atau tutup tidak bisa dipilih.</span></li>
        <li class="flex items-start gap-2.5 text-body-default text-on-surface-variant"><span class="w-1.5 h-1.5 rounded-full bg-primary-container mt-2 shrink-0"></span><span>Keluhan 10 sampai 1000 karakter.</span></li>
    </ul>
</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const t = document.getElementById('keluhan');
        const c = document.getElementById('hitung');
        const e = document.getElementById('err-keluhan');
        if (!t) return;
        const cek = () => {
            c.textContent = `${t.value.length} / 1000`;
            if (!e.dataset.server) {
                e.classList.toggle('opacity-0', t.value.length === 0 || t.value.length >= 10);
            }
        };
        t.addEventListener('input', cek);
        cek();
    });
</script>
@endpush