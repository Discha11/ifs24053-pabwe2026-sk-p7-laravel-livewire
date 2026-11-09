<?php

namespace App\Features\Ai\DTO;

final class GeneratedStockItem
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $sku,
        public readonly ?string $description,
        public readonly int $quantity,
        public readonly string $unit,
        public readonly ?float $price,
        public readonly int $minStock,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) ($data['name'] ?? 'Tanpa nama'),
            sku: isset($data['sku']) ? (string) $data['sku'] : null,
            description: isset($data['description']) ? (string) $data['description'] : null,
            quantity: max(0, (int) ($data['quantity'] ?? 0)),
            unit: (string) ($data['unit'] ?? 'pcs'),
            price: isset($data['price']) ? (float) $data['price'] : null,
            minStock: max(0, (int) ($data['min_stock'] ?? 5)),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'price' => $this->price,
            'min_stock' => $this->minStock,
        ];
    }
}
