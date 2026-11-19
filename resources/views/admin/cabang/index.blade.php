@extends('layouts.app')

@section('title', 'Admin - Mengelola Data Cabang Klinik (PBI-12)')

@section('content')
<div class="row g-4">
    {{-- Kolom Kiri: Form Tambah / Ubah Cabang Klinik --}}
    <div class="col-lg-5">
        <div class="card-mockup">
            <div class="card-mockup-header">
                <h5 class="fw-bold mb-0 text-dark">
                    @if(isset($editCabang) && $editCabang)
                        Ubah Cabang Klinik
                    @else
                        Tambah Cabang Klinik
                    @endif
                </h5>
            </div>
            <div class="card-mockup-body">
                <form method="POST"
                      action="{{ isset($editCabang) && $editCabang ? route('admin.cabang.update', $editCabang) : route('admin.cabang.store') }}"
                      dusk="{{ isset($editCabang) && $editCabang ? 'form-edit-cabang' : 'form-tambah-cabang' }}">
                    @csrf
                    @if(isset($editCabang) && $editCabang)
                        @method('PUT')
                    @endif

                    {{-- Nama Cabang --}}
                    <div class="mb-3">
                        <label for="nama_cabang" class="form-label fw-semibold text-secondary" style="font-size: 0.875rem;">
                            Nama Cabang <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="nama_cabang"
                               name="nama_cabang"
                               class="form-control rounded-3 py-2 @error('nama_cabang') is-invalid @enderror"
                               value="{{ old('nama_cabang', $editCabang->nama_cabang ?? '') }}"
                               placeholder="Contoh: Klinik Bonamu Baleendah"
                               maxlength="150"
                               required
                               dusk="input-nama-cabang">
                        <div class="form-text text-muted" style="font-size: 0.775rem;">
                            Nama unik unit cabang Klinik Bona Mitra Keluarga.
                        </div>
                        @error('nama_cabang')
                            <div class="invalid-feedback" dusk="error-nama-cabang">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Nomor Telepon --}}
                    <div class="mb-3">
                        <label for="nomor_telepon" class="form-label fw-semibold text-secondary" style="font-size: 0.875rem;">
                            Nomor Telepon
                        </label>
                        <input type="text"
                               id="nomor_telepon"
                               name="nomor_telepon"
                               class="form-control rounded-3 py-2 @error('nomor_telepon') is-invalid @enderror"
                               value="{{ old('nomor_telepon', $editCabang->nomor_telepon ?? '') }}"
                               placeholder="Contoh: 022-5941234"
                               maxlength="20"
                               dusk="input-nomor-telepon">
                        @error('nomor_telepon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Alamat Lengkap --}}
                    <div class="mb-3">
                        <label for="alamat" class="form-label fw-semibold text-secondary" style="font-size: 0.875rem;">
                            Alamat Lengkap <span class="text-danger">*</span>
                        </label>
                        <textarea id="alamat"
                                  name="alamat"
                                  class="form-control rounded-3 py-2 @error('alamat') is-invalid @enderror"
                                  rows="3"
                                  placeholder="Alamat lengkap lokasi cabang klinik"
                                  required
                                  dusk="input-alamat">{{ old('alamat', $editCabang->alamat ?? '') }}</textarea>
                        @error('alamat')
                            <div class="invalid-feedback" dusk="error-alamat">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Checkbox Status Aktif --}}
                    <div class="mb-4">
                        <div class="form-check">
                            <input type="hidden" name="status" value="0">
                            <input type="checkbox"
                                   id="status"
                                   name="status"
                                   value="1"
                                   class="form-check-input"
                                   {{ old('status', (isset($editCabang) ? $editCabang->status : 1)) ? 'checked' : '' }}
                                   dusk="input-status">
                            <label for="status" class="form-check-label fw-semibold" style="font-size: 0.875rem;">
                                Aktif (beroperasi menerima pasien)
                            </label>
                        </div>
                    </div>

                    {{-- Tombol Submit --}}
                    @if(isset($editCabang) && $editCabang)
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-app-primary flex-grow-1" dusk="tombol-simpan">
                                Perbarui Cabang
                            </button>
                            <a href="{{ route('admin.cabang.index') }}" class="btn btn-outline-secondary px-3" style="border-radius: 8px;">
                                Batal
                            </a>
                        </div>
                    @else
                        <button type="submit" class="btn btn-app-primary w-100" dusk="tombol-simpan">
                            Simpan Cabang
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Tabel Daftar Cabang Klinik --}}
    <div class="col-lg-7">
        <div class="card-mockup">
            <div class="card-mockup-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Daftar Cabang Klinik</h5>
                    <small class="text-muted">Total terdaftar: {{ $cabangs->count() }} cabang</small>
                </div>
                <a href="{{ route('admin.cabang.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" style="font-size: 0.8rem;" dusk="tombol-tambah-cabang">
                    + Tambah Baru
                </a>
            </div>
            <div class="card-mockup-body p-0">
                <div class="table-responsive">
                    <table class="table table-mockup align-middle" dusk="tabel-cabang">
                        <thead>
                            <tr>
                                <th style="width: 35%">
                                    <a href="{{ route('admin.cabang.index', ['sort' => 'nama_cabang', 'dir' => request('sort') === 'nama_cabang' && request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="text-secondary text-decoration-none">
                                        NAMA CABANG <span class="text-primary" style="font-size: 0.75rem;">{{ request('sort') === 'nama_cabang' && request('dir') === 'desc' ? '▼' : '▲' }}</span>
                                    </a>
                                </th>
                                <th style="width: 25%">KONTAK &amp; ALAMAT</th>
                                <th style="width: 15%">STATUS</th>
                                <th style="width: 10%" class="text-center">POLI</th>
                                <th style="width: 15%" class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cabangs as $c)
                                <tr dusk="baris-cabang-{{ $c->id }}" class="{{ isset($editCabang) && $editCabang->id === $c->id ? 'table-warning bg-opacity-10' : '' }}">
                                    <td>
                                        <div class="fw-bold text-dark">{{ $c->nama_cabang }}</div>
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">
                                            <i class="bi bi-geo-alt me-1"></i>{{ Str::limit($c->alamat, 40) }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge-kode">
                                            <i class="bi bi-telephone me-1"></i>{{ $c->nomor_telepon ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($c->status)
                                            <span class="status-dot-active" dusk="status-aktif-{{ $c->id }}">
                                                &bull; Aktif
                                            </span>
                                        @else
                                            <span class="status-dot-inactive" dusk="status-nonaktif-{{ $c->id }}">
                                                &bull; Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border px-2 py-1">
                                            {{ $c->polis_count ?? 0 }} Poli
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('admin.cabang.index', ['edit' => $c->id]) }}"
                                               class="btn-action-outline"
                                               dusk="tombol-edit-{{ $c->id }}"
                                               title="Ubah data cabang">
                                                Ubah
                                            </a>

                                            {{-- Form Toggle Status --}}
                                            <form method="POST"
                                                  action="{{ route('admin.cabang.toggle-status', $c) }}"
                                                  class="d-inline m-0">
                                                @csrf
                                                @method('PATCH')
                                                <button type="button"
                                                        class="{{ $c->status ? 'btn-action-delete' : 'btn-action-activate' }}"
                                                        dusk="tombol-toggle-{{ $c->id }}"
                                                        onclick="konfirmasiToggleCabang(this, '{{ $c->nama_cabang }}', {{ $c->status ? 'true' : 'false' }})">
                                                    {{ $c->status ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-building-x fs-3 d-block mb-1"></i>
                                        Belum ada data cabang klinik yang terdaftar.
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

{{-- Modal Konfirmasi Toggle Status --}}
<div class="modal fade" id="modalToggleCabang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-body text-center p-4">
                <i id="iconToggleModal" class="bi bi-exclamation-circle text-warning fs-1 d-block mb-2"></i>
                <h6 class="fw-bold text-dark mb-1" id="judulToggleModal">Konfirmasi Status</h6>
                <p class="small text-muted mb-4" id="pesanToggleModal">Apakah Anda yakin ingin mengubah status cabang ini?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" id="btnKonfirmasiAksiCabang" class="btn btn-sm btn-danger rounded-pill px-3">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let formTargetCabang = null;
    const modalCabangEl = document.getElementById('modalToggleCabang');
    let modalCabangBs = null;

    document.addEventListener('DOMContentLoaded', function () {
        if (modalCabangEl && typeof bootstrap !== 'undefined') {
            modalCabangBs = new bootstrap.Modal(modalCabangEl);
        }
        document.getElementById('btnKonfirmasiAksiCabang').addEventListener('click', function () {
            if (formTargetCabang) {
                formTargetCabang.submit();
            }
        });
    });

    function konfirmasiToggleCabang(btn, namaCabang, isAktif) {
        formTargetCabang = btn.closest('form');
        const aksi = isAktif ? 'menonaktifkan' : 'mengaktifkan';
        const judul = isAktif ? 'Nonaktifkan Cabang' : 'Aktifkan Cabang';
        const btnClass = isAktif ? 'btn-danger' : 'btn-success';

        document.getElementById('judulToggleModal').innerText = judul;
        document.getElementById('pesanToggleModal').innerText = 'Apakah Anda yakin ingin ' + aksi + ' cabang "' + namaCabang + '"?';

        const btnKonfirm = document.getElementById('btnKonfirmasiAksiCabang');
        btnKonfirm.className = 'btn btn-sm rounded-pill px-3 ' + btnClass;
        btnKonfirm.innerText = isAktif ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan';

        if (modalCabangBs) {
            modalCabangBs.show();
        } else {
            if (confirm('Apakah Anda yakin ingin ' + aksi + ' cabang "' + namaCabang + '"?')) {
                formTargetCabang.submit();
            }
        }
    }
</script>
@endpush
@endsection
