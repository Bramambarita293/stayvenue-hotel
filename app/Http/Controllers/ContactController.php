<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Halaman Contact & Lokasi.
     */
    public function index()
    {
        return view('contact.index');
    }

    /**
     * Simpan pesan tamu dari form contact.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\-\s()]*$/'],
            'subject' => ['nullable', 'string', 'max:150', 'in:Reservasi kamar,Sewa gedung / acara,Kerjasama & event,Feedback,Lainnya'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'required' => ':attribute wajib diisi.',
            'email' => 'Format email tidak valid.',
            'min' => [
                'string' => ':attribute minimal :min karakter.',
            ],
        ]);

        ContactMessage::create($validated);

        return back()
            ->with('success', 'Terima kasih! Pesan Anda sudah kami terima dan akan dibalas segera oleh tim kami.');
    }
}
