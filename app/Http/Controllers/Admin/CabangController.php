<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCabangRequest;
use App\Http\Requests\Admin\UpdateCabangRequest;
use App\Models\KlinikCabang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CabangController extends Controller
{
    /**
     * Tampilkan halaman utama data cabang klinik (Form di kiri, Tabel di kanan sesuai mockup).
     */
    public function index(Request $request): View
    {
        $sort = $request->query('sort', 'created_at');
        $dir = $request->query('dir', 'desc');

        if (! in_array($sort, ['nama_cabang', 'created_at'], true)) {
            $sort = 'created_at';
        }
        if (! in_array($dir, ['asc', 'desc'], true)) {
            $dir = 'desc';
        }

        $cabangs = KlinikCabang::withCount('polis')->orderBy($sort, $dir)->get();
        $editCabang = $request->filled('edit') ? KlinikCabang::find($request->query('edit')) : null;

        return view('admin.cabang.index', compact('cabangs', 'editCabang'));
    }

    /**
     * Form tambah cabang (mengarahkan ke index dengan form tambah).
     */
    public function create(): View
    {
        $cabangs = KlinikCabang::withCount('polis')->latest()->get();
        $editCabang = null;

        return view('admin.cabang.index', compact('cabangs', 'editCabang'));
    }

    /**
     * Simpan data cabang baru.
     */
    public function store(StoreCabangRequest $request): RedirectResponse
    {
        KlinikCabang::create($request->validated());

        return redirect()->route('admin.cabang.index')
            ->with('sukses', 'Cabang klinik baru berhasil ditambahkan.');
    }

    /**
     * Tampilkan form ubah data cabang.
     */
    public function edit(KlinikCabang $cabang): View
    {
        $cabangs = KlinikCabang::withCount('polis')->latest()->get();
        $editCabang = $cabang;

        return view('admin.cabang.index', compact('cabangs', 'editCabang'));
    }

    /**
     * Perbarui data cabang klinik.
     */
    public function update(UpdateCabangRequest $request, KlinikCabang $cabang): RedirectResponse
    {
        $cabang->update($request->validated());

        return redirect()->route('admin.cabang.index')
            ->with('sukses', 'Data cabang klinik berhasil diperbarui.');
    }

    /**
     * Toggle status aktif/nonaktif cabang klinik (soft toggle sesuai standar).
     */
    public function toggleStatus(KlinikCabang $cabang): RedirectResponse
    {
        $cabang->update(['status' => ! $cabang->status]);

        $label = $cabang->status ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.cabang.index')
            ->with('sukses', "Cabang klinik {$cabang->nama_cabang} berhasil {$label}.");
    }
}
