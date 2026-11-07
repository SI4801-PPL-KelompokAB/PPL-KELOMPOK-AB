<?php

namespace App\Http\Controllers;

use App\Exceptions\AturanBisnisException;
use App\Http\Requests\Pasien\StoreQueueRequest;
use App\Models\DoctorSchedule;
use App\Models\Poli;
use App\Models\Queue;
use App\Services\QueueService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    protected QueueService $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * Menampilkan daftar antrean milik pasien yang sedang login.
     */
    public function index(Request $request)
    {
        $profil = $request->user()->patientProfile;

        $queues = $profil
            ? $profil->queues()
                ->with(['doctorSchedule.doctor.user', 'doctorSchedule.poli'])
                ->latest()
                ->paginate(10)
            : collect();

        return view('antrean.index', compact('queues', 'profil'));
    }

    /**
     * Menampilkan form pendaftaran antrean online.
     */
    public function create(Request $request)
    {
        $profil = $request->user()->patientProfile;

        // Tanggal yang dipilih (default hari ini)
        try {
            $tanggal = Carbon::parse($request->query('tanggal', today()))->startOfDay();
        } catch (\Exception $e) {
            $tanggal = today()->startOfDay();
        }

        if ($tanggal->lt(today()->startOfDay())) {
            $tanggal = today()->startOfDay();
        }

        $tanggalString = $tanggal->toDateString();
        $namaHari = QueueService::hariDariTanggal($tanggalString);

        $polis = Poli::where('status', true)->orderBy('nama_poli')->get();
        $poliId = (int) $request->query('poli', $polis->first()?->id);

        if (! $polis->contains('id', $poliId)) {
            $poliId = $polis->first()?->id;
        }

        // Ambil jadwal dokter yang aktif di poli terpilih pada hari tanggal tersebut
        $schedules = DoctorSchedule::with(['doctor.user', 'poli'])
            ->where('status', true)
            ->where('poli_id', $poliId)
            ->where('hari', $namaHari)
            ->orderBy('jam_mulai')
            ->get()
            ->map(function (DoctorSchedule $item) use ($tanggalString) {
                // Hitung kuota yang sudah terpakai
                $terpakai = Queue::where('doctor_schedule_id', $item->id)
                    ->whereDate('tanggal_antrean', $tanggalString)
                    ->count();

                $sisaKuota = max(0, $item->kuota - $terpakai);
                $isPenuh = $sisaKuota === 0;

                return [
                    'schedule' => $item,
                    'terpakai' => $terpakai,
                    'sisa_kuota' => $sisaKuota,
                    'is_penuh' => $isPenuh,
                ];
            });

        // Cek apakah pasien punya antrean aktif
        $antreanAktif = $profil
            ? Queue::where('patient_profile_id', $profil->id)
                ->whereIn('status', Queue::ACTIVE_STATUSES)
                ->first()
            : null;

        return view('antrean.create', compact(
            'profil',
            'polis',
            'poliId',
            'tanggalString',
            'namaHari',
            'schedules',
            'antreanAktif'
        ));
    }

    /**
     * Menyimpan pendaftaran antrean online baru.
     */
    public function store(StoreQueueRequest $request)
    {
        try {
            $queue = $this->queueService->buatAntreanOnline($request->user(), $request->validated());

            return redirect()->route('antrean.show', $queue)
                ->with('success', "Nomor antrean berhasil diambil! Nomor Antrean Anda: {$queue->formatted_nomor}");
        } catch (AturanBisnisException $e) {
            return back()->withInput()->withErrors(['antrean' => $e->getMessage()]);
        }
    }

    /**
     * Menampilkan bukti / tiket nomor antrean pasien.
     */
    public function show(Request $request, Queue $queue)
    {
        $profil = $request->user()->patientProfile;

        // Otorisasi: hanya pasien pemilik antrean atau admin yang boleh melihat tiket
        if (! $request->user()->isAdmin() && (! $profil || $queue->patient_profile_id !== $profil->id)) {
            abort(403, 'Anda tidak berhak melihat tiket antrean ini.');
        }

        $queue->load(['patientProfile.user', 'doctorSchedule.doctor.user', 'doctorSchedule.poli']);

        // Hitung antrean di depan pasien yang masih menunggu
        $antreanDiDepan = Queue::where('doctor_schedule_id', $queue->doctor_schedule_id)
            ->whereDate('tanggal_antrean', $queue->tanggal_antrean)
            ->where('nomor_antrean', '<', $queue->nomor_antrean)
            ->whereIn('status', [Queue::STATUS_WAITING, Queue::STATUS_CALLED])
            ->count();

        return view('antrean.show', compact('queue', 'antreanDiDepan'));
    }
}
