<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePoliRequest;
use App\Http\Requests\Admin\UpdatePoliRequest;
use App\Models\Poli;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PoliController extends Controller
{
    /**
     * Tampilkan halaman utama data poli (Form di kiri, Tabel di kanan sesuai mockup).
     */
    public function index(Request $request): View
    {
        $sort = $request->query('sort', 'created_at');
        $dir = $request->query('dir', 'desc');

        if (! in_array($sort, ['nama_poli', 'kode_poli', 'created_at'], true)) {
            $sort = 'created_at';
        }
        if (! in_array($dir, ['asc', 'desc'], true)) {
            $dir = 'desc';
        }

        $polis = Poli::orderBy($sort, $dir)->get();
        $editPoli = $request->filled('edit') ? Poli::find($request->query('edit')) : null;

        return view('admin.poli.index', compact('polis', 'editPoli'));
    }

    /**
     * Form tambah poli (bisa mengarahkan ke halaman index).
     */
    public function create(): View
    {
        $polis = Poli::latest()->get();
        $editPoli = null;

        return view('admin.poli.index', compact('polis', 'editPoli'));
    }

    /**
     * Simpan poli baru ke database.
     */
    public function store(StorePoliRequest $request): RedirectResponse
    {
        Poli::create($request->validated());

        return redirect()->route('admin.poli.index')
            ->with('sukses', 'Poli berhasil ditambahkan.');
    }

    /**
     * Tampilkan form ubah data poli (menggunakan layout 2 kolom yang sama).
     */
    public function edit(Poli $poli): View
    {
        $polis = Poli::latest()->get();
        $editPoli = $poli;

        return view('admin.poli.index', compact('polis', 'editPoli'));
    }

    /**
     * Perbarui data poli di database.
     */
    public function update(UpdatePoliRequest $request, Poli $poli): RedirectResponse
    {
        $poli->update($request->validated());

        return redirect()->route('admin.poli.index')
            ->with('sukses', 'Poli berhasil diperbarui.');
    }

    /**
     * Toggle status aktif/nonaktif poli (bukan hapus — sesuai standar K17).
     */
    public function toggleStatus(Poli $poli): RedirectResponse
    {
        $poli->update(['status' => ! $poli->status]);

        $label = $poli->status ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.poli.index')
            ->with('sukses', "Poli berhasil {$label}.");
    }
}
