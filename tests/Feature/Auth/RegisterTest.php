<?php

namespace Tests\Feature\Auth;

use App\Features\Auth\Livewire\Register;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_renders(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_user_can_register_as_member(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'Budi')
            ->set('username', 'budi')
            ->set('email', 'budi@example.com')
            ->set('password', 'rahasia123')
            ->set('password_confirmation', 'rahasia123')
            ->call('submit')
            ->assertRedirect(route('stocks.index'));

        $user = User::where('username', 'budi')->firstOrFail();
        $this->assertSame(User::ROLE_MEMBER, $user->role);
        $this->assertAuthenticatedAs($user);
    }

    public function test_register_validates_input(): void
    {
        User::factory()->create(['username' => 'budi', 'email' => 'budi@example.com']);

        Livewire::test(Register::class)
            ->set('name', 'Budi')
            ->set('username', 'budi')
            ->set('email', 'budi@example.com')
            ->set('password', 'pendek')
            ->set('password_confirmation', 'beda')
            ->call('submit')
            ->assertHasErrors(['username', 'email', 'password']);
    }
}
