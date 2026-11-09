<?php

namespace Tests\Unit\Services;

use App\Features\Admin\Services\AdminUserService;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_lists_only_members_with_counts(): void
    {
        User::factory()->admin()->create();
        $member = User::factory()->create(['name' => 'Anggota']);
        Stock::factory()->count(2)->for($member)->create();
        User::factory()->create(['name' => 'Lain']);

        $result = app(AdminUserService::class)->users();

        $this->assertSame(2, $result->total());
        $this->assertSame(2, $result->firstWhere('id', $member->id)->stocks_count);
    }

    public function test_users_search(): void
    {
        User::factory()->create(['name' => 'Alpha', 'username' => 'alpha1']);
        User::factory()->create(['name' => 'Bravo', 'username' => 'bravo1']);

        $service = app(AdminUserService::class);

        $this->assertSame(1, $service->users('Alpha')->total());
        $this->assertSame(1, $service->users('bravo1')->total());
    }

    public function test_stocks_of_user(): void
    {
        $member = User::factory()->create();
        Stock::factory()->for($member)->create(['name' => 'Beras']);
        Stock::factory()->create();

        $service = app(AdminUserService::class);

        $this->assertSame(1, $service->stocksOf($member)->total());
        $this->assertSame(0, $service->stocksOf($member, 'zzz')->total());
    }
}
