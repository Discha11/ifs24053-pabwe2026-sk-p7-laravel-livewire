<?php

namespace Tests\Unit\DTO;

use App\Features\Ai\DTO\GeneratedStockItem;
use PHPUnit\Framework\TestCase;

class GeneratedStockItemTest extends TestCase
{
    public function test_from_array_and_to_array(): void
    {
        $item = GeneratedStockItem::fromArray([
            'name' => 'Kertas', 'sku' => 'K-1', 'description' => 'A4', 'quantity' => '10',
            'unit' => 'rim', 'price' => '45000', 'min_stock' => 2,
        ]);

        $this->assertSame([
            'name' => 'Kertas', 'sku' => 'K-1', 'description' => 'A4', 'quantity' => 10,
            'unit' => 'rim', 'price' => 45000.0, 'min_stock' => 2,
        ], $item->toArray());
    }

    public function test_defaults(): void
    {
        $item = GeneratedStockItem::fromArray(['quantity' => -4]);

        $this->assertSame('Tanpa nama', $item->name);
        $this->assertNull($item->sku);
        $this->assertNull($item->description);
        $this->assertNull($item->price);
        $this->assertSame(0, $item->quantity);
        $this->assertSame('pcs', $item->unit);
        $this->assertSame(5, $item->minStock);
    }
}
