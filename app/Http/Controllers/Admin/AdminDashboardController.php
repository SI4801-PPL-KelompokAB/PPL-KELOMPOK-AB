<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\KlinikCabang;
use App\Models\Layanan;
use App\Models\Poli;
use App\Models\Queue;
use App\Models\User;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $todayEnglish = strtolower(Carbon::today()->format('l'));
        $dayMap = [
            'monday'    => 'senin',
            'tuesday'   => 'selasa',
            'wednesday' => 'rabu',
            'thursday'  => 'kamis',
            'friday'    => 'jumat',
            'saturday'  => 'sabtu',
            'sunday'    => 'minggu',
        ];
        $hariIni = $dayMap[$todayEnglish] ?? 'senin';

        $totalCabang = KlinikCabang::count();
        $totalLayanan = Layanan::count();
        $totalPoli = Poli::where('status', true)->count();
        $totalDokter = Doctor::count();
        $totalJadwal = DoctorSchedule::where('status', true)->count();
        $totalPasien = User::where('role', 'pasien')->count();
        $antreanHariIni = Queue::whereDate('tanggal_antrean', Carbon::today())->count();

        // Jadwal praktik untuk hari ini
        $jadwalHariIni = DoctorSchedule::with(['doctor.user', 'poli'])
            ->where('hari', $hariIni)
            ->where('status', true)
            ->orderBy('jam_mulai')
            ->get();

        // Daftar poli terbaru
        $polis = Poli::orderBy('nama_poli')->take(6)->get();

        return view('admin.dashboard', compact(
            'totalCabang',
            'totalLayanan',
            'totalPoli',
            'totalDokter',
            'totalJadwal',
            'totalPasien',
            'antreanHariIni',
            'jadwalHariIni',
            'polis',
            'hariIni'
        ));
    }
}
