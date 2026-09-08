<?php

namespace Tests\Feature;

use App\Models\Hall;
use App\Models\HallSession;
use App\Models\Reservation;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class LegalAndRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_and_terms_pages_render(): void
    {
        $this->get('/privacy')->assertStatus(200)->assertSee('Privacy Policy');
        $this->get('/terms')->assertStatus(200)->assertSee('Terms of Service');
    }

    public function test_forgot_password_sends_link_without_enumeration(): void
    {
        Notification::fake();
        User::create(['name' => 'T', 'email' => 't@t.com', 'password' => 'x']);

        $this->post('/forgot-password', ['email' => 't@t.com'])
            ->assertRedirect()
            ->assertSessionHas('status');

        // Email tak terdaftar: respons sama (tak bocorkan keberadaan akun).
        $this->post('/forgot-password', ['email' => 'ghost@t.com'])
            ->assertRedirect()
            ->assertSessionHas('status');
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();
        $user = User::create(['name' => 'T', 'email' => 't@t.com', 'password' => Hash::make('old-secret')]);

        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => 't@t.com',
            'password' => 'new-secret-1',
            'password_confirmation' => 'new-secret-1',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-secret-1', $user->fresh()->password));
    }

    public function test_hall_checkout_accepts_enum_event_type(): void
    {
        $this->mock(MidtransService::class, fn ($m) => $m->shouldReceive('createSnapToken')->once()->andReturn(['HTL-H--1', 'tok']));

        $user = User::create(['name' => 'T', 'email' => 't@t.com', 'password' => 'x', 'phone_number' => '081']);
        $hall = Hall::create(['name' => 'H', 'capacity_pax' => 50, 'base_rental_price' => 1000000, 'is_active' => true]);
        $session = HallSession::create(['session_name' => 'Pagi', 'start_time' => '08:00', 'end_time' => '12:00']);

        $response = $this->actingAs($user)->post('/booking/hall', [
            'hall_id' => $hall->id,
            'session_id' => $session->id,
            'event_date' => now()->addDay()->format('Y-m-d'),
            'event_type' => 'wedding',
        ]);

        $response->assertRedirect();
        $this->assertSame(1, Reservation::count());
        $this->assertSame('wedding', Reservation::sole()->hallBooking->event_type);
    }

    public function test_hall_checkout_rejects_free_text_event_type(): void
    {
        $user = User::create(['name' => 'T', 'email' => 't@t.com', 'password' => 'x']);
        $hall = Hall::create(['name' => 'H', 'capacity_pax' => 50, 'base_rental_price' => 1000000, 'is_active' => true]);
        $session = HallSession::create(['session_name' => 'Pagi', 'start_time' => '08:00', 'end_time' => '12:00']);

        $this->actingAs($user)->post('/booking/hall', [
            'hall_id' => $hall->id,
            'session_id' => $session->id,
            'event_date' => now()->addDay()->format('Y-m-d'),
            'event_type' => 'Pernikahan',
        ])->assertSessionHasErrors('event_type');

        $this->assertSame(0, Reservation::count());
    }
}
