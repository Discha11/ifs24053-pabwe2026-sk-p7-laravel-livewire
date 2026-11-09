<?php

namespace Tests\Feature\Stock;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Livewire\StockDetail;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class StockDetailLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_and_edit(): void
    {
        $user = User::factory()->create();
        $stock = Stock::factory()->for($user)->create();

        Livewire::actingAs($user)->test(StockDetail::class, ['stock' => $stock])
            ->assertSee($stock->name)
            ->call('startEdit')
            ->assertSet('editing', true)
            ->set('name', 'Nama Baru')
            ->set('description', '# Judul')
            ->set('quantity', 2)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('editing', false);

        $this->assertSame('Nama Baru', $stock->refresh()->name);
        $this->assertSame(StockStatus::LowStock, $stock->status);
    }

    public function test_cancel_edit(): void
    {
        $user = User::factory()->create();
        $stock = Stock::factory()->for($user)->create();

        Livewire::actingAs($user)->test(StockDetail::class, ['stock' => $stock])
            ->call('startEdit')
            ->call('cancelEdit')
            ->assertSet('editing', false);
    }

    public function test_save_validation(): void
    {
        $user = User::factory()->create();
        $stock = Stock::factory()->for($user)->create();

        Livewire::actingAs($user)->test(StockDetail::class, ['stock' => $stock])
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_adjust_quantity(): void
    {
        $user = User::factory()->create();
        $stock = Stock::factory()->for($user)->withQuantity(1, 5)->create();

        Livewire::actingAs($user)->test(StockDetail::class, ['stock' => $stock])
            ->call('adjust', -5)
            ->assertSet('quantity', 0);

        $this->assertSame(StockStatus::OutOfStock, $stock->refresh()->status);
    }

    public function test_upload_cover_replaces_old_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $stock = Stock::factory()->for($user)->create();

        $component = Livewire::actingAs($user)->test(StockDetail::class, ['stock' => $stock]);

        $component->set('cover', UploadedFile::fake()->image('a.jpg'))->call('uploadCover')->assertHasNoErrors();
        $first = $stock->refresh()->cover_image_path;
        Storage::disk('public')->assertExists($first);

        $component->set('cover', UploadedFile::fake()->image('b.jpg'))->call('uploadCover');
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($stock->refresh()->cover_image_path);
    }

    public function test_cover_must_be_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $stock = Stock::factory()->for($user)->create();

        Livewire::actingAs($user)->test(StockDetail::class, ['stock' => $stock])
            ->set('cover', UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'))
            ->call('uploadCover')
            ->assertHasErrors(['cover']);
    }

    public function test_other_user_cannot_mount(): void
    {
        $stock = Stock::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(StockDetail::class, ['stock' => $stock])
            ->assertForbidden();
    }
}
