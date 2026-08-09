<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Tampilkan seluruh katalog tipe kamar
     */
    public function index()
    {
        $rooms = RoomType::all();
        return view('rooms.index', compact('rooms'));
    }

    /**
     * Tampilkan detail tipe kamar spesifik
     */
    public function show($id)
    {
        $roomType = RoomType::findOrFail($id);
        return view('rooms.show', compact('roomType'));
    }
}