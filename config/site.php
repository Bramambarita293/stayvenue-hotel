<?php

return [

    /*
    | Static hotel identity details surfaced across the public frontend.
    | These are intentionally static (address, contact) rather than dummy.
    */

    'name' => 'StayVenue',

    'long_name' => 'Stay Venue Hotel',

    'tagline' => 'Refined stays & unforgettable gatherings',

    'address' => 'Jl. Raya Utama No. 1, Menteng, Jakarta Pusat 10310, Indonesia',

    'phone' => '+62 813 7314-6527',

    'email' => 'reservations@stayvenue.id',

    'hours' => 'Front Desk 24/7',

    'whatsapp' => '6281373146527',

    // Embed Google Maps gratis (tanpa API key)
    'map_embed' => 'https://maps.google.com/maps?q=Jl.%20Raya%20Utama%20No.%201,%20Menteng,%20Jakarta%20Pusat&t=&z=15&ie=UTF8&iwloc=&output=embed',

    'map_link' => 'https://www.google.com/maps/search/?api=1&query=Jl.+Raya+Utama+No.+1,+Menteng,+Jakarta+Pusat',

    'socials' => [
        'instagram' => 'https://instagram.com/stayvenue',
        'facebook' => 'https://facebook.com/stayvenue',
        'tiktok' => 'https://tiktok.com/@stayvenue',
    ],

    /*
    | Fasilitas hotel yang ditampilkan di homepage.
    | icon memakai nama ligature Material Symbols Outlined.
    */
    'facilities' => [
        ['icon' => 'pool', 'title' => 'Infinity Pool', 'description' => 'Kolam renang rooftop dengan pemandangan kota.'],
        ['icon' => 'fitness_center', 'title' => 'Fitness Center', 'description' => 'Gym modern lengkap, buka 24 jam untuk tamu.'],
        ['icon' => 'restaurant', 'title' => 'Restaurant & Bar', 'description' => 'Sarapan hingga fine dining dengan menu khas nusantara.'],
        ['icon' => 'spa', 'title' => 'Spa & Wellness', 'description' => 'Perawatan spa tradisional dan aromaterapi.'],
        ['icon' => 'wifi', 'title' => 'High-Speed WiFi', 'description' => 'Internet fiber gratis di seluruh area hotel.'],
        ['icon' => 'local_parking', 'title' => 'Parkir Luas & Valet', 'description' => 'Area parkir aman plus layanan valet.'],
        ['icon' => 'meeting_room', 'title' => 'Ruang Rapat', 'description' => 'Meeting room berkapasitas 10–150 orang.'],
        ['icon' => 'airport_shuttle', 'title' => 'Airport Shuttle', 'description' => 'Layanan antar-jemput bandara atas permintaan.'],
    ],

    /*
    | Testimoni tamu (statis). rating dalam skala 5.
    */
    'testimonials' => [
        [
            'name' => 'Andini Prameswari',
            'origin' => 'Jakarta',
            'rating' => 5,
            'text' => 'Kamar bersih, pemandangan kota luar biasa, dan stafnya sangat ramah. Wedding reception di Grand Ballroom juga lancar berkat tim event yang responsif.',
        ],
        [
            'name' => 'Marcus Chen',
            'origin' => 'Singapore',
            'rating' => 5,
            'text' => 'Perfect for business trips. Fast check-in, comfortable bed, and the meeting room facilities exceeded my expectations.',
        ],
        [
            'name' => 'Rizky Mahendra',
            'origin' => 'Bandung',
            'rating' => 4,
            'text' => 'Lokasi strategis di pusat kota, mudah dijangkau. Sarapannya variatif dan kolam renang rooftop jadi favorit anak-anak.',
        ],
        [
            'name' => 'Sarah Wibowo',
            'origin' => 'Surabaya',
            'rating' => 5,
            'text' => 'Booking via website gampang banget, e-voucher langsung ada QR code buat express check-in. Pengalaman menginap yang menyenangkan!',
        ],
    ],

    /*
    | FAQ yang tampil di halaman contact.
    */
    'faqs' => [
        [
            'q' => 'Jam check-in dan check-out?',
            'a' => 'Check-in mulai pukul 14:00 WIB dan check-out sebelum pukul 12:00 WIB. Early check-in atau late check-out tergantung ketersediaan — silakan hubungi front desk.',
        ],
        [
            'q' => 'Bagaimana cara pembayaran?',
            'a' => 'Pembayaran dilakukan online melalui Midtrans (transfer bank, e-wallet, QRIS, atau kartu kredit) setelah checkout di website. E-voucher terbit otomatis setelah pembayaran terkonfirmasi.',
        ],
        [
            'q' => 'Apakah bisa membatalkan pesanan?',
            'a' => 'Pesanan yang belum dibayar akan hangus otomatis setelah 24 jam tanpa biaya. Untuk pesanan yang sudah lunas, kebijakan pembatalan mengikuti rate plan yang dipilih — hubungi kami via telepon atau WhatsApp.',
        ],
        [
            'q' => 'Apakah harga sewa gedung sudah termasuk paket?',
            'a' => 'Harga sewa adalah base rental. Anda dapat menambahkan paket acara (dekorasi, katering, sound system) pada saat checkout di halaman detail gedung.',
        ],
        [
            'q' => 'Tersedia parkir untuk tamu?',
            'a' => 'Ya, tersedia area parkir luas dan layanan valet gratis untuk seluruh tamu menginap maupun pengunjung acara.',
        ],
    ],

    /*
    | Promo homepage (statis). valid_until dalam Y-m-d (WIB); countdown
    | dihitung di browser. Promo kedaluwarsa otomatis diredupkan.
    */
    'promos' => [
        [
            'badge' => 'Penawaran Spesial',
            'title' => 'Elevate Your Stay',
            'desc' => 'Nikmati pengalaman menginap yang lebih istimewa dengan kenyamanan, pelayanan eksklusif, dan suasana yang dirancang untuk memanjakan Anda.',
            'cta_label' => 'Book Your Stay',
            'cta_route' => 'rooms.index',
            'theme' => 'gold',
            'valid_until' => '2026-09-30',
        ],
        [
            'badge' => 'Eksklusif',
            'title' => 'A Stay Worth Remembering',
            'desc' => 'Temukan standar kenyamanan baru dalam setiap detail. Jadikan perjalanan Anda lebih berkesan dengan pengalaman menginap yang eksklusif.',
            'cta_label' => 'Reserve Your Room',
            'cta_route' => 'rooms.index',
            'theme' => 'onyx',
            'valid_until' => '2026-09-19',
        ],
        [
            'badge' => 'Luxury Moment',
            'title' => 'Stay in Luxury. Live the Moment.',
            'desc' => 'Rasakan kemewahan yang tenang, pelayanan berkelas, dan kenyamanan tanpa kompromi dalam setiap momen Anda.',
            'cta_label' => 'Discover Your Stay',
            'cta_route' => 'halls.index',
            'theme' => 'gold',
            'valid_until' => '2026-10-05',
        ],
    ],

];
