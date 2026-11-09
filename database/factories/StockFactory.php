<?php

namespace Database\Factories;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stock>
 */
class StockFactory extends Factory
{
    protected $model = Stock::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(20, 100);
        $min = 5;

        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true),
            'sku' => strtoupper(fake()->bothify('SKU-####')),
            'description' => fake()->sentence(),
            'quantity' => $quantity,
            'unit' => 'pcs',
            'price' => fake()->numberBetween(1000, 500000),
            'min_stock' => $min,
            'status' => StockStatus::fromQuantity($quantity, $min),
        ];
    }

    public function withQuantity(int $quantity, int $min = 5): static
    {
        return $this->state(fn () => [
            'quantity' => $quantity,
            'min_stock' => $min,
            'status' => StockStatus::fromQuantity($quantity, $min),
        ]);
    }
}
