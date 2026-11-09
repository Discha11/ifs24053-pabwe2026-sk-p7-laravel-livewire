<?php

namespace Tests\Feature\Profile;

use App\Features\Profile\Livewire\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_mount_fills_form(): void
    {
        $user = User::factory()->create(['name' => 'Budi']);

        Livewire::actingAs($user)->test(Profile::class)
            ->assertSet('name', 'Budi')
            ->assertSet('username', $user->username)
            ->assertSet('email', $user->email);
    }

    public function test_update_profile(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(Profile::class)
            ->set('name', 'Nama Baru')
            ->set('username', 'baru_01')
            ->set('email', 'baru@example.com')
            ->call('saveProfile')
            ->assertHasNoErrors();

        $this->assertSame('baru_01', $user->refresh()->username);
    }

    public function test_update_profile_rejects_taken_username(): void
    {
        $other = User::factory()->create();
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(Profile::class)
            ->set('username', $other->username)
            ->call('saveProfile')
            ->assertHasErrors(['username']);
    }

    public function test_change_password(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(Profile::class)
            ->set('current_password', 'password')
            ->set('password', 'barubaru123')
            ->set('password_confirmation', 'barubaru123')
            ->call('savePassword')
            ->assertHasNoErrors()
            ->assertSet('password', '');

        $this->assertTrue(Hash::check('barubaru123', $user->refresh()->password));
    }

    public function test_change_password_requires_correct_current_password(): void
    {
        Livewire::actingAs(User::factory()->create())->test(Profile::class)
            ->set('current_password', 'salah')
            ->set('password', 'barubaru123')
            ->set('password_confirmation', 'barubaru123')
            ->call('savePassword')
            ->assertHasErrors(['current_password']);
    }

    public function test_upload_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)->test(Profile::class);
        $component->set('photo', UploadedFile::fake()->image('a.jpg'))->call('savePhoto')->assertHasNoErrors();

        Storage::disk('public')->assertExists($user->refresh()->profile_photo_path);
        $component->assertSee('Foto profil');
    }

    public function test_photo_must_be_image(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())->test(Profile::class)
            ->set('photo', UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'))
            ->call('savePhoto')
            ->assertHasErrors(['photo']);
    }
}
