<?php

namespace Tests\Feature\Stock;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Livewire\StockList;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockExcelService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class StockListLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_shows_only_own_stocks(): void
    {
        Stock::factory()->for($this->user)->create(['name' => 'Milik Saya']);
        Stock::factory()->create(['name' => 'Milik Orang Lain']);

        Livewire::actingAs($this->user)->test(StockList::class)
            ->assertSee('Milik Saya')
            ->assertDontSee('Milik Orang Lain');
    }

    public function test_search_and_filter(): void
    {
        Stock::factory()->for($this->user)->withQuantity(50)->create(['name' => 'Beras']);
        Stock::factory()->for($this->user)->withQuantity(0)->create(['name' => 'Gula']);

        Livewire::actingAs($this->user)->test(StockList::class)
            ->set('search', 'Beras')
            ->assertSee('Beras')->assertDontSee('Gula')
            ->set('search', '')
            ->set('statusFilter', StockStatus::OutOfStock->value)
            ->assertSee('Gula')->assertDontSee('Beras');
    }

    public function test_create_stock(): void
    {
        Livewire::actingAs($this->user)->test(StockList::class)
            ->call('openCreate')
            ->assertSet('showForm', true)
            ->set('name', 'Pulpen')
            ->set('sku', 'PLP-1')
            ->set('description', '**bagus**')
            ->set('quantity', 3)
            ->set('unit', 'box')
            ->set('price', '2500')
            ->set('min_stock', 5)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showForm', false);

        $stock = Stock::where('name', 'Pulpen')->firstOrFail();
        $this->assertSame($this->user->id, $stock->user_id);
        $this->assertSame(StockStatus::LowStock, $stock->status);
    }

    public function test_create_validation(): void
    {
        Livewire::actingAs($this->user)->test(StockList::class)
            ->call('openCreate')
            ->set('name', '')
            ->set('quantity', -1)
            ->call('save')
            ->assertHasErrors(['name', 'quantity']);
    }

    public function test_edit_stock(): void
    {
        $stock = Stock::factory()->for($this->user)->create();

        Livewire::actingAs($this->user)->test(StockList::class)
            ->call('openEdit', $stock->id)
            ->assertSet('name', $stock->name)
            ->set('name', 'Nama Baru')
            ->set('quantity', 0)
            ->call('save')
            ->assertHasNoErrors();

        $stock->refresh();
        $this->assertSame('Nama Baru', $stock->name);
        $this->assertSame(StockStatus::OutOfStock, $stock->status);
    }

    public function test_close_form_resets_state(): void
    {
        Livewire::actingAs($this->user)->test(StockList::class)
            ->call('openCreate')
            ->set('name', 'x')
            ->call('closeForm')
            ->assertSet('showForm', false)
            ->assertSet('name', '');
    }

    public function test_cannot_edit_or_delete_or_adjust_other_users_stock(): void
    {
        $other = Stock::factory()->create();

        $component = Livewire::actingAs($this->user)->test(StockList::class);
        $component->call('openEdit', $other->id)->assertForbidden();
        $component->call('delete', $other->id)->assertForbidden();
        $component->call('adjust', $other->id, 1)->assertForbidden();

        $this->assertDatabaseHas('stocks', ['id' => $other->id]);
    }

    public function test_save_edit_of_other_users_stock_is_forbidden(): void
    {
        $other = Stock::factory()->create();

        Livewire::actingAs($this->user)->test(StockList::class)
            ->set('editingId', $other->id)
            ->set('name', 'Hack')
            ->set('quantity', 1)
            ->set('unit', 'pcs')
            ->set('min_stock', 1)
            ->call('save')
            ->assertForbidden();
    }

    public function test_delete_stock(): void
    {
        $stock = Stock::factory()->for($this->user)->create();

        Livewire::actingAs($this->user)->test(StockList::class)->call('delete', $stock->id);

        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }

    public function test_adjust_quantity_syncs_status(): void
    {
        $stock = Stock::factory()->for($this->user)->withQuantity(6, 5)->create();

        $component = Livewire::actingAs($this->user)->test(StockList::class);

        $component->call('adjust', $stock->id, -1);
        $this->assertSame(StockStatus::LowStock, $stock->refresh()->status);

        $component->call('adjust', $stock->id, -5);
        $this->assertSame(0, $stock->refresh()->quantity);
        $this->assertSame(StockStatus::OutOfStock, $stock->status);

        $component->call('adjust', $stock->id, 20);
        $this->assertSame(StockStatus::Available, $stock->refresh()->status);
    }

    public function test_export_excel_downloads_file(): void
    {
        Stock::factory()->for($this->user)->create();

        Livewire::actingAs($this->user)->test(StockList::class)
            ->call('exportExcel')
            ->assertFileDownloaded();
    }

    public function test_import_excel(): void
    {
        $content = app(StockExcelService::class)->export(
            Stock::factory()->count(2)->for($this->user)->create()
        );
        Stock::query()->delete();

        Livewire::actingAs($this->user)->test(StockList::class)
            ->set('importFile', UploadedFile::fake()->createWithContent('stok.xlsx', $content))
            ->call('import')
            ->assertHasNoErrors();

        $this->assertSame(2, Stock::where('user_id', $this->user->id)->count());
    }

    public function test_import_requires_file(): void
    {
        Livewire::actingAs($this->user)->test(StockList::class)
            ->call('import')
            ->assertHasErrors(['importFile']);
    }

    public function test_ai_generate_and_save(): void
    {
        $client = $this->createStub(AiClientInterface::class);
        $client->method('generate')->willReturn(json_encode([
            ['name' => 'Kertas A4', 'sku' => 'KRT-1', 'description' => 'Kertas', 'quantity' => 10, 'unit' => 'rim', 'price' => 50000, 'min_stock' => 2],
            ['name' => 'Spidol', 'quantity' => 4],
        ]));
        $this->app->instance(AiClientInterface::class, $client);

        Livewire::actingAs($this->user)->test(StockList::class)
            ->call('openAi')
            ->assertSet('showAi', true)
            ->set('aiTopic', 'alat tulis kantor')
            ->call('generateAi')
            ->assertCount('aiItems', 2)
            ->assertSet('aiError', null)
            ->set('aiSelected', ['1'])
            ->call('saveAi')
            ->assertSet('showAi', false);

        $this->assertDatabaseHas('stocks', ['user_id' => $this->user->id, 'name' => 'Spidol']);
        $this->assertDatabaseMissing('stocks', ['name' => 'Kertas A4']);
    }

    public function test_ai_generate_handles_failure(): void
    {
        $client = $this->createStub(AiClientInterface::class);
        $client->method('generate')->willThrowException(new RuntimeException('boom'));
        $this->app->instance(AiClientInterface::class, $client);

        Livewire::actingAs($this->user)->test(StockList::class)
            ->set('aiTopic', 'apa saja')
            ->call('generateAi')
            ->assertSet('aiItems', [])
            ->assertSee('boom');
    }

    public function test_ai_topic_is_required_and_close_resets(): void
    {
        Livewire::actingAs($this->user)->test(StockList::class)
            ->call('openAi')
            ->call('generateAi')
            ->assertHasErrors(['aiTopic'])
            ->call('closeAi')
            ->assertSet('showAi', false);
    }
}
