<?php

namespace Tests\Feature;

use App\Models\Hall;
use App\Models\HallBooking;
use App\Models\HallSession;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoCompleteStaysTest extends TestCase
{
    use RefreshDatabase;

    private function makeRoomType(): RoomType
    {
        return RoomType::create([
            'name' => 'Deluxe Test',
            'slug' => 'deluxe-test-'.uniqid(),
            'base_price' => 500000,
            'max_guests' => 2,
        ]);
    }

    private function makeReservation(array $overrides = []): Reservation
    {
        return Reservation::create(array_merge([
            'reservation_code' => 'HTL-TEST-'.strtoupper(uniqid()),
            'guest_name' => 'Tamu Uji',
            'guest_email' => 'tamu@example.com',
            'guest_phone' => '081234567890',
            'reservation_type' => 'ROOM',
            'total_amount' => 500000,
            'status' => 'CONFIRMED',
        ], $overrides));
    }

    public function test_checked_in_past_checkout_becomes_checked_out_and_room_cleaning(): void
    {
        $type = $this->makeRoomType();
        $room = Room::create(['room_type_id' => $type->id, 'room_number' => 'T101', 'status' => 'OCCUPIED']);
        $res = $this->makeReservation(['status' => 'CHECKED_IN']);
        RoomBooking::create([
            'reservation_id' => $res->id,
            'room_type_id' => $type->id,
            'room_id' => $room->id,
            'check_in_date' => now()->subDays(3)->format('Y-m-d'),
            'check_out_date' => now()->subDay()->format('Y-m-d'),
            'number_of_rooms' => 1,
            'assigned_room_number' => 'T101',
        ]);

        $this->artisan('stays:auto-complete')->assertSuccessful();

        $this->assertSame('CHECKED_OUT', $res->fresh()->status);
        $this->assertSame('CLEANING', $room->fresh()->status);
    }

    public function test_confirmed_noshow_is_treated_as_checkout(): void
    {
        $type = $this->makeRoomType();
        $res = $this->makeReservation(['status' => 'CONFIRMED']);
        RoomBooking::create([
            'reservation_id' => $res->id,
            'room_type_id' => $type->id,
            'check_in_date' => now()->subDays(2)->format('Y-m-d'),
            'check_out_date' => now()->subDay()->format('Y-m-d'),
            'number_of_rooms' => 1,
        ]);

        $this->artisan('stays:auto-complete')->assertSuccessful();

        // Kemarin checkout -> berhenti di CHECKED_OUT (arsip COMPLETED besok).
        $this->assertSame('CHECKED_OUT', $res->fresh()->status);
    }

    public function test_long_overdue_noshow_flows_to_completed_in_one_run(): void
    {
        $type = $this->makeRoomType();
        $res = $this->makeReservation(['status' => 'CONFIRMED']);
        RoomBooking::create([
            'reservation_id' => $res->id,
            'room_type_id' => $type->id,
            'check_in_date' => now()->subDays(4)->format('Y-m-d'),
            'check_out_date' => now()->subDays(2)->format('Y-m-d'),
            'number_of_rooms' => 1,
        ]);

        $this->artisan('stays:auto-complete')->assertSuccessful();

        $this->assertSame('COMPLETED', $res->fresh()->status);
    }

    public function test_today_checkout_is_untouched(): void
    {
        $type = $this->makeRoomType();
        $room = Room::create(['room_type_id' => $type->id, 'room_number' => 'T102', 'status' => 'OCCUPIED']);
        $res = $this->makeReservation(['status' => 'CHECKED_IN']);
        RoomBooking::create([
            'reservation_id' => $res->id,
            'room_type_id' => $type->id,
            'room_id' => $room->id,
            'check_in_date' => now()->subDay()->format('Y-m-d'),
            'check_out_date' => now()->format('Y-m-d'),
            'number_of_rooms' => 1,
        ]);

        $this->artisan('stays:auto-complete')->assertSuccessful();

        $this->assertSame('CHECKED_IN', $res->fresh()->status);
        $this->assertSame('OCCUPIED', $room->fresh()->status);
    }

    public function test_old_checked_out_becomes_completed_and_rerun_is_idempotent(): void
    {
        $type = $this->makeRoomType();
        $res = $this->makeReservation(['status' => 'CHECKED_OUT']);
        RoomBooking::create([
            'reservation_id' => $res->id,
            'room_type_id' => $type->id,
            'check_in_date' => now()->subDays(5)->format('Y-m-d'),
            'check_out_date' => now()->subDays(3)->format('Y-m-d'),
            'number_of_rooms' => 1,
        ]);

        $this->artisan('stays:auto-complete')->assertSuccessful();
        $this->assertSame('COMPLETED', $res->fresh()->status);

        $this->artisan('stays:auto-complete')->assertSuccessful();
        $this->assertSame('COMPLETED', $res->fresh()->status);
    }

    public function test_past_hall_event_becomes_completed(): void
    {
        $hall = Hall::create(['name' => 'Hall Uji', 'capacity_pax' => 100, 'base_rental_price' => 1000000]);
        $session = HallSession::create(['session_name' => 'Pagi', 'start_time' => '08:00', 'end_time' => '12:00']);
        $res = $this->makeReservation(['reservation_type' => 'HALL', 'status' => 'CONFIRMED']);
        HallBooking::create([
            'reservation_id' => $res->id,
            'hall_id' => $hall->id,
            'session_id' => $session->id,
            'event_date' => now()->subDay()->format('Y-m-d'),
            'event_type' => 'wedding',
        ]);

        $this->artisan('stays:auto-complete')->assertSuccessful();

        $this->assertSame('COMPLETED', $res->fresh()->status);
    }
}
