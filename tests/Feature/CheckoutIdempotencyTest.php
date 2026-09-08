<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::create([
            'name' => 'Tamu Uji',
            'email' => 'tamu@example.com',
            'password' => 'password123',
            'phone_number' => '081234567890',
        ]);
    }

    private function makeRoomType(): RoomType
    {
        $type = RoomType::create([
            'name' => 'Deluxe Test',
            'slug' => 'deluxe-test-'.uniqid(),
            'base_price' => 500000,
            'max_guests' => 2,
        ]);
        Room::create(['room_type_id' => $type->id, 'room_number' => 'T201', 'status' => 'AVAILABLE']);
        Room::create(['room_type_id' => $type->id, 'room_number' => 'T202', 'status' => 'AVAILABLE']);

        return $type;
    }

    public function test_double_submit_reuses_single_pending_reservation(): void
    {
        $this->mock(MidtransService::class, function ($mock) {
            $mock->shouldReceive('createSnapToken')->once()->andReturn(['HTL-X--1', 'snap-token-1']);
        });

        $user = $this->makeUser();
        $type = $this->makeRoomType();
        $payload = [
            'room_type_id' => $type->id,
            'check_in_date' => now()->addDay()->format('Y-m-d'),
            'check_out_date' => now()->addDays(3)->format('Y-m-d'),
            'number_of_rooms' => 1,
        ];

        $first = $this->actingAs($user)->post('/booking/room', $payload);
        $first->assertRedirect();
        $code = Reservation::sole()->reservation_code;
        $this->assertStringContainsString("/booking/pay/{$code}", $first->headers->get('Location'));

        // Submit ganda identik: dipakai ulang, tanpa reservasi/payment baru, tanpa Snap baru.
        $second = $this->actingAs($user)->post('/booking/room', $payload);
        $second->assertRedirect(route('booking.pay', $code));

        $this->assertSame(1, Reservation::count());
        $this->assertSame(1, $user->reservations()->count());
        $res = Reservation::sole();
        $this->assertSame(1, $res->payments()->count());
        $this->assertSame('PENDING', $res->payments()->sole()->status);
        $this->assertSame('HTL-X--1', $res->payments()->sole()->transaction_id);
    }

    public function test_webhook_without_signature_fields_is_rejected(): void
    {
        $this->postJson('/api/midtrans/notification', ['order_id' => 'HTL-X--1'])
            ->assertStatus(403);

        $this->postJson('/api/midtrans/notification', [
            'order_id' => 'HTL-X--1',
            'status_code' => '200',
            'gross_amount' => '500000',
            'signature_key' => 'salah',
        ])->assertStatus(403);
    }

    public function test_order_id_parser_handles_new_and_legacy_format(): void
    {
        $this->assertSame('HTL-20240101-ABCDE', MidtransService::reservationCodeFromOrderId('HTL-20240101-ABCDE--1700000000'));
        // Kode full-digit + format lama tetap best-effort.
        $this->assertSame('HTL-20240101', MidtransService::reservationCodeFromOrderId('HTL-20240101-12345'));
    }
}
