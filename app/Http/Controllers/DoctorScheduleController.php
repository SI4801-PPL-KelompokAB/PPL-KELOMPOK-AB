<?php

namespace App\Http\Controllers;

use App\Exceptions\AturanBisnisException;
use App\Http\Requests\Admin\StoreDoctorScheduleRequest;
use App\Http\Requests\Admin\UpdateDoctorScheduleRequest;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Poli;
use App\Services\DoctorScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DoctorScheduleController extends Controller
{
    protected DoctorScheduleService $scheduleService;

    // hubungkan controller dengan service logika jadwal dokter
    public function __construct(DoctorScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    // tampilkan halaman daftar jadwal praktik dokter beserta filter pencarian
    public function index(Request $request)
    {
        $query = DoctorSchedule::with(['doctor.user', 'poli']);

        // saring berdasarkan poli jika dipilih
        if ($request->filled('poli_id')) {
            $query->where('poli_id', $request->poli_id);
        }

        // saring berdasarkan dokter jika dipilih
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // saring berdasarkan hari jika dipilih
        if ($request->filled('hari')) {
            $query->where('hari', strtolower($request->hari));
        }

        // saring berdasarkan status aktif atau nonaktif jika dipilih
        if ($request->filled('status')) {
            $query->where('status', (bool) $request->status);
        }

        // urutkan berdasarkan hari dan jam mulai, lalu bagi 15 data per halaman
        $schedules = $query->orderBy('hari')
            ->orderBy('jam_mulai')
            ->paginate(15)
            ->withQueryString();

        $doctors = Doctor::with('user')->get();
        $polis = Poli::where('status', true)->get();
        $daftarHari = DoctorScheduleService::HARI;

        return view('admin.jadwal.index', compact('schedules', 'doctors', 'polis', 'daftarHari'));
    }

    // tampilkan halaman form tambah jadwal praktik baru
    public function create()
    {
        $doctors = Doctor::with('user')->get();
        $polis = Poli::where('status', true)->get();
        $daftarHari = DoctorScheduleService::HARI;

        return view('admin.jadwal.create', compact('doctors', 'polis', 'daftarHari'));
    }

    // simpan jadwal baru ke database lewat service (kalau jam bentrok, tangkap error-nya)
    public function store(StoreDoctorScheduleRequest $request)
    {
        try {
            $this->scheduleService->buatJadwal($request->validated());

            return redirect()->route('admin.jadwal.index')
                ->with('success', 'Jadwal praktik dokter berhasil ditambahkan.');
        } catch (AturanBisnisException $e) {
            return back()->withInput()->withErrors(['jadwal' => $e->getMessage()]);
        }
    }

    // tampilkan halaman form edit jadwal praktik yang dipilih
    public function edit(DoctorSchedule $doctorSchedule)
    {
        $doctors = Doctor::with('user')->get();
        $polis = Poli::where('status', true)->get();
        $daftarHari = DoctorScheduleService::HARI;

        // ubah format jam dari 08:00:00 jadi 08:00 supaya terbaca di input form html
        $doctorSchedule->jam_mulai_formatted = Carbon::parse($doctorSchedule->jam_mulai)->format('H:i');
        $doctorSchedule->jam_selesai_formatted = Carbon::parse($doctorSchedule->jam_selesai)->format('H:i');

        return view('admin.jadwal.edit', compact('doctorSchedule', 'doctors', 'polis', 'daftarHari'));
    }

    // simpan perubahan edit jadwal ke database lewat service
    public function update(UpdateDoctorScheduleRequest $request, DoctorSchedule $doctorSchedule)
    {
        try {
            $this->scheduleService->perbaruiJadwal($doctorSchedule, $request->validated());

            return redirect()->route('admin.jadwal.index')
                ->with('success', 'Jadwal praktik dokter berhasil diperbarui.');
        } catch (AturanBisnisException $e) {
            return back()->withInput()->withErrors(['jadwal' => $e->getMessage()]);
        }
    }

    // ubah status jadwal jadi aktif atau nonaktif tanpa menghapus datanya dari database
    public function destroy(DoctorSchedule $doctorSchedule)
    {
        try {
            $this->scheduleService->toggleStatus($doctorSchedule);

            $pesan = $doctorSchedule->status
                ? 'Jadwal praktik berhasil diaktifkan kembali.'
                : 'Jadwal praktik berhasil dinonaktifkan.';

            return redirect()->route('admin.jadwal.index')
                ->with('success', $pesan);
        } catch (AturanBisnisException $e) {
            return back()->withErrors(['jadwal' => $e->getMessage()]);
        }
    }

    // panggil fungsi ubah status aktif atau nonaktif jadwal
    public function toggleStatus(DoctorSchedule $doctorSchedule)
    {
        return $this->destroy($doctorSchedule);
    }
}
