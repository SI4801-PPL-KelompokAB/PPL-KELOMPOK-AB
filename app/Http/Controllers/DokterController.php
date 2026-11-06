<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use Illuminate\Http\Request;

class DokterController extends Controller
{
    public function index()
    {
        $dokters = Dokter::latest()->get();
        return view('dokter.index', compact('dokters'));
    }

    public function create()
    {
        return view('dokter.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'         => 'required|string|max:255',
            'spesialisasi' => 'required|string|max:255',
            'no_telp'      => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:255',
        ]);

        Dokter::create($data);
        return redirect()->route('dokter.index')->with('success', 'Dokter berhasil ditambahkan');
    }

    public function edit(Dokter $dokter)
    {
        return view('dokter.edit', compact('dokter'));
    }

    public function update(Request $request, Dokter $dokter)
    {
        $data = $request->validate([
            'nama'         => 'required|string|max:255',
            'spesialisasi' => 'required|string|max:255',
            'no_telp'      => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:255',
        ]);

        $dokter->update($data);
        return redirect()->route('dokter.index')->with('success', 'Data dokter berhasil diubah');
    }

    public function destroy(Dokter $dokter)
    {
        $dokter->delete();
        return redirect()->route('dokter.index')->with('success', 'Dokter berhasil dihapus');
    }
}