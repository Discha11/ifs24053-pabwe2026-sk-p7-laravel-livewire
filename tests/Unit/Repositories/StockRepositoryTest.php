<?php

namespace Tests\Unit\Repositories;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Repositories\StockRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private StockRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new StockRepository;
    }

    public function test_paginate_scopes_to_user_and_filters(): void
    {
        $user = User::factory()->create();
        Stock::factory()->for($user)->withQuantity(50)->create(['name' => 'Beras', 'sku' => 'BRS-1']);
        Stock::factory()->for($user)->withQuantity(0)->create(['name' => 'Gula', 'sku' => 'GLA-1']);
        Stock::factory()->create(['name' => 'Beras Orang Lain']);

        $this->assertSame(2, $this->repo->paginateForUser($user->id)->total());
        $this->assertSame(1, $this->repo->paginateForUser($user->id, 'Beras')->total());
        $this->assertSame(1, $this->repo->paginateForUser($user->id, 'GLA-1')->total());
        $this->assertSame(1, $this->repo->paginateForUser($user->id, '', 'out_of_stock')->total());
        $this->assertSame(0, $this->repo->paginateForUser($user->id, 'Beras', 'out_of_stock')->total());
    }

    public function test_all_for_user(): void
    {
        $user = User::factory()->create();
        Stock::factory()->count(2)->for($user)->create();
        Stock::factory()->create();

        $this->assertCount(2, $this->repo->allForUser($user->id));
    }

    public function test_create_update_delete(): void
    {
        $user = User::factory()->create();

        $stock = $this->repo->create($user->id, [
            'name' => 'Pulpen', 'quantity' => 5, 'unit' => 'pcs', 'min_stock' => 2, 'status' => 'available',
        ]);
        $this->assertSame($user->id, $stock->user_id);

        $updated = $this->repo->update($stock, ['name' => 'Pensil']);
        $this->assertSame('Pensil', $updated->name);

        $this->repo->delete($stock);
        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }
}
