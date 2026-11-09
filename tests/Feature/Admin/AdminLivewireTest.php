<?php

namespace Tests\Feature\Admin;

use App\Features\Admin\Livewire\AdminUserStockList;
use App\Features\Admin\Livewire\UserList;
use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_list_shows_members_with_stock_counts(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Bos Besar']);
        $member = User::factory()->create(['name' => 'Anggota Satu']);
        Stock::factory()->count(3)->for($member)->create();

        Livewire::actingAs($admin)->test(UserList::class)
            ->assertSee('Anggota Satu')
            ->assertSee('3')
            ->assertDontSee('Bos Besar');
    }

    public function test_user_list_search(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Alpha']);
        User::factory()->create(['name' => 'Bravo']);

        Livewire::actingAs($admin)->test(UserList::class)
            ->set('search', 'Alpha')
            ->assertSee('Alpha')
            ->assertDontSee('Bravo');
    }

    public function test_user_stock_list_is_read_only_and_filterable(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();
        Stock::factory()->for($member)->withQuantity(20)->create(['name' => 'Barang Ada']);
        Stock::factory()->for($member)->withQuantity(0)->create(['name' => 'Barang Habis']);

        Livewire::actingAs($admin)->test(AdminUserStockList::class, ['user' => $member])
            ->assertSee('Barang Ada')
            ->assertSee('Barang Habis')
            ->assertDontSee('wire:click="delete')
            ->set('search', 'Habis')
            ->assertDontSee('Barang Ada')
            ->set('search', '')
            ->set('statusFilter', StockStatus::Available->value)
            ->assertSee('Barang Ada')
            ->assertDontSee('Barang Habis');
    }
}
