<?php

namespace Database\Seeders;

use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => 'admin',
        ]);

        $member = User::factory()->create([
            'name' => 'Member Demo',
            'username' => 'member',
            'email' => 'member@example.com',
            'password' => 'member',
        ]);

        Stock::factory()->count(3)->for($member)->withQuantity(50)->create();
        Stock::factory()->for($member)->withQuantity(3)->create(['name' => 'Kertas A4']);
        Stock::factory()->for($member)->withQuantity(0)->create(['name' => 'Tinta Printer']);
    }
}
