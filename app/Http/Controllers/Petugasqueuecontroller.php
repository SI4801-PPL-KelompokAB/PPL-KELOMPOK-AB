<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PetugasQueueController extends Controller
{
    // aksi => [status asal yang boleh, status tujuan]
    private const TRANSITIONS = [
        'call'     => [['waiting'], 'called'],
        'serve'    => [['called'], 'serving'],
        'complete' => [['serving'], 'completed'],
        'skip'     => [['waiting', 'called'], 'skipped'],
    ];

    public function updateStatus(Request $request, Queue $queue)
    {
        abort_unless(in_array(Auth::user()->role, ['petugas', 'admin']), 403);

        $data = $request->validate([
            'action' => 'required|in:call,serve,complete,skip',
        ]);

        [$from, $to] = self::TRANSITIONS[$data['action']];

        if (! in_array($queue->status, $from)) {
            return back()->with('error', 'Status antrean tidak bisa diubah dari ' . $queue->status . '.');
        }

        $queue->update(['status' => $to]);

        return back()->with('success', 'Antrean nomor ' . $queue->nomor_antrean . ' diubah menjadi ' . $to . '.');
    }
}