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

    public function __construct(DoctorScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Menampilkan daftar jadwal praktik dokter.
     */
    public function index(Request $request)
    {
        $query = DoctorSchedule::with(['doctor.user', 'poli']);

        // Filter poli
        if ($request->filled('poli_id')) {
            $query->where('poli_id', $request->poli_id);
        }

        // Filter dokter
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // Filter hari
        if ($request->filled('hari')) {
            $query->where('hari', strtolower($request->hari));
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', (bool) $request->status);
        }

        $schedules = $query->orderBy('hari')
            ->orderBy('jam_mulai')
            ->paginate(15)
            ->withQueryString();

        $doctors = Doctor::with('user')->get();
        $polis = Poli::where('status', true)->get();
        $daftarHari = DoctorScheduleService::HARI;

        return view('admin.jadwal.index', compact('schedules', 'doctors', 'polis', 'daftarHari'));
    }

    /**
     * Menampilkan form tambah jadwal praktik dokter.
     */
    public function create()
    {
        $doctors = Doctor::with('user')->get();
        $polis = Poli::where('status', true)->get();
        $daftarHari = DoctorScheduleService::HARI;

        return view('admin.jadwal.create', compact('doctors', 'polis', 'daftarHari'));
    }

    /**
     * Menyimpan jadwal praktik dokter baru.
     */
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

    /**
     * Menampilkan form edit jadwal praktik dokter.
     */
    public function edit(DoctorSchedule $doctorSchedule)
    {
        $doctors = Doctor::with('user')->get();
        $polis = Poli::where('status', true)->get();
        $daftarHari = DoctorScheduleService::HARI;

        // Format waktu untuk input form (H:i)
        $doctorSchedule->jam_mulai_formatted = Carbon::parse($doctorSchedule->jam_mulai)->format('H:i');
        $doctorSchedule->jam_selesai_formatted = Carbon::parse($doctorSchedule->jam_selesai)->format('H:i');

        return view('admin.jadwal.edit', compact('doctorSchedule', 'doctors', 'polis', 'daftarHari'));
    }

    /**
     * Memperbarui jadwal praktik dokter.
     */
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

    /**
     * Menonaktifkan atau mengaktifkan jadwal praktik (tidak menghapus permanen).
     */
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

    /**
     * Toggle status aktif / nonaktif jadwal.
     */
    public function toggleStatus(DoctorSchedule $doctorSchedule)
    {
        return $this->destroy($doctorSchedule);
    }
}
