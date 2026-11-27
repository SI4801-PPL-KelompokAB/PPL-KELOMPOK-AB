<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Poli;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminQueueController extends Controller
{
    /**
     * Menampilkan seluruh daftar antrean pasien untuk dikelola Admin.
     */
    public function index(Request $request)
    {
        $tanggal = $request->query('tanggal', today()->toDateString());
        $poliId = $request->query('poli');
        $status = $request->query('status');

        $query = Queue::with([
            'patientProfile.user',
            'doctorSchedule.doctor.user',
            'doctorSchedule.poli',
        ]);

        if ($tanggal) {
            $query->whereDate('tanggal_antrean', Carbon::parse($tanggal)->toDateString());
        }

        if ($poliId) {
            $query->whereHas('doctorSchedule', function ($q) use ($poliId) {
                $q->where('poli_id', $poliId);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $queues = $query->orderBy('tanggal_antrean', 'desc')
            ->orderBy('doctor_schedule_id', 'asc')
            ->orderBy('nomor_antrean', 'asc')
            ->paginate(15)
            ->withQueryString();

        $polis = Poli::orderBy('nama_poli')->get();

        // Ringkasan jumlah per status pada tanggal yang dipilih
        $baseStatsQuery = Queue::when($tanggal, function ($q) use ($tanggal) {
            $q->whereDate('tanggal_antrean', Carbon::parse($tanggal)->toDateString());
        });

        $stats = [
            'waiting'   => (clone $baseStatsQuery)->where('status', Queue::STATUS_WAITING)->count(),
            'called'    => (clone $baseStatsQuery)->where('status', Queue::STATUS_CALLED)->count(),
            'serving'   => (clone $baseStatsQuery)->where('status', Queue::STATUS_SERVING)->count(),
            'completed' => (clone $baseStatsQuery)->where('status', Queue::STATUS_COMPLETED)->count(),
        ];

        return view('admin.antrean.index', compact('queues', 'polis', 'tanggal', 'poliId', 'status', 'stats'));
    }

    /**
     * Mengubah status antrean pasien (Waiting -> Called/Serving -> Completed / Skipped).
     */
    public function updateStatus(Request $request, Queue $queue)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', [
                Queue::STATUS_WAITING,
                Queue::STATUS_CALLED,
                Queue::STATUS_SERVING,
                Queue::STATUS_COMPLETED,
                Queue::STATUS_SKIPPED,
            ]),
        ]);

        $queue->update([
            'status' => $validated['status'],
        ]);

        return back()->with(
            'success',
            "Status antrean #{$queue->formatted_nomor} ({$queue->patientProfile->user->name}) berhasil diubah menjadi {$queue->status_label}."
        );
    }
}