<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKonsultasiOnlineRequest;
use App\Models\Consultation;
use App\Models\DoctorSchedule;
use App\Models\PatientProfile;
use App\Models\Poli;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class KonsultasiOnlineController extends Controller
{
    /** Nilai enum doctor_schedules.hari, urut dayOfWeekIso (1 = Senin). */
    private const HARI = [1 => 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

    public function create(Request $request)
    {
        $profil = $this->profilPasien();

        try {
            $tanggal = Carbon::parse($request->query('tanggal', today()))->startOfDay();
        } catch (\Exception $e) {
            $tanggal = today()->startOfDay();
        }

        if ($tanggal->lt(today()->startOfDay())) {
            $tanggal = today()->startOfDay();
        }

        $polis = Poli::where('status', true)->orderBy('nama_poli')->get();

        $poliId = (int) $request->query('poli', $polis->first()?->id);

        if (! $polis->contains('id', $poliId)) {
            $poliId = $polis->first()?->id;
        }

        // Jadwal dokter di poli terpilih pada hari tersebut
        $jadwals = $this->queryJadwalAktif($tanggal)
            ->where('poli_id', $poliId)
            ->orderBy('jam_mulai')
            ->get()
            ->map(function (DoctorSchedule $jadwal) use ($tanggal) {
                $terpakai = $this->queryKuotaTerpakai($jadwal->id, $tanggal)->count();

                $jadwal->kuota_maksimal = $jadwal->kuota;
                $jadwal->kuota_terpakai = $terpakai;
                $jadwal->sisa_kuota = max($jadwal->kuota - $terpakai, 0);
                $jadwal->sudah_tutup = $tanggal->isToday()
                    && now()->format('H:i:s') > $jadwal->jam_selesai;

                return $jadwal;
            });

        $dokters = $jadwals->pluck('doctor')->unique('id')->values();

        $aktif = Consultation::where('patient_profile_id', $profil->id)
            ->whereIn('status', Consultation::STATUS_AKTIF)
            ->count();

        return view('konsultasi.create', [
            'polis' => $polis,
            'poliId' => $poliId,
            'dokters' => $dokters,
            'jadwals' => $jadwals,
            'tanggal' => $tanggal->toDateString(),
            'aktif' => $aktif,
        ]);
    }

    public function store(StoreKonsultasiOnlineRequest $request)
    {
        $profil = $this->profilPasien();

        $tanggal = Carbon::parse($request->validated('tanggal_konsultasi'))->startOfDay();

        if ($tanggal->lt(today()->startOfDay())) {
            $this->tolak('tanggal_konsultasi', 'Tanggal konsultasi tidak boleh sebelum hari ini.');
        }

        $konsultasi = DB::transaction(function () use ($request, $tanggal, $profil) {
            // Kunci jadwal agar dua pasien tidak mengambil kuota terakhir bersamaan
            $jadwal = $this->queryJadwalAktif($tanggal)
                ->whereKey($request->validated('doctor_schedule_id'))
                ->lockForUpdate()
                ->first();

            if (! $jadwal) {
                $this->tolak('doctor_schedule_id', 'Dokter tidak praktik pada tanggal yang dipilih.');
            }

            if ($tanggal->isToday() && now()->format('H:i:s') > $jadwal->jam_selesai) {
                $this->tolak('tanggal_konsultasi', 'Jadwal dokter untuk hari ini sudah berakhir.');
            }

            $konsultasiAktif = $this->queryKuotaTerpakai($jadwal->id, $tanggal);

            if ((clone $konsultasiAktif)->count() >= $jadwal->kuota) {
                $this->tolak('doctor_schedule_id', 'Kuota konsultasi untuk jadwal ini sudah penuh.');
            }

            $ganda = (clone $konsultasiAktif)
                ->where('patient_profile_id', $profil->id)
                ->exists();

            if ($ganda) {
                $this->tolak('doctor_schedule_id', 'Kamu sudah memiliki konsultasi pada jadwal dan tanggal tersebut.');
            }

            // Nomor konsultasi: KON-YYYYMMDD-XXXX
            $prefix = 'KON-' . $tanggal->format('Ymd') . '-';

            $nomorUrut = Consultation::where('nomor_konsultasi', 'like', $prefix . '%')
                ->lockForUpdate()
                ->count() + 1;

            $keluhan = $request->validated('keluhan');

            return Consultation::create([
                'nomor_konsultasi' => $prefix . str_pad($nomorUrut, 4, '0', STR_PAD_LEFT),
                'patient_profile_id' => $profil->id,
                'doctor_id' => $jadwal->doctor_id,
                'doctor_schedule_id' => $jadwal->id,
                'tanggal_konsultasi' => $tanggal->toDateString(),
                'subject' => Str::limit($keluhan, 100),
                'keluhan' => $keluhan,
                'status' => 'pending',
            ]);
        });

        return redirect()
            ->route('konsultasi.show', $konsultasi)
            ->with('success', 'Konsultasi berhasil dibuat.');
    }

    public function show(Consultation $consultation)
    {
        $profil = $this->profilPasien();

        abort_unless((int) $consultation->patient_profile_id === (int) $profil->id, 403);

        $consultation->load('schedule.poli', 'doctor.user');

        $jadwal = $consultation->schedule;

        $sama = $this->queryKuotaTerpakai($consultation->doctor_schedule_id, $consultation->tanggal_konsultasi);

        $antreanDiDepan = (clone $sama)
            ->where('status', 'pending')
            ->where('id', '<', $consultation->id)
            ->count();

        $sedangDilayani = (clone $sama)
            ->where('status', 'ongoing')
            ->orderBy('id')
            ->value('nomor_konsultasi');

        $kuotaMaksimal = $jadwal?->kuota ?? 0;
        $sisaKuota = max($kuotaMaksimal - (clone $sama)->count(), 0);

        return view('konsultasi.success', [
            'konsultasi' => $consultation,
            'jadwal' => $jadwal,
            'antreanDiDepan' => $antreanDiDepan,
            'sedangDilayani' => $sedangDilayani,
            'kuotaMaksimal' => $kuotaMaksimal,
            'sisaKuota' => $sisaKuota,
        ]);
    }

    /** Jadwal aktif di hari dari tanggal, dengan poli dan dokter yang aktif. */
    private function queryJadwalAktif(Carbon $tanggal)
    {
        return DoctorSchedule::with('poli', 'doctor.user')
            ->where('status', true)
            ->where('hari', self::HARI[$tanggal->dayOfWeekIso])
            ->whereHas('poli', fn ($q) => $q->where('status', true))
            ->whereHas('doctor.user', fn ($q) => $q->where('is_active', true));
    }

    /** Konsultasi yang memakai kuota pada satu jadwal dan tanggal. */
    private function queryKuotaTerpakai(?int $jadwalId, Carbon $tanggal)
    {
        return Consultation::where('doctor_schedule_id', $jadwalId)
            ->whereDate('tanggal_konsultasi', $tanggal->toDateString())
            ->where('status', '!=', 'rejected');
    }

    private function tolak(string $field, string $pesan): never
    {
        throw ValidationException::withMessages([$field => $pesan]);
    }

    /** Hanya pasien yang sudah punya profil pasien. */
    private function profilPasien(): PatientProfile
    {
        $user = auth()->user();

        abort_unless($user?->role === 'pasien', 403);
        abort_unless($user->patientProfile, 403, 'Lengkapi profil pasien terlebih dahulu.');

        return $user->patientProfile;
    }
}