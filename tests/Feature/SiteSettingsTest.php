<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret123',
        ]);
        // is_admin sengaja non-fillable (anti mass-assignment) -> set eksplisit.
        $user->forceFill(['is_admin' => true])->save();

        return $user->fresh();
    }

    public function test_admin_can_open_site_settings_page(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get('/admin/site-settings')
            ->assertStatus(200);
    }

    public function test_guest_cannot_open_site_settings_page(): void
    {
        $this->get('/admin/site-settings')->assertRedirect('/admin/login');
    }

    public function test_setting_get_set_roundtrip_and_cache_invalidation(): void
    {
        $this->assertNull(SiteSetting::get(SiteSetting::AUTH_LOGIN_COVER));

        SiteSetting::set(SiteSetting::AUTH_LOGIN_COVER, 'site/login.jpg');
        $this->assertSame('site/login.jpg', SiteSetting::get(SiteSetting::AUTH_LOGIN_COVER));

        SiteSetting::set(SiteSetting::AUTH_LOGIN_COVER, null);
        $this->assertNull(SiteSetting::get(SiteSetting::AUTH_LOGIN_COVER));
    }

    public function test_login_cover_prefers_setting_over_fallback(): void
    {
        SiteSetting::set(SiteSetting::AUTH_LOGIN_COVER, 'site/login.jpg');
        SiteSetting::set(SiteSetting::AUTH_REGISTER_COVER, 'site/register.jpg');

        $login = $this->get('/login')->assertStatus(200);
        $login->assertSee('site/login.jpg', false);
        $login->assertDontSee('site/register.jpg', false);

        $register = $this->get('/register')->assertStatus(200);
        $register->assertSee('site/register.jpg', false);
    }
}
