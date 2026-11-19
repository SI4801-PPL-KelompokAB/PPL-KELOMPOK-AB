<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\JadwalDokter;
use Illuminate\Http\Request;

class JadwalDokterController extends Controller
{
 public function index()
{
    $dokters = Dokter::with('jadwal')->orderBy('nama')->get();
    $poliList = $dokters->pluck('spesialisasi')->unique()->sort()->values();

    return view('jadwal.index', compact('dokters', 'poliList'));
}

    public function store(Request $request)
    {
        $data = $request->validate([
            'dokter_id'   => 'required|exists:dokters,id',
            'hari'        => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
        ]);

        JadwalDokter::create($data);
        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil ditambahkan');
    }
}