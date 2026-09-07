<?php

namespace Database\Seeders;

use App\Models\EventPackage;
use App\Models\Hall;
use App\Models\HallSession;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // --- Pengguna ---
        $admin = User::factory()->admin()->create([
            'name' => 'Admin StayVenue',
            'email' => 'admin@stayvenue.test',
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // --- Tipe kamar + unit fisik ---
        $deluxe = RoomType::firstOrCreate(
            ['slug' => 'deluxe-double'],
            [
                'name' => 'Deluxe Double',
                'description' => 'Kamar deluxe dengan tempat tidur double dan pemandangan kota.',
                'base_price' => 750000,
                'max_guests' => 2,
            ]
        );

        $suite = RoomType::firstOrCreate(
            ['slug' => 'executive-suite'],
            [
                'name' => 'Executive Suite',
                'description' => 'Suite luas dengan ruang tamu terpisah untuk tamu bisnis.',
                'base_price' => 1500000,
                'max_guests' => 4,
            ]
        );

        foreach (['201', '202', '203', '204'] as $number) {
            Room::firstOrCreate(['room_number' => $number], [
                'room_type_id' => $deluxe->id,
                'status' => 'AVAILABLE',
            ]);
        }

        foreach (['301', '302'] as $number) {
            Room::firstOrCreate(['room_number' => $number], [
                'room_type_id' => $suite->id,
                'status' => 'AVAILABLE',
            ]);
        }

        // --- Gedung / ballroom + sesi ---
        $ballroom = Hall::firstOrCreate(
            ['name' => 'Grand Ballroom'],
            [
                'capacity_pax' => 1000,
                'base_rental_price' => 25000000,
                'description' => 'Ballroom utama untuk wedding dan konferensi skala besar.',
                'is_active' => true,
            ]
        );

        $meetingHall = Hall::firstOrCreate(
            ['name' => 'Sapphire Meeting Room'],
            [
                'capacity_pax' => 150,
                'base_rental_price' => 5000000,
                'description' => 'Ruang rapat modern untuk acara korporat.',
                'is_active' => true,
            ]
        );

        $morningSession = HallSession::firstOrCreate(
            ['session_name' => 'Morning (08:00 - 12:00)'],
            ['start_time' => '08:00:00', 'end_time' => '12:00:00']
        );

        $eveningSession = HallSession::firstOrCreate(
            ['session_name' => 'Evening (18:00 - 23:00)'],
            ['start_time' => '18:00:00', 'end_time' => '23:00:00']
        );

        // --- Paket event ---
        EventPackage::firstOrCreate(
            ['hall_id' => $ballroom->id, 'package_name' => 'Wedding Package Gold'],
            ['price' => 15000000, 'description' => 'Dekorasi, katering 1000 pax, sound system.']
        );

        EventPackage::firstOrCreate(
            ['hall_id' => $meetingHall->id, 'package_name' => 'Corporate Half Day'],
            ['price' => 3500000, 'description' => 'Proyektor, whiteboard, coffee break 2x.']
        );
    }
}
