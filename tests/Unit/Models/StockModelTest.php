<?php

namespace Tests\Unit\Models;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StockModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_and_relation(): void
    {
        $stock = Stock::factory()->create(['price' => 1500, 'quantity' => 7]);

        $this->assertInstanceOf(StockStatus::class, $stock->status);
        $this->assertSame(7, $stock->quantity);
        $this->assertSame('1500.00', $stock->price);
        $this->assertInstanceOf(User::class, $stock->user);
    }

    public function test_cover_url(): void
    {
        Storage::fake('public');

        $this->assertNull(Stock::factory()->make()->coverUrl());
        $this->assertStringContainsString('x.jpg', Stock::factory()->make(['cover_image_path' => 'covers/x.jpg'])->coverUrl());
    }

    public function test_cascade_delete_with_user(): void
    {
        $stock = Stock::factory()->create();
        $stock->user->delete();

        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }
}
