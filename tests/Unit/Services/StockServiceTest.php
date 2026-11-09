<?php

namespace Tests\Unit\Services;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockExcelService;
use App\Features\Stock\Services\StockService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(StockService::class);
    }

    public function test_create_normalizes_data_and_sets_status(): void
    {
        $user = User::factory()->create();

        $stock = $this->service->create($user, [
            'name' => '  Pulpen  ', 'sku' => '', 'description' => '', 'quantity' => '4',
            'unit' => '', 'price' => '', 'min_stock' => '5',
        ]);

        $this->assertSame('Pulpen', $stock->name);
        $this->assertNull($stock->sku);
        $this->assertNull($stock->description);
        $this->assertNull($stock->price);
        $this->assertSame('pcs', $stock->unit);
        $this->assertSame(StockStatus::LowStock, $stock->status);
    }

    public function test_create_many_and_list(): void
    {
        $user = User::factory()->create();

        $count = $this->service->createMany($user, [
            ['name' => 'A', 'quantity' => 10, 'min_stock' => 2, 'unit' => 'pcs', 'sku' => 'A-1', 'price' => 100, 'description' => 'x'],
            ['name' => 'B', 'quantity' => 0],
        ]);

        $this->assertSame(2, $count);
        $this->assertSame(2, $this->service->list($user)->total());
        $this->assertSame(1, $this->service->list($user, 'A')->total());
    }

    public function test_update_recomputes_status(): void
    {
        $stock = Stock::factory()->withQuantity(50)->create();

        $updated = $this->service->update($stock, ['name' => 'Baru', 'quantity' => 0, 'min_stock' => 5]);

        $this->assertSame(StockStatus::OutOfStock, $updated->status);
    }

    public function test_adjust_quantity_never_goes_below_zero(): void
    {
        $stock = Stock::factory()->withQuantity(2, 5)->create();

        $this->assertSame(0, $this->service->adjustQuantity($stock, -10)->quantity);
        $this->assertSame(StockStatus::OutOfStock, $stock->refresh()->status);
        $this->assertSame(StockStatus::Available, $this->service->adjustQuantity($stock, 10)->status);
    }

    public function test_cover_upload_and_delete_cleanup(): void
    {
        Storage::fake('public');
        $stock = Stock::factory()->create();

        $stock = $this->service->uploadCover($stock, UploadedFile::fake()->image('a.jpg'));
        $first = $stock->cover_image_path;
        Storage::disk('public')->assertExists($first);

        $stock = $this->service->uploadCover($stock, UploadedFile::fake()->image('b.jpg'));
        Storage::disk('public')->assertMissing($first);

        $this->service->delete($stock);
        Storage::disk('public')->assertMissing($stock->cover_image_path);
        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }

    public function test_delete_without_cover(): void
    {
        $stock = Stock::factory()->create();

        $this->service->delete($stock);

        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }

    public function test_export_and_import_roundtrip(): void
    {
        $user = User::factory()->create();
        Stock::factory()->count(3)->for($user)->create();

        $binary = $this->service->export($user);
        $path = tempnam(sys_get_temp_dir(), 'imp');
        file_put_contents($path, $binary);

        $other = User::factory()->create();
        $this->assertSame(3, $this->service->import($other, $path));
        $this->assertSame(3, Stock::where('user_id', $other->id)->count());

        unlink($path);
        $this->assertInstanceOf(StockExcelService::class, app(StockExcelService::class));
    }
}
