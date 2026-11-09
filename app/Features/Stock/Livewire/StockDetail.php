<?php

namespace App\Features\Stock\Livewire;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class StockDetail extends Component
{
    use WithFileUploads;

    public Stock $stock;

    public string $name = '';

    public ?string $sku = null;

    public ?string $description = null;

    public $quantity = 0;

    public string $unit = 'pcs';

    public $price = null;

    public $min_stock = 5;

    public $cover = null;

    public bool $editing = false;

    public ?string $message = null;

    public function mount(Stock $stock): void
    {
        Gate::authorize('view', $stock);
        $this->stock = $stock;
        $this->fillForm();
    }

    public function startEdit(): void
    {
        $this->fillForm();
        $this->editing = true;
    }

    public function cancelEdit(): void
    {
        $this->editing = false;
    }

    public function save(StockService $service): void
    {
        Gate::authorize('update', $this->stock);

        $data = $this->validate(StockService::RULES);
        $this->stock = $service->update($this->stock, $data);
        $this->fillForm();
        $this->editing = false;
        $this->message = 'Stok berhasil diperbarui.';
    }

    public function uploadCover(StockService $service): void
    {
        Gate::authorize('update', $this->stock);

        $this->validate(['cover' => 'required|image|max:2048']);
        $this->stock = $service->uploadCover($this->stock, $this->cover);
        $this->reset('cover');
        $this->message = 'Cover berhasil diunggah.';
    }

    public function adjust(int $delta, StockService $service): void
    {
        Gate::authorize('update', $this->stock);

        $this->stock = $service->adjustQuantity($this->stock, $delta);
        $this->quantity = $this->stock->quantity;
    }

    public function render()
    {
        return view('livewire.stock.stock-detail');
    }

    private function fillForm(): void
    {
        $this->name = $this->stock->name;
        $this->sku = $this->stock->sku;
        $this->description = $this->stock->description;
        $this->quantity = $this->stock->quantity;
        $this->unit = $this->stock->unit;
        $this->price = $this->stock->price;
        $this->min_stock = $this->stock->min_stock;
    }
}
