<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLayananRequest;
use App\Http\Requests\Admin\UpdateLayananRequest;
use App\Models\Layanan;
use App\Models\Poli;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LayananController extends Controller
{
    /**
     * Tampilkan halaman utama kelola data layanan dan tarif (2-kolom mockup).
     */
    public function index(Request $request): View
    {
        $sort = $request->query('sort', 'created_at');
        $dir = $request->query('dir', 'desc');

        if (! in_array($sort, ['nama_layanan', 'tarif_umum', 'created_at'], true)) {
            $sort = 'created_at';
        }
        if (! in_array($dir, ['asc', 'desc'], true)) {
            $dir = 'desc';
        }

        $query = Layanan::with('poli');

        $search = $request->query('q') ?? $request->query('search');
        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_layanan', 'like', "%{$search}%")
                  ->orWhere('kode_layanan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('poli_id')) {
            $query->where('poli_id', $request->query('poli_id'));
        }

        $layanans = $query->orderBy($sort, $dir)->get();
        $polis = Poli::where('status', true)->orderBy('nama_poli')->get();
        $editLayanan = $request->filled('edit') ? Layanan::find($request->query('edit')) : null;

        return view('admin.layanan.index', compact('layanans', 'polis', 'editLayanan'));
    }

    /**
     * Form tambah layanan baru (mengarahkan ke index).
     */
    public function create(): View
    {
        $layanans = Layanan::with('poli')->latest()->get();
        $polis = Poli::where('status', true)->orderBy('nama_poli')->get();
        $editLayanan = null;

        return view('admin.layanan.index', compact('layanans', 'polis', 'editLayanan'));
    }

    /**
     * Simpan data layanan & tarif baru ke database.
     */
    public function store(StoreLayananRequest $request): RedirectResponse
    {
        Layanan::create($request->validated());

        return redirect()->route('admin.layanan.index')
            ->with('sukses', 'Layanan dan tarif baru berhasil ditambahkan.');
    }

    /**
     * Tampilkan form ubah data layanan (menggunakan layout 2 kolom).
     */
    public function edit(Layanan $layanan): View
    {
        $layanans = Layanan::with('poli')->latest()->get();
        $polis = Poli::where('status', true)->orderBy('nama_poli')->get();
        $editLayanan = $layanan;

        return view('admin.layanan.index', compact('layanans', 'polis', 'editLayanan'));
    }

    /**
     * Perbarui data layanan & tarif di database.
     */
    public function update(UpdateLayananRequest $request, Layanan $layanan): RedirectResponse
    {
        $layanan->update($request->validated());

        return redirect()->route('admin.layanan.index')
            ->with('sukses', 'Data layanan dan tarif berhasil diperbarui.');
    }

    /**
     * Toggle status aktif/nonaktif layanan (soft-delete / status boolean).
     */
    public function toggleStatus(Layanan $layanan): RedirectResponse
    {
        $layanan->update(['status' => ! $layanan->status]);

        $label = $layanan->status ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.layanan.index')
            ->with('sukses', "Layanan {$layanan->nama_layanan} berhasil {$label}.");
    }
}
