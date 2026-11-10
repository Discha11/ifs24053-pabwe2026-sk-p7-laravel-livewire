<?php

namespace Tests\Feature;

use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_root_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_member_root_redirects_to_stocks(): void
    {
        $this->actingAs(User::factory()->create())->get('/')->assertRedirect(route('stocks.index'));
    }

    public function test_admin_root_redirects_to_admin_users(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/')->assertRedirect(route('admin.users'));
    }

    public function test_guest_is_redirected_from_protected_pages(): void
    {
        $this->get('/stocks')->assertRedirect(route('login'));
        $this->get('/profile')->assertRedirect(route('login'));
        $this->get('/admin/users')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_cannot_open_login_page(): void
    {
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect('/stocks');
        $this->actingAs(User::factory()->admin()->create())->get('/register')->assertRedirect('/admin/users');
    }

    public function test_member_pages_render(): void
    {
        $member = User::factory()->create();
        $stock = Stock::factory()->for($member)->create();

        $this->actingAs($member)->get('/stocks')->assertOk()->assertSee('Daftar Stok');
        $this->actingAs($member)->get("/stocks/{$stock->id}")->assertOk()->assertSee($stock->name);
        $this->actingAs($member)->get('/profile')->assertOk();
    }

    public function test_member_cannot_open_other_members_stock(): void
    {
        $stock = Stock::factory()->create();

        $this->actingAs(User::factory()->create())->get("/stocks/{$stock->id}")->assertForbidden();
    }

    public function test_member_cannot_access_admin_pages(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get('/admin/users')->assertForbidden();
        $this->actingAs($member)->get("/admin/users/{$member->id}/stocks")->assertForbidden();
    }

    public function test_admin_cannot_access_member_stock_pages(): void
    {
        $admin = User::factory()->admin()->create();
        $stock = Stock::factory()->create();

        $this->actingAs($admin)->get('/stocks')->assertForbidden();
        $this->actingAs($admin)->get("/stocks/{$stock->id}")->assertForbidden();
    }

    public function test_admin_pages_render(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get("/admin/users/{$member->id}/stocks")->assertOk();
    }

    public function test_logout(): void
    {
        $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_public_and_detail_pages_optimize_performance_seo_and_bfcache(): void
    {
        $loginResponse = $this->get('/login');
        $loginResponse->assertOk()
            ->assertSee('<meta name="description"', false)
            ->assertDontSee('cdn.tailwindcss.com', false)
            ->assertSee('defer="defer"', false);
        $this->assertStringNotContainsString('no-store', (string) $loginResponse->headers->get('Cache-Control'));

        $member = User::factory()->create();
        $stock = Stock::factory()->for($member)->create();

        $detailResponse = $this->actingAs($member)->get("/stocks/{$stock->id}");
        $detailResponse->assertOk()
            ->assertDontSee('<script src="https://uicdn.toast.com/editor/latest/toastui-editor-all.min.js"></script>', false);
    }
}
