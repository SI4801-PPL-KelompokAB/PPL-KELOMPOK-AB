@extends('layouts.konsultasi')

@section('title', 'Profil Pasien')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-headline-md font-headline-md text-on-surface">Profil Identitas Pasien</h1>
        <p class="text-body-sm text-outline">Lengkapi data identitasmu. Profil lengkap diperlukan untuk mengambil antrean (PBI04).</p>
    </div>

    @if (session('success'))
        <div class="bg-[#DCFCE7] text-[#15803D] text-body-default rounded-xl px-4 py-3">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="bg-error-container text-on-error-container text-body-default rounded-xl px-4 py-3">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('pasien.profil.update') }}" dusk="form-profil" class="bg-surface-container-lowest rounded-2xl border border-outline-variant/40 shadow-sm p-6 space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block font-body-semibold text-body-default mb-1">Tanggal lahir</label>
            <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', optional($profil->tanggal_lahir)->format('Y-m-d')) }}" dusk="tanggal_lahir" required class="w-full border border-outline-variant rounded-xl px-3 py-2 text-body-default">
            @error('tanggal_lahir')<p class="text-error text-body-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block font-body-semibold text-body-default mb-1">Jenis kelamin</label>
            <select name="jenis_kelamin" dusk="jenis_kelamin" required class="w-full border border-outline-variant rounded-xl px-3 py-2 text-body-default bg-white">
                <option value="">Pilih</option>
                <option value="L" @selected(old('jenis_kelamin', $profil->jenis_kelamin) === 'L')>Laki-laki (L)</option>
                <option value="P" @selected(old('jenis_kelamin', $profil->jenis_kelamin) === 'P')>Perempuan (P)</option>
            </select>
            @error('jenis_kelamin')<p class="text-error text-body-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block font-body-semibold text-body-default mb-1">Alamat</label>
            <textarea name="alamat" dusk="alamat" rows="3" required class="w-full border border-outline-variant rounded-xl px-3 py-2 text-body-default">{{ old('alamat', $profil->alamat) }}</textarea>
            @error('alamat')<p class="text-error text-body-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block font-body-semibold text-body-default mb-1">Jenis pasien</label>
            <select name="jenis_pasien" dusk="jenis_pasien" required class="w-full border border-outline-variant rounded-xl px-3 py-2 text-body-default bg-white">
                <option value="">Pilih</option>
                <option value="BPJS" @selected(old('jenis_pasien', $profil->jenis_pasien) === 'BPJS')>BPJS</option>
                <option value="UMUM" @selected(old('jenis_pasien', $profil->jenis_pasien) === 'UMUM')>UMUM</option>
            </select>
            @error('jenis_pasien')<p class="text-error text-body-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block font-body-semibold text-body-default mb-1">Nomor BPJS <span class="text-body-sm text-outline">(wajib bila BPJS, kosong bila UMUM)</span></label>
            <input type="text" name="no_bpjs" value="{{ old('no_bpjs', $profil->no_bpjs) }}" dusk="no_bpjs" class="w-full border border-outline-variant rounded-xl px-3 py-2 text-body-default">
            @error('no_bpjs')<p class="text-error text-body-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <button type="submit" dusk="tombol-simpan-profil" class="bg-primary-container hover:bg-primary text-on-primary px-5 py-2.5 rounded-xl font-body-semibold text-body-default">Simpan profil</button>
    </form>
</div>
@endsection
