<?php

namespace Tests\Feature\Auth;

use App\Features\Auth\Livewire\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_has_required_ids(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('id="login-username"', false)
            ->assertSee('id="login-password"', false)
            ->assertSee('id="login-submit"', false);
    }

    public function test_member_can_login_with_username(): void
    {
        User::factory()->create(['username' => 'budi']);

        Livewire::test(Login::class)
            ->set('login', 'budi')
            ->set('password', 'password')
            ->call('submit')
            ->assertRedirect(route('stocks.index'));

        $this->assertAuthenticated();
    }

    public function test_admin_login_with_email_redirects_to_admin_users(): void
    {
        User::factory()->admin()->create(['email' => 'boss@example.com']);

        Livewire::test(Login::class)
            ->set('login', 'boss@example.com')
            ->set('password', 'password')
            ->call('submit')
            ->assertRedirect(route('admin.users'));
    }

    public function test_wrong_password_shows_error(): void
    {
        User::factory()->create(['username' => 'budi']);

        Livewire::test(Login::class)
            ->set('login', 'budi')
            ->set('password', 'salah')
            ->call('submit')
            ->assertHasErrors(['login']);

        $this->assertGuest();
    }

    public function test_validation_requires_fields(): void
    {
        Livewire::test(Login::class)
            ->call('submit')
            ->assertHasErrors(['login' => 'required', 'password' => 'required']);
    }
}
