<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\Reservation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Room;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Total Pendapatan 
        $totalRevenue = Payment::where('status', 'SUCCESS')->sum('amount');

        // Jumlah Reservasi Terkonfirmasi 
        $confirmedBookings = Reservation::whereIn('status', ['CONFIRMED', 'COMPLETED'])->count();

        // Reservasi Menunggu Check-in (kamar saja; HALL tidak punya alur check-in)
        $pendingCheckIns = Reservation::where('status', 'CONFIRMED')
            ->where('reservation_type', 'ROOM')
            ->count();

        $occupiedRooms = Room::where('status', 'OCCUPIED')->count();
        $cleaningRooms = Room::where('status', 'CLEANING')->count();

        return [
            Stat::make('Total Pendapatan', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Transaksi Berhasil ')
                ->color('success'),

            Stat::make('Total Pemesanan', $confirmedBookings)
                ->description('Kamar & Sewa Gedung')
                ->color('primary'),

            Stat::make('Siap Check-In', $pendingCheckIns)
                ->description('Tamu yang belum Check-In')
                ->color('warning'),

            Stat::make('Kamar Terisi', $occupiedRooms . ' Kamar')
                ->description('Tamu sedang menginap')
                ->color('danger'),

            Stat::make('Perlu Dibersihkan (Cleaning)', $cleaningRooms . ' Kamar')
                ->description('Antrean tugas Housekeeping')
                ->color('warning'),
        ];
    }
}
