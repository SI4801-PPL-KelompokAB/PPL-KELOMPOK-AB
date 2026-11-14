@extends('layouts.app')

@section('title', 'admin-Mengelola Data Poli')

@section('content')
<div class="row g-4">
    {{-- Kolom Kiri: Form Tambah / Ubah Poli --}}
    <div class="col-lg-5">
        <div class="card-mockup">
            <div class="card-mockup-header">
                <h5 class="fw-bold mb-0 text-dark">
                    @if(isset($editPoli) && $editPoli)
                        Ubah Poli
                    @else
                        Tambah Poli
                    @endif
                </h5>
            </div>
            <div class="card-mockup-body">
                <form method="POST"
                      action="{{ isset($editPoli) && $editPoli ? route('admin.poli.update', $editPoli) : route('admin.poli.store') }}"
                      dusk="{{ isset($editPoli) && $editPoli ? 'form-edit-poli' : 'form-tambah-poli' }}">
                    @csrf
                    @if(isset($editPoli) && $editPoli)
                        @method('PUT')
                    @endif

                    {{-- Kode Poli --}}
                    <div class="mb-3">
                        <label for="kode_poli" class="form-label fw-semibold text-secondary" style="font-size: 0.875rem;">
                            Kode Poli <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="kode_poli"
                               name="kode_poli"
                               class="form-control rounded-3 py-2 @error('kode_poli') is-invalid @enderror"
                               value="{{ old('kode_poli', $editPoli->kode_poli ?? '') }}"
                               placeholder="PGT"
                               maxlength="20"
                               required
                               dusk="input-kode-poli">
                        <div class="form-text text-muted" style="font-size: 0.775rem;">
                            Singkatan poli, dipakai sebagai awalan nomor antrean.
                        </div>
                        @error('kode_poli')
                            <div class="invalid-feedback" dusk="error-kode-poli">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Nama Poli --}}
                    <div class="mb-3">
                        <label for="nama_poli" class="form-label fw-semibold text-secondary" style="font-size: 0.875rem;">
                            Nama Poli <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="nama_poli"
                               name="nama_poli"
                               class="form-control rounded-3 py-2 @error('nama_poli') is-invalid @enderror"
                               value="{{ old('nama_poli', $editPoli->nama_poli ?? '') }}"
                               placeholder="Poli Gigi dan Mulut"
                               maxlength="100"
                               required
                               dusk="input-nama-poli">
                        @error('nama_poli')
                            <div class="invalid-feedback" dusk="error-nama-poli">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label fw-semibold text-secondary" style="font-size: 0.875rem;">
                            Deskripsi
                        </label>
                        <textarea id="deskripsi"
                                  name="deskripsi"
                                  class="form-control rounded-3 py-2 @error('deskripsi') is-invalid @enderror"
                                  rows="3"
                                  placeholder="Deskripsi singkat layanan"
                                  dusk="input-deskripsi">{{ old('deskripsi', $editPoli->deskripsi ?? '') }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Checkbox Aktif --}}
                    <div class="mb-4">
                        <div class="form-check">
                            <input type="hidden" name="status" value="0">
                            <input type="checkbox"
                                   id="status"
                                   name="status"
                                   value="1"
                                   class="form-check-input"
                                   {{ old('status', (isset($editPoli) ? $editPoli->status : 1)) ? 'checked' : '' }}
                                   dusk="input-status">
                            <label for="status" class="form-check-label fw-semibold" style="font-size: 0.875rem;">
                                Aktif (tampil bagi pasien)
                            </label>
                        </div>
                    </div>

                    {{-- Tombol Submit --}}
                    @if(isset($editPoli) && $editPoli)
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-app-primary flex-grow-1" dusk="tombol-simpan">
                                Perbarui Poli
                            </button>
                            <a href="{{ route('admin.poli.index') }}" class="btn btn-outline-secondary px-3" style="border-radius: 8px;">
                                Batal
                            </a>
                        </div>
                    @else
                        <button type="submit" class="btn btn-app-primary w-100" dusk="tombol-simpan">
                            Simpan Poli
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Tabel Daftar Poli --}}
    <div class="col-lg-7">
        <div class="card-mockup">
            <div class="card-mockup-header d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">Daftar Poli</h5>
                <a href="{{ route('admin.poli.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" style="font-size: 0.8rem;" dusk="tombol-tambah-poli">
                    + Tambah Baru
                </a>
            </div>
            <div class="card-mockup-body p-0">
                <div class="table-responsive">
                    <table class="table table-mockup align-middle" dusk="tabel-poli">
                        <thead>
                            <tr>
                                <th style="width: 15%">
                                    <a href="{{ route('admin.poli.index', ['sort' => 'kode_poli', 'dir' => request('sort') === 'kode_poli' && request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="text-secondary text-decoration-none">
                                        KODE <i class="bi bi-arrow-down-up" style="font-size: 0.7rem;"></i>
                                    </a>
                                </th>
                                <th style="width: 35%">
                                    <a href="{{ route('admin.poli.index', ['sort' => 'nama_poli', 'dir' => request('sort') === 'nama_poli' && request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="text-secondary text-decoration-none">
                                        NAMA <span class="text-primary" style="font-size: 0.75rem;">{{ request('sort') === 'nama_poli' && request('dir') === 'desc' ? '▼' : '▲' }}</span>
                                    </a>
                                </th>
                                <th style="width: 18%">STATUS</th>
                                <th style="width: 12%" class="text-center">ANTREAN</th>
                                <th style="width: 20%" class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($polis as $poli)
                                <tr dusk="baris-poli-{{ $poli->id }}" class="{{ isset($editPoli) && $editPoli->id === $poli->id ? 'table-warning bg-opacity-10' : '' }}">
                                    <td>
                                        <span class="badge-kode" dusk="kode-poli-{{ $poli->id }}">
                                            {{ $poli->kode_poli ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $poli->nama_poli }}</div>
                                        @if($poli->deskripsi)
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">
                                                {{ Str::limit($poli->deskripsi, 40) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($poli->status)
                                            <span class="status-dot-active" dusk="badge-aktif-{{ $poli->id }}">
                                                ● Aktif
                                            </span>
                                        @else
                                            <span class="status-dot-inactive" dusk="badge-nonaktif-{{ $poli->id }}">
                                                ● Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-medium text-secondary">
                                        {{ $poli->queues()->count() }}
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ route('admin.poli.edit', $poli) }}"
                                           class="btn-action-outline me-1"
                                           dusk="tombol-edit-{{ $poli->id }}">
                                            Ubah
                                        </a>

                                        <form method="POST"
                                              action="{{ route('admin.poli.toggle-status', $poli) }}"
                                              class="d-inline"
                                              dusk="form-toggle-{{ $poli->id }}">
                                            @csrf
                                            @method('PATCH')
                                            @if($poli->status)
                                                <button type="submit"
                                                        class="btn-action-delete"
                                                        dusk="tombol-toggle-{{ $poli->id }}"
                                                        onclick="return confirm('Apakah Anda yakin ingin menonaktifkan {{ $poli->nama_poli }}?')">
                                                    Hapus
                                                </button>
                                            @else
                                                <button type="submit"
                                                        class="btn-action-activate"
                                                        dusk="tombol-toggle-{{ $poli->id }}">
                                                    Aktifkan
                                                </button>
                                            @endif
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5" dusk="pesan-kosong">
                                        <div class="mb-2"><i class="bi bi-inbox fs-2 text-secondary"></i></div>
                                        Belum ada data poli terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
