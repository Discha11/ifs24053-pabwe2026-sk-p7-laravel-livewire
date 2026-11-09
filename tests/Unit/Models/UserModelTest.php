<?php

namespace Tests\Unit\Models;

use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles(): void
    {
        $this->assertTrue(User::factory()->admin()->make()->isAdmin());
        $this->assertFalse(User::factory()->make()->isAdmin());
        $this->assertTrue(User::factory()->make()->isMember());
        $this->assertFalse(User::factory()->admin()->make()->isMember());
    }

    public function test_has_many_stocks(): void
    {
        $user = User::factory()->create();
        Stock::factory()->count(2)->for($user)->create();

        $this->assertCount(2, $user->stocks);
    }

    public function test_password_is_hashed(): void
    {
        $user = User::factory()->create(['password' => 'rahasia']);

        $this->assertTrue(Hash::check('rahasia', $user->password));
    }

    public function test_profile_photo_url(): void
    {
        Storage::fake('public');

        $this->assertNull(User::factory()->make()->profilePhotoUrl());
        $this->assertStringContainsString('me.jpg', User::factory()->make(['profile_photo_path' => 'p/me.jpg'])->profilePhotoUrl());
    }
}
