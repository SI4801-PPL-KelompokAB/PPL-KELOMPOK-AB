<?php

namespace App\Http\Controllers;

use App\Models\DoctorSchedule;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
{
    $role = Auth::user()->role;

    return match ($role) {
        'admin'   => $this->admin(),
        'petugas' => $this->petugas(),
        // Dokter dipakai untuk konsultasi online
        'dokter'  => redirect()->route('dokter.konsultasi.index'),
        // TODO: ganti ke redirect()->route('...home pasien...') kalau halaman home sudah ada
        'pasien'  => view('patient.dashboard'),
        default   => abort(403, 'Role tidak dikenali.'),
    };
}

    private function admin()
    {
        $today = today();

        // Jumlah antrean hari ini per status
        $statusCounts = Queue::today()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'pasien'           => User::where('role', 'pasien')->count(),
            'dokter'           => User::where('role', 'dokter')->count(),
            'petugas'          => User::where('role', 'petugas')->count(),
            'antrean_hari_ini' => $statusCounts->sum(),
        ];

        // Antrean hari ini per poli (dan cabang)
        $perPoli = DB::table('queues')
            ->join('doctor_schedules', 'queues.doctor_schedule_id', '=', 'doctor_schedules.id')
            ->join('polis', 'doctor_schedules.poli_id', '=', 'polis.id')
            ->join('klinik_cabang', 'polis.klinik_cabang_id', '=', 'klinik_cabang.id')
            ->whereDate('queues.tanggal_antrean', $today)
            ->selectRaw('polis.nama_poli, klinik_cabang.nama_cabang, count(*) as total')
            ->groupBy('polis.id', 'polis.nama_poli', 'klinik_cabang.nama_cabang')
            ->orderByDesc('total')
            ->get();

        // Kunjungan 7 hari terakhir
        $start = $today->copy()->subDays(6);
        $perHari = DB::table('queues')
            ->whereBetween('tanggal_antrean', [$start->toDateString(), $today->toDateString()])
            ->selectRaw('tanggal_antrean, count(*) as total')
            ->groupBy('tanggal_antrean')
            ->pluck('total', 'tanggal_antrean');

        $chart = [];
        for ($i = 0; $i < 7; $i++) {
            $d = $start->copy()->addDays($i);
            $chart[] = [
                'label' => $d->format('d/m'),
                'total' => (int) ($perHari[$d->toDateString()] ?? 0),
            ];
        }

        $latestQueues = Queue::with([
                'patientProfile.user',
                'doctorSchedule.doctor.user',
                'doctorSchedule.poli.klinikCabang',
            ])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'statusCounts', 'perPoli', 'chart', 'latestQueues'
        ));
    }

    private function petugas()
    {
        $statusCounts = Queue::today()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Jadwal dokter yang punya antrean hari ini
        $schedules = DoctorSchedule::with([
                'doctor.user',
                'poli.klinikCabang',
                'queues' => fn ($q) => $q->today()
                    ->with('patientProfile.user')
                    ->orderBy('nomor_antrean'),
            ])
            ->whereHas('queues', fn ($q) => $q->today())
            ->get();

        return view('petugas.dashboard', compact('statusCounts', 'schedules'));
    }
}