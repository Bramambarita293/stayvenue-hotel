<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_can_be_rendered(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
        $response->assertSee('Contact & Location');
        $response->assertSee('Pertanyaan Umum');
    }

    public function test_guest_can_submit_a_contact_message(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Tamu Uji',
            'email' => 'tamu@example.com',
            'phone' => '081234567890',
            'subject' => 'Reservasi kamar',
            'message' => 'Halo, saya ingin bertanya tentang ketersediaan kamar untuk akhir pekan ini.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Tamu Uji',
            'email' => 'tamu@example.com',
            'is_read' => false,
        ]);
    }

    public function test_contact_message_requires_valid_input(): void
    {
        $response = $this->post('/contact', [
            'name' => '',
            'email' => 'bukan-email',
            'message' => 'pendek',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'message']);
        $this->assertDatabaseCount('contact_messages', 0);
    }
}
