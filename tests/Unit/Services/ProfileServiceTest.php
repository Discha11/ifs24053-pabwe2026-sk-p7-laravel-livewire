<?php

namespace Tests\Unit\Services;

use App\Features\Profile\Services\ProfileService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_profile(): void
    {
        $user = User::factory()->create();

        $updated = (new ProfileService)->updateProfile($user, ['name' => 'Baru', 'username' => 'baru', 'email' => 'b@example.com']);

        $this->assertSame('baru', $updated->username);
        $this->assertSame('b@example.com', $updated->email);
    }

    public function test_update_password(): void
    {
        $user = User::factory()->create();

        (new ProfileService)->updatePassword($user, 'passwordbaru');

        $this->assertTrue(Hash::check('passwordbaru', $user->refresh()->password));
    }

    public function test_update_photo_replaces_previous(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $service = new ProfileService;

        $user = $service->updatePhoto($user, UploadedFile::fake()->image('a.jpg'));
        $first = $user->profile_photo_path;
        Storage::disk('public')->assertExists($first);

        $user = $service->updatePhoto($user, UploadedFile::fake()->image('b.jpg'));
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($user->profile_photo_path);
    }
}
