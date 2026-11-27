<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\DoctorSchedule;
use App\Models\Queue;
use Carbon\Carbon;

class PatientDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $profil = $user->patientProfile;

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

        // Antrean aktif pasien saat ini
        $antreanAktif = null;
        if ($profil) {
            $antreanAktif = Queue::with(['doctorSchedule.doctor.user', 'doctorSchedule.poli'])
                ->where('patient_profile_id', $profil->id)
                ->whereIn('status', Queue::ACTIVE_STATUSES)
                ->whereDate('tanggal_antrean', '>=', Carbon::today())
                ->orderBy('tanggal_antrean')
                ->first();
        }

        // Riwayat antrean pasien (5 terakhir)
        $riwayatAntrean = collect();
        if ($profil) {
            $riwayatAntrean = Queue::with(['doctorSchedule.doctor.user', 'doctorSchedule.poli'])
                ->where('patient_profile_id', $profil->id)
                ->orderByDesc('tanggal_antrean')
                ->orderByDesc('id')
                ->take(5)
                ->get();
        }

        // Jadwal dokter praktik hari ini untuk informasi pasien
        $jadwalHariIni = DoctorSchedule::with(['doctor.user', 'poli'])
            ->where('hari', $hariIni)
            ->where('status', true)
            ->orderBy('jam_mulai')
            ->get();

        return view('pasien.dashboard', compact(
            'user',
            'profil',
            'antreanAktif',
            'riwayatAntrean',
            'jadwalHariIni',
            'hariIni'
        ));
    }
}
