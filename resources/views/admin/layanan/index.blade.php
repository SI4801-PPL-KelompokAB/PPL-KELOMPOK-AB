@extends('layouts.app')

@section('title', 'Layanan dan Tarif — Puskesmas')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --brand-green-dark: #0f3d32;
        --brand-green-header: #0e382f;
        --brand-mint-badge: #d4f4ea;
        --brand-mint-text: #095946;
        --brand-mint-pill: #bbf0e1;
        --brand-sage-header: #dbeae3;
        --brand-sage-text: #194638;
        --brand-border: #edf2f0;
    }

    .mockup-container {
        max-width: 1040px;
        margin: 0 auto;
        padding-top: 1rem;
    }

    /* Badge Tag */
    .badge-mockup-layanan {
        display: inline-block;
        background-color: var(--brand-mint-badge);
        color: var(--brand-mint-text);
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        padding: 0.35rem 0.95rem;
        border-radius: 50rem;
        margin-bottom: 0.65rem;
    }

    /* Big Serif Title */
    .mockup-title {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 2.35rem;
        font-weight: 700;
        color: var(--brand-green-dark);
        letter-spacing: -0.02em;
        margin-bottom: 0.35rem;
        line-height: 1.2;
    }

    .mockup-subtitle {
        color: #64748b;
        font-size: 0.95rem;
        margin-bottom: 1.5rem;
    }

    /* Search Input */
    .mockup-search-input {
        background-color: #ffffff;
        border: 1px solid #d8e2dc;
        border-radius: 10px;
        padding: 0.65rem 1.15rem;
        font-size: 0.9rem;
        color: #1e293b;
        width: 340px;
        max-width: 100%;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .mockup-search-input:focus {
        border-color: #005954;
        box-shadow: 0 0 0 3px rgba(0, 89, 84, 0.12);
        outline: none;
    }

    /* Filter Pills */
    .filter-pills-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.85rem;
        margin-bottom: 1.75rem;
    }

    .filter-pill-btn {
        background: #ffffff;
        border: 1px solid #d8e2dc;
        color: #334155;
        font-size: 0.825rem;
        font-weight: 500;
        padding: 0.45rem 1.25rem;
        border-radius: 50rem;
        text-decoration: none;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
    }

    .filter-pill-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: var(--brand-green-dark);
    }

    .filter-pill-btn.active {
        background: var(--brand-green-dark) !important;
        border-color: var(--brand-green-dark) !important;
        color: #ffffff !important;
        font-weight: 600;
    }

    /* Table Container & Sage Header */
    .mockup-table-wrap {
        border-radius: 12px;
    }

    .mockup-table-header {
        background-color: var(--brand-sage-header);
        border-radius: 8px;
        padding: 0.85rem 1.25rem;
        display: grid;
        grid-template-columns: 2.2fr 1.6fr 1.4fr 1.4fr 1.1fr;
        align-items: center;
        margin-bottom: 0.65rem;
    }

    .mockup-th {
        font-size: 0.725rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--brand-sage-text);
    }

    /* Row Cards */
    .mockup-row-card {
        background: #ffffff;
        border: 1px solid var(--brand-border);
        border-radius: 8px;
        padding: 1rem 1.25rem;
        display: grid;
        grid-template-columns: 2.2fr 1.6fr 1.4fr 1.4fr 1.1fr;
        align-items: center;
        margin-bottom: 0.55rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.015);
        transition: transform 0.15s, box-shadow 0.15s, border-color 0.15s;
    }

    .mockup-row-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.035);
    }

    .mockup-row-card.row-inactive {
        background-color: #f8fafc;
        opacity: 0.7;
    }

    .layanan-name {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.35;
    }

    .layanan-code {
        font-size: 0.725rem;
        color: #64748b;
        font-family: monospace;
        margin-top: 2px;
        display: block;
    }

    /* Poli Badge in Row */
    .badge-poli-mint {
        background-color: #d7f3ea;
        color: #085a46;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.3rem 0.85rem;
        border-radius: 50rem;
        display: inline-block;
        white-space: nowrap;
    }

    .price-text {
        font-size: 0.9rem;
        font-weight: 500;
        color: #334155;
    }

    .mockup-footer-note {
        font-size: 0.775rem;
        color: #64748b;
        margin-top: 1.25rem;
        margin-bottom: 2rem;
    }

    /* Admin Form & Controls */
    .btn-admin-add {
        background-color: var(--brand-green-dark);
        color: #ffffff;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.55rem 1.15rem;
        border-radius: 8px;
        border: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .btn-admin-add:hover {
        background-color: #092c24;
        color: #ffffff;
    }

    .card-admin-form {
        background: #ffffff;
        border: 1px solid #dbeae3;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(15, 61, 50, 0.05);
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .card-admin-form-header {
        background-color: #f4f9f7;
        border-bottom: 1px solid #e6ede9;
        padding: 1rem 1.5rem;
    }

    @media (max-width: 768px) {
        .mockup-table-header {
            display: none;
        }
        .mockup-row-card {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="mockup-container">

    {{-- Tag Badge Mockup --}}
    <div>
        <span class="badge-mockup-layanan">LAYANAN</span>
    </div>

    {{-- Main Heading & Subtitle Mockup --}}
    <h1 class="mockup-title">Layanan dan Tarif</h1>
    <p class="mockup-subtitle">Lihat daftar layanan puskesmas beserta tarifnya sebelum Anda datang.</p>

    {{-- Search Bar & Admin Action --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
        <form method="GET" action="{{ route('admin.layanan.index') }}" class="d-flex align-items-center gap-2 m-0" id="formCariLayanan">
            @if(request('poli_id'))
                <input type="hidden" name="poli_id" value="{{ request('poli_id') }}">
            @endif
            @if(request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
                <input type="hidden" name="dir" value="{{ request('dir', 'asc') }}">
            @endif
            <div class="position-relative">
                <input type="text"
                       name="q"
                       id="inputCariLayanan"
                       class="form-control mockup-search-input"
                       placeholder="Cari nama layanan..."
                       value="{{ request('q') ?? request('search') }}"
                       autocomplete="off">
            </div>
            @if(request('q') || request('search'))
                <a href="{{ route('admin.layanan.index', request('poli_id') ? ['poli_id' => request('poli_id')] : []) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" style="font-size: 0.8rem;">
                    Reset
                </a>
            @endif
        </form>

        {{-- Tombol Tambah Layanan untuk Admin --}}
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-admin-add" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFormLayanan" aria-expanded="{{ isset($editLayanan) || $errors->any() ? 'true' : 'false' }}">
                <i class="bi bi-plus-circle me-1"></i> {{ isset($editLayanan) ? 'Buka Form Edit' : 'Tambah Layanan' }}
            </button>
        </div>
    </div>

    {{-- Filter Category Pills Mockup (Semua, Poli Umum, Poli Gigi dan Mulut, Poli Anak, dst) --}}
    <div class="filter-pills-wrap">
        <a href="{{ route('admin.layanan.index', array_filter(['q' => request('q'), 'sort' => request('sort'), 'dir' => request('dir')])) }}"
           class="filter-pill-btn {{ !request('poli_id') ? 'active' : '' }}">
            Semua
        </a>
        @foreach($polis as $p)
            <a href="{{ route('admin.layanan.index', array_filter(['poli_id' => $p->id, 'q' => request('q'), 'sort' => request('sort'), 'dir' => request('dir')])) }}"
               class="filter-pill-btn {{ request('poli_id') == $p->id ? 'active' : '' }}">
                {{ $p->nama_poli }}
            </a>
        @endforeach
    </div>

    {{-- Collapsible Admin Form Card (Tambah / Ubah) --}}
    <div class="collapse {{ isset($editLayanan) || $errors->any() ? 'show' : '' }} mb-4" id="collapseFormLayanan">
        <div class="card-admin-form">
            <div class="card-admin-form-header d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">
                    {{ isset($editLayanan) ? 'Ubah Layanan & Tarif' : 'Tambah Layanan & Tarif Baru' }}
                </h5>
                <button type="button" class="btn-close" data-bs-toggle="collapse" data-bs-target="#collapseFormLayanan"></button>
            </div>
            <div class="p-4">
                <form method="POST"
                      action="{{ isset($editLayanan) && $editLayanan ? route('admin.layanan.update', $editLayanan) : route('admin.layanan.store') }}"
                      dusk="{{ isset($editLayanan) && $editLayanan ? 'form-edit-layanan' : 'form-tambah-layanan' }}">
                    @csrf
                    @if(isset($editLayanan) && $editLayanan)
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        {{-- Nama Layanan --}}
                        <div class="col-md-6">
                            <label for="nama_layanan" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">
                                Nama Layanan / Tindakan <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="nama_layanan"
                                   name="nama_layanan"
                                   class="form-control rounded-3 @error('nama_layanan') is-invalid @enderror"
                                   value="{{ old('nama_layanan', $editLayanan->nama_layanan ?? '') }}"
                                   placeholder="Contoh: Pemeriksaan Umum / Scaling Gigi"
                                   maxlength="150"
                                   required
                                   dusk="input-nama-layanan">
                            @error('nama_layanan')
                                <div class="invalid-feedback" dusk="error-nama-layanan">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Kode Layanan --}}
                        <div class="col-md-3">
                            <label for="kode_layanan" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">
                                Kode Layanan
                            </label>
                            <input type="text"
                                   id="kode_layanan"
                                   name="kode_layanan"
                                   class="form-control rounded-3 @error('kode_layanan') is-invalid @enderror"
                                   value="{{ old('kode_layanan', $editLayanan->kode_layanan ?? '') }}"
                                   placeholder="LAY-001"
                                   maxlength="30"
                                   dusk="input-kode-layanan">
                            @error('kode_layanan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Poli Terkait --}}
                        <div class="col-md-3">
                            <label for="poli_id" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">
                                Poli Terkait
                            </label>
                            <select id="poli_id"
                                    name="poli_id"
                                    class="form-select rounded-3 @error('poli_id') is-invalid @enderror"
                                    dusk="select-poli">
                                <option value="">-- Semua Poli / Umum --</option>
                                @foreach($polis as $poli)
                                    <option value="{{ $poli->id }}" {{ old('poli_id', $editLayanan->poli_id ?? '') == $poli->id ? 'selected' : '' }}>
                                        {{ $poli->nama_poli }}
                                    </option>
                                @endforeach
                            </select>
                            @error('poli_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tarif Umum --}}
                        <div class="col-md-6">
                            <label for="tarif_umum" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">
                                Tarif Pasien Umum (Rp) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">Rp</span>
                                <input type="number"
                                       id="tarif_umum"
                                       name="tarif_umum"
                                       step="500"
                                       min="0"
                                       class="form-control rounded-end-3 @error('tarif_umum') is-invalid @enderror"
                                       value="{{ old('tarif_umum', isset($editLayanan) ? (int)$editLayanan->tarif_umum : '50000') }}"
                                       placeholder="50000"
                                       required
                                       dusk="input-tarif-umum">
                                @error('tarif_umum')
                                    <div class="invalid-feedback d-block" dusk="error-tarif-umum">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Tarif BPJS --}}
                        <div class="col-md-6">
                            <label for="tarif_bpjs" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">
                                Tarif Pasien BPJS (Rp) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">Rp</span>
                                <input type="number"
                                       id="tarif_bpjs"
                                       name="tarif_bpjs"
                                       step="500"
                                       min="0"
                                       class="form-control rounded-end-3 @error('tarif_bpjs') is-invalid @enderror"
                                       value="{{ old('tarif_bpjs', isset($editLayanan) ? (int)$editLayanan->tarif_bpjs : '0') }}"
                                       placeholder="0"
                                       required
                                       dusk="input-tarif-bpjs">
                                @error('tarif_bpjs')
                                    <div class="invalid-feedback d-block" dusk="error-tarif-bpjs">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted" style="font-size: 0.75rem;">Isi 0 jika layanan gratis / ditanggung penuh BPJS.</small>
                        </div>

                        {{-- Deskripsi --}}
                        <div class="col-12">
                            <label for="deskripsi" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">
                                Deskripsi Layanan / Catatan Persyaratan
                            </label>
                            <textarea id="deskripsi"
                                      name="deskripsi"
                                      class="form-control rounded-3 @error('deskripsi') is-invalid @enderror"
                                      rows="2"
                                      placeholder="Informasi persiapan pasien, durasi tindakan, atau keterangan lainnya."
                                      dusk="input-deskripsi">{{ old('deskripsi', $editLayanan->deskripsi ?? '') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Checkbox Aktif & Submit --}}
                        <div class="col-12 d-flex flex-wrap justify-content-between align-items-center pt-2 gap-2">
                            <div class="form-check">
                                <input type="hidden" name="status" value="0">
                                <input type="checkbox"
                                       id="status"
                                       name="status"
                                       value="1"
                                       class="form-check-input"
                                       {{ old('status', (isset($editLayanan) ? $editLayanan->status : 1)) ? 'checked' : '' }}
                                       dusk="input-status">
                                <label for="status" class="form-check-label fw-semibold text-dark" style="font-size: 0.85rem;">
                                    Aktif (ditampilkan kepada pasien)
                                </label>
                            </div>

                            <div class="d-flex gap-2">
                                @if(isset($editLayanan) && $editLayanan)
                                    <a href="{{ route('admin.layanan.index') }}" class="btn btn-outline-secondary px-3 py-2 rounded-3" style="font-size: 0.85rem;">Batal</a>
                                @endif
                                <button type="submit" class="btn btn-admin-add px-4 py-2" dusk="btn-simpan-layanan">
                                    <i class="bi bi-check2-circle me-1"></i> Simpan Layanan &amp; Tarif
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Mockup Table / List Data Layanan --}}
    <div class="mockup-table-wrap">
        {{-- Table Header Mockup (Sage/Mint Tint) --}}
        <div class="mockup-table-header">
            <div class="mockup-th">LAYANAN</div>
            <div class="mockup-th">POLI</div>
            <div class="mockup-th">PASIEN UMUM</div>
            <div class="mockup-th">PASIEN BPJS</div>
            <div class="mockup-th text-end">AKSI</div>
        </div>

        {{-- Row Items Mockup --}}
        @forelse($layanans as $layanan)
            <div class="mockup-row-card {{ ! $layanan->status ? 'row-inactive' : '' }}" dusk="layanan-row-{{ $layanan->id }}">
                {{-- Kolom 1: LAYANAN --}}
                <div>
                    <div class="layanan-name">{{ $layanan->nama_layanan }}</div>
                    @if($layanan->kode_layanan)
                        <span class="layanan-code">{{ $layanan->kode_layanan }}</span>
                    @endif
                </div>

                {{-- Kolom 2: POLI (Mint Badge) --}}
                <div>
                    <span class="badge-poli-mint">
                        {{ $layanan->poli->nama_poli ?? 'Semua Poli' }}
                    </span>
                </div>

                {{-- Kolom 3: PASIEN UMUM --}}
                <div>
                    <span class="price-text">{{ $layanan->tarif_umum_formatted }}</span>
                </div>

                {{-- Kolom 4: PASIEN BPJS --}}
                <div>
                    <span class="price-text">
                        {{ (float)$layanan->tarif_bpjs == 0 ? 'Rp 0' : $layanan->tarif_bpjs_formatted }}
                    </span>
                </div>

                {{-- Kolom 5: AKSI (ADMIN) --}}
                <div class="d-flex align-items-center justify-content-end gap-2">
                    @if(! $layanan->status)
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-0" style="font-size: 0.7rem;">Nonaktif</span>
                    @endif
                    <a href="{{ route('admin.layanan.index', ['edit' => $layanan->id]) }}"
                       class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2"
                       style="font-size: 0.775rem;"
                       dusk="btn-edit-layanan-{{ $layanan->id }}"
                       title="Ubah Layanan">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form method="POST" action="{{ route('admin.layanan.toggle-status', $layanan) }}" class="d-inline m-0">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="btn btn-sm {{ $layanan->status ? 'btn-outline-danger' : 'btn-outline-success' }} rounded-pill py-0 px-2"
                                style="font-size: 0.775rem;"
                                dusk="btn-toggle-status-{{ $layanan->id }}"
                                title="{{ $layanan->status ? 'Nonaktifkan layanan' : 'Aktifkan layanan' }}">
                            <i class="bi {{ $layanan->status ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-center py-5 bg-white rounded-3 border">
                <i class="bi bi-journal-x fs-1 text-muted d-block mb-2"></i>
                <div class="fw-semibold text-secondary">Tidak ada data layanan &amp; tarif yang sesuai.</div>
                <small class="text-muted">Coba ubah kata kunci pencarian atau filter poli di atas.</small>
            </div>
        @endforelse
    </div>

    {{-- Catatan Footer Sesuai Mockup --}}
    <div class="mockup-footer-note">
        Nominal tarif akan mengikuti data yang diisi admin.
    </div>

</div>
@endsection
