@extends('layouts.app')

@section('title', 'Edit Jadwal Praktik Dokter')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="h4 mb-1 text-dark fw-bold">Edit Jadwal Praktik</h2>
                <p class="text-muted small mb-0">Ubah konfigurasi hari, jam kerja, atau kuota antrean dokter</p>
            </div>
            <a href="{{ route('admin.jadwal.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('admin.jadwal.update', $doctorSchedule) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <!-- Pilihan Dokter -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Dokter <span class="text-danger">*</span></label>
                            <select name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required dusk="input-doctor-id">
                                <option value="">-- Pilih Dokter --</option>
                                @foreach($doctors as $doc)
                                    <option value="{{ $doc->id }}" {{ old('doctor_id', $doctorSchedule->doctor_id) == $doc->id ? 'selected' : '' }}>
                                        {{ $doc->user->name ?? 'Dokter #'.$doc->id }} ({{ $doc->nomor_sip ? 'SIP: '.$doc->nomor_sip : 'Umum' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Pilihan Poli -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Poli Klinik <span class="text-danger">*</span></label>
                            <select name="poli_id" class="form-select @error('poli_id') is-invalid @enderror" required dusk="input-poli-id">
                                <option value="">-- Pilih Poli --</option>
                                @foreach($polis as $poli)
                                    <option value="{{ $poli->id }}" {{ old('poli_id', $doctorSchedule->poli_id) == $poli->id ? 'selected' : '' }}>
                                        {{ $poli->nama_poli }}
                                    </option>
                                @endforeach
                            </select>
                            @error('poli_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Pilihan Hari -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Hari Praktik <span class="text-danger">*</span></label>
                            <select name="hari" class="form-select @error('hari') is-invalid @enderror" required dusk="input-hari">
                                <option value="">-- Pilih Hari --</option>
                                @foreach($daftarHari as $h)
                                    <option value="{{ $h }}" {{ old('hari', $doctorSchedule->hari) == $h ? 'selected' : '' }}>
                                        {{ ucfirst($h) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('hari')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Jam Mulai -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_mulai" class="form-control @error('jam_mulai') is-invalid @enderror" value="{{ old('jam_mulai', $doctorSchedule->jam_mulai_formatted) }}" required dusk="input-jam-mulai">
                            @error('jam_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Jam Selesai -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_selesai" class="form-control @error('jam_selesai') is-invalid @enderror" value="{{ old('jam_selesai', $doctorSchedule->jam_selesai_formatted) }}" required dusk="input-jam-selesai">
                            @error('jam_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Kuota Antrean -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Kuota Maksimal Antrean <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="kuota" class="form-control @error('kuota') is-invalid @enderror" value="{{ old('kuota', $doctorSchedule->kuota) }}" min="1" required dusk="input-kuota">
                                <span class="input-group-text">pasien</span>
                            </div>
                            <div class="form-text small">Batas jumlah nomor antrean yang dapat diambil untuk sesi jadwal ini.</div>
                            @error('kuota')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status Aktif -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Status Jadwal</label>
                            <div class="form-check form-switch mt-2">
                                <input type="hidden" name="status" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" name="status" value="1" id="switchStatus" {{ old('status', $doctorSchedule->status) ? 'checked' : '' }}>
                                <label class="form-check-label" for="switchStatus">Jadwal Aktif</label>
                            </div>
                            <div class="form-text small">Hapus centang untuk menonaktifkan jadwal tanpa menghapusnya.</div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.jadwal.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4" dusk="btn-update-jadwal">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

