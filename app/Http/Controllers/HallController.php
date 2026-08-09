<?php

namespace App\Http\Controllers;

use App\Models\Hall;

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
}