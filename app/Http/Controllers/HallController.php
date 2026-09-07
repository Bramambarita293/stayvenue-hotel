<?php

namespace App\Http\Controllers;

use App\Models\Hall;
use App\Models\HallSession;

class HallController extends Controller
{
    /**
     * Tampilkan seluruh gedung / ballroom yang aktif.
     */
    public function index()
    {
        $halls = Hall::where('is_active', true)->get();
        return view('halls.index', compact('halls'));
    }

    /**
     * Detail gedung aktif. Sesi bersifat global (tidak ada hall_id),
     * jadi tampilkan terurut; ketersediaan dicek saat checkout.
     */
    public function show(int $id)
    {
        $hall = Hall::where('is_active', true)->findOrFail($id);
        $sessions = HallSession::orderBy('start_time')->get();

        return view('halls.show', compact('hall', 'sessions'));
    }
}