<?php

namespace Database\Seeders;

use App\Models\EventPackage;
use App\Models\Hall;
use App\Models\HallSession;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    private function downloadAndUpload(string $url, string $directory, string $filename): string
    {
        $path = "{$directory}/{$filename}";

        if (Storage::disk('s3')->exists($path)) {
            return $path;
        }

        $response = Http::timeout(30)->get($url);

        if ($response->successful()) {
            Storage::disk('s3')->put($path, $response->body());
        }

        return $path;
    }

    private function uploadImages(array $urls, string $directory): array
    {
        $paths = [];

        foreach ($urls as $i => $url) {
            $filename = Str::random(10) . ".jpg";
            $paths[] = $this->downloadAndUpload($url, $directory, $filename);
        }

        return $paths;
    }

    public function run(): void
    {
        // ==========================================
        // ROOM TYPES
        // ==========================================

        $deluxeImages = $this->uploadImages([
            'https://picsum.photos/seed/deluxe1/1200/800',
            'https://picsum.photos/seed/deluxe2/1200/800',
            'https://picsum.photos/seed/deluxe3/1200/800',
            'https://picsum.photos/seed/deluxe4/1200/800',
            'https://picsum.photos/seed/deluxe5/1200/800',
        ], 'room-types/deluxe-double');

        $deluxe = RoomType::firstOrCreate(
            ['slug' => 'deluxe-double'],
            [
                'name' => 'Deluxe Double',
                'description' => 'Kamar premium dengan tempat tidur king size, balkon pribadi, dan pemandangan kota. Dilengkapi fasilitas mini bar, smart TV 55 inch, dan kamar mandi shower hujan.',
                'base_price' => 750000,
                'max_guests' => 2,
                'images' => $deluxeImages,
            ]
        );

        $suiteImages = $this->uploadImages([
            'https://picsum.photos/seed/suite1/1200/800',
            'https://picsum.photos/seed/suite2/1200/800',
            'https://picsum.photos/seed/suite3/1200/800',
            'https://picsum.photos/seed/suite4/1200/800',
            'https://picsum.photos/seed/suite5/1200/800',
        ], 'room-types/executive-suite');

        $suite = RoomType::firstOrCreate(
            ['slug' => 'executive-suite'],
            [
                'name' => 'Executive Suite',
                'description' => 'Suite mewah dengan ruang tamu terpisah, bathtub Jacuzzi, akses executive lounge, dan layanan butler 24 jam. Ideal untuk tamu bisnis dan honeymoon.',
                'base_price' => 1500000,
                'max_guests' => 4,
                'images' => $suiteImages,
            ]
        );

        $familyImages = $this->uploadImages([
            'https://picsum.photos/seed/family1/1200/800',
            'https://picsum.photos/seed/family2/1200/800',
            'https://picsum.photos/seed/family3/1200/800',
            'https://picsum.photos/seed/family4/1200/800',
            'https://picsum.photos/seed/family5/1200/800',
        ], 'room-types/family-room');

        $family = RoomType::firstOrCreate(
            ['slug' => 'family-room'],
            [
                'name' => 'Family Room',
                'description' => 'Kamar luas dengan 2 tempat tidur queen, area bermain anak, mini kitchen, dan sofa bed. Sempurna untuk liburan keluarga.',
                'base_price' => 1200000,
                'max_guests' => 4,
                'images' => $familyImages,
            ]
        );

        // ==========================================
        // ROOMS
        // ==========================================

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

        foreach (['401', '402', '403', '404'] as $number) {
            Room::firstOrCreate(['room_number' => $number], [
                'room_type_id' => $family->id,
                'status' => 'AVAILABLE',
            ]);
        }

        // ==========================================
        // HALLS
        // ==========================================

        $ballroomImages = $this->uploadImages([
            'https://picsum.photos/seed/ballroom1/1200/800',
            'https://picsum.photos/seed/ballroom2/1200/800',
            'https://picsum.photos/seed/ballroom3/1200/800',
            'https://picsum.photos/seed/ballroom4/1200/800',
            'https://picsum.photos/seed/ballroom5/1200/800',
        ], 'halls/grand-ballroom');

        $ballroom = Hall::firstOrCreate(
            ['name' => 'Grand Ballroom'],
            [
                'capacity_pax' => 500,
                'base_rental_price' => 15000000,
                'description' => 'Ballroom utama hotel dengan desain modern elegan. Dilengkapi panggung utama, layar LED 200 inch, tata suara JBL profesional, dan pencahayaan RGB custom. Cocok untuk wedding reception, gala dinner, dan acara berskala besar.',
                'is_active' => true,
                'images' => $ballroomImages,
            ]
        );

        $meetingImages = $this->uploadImages([
            'https://picsum.photos/seed/meeting1/1200/800',
            'https://picsum.photos/seed/meeting2/1200/800',
            'https://picsum.photos/seed/meeting3/1200/800',
            'https://picsum.photos/seed/meeting4/1200/800',
            'https://picsum.photos/seed/meeting5/1200/800',
        ], 'halls/meeting-room');

        $meeting = Hall::firstOrCreate(
            ['name' => 'Meeting Room A'],
            [
                'capacity_pax' => 50,
                'base_rental_price' => 2500000,
                'description' => 'Ruang rapat eksklusif dengan meja konferensi U-shaped, proyektor 4K, whiteboard digital, dan Wi-Fi dedicated. Ideal untuk meeting perusahaan, presentasi klien, dan workshop.',
                'is_active' => true,
                'images' => $meetingImages,
            ]
        );

        $gardenImages = $this->uploadImages([
            'https://picsum.photos/seed/garden1/1200/800',
            'https://picsum.photos/seed/garden2/1200/800',
            'https://picsum.photos/seed/garden3/1200/800',
            'https://picsum.photos/seed/garden4/1200/800',
            'https://picsum.photos/seed/garden5/1200/800',
        ], 'halls/garden-pavilion');

        $garden = Hall::firstOrCreate(
            ['name' => 'Garden Pavilion'],
            [
                'capacity_pax' => 200,
                'base_rental_price' => 8000000,
                'description' => 'Paviliun outdoor dengan taman asri dan pemandangan pepohonan. Tersedia tenda transparan, dekorasi bunga, area buffet outdoor, dan taman bermain anak. Sempurna untuk garden party, lamaran, dan intimate wedding.',
                'is_active' => true,
                'images' => $gardenImages,
            ]
        );

        // ==========================================
        // HALL SESSIONS
        // ==========================================

        $morning = HallSession::firstOrCreate(
            ['session_name' => 'Half Day Morning'],
            ['start_time' => '08:00:00', 'end_time' => '13:00:00']
        );

        $afternoon = HallSession::firstOrCreate(
            ['session_name' => 'Half Day Afternoon'],
            ['start_time' => '13:00:00', 'end_time' => '18:00:00']
        );

        $fullDay = HallSession::firstOrCreate(
            ['session_name' => 'Full Day'],
            ['start_time' => '08:00:00', 'end_time' => '22:00:00']
        );

        // ==========================================
        // EVENT PACKAGES
        // ==========================================

        EventPackage::firstOrCreate(
            ['hall_id' => $ballroom->id, 'package_name' => 'Silver Wedding'],
            [
                'price' => 5000000,
                'description' => 'Paket pernikahan standar: dekorasi pelaminan, catering 200 pax, sound system standar, MC, dan dokumentasi foto.',
            ]
        );

        EventPackage::firstOrCreate(
            ['hall_id' => $ballroom->id, 'package_name' => 'Gold Wedding'],
            [
                'price' => 10000000,
                'description' => 'Paket premium: dekorasi full bunga segar, catering 400 pax, sound system profesional, videografer, photobooth, dan farewell dinner.',
            ]
        );

        EventPackage::firstOrCreate(
            ['hall_id' => $ballroom->id, 'package_name' => 'Platinum Wedding'],
            [
                'price' => 18000000,
                'description' => 'Paket eksklusif: dekorasi custom sesuai tema, catering 600 pax, live band, fireworks midnight, photo drone, dan wedding advisor.',
            ]
        );

        EventPackage::firstOrCreate(
            ['hall_id' => $meeting->id, 'package_name' => 'Corporate Meeting'],
            [
                'price' => 1500000,
                'description' => 'Paket meeting korporat: snacks & lunch 50 pax, proyektor 4K, notepad, pulpen, Wi-Fi dedicated, dan documentation.',
            ]
        );

        EventPackage::firstOrCreate(
            ['hall_id' => $garden->id, 'package_name' => 'Garden Party'],
            [
                'price' => 3500000,
                'description' => 'Paket pesta taman: dekorasi taman, catering standing party 150 pax, live acoustic band, photobooth corner, dan garden lights.',
            ]
        );

        $this->command->info('Dummy data berhasil dibuat! Termasuk gambar yang diupload ke S3.');
    }
}
