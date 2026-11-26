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

// =========================================================================
// controller antrean online pasien:
// - pbi 09 (spk-4): index, create, store, show (sprint 1)
// - pbi 16 (spk-44): rescheduleform, reschedule, cancel (sprint 2)
// =========================================================================
class QueueController extends Controller
{
    protected QueueService $queueService;

    // hubungkan controller dengan service logika antrean online
    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    // =====================================================================
    // [pbi 09 - spk-4] tampilkan halaman daftar riwayat antrean milik pasien
    // =====================================================================
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

    // =====================================================================
    // [pbi 09 - spk-4] tampilkan halaman pilih poli, tanggal, dan jadwal
    // =====================================================================
    public function create(Request $request)
    {
        $profil = $request->user()->patientProfile;

        // baca tanggal yang dipilih pasien (kalau kosong, pakai tanggal hari ini)
        try {
            $tanggal = Carbon::parse($request->query('tanggal', today()))->startOfDay();
        } catch (\Exception $e) {
            $tanggal = today()->startOfDay();
        }

        // cegah tanggal masa lalu: kalau tanggalnya sudah lewat, otomatis kembalikan ke hari ini
        if ($tanggal->lt(today()->startOfDay())) {
            $tanggal = today()->startOfDay();
        }

        $tanggalString = $tanggal->toDateString();
        // cari tahu nama hari dari tanggal tersebut (misal: senin, selasa, sabtu)
        $namaHari = QueueService::hariDariTanggal($tanggalString);

        // ambil daftar poli yang aktif
        $polis = Poli::where('status', true)->orderBy('nama_poli')->get();
        $poliId = (int) $request->query('poli', $polis->first()?->id);

        if (! $polis->contains('id', $poliId)) {
            $poliId = $polis->first()?->id;
        }

        // cari jadwal dokter yang aktif di poli dan hari tersebut, lalu hitung sisa kuotanya
        $schedules = DoctorSchedule::with(['doctor.user', 'poli'])
            ->where('status', true)
            ->where('poli_id', $poliId)
            ->where('hari', $namaHari)
            ->orderBy('jam_mulai')
            ->get()
            ->map(function (DoctorSchedule $item) use ($tanggalString) {
                // hitung jumlah pasien aktif yang sudah ambil antrean di jadwal dan tanggal ini
                $terpakai = Queue::where('doctor_schedule_id', $item->id)
                    ->whereDate('tanggal_antrean', $tanggalString)
                    ->where('status', '!=', Queue::STATUS_SKIPPED)
                    ->count();

                // sisa kuota = kuota maksimal dikurangi yang sudah terpakai
                $sisaKuota = max(0, $item->kuota - $terpakai);
                $isPenuh = $sisaKuota === 0;

                return [
                    'schedule' => $item,
                    'terpakai' => $terpakai,
                    'sisa_kuota' => $sisaKuota,
                    'is_penuh' => $isPenuh,
                ];
            });

        // cek apakah pasien masih punya antrean aktif yang belum selesai (cegah antrean ganda)
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

    // =====================================================================
    // [pbi 09 - spk-4] proses simpan pendaftaran antrean baru
    // =====================================================================
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

    // =====================================================================
    // [pbi 09 - spk-4] tampilkan halaman tiket nomor antrean pasien
    // =====================================================================
    public function show(Request $request, Queue $queue)
    {
        $this->pastikanPemilikAntrean($request, $queue);

        $queue->load(['patientProfile.user', 'doctorSchedule.doctor.user', 'doctorSchedule.poli']);

        // hitung berapa orang antrean di depan pasien ini yang masih menunggu
        $antreanDiDepan = Queue::where('doctor_schedule_id', $queue->doctor_schedule_id)
            ->whereDate('tanggal_antrean', $queue->tanggal_antrean)
            ->where('nomor_antrean', '<', $queue->nomor_antrean)
            ->whereIn('status', [Queue::STATUS_WAITING, Queue::STATUS_CALLED])
            ->count();

        return view('antrean.show', compact('queue', 'antreanDiDepan'));
    }

    // =====================================================================
    // [pbi 16 - spk-44] tampilkan halaman form reschedule antrean pasien
    // =====================================================================
    public function rescheduleForm(Request $request, Queue $queue)
    {
        $this->pastikanPemilikAntrean($request, $queue);

        $queue->load(['doctorSchedule.doctor.user', 'doctorSchedule.poli']);

        // baca tanggal tujuan yang dipilih (default: tanggal antrean saat ini atau hari ini)
        $defaultTanggal = $queue->tanggal_antrean->gte(today())
            ? $queue->tanggal_antrean->toDateString()
            : today()->toDateString();

        try {
            $tanggal = Carbon::parse($request->query('tanggal', $defaultTanggal))->startOfDay();
        } catch (\Exception $e) {
            $tanggal = today()->startOfDay();
        }

        if ($tanggal->lt(today()->startOfDay())) {
            $tanggal = today()->startOfDay();
        }

        $tanggalString = $tanggal->toDateString();
        $namaHari = QueueService::hariDariTanggal($tanggalString);

        $polis = Poli::where('status', true)->orderBy('nama_poli')->get();
        $poliId = (int) $request->query('poli', $queue->doctorSchedule->poli_id ?? $polis->first()?->id);

        if (! $polis->contains('id', $poliId)) {
            $poliId = $polis->first()?->id;
        }

        // ambil daftar jadwal dokter aktif pada poli dan hari tujuan
        $schedules = DoctorSchedule::with(['doctor.user', 'poli'])
            ->where('status', true)
            ->where('poli_id', $poliId)
            ->where('hari', $namaHari)
            ->orderBy('jam_mulai')
            ->get()
            ->map(function (DoctorSchedule $item) use ($tanggalString, $queue) {
                $terpakai = Queue::where('doctor_schedule_id', $item->id)
                    ->whereDate('tanggal_antrean', $tanggalString)
                    ->where('status', '!=', Queue::STATUS_SKIPPED)
                    ->count();

                $sisaKuota = max(0, $item->kuota - $terpakai);
                $isPenuh = $sisaKuota === 0;
                $isJadwalSaatIni = (int) $item->id === (int) $queue->doctor_schedule_id
                    && $queue->tanggal_antrean->toDateString() === $tanggalString;

                return [
                    'schedule' => $item,
                    'terpakai' => $terpakai,
                    'sisa_kuota' => $sisaKuota,
                    'is_penuh' => $isPenuh,
                    'is_jadwal_saat_ini' => $isJadwalSaatIni,
                ];
            });

        return view('antrean.reschedule', compact(
            'queue',
            'polis',
            'poliId',
            'tanggalString',
            'namaHari',
            'schedules'
        ));
    }

    // =====================================================================
    // [pbi 16 - spk-44] simpan perubahan penjadwalan ulang (reschedule)
    // =====================================================================
    public function reschedule(StoreQueueRequest $request, Queue $queue)
    {
        $this->pastikanPemilikAntrean($request, $queue);

        try {
            $updatedQueue = $this->queueService->rescheduleAntrean($queue, $request->validated());

            return redirect()->route('antrean.show', $updatedQueue)
                ->with('success', "Jadwal antrean berhasil diubah! Nomor Antrean Baru Anda: {$updatedQueue->formatted_nomor}");
        } catch (AturanBisnisException $e) {
            return back()->withInput()->withErrors(['antrean' => $e->getMessage()]);
        }
    }

    // =====================================================================
    // [pbi 16 - spk-44] proses pembatalan antrean oleh pasien
    // =====================================================================
    public function cancel(Request $request, Queue $queue)
    {
        $this->pastikanPemilikAntrean($request, $queue);

        try {
            $this->queueService->batalkanAntrean($queue);

            return redirect()->route('antrean.index')
                ->with('success', 'Antrean Anda berhasil dibatalkan.');
        } catch (AturanBisnisException $e) {
            return back()->withErrors(['antrean' => $e->getMessage()]);
        }
    }

    // fungsi bantu keamanan: pastikan hanya pemilik antrean atau admin yang boleh mengakses
    protected function pastikanPemilikAntrean(Request $request, Queue $queue): void
    {
        $profil = $request->user()->patientProfile;

        if (! $request->user()->isAdmin() && (! $profil || $queue->patient_profile_id !== $profil->id)) {
            abort(403, 'Anda tidak berhak mengakses tiket antrean ini.');
        }
    }
}
