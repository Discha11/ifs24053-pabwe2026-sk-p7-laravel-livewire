<?php

namespace App\Features\Stock\Livewire;

use App\Features\Ai\Services\OpenAiStockGeneratorService;
use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Throwable;

#[Layout('components.layouts.app')]
class StockList extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public ?string $sku = null;

    public ?string $description = null;

    public $quantity = 0;

    public string $unit = 'pcs';

    public $price = null;

    public $min_stock = 5;

    public $importFile = null;

    public bool $showAi = false;

    public string $aiTopic = '';

    public array $aiItems = [];

    public array $aiSelected = [];

    public ?string $aiError = null;

    public ?string $message = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $stock = Stock::findOrFail($id);
        Gate::authorize('update', $stock);

        $this->editingId = $stock->id;
        $this->name = $stock->name;
        $this->sku = $stock->sku;
        $this->description = $stock->description;
        $this->quantity = $stock->quantity;
        $this->unit = $stock->unit;
        $this->price = $stock->price;
        $this->min_stock = $stock->min_stock;
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->resetForm();
    }

    public function save(StockService $service): void
    {
        $data = $this->validate(StockService::RULES);

        if ($this->editingId) {
            $stock = Stock::findOrFail($this->editingId);
            Gate::authorize('update', $stock);
            $service->update($stock, $data);
            $this->message = 'Stok berhasil diperbarui.';
        } else {
            $service->create(Auth::user(), $data);
            $this->message = 'Stok berhasil ditambahkan.';
        }

        $this->resetForm();
    }

    public function delete(int $id, StockService $service): void
    {
        $stock = Stock::findOrFail($id);
        Gate::authorize('delete', $stock);
        $service->delete($stock);
        $this->message = 'Stok berhasil dihapus.';
    }

    public function adjust(int $id, int $delta, StockService $service): void
    {
        $stock = Stock::findOrFail($id);
        Gate::authorize('update', $stock);
        $service->adjustQuantity($stock, $delta);
    }

    public function exportExcel(StockService $service)
    {
        $content = $service->export(Auth::user());

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, 'stok-'.now()->format('Ymd-His').'.xlsx');
    }

    public function import(StockService $service): void
    {
        $this->validate(['importFile' => 'required|file|mimes:xlsx,xls|max:5120']);

        $count = $service->import(Auth::user(), $this->importFile->getRealPath());
        $this->reset('importFile');
        $this->message = "{$count} item berhasil diimpor.";
    }

    public function openAi(): void
    {
        $this->showAi = true;
    }

    public function closeAi(): void
    {
        $this->reset('showAi', 'aiTopic', 'aiItems', 'aiSelected', 'aiError');
    }

    public function generateAi(OpenAiStockGeneratorService $generator): void
    {
        $this->validate(['aiTopic' => 'required|string|max:255']);
        $this->aiError = null;

        try {
            $items = $generator->generate($this->aiTopic);
        } catch (Throwable $e) {
            $this->aiItems = [];
            $this->aiError = 'Gagal membuat item dengan AI: '.$e->getMessage();

            return;
        }

        $this->aiItems = array_map(fn ($item) => $item->toArray(), $items);
        $this->aiSelected = array_map('strval', array_keys($this->aiItems));
    }

    public function saveAi(StockService $service): void
    {
        $rows = [];
        foreach ($this->aiSelected as $index) {
            $rows[] = $this->aiItems[(int) $index];
        }

        $count = $service->createMany(Auth::user(), $rows);
        $this->closeAi();
        $this->message = "{$count} item hasil AI berhasil disimpan.";
    }

    public function render(StockService $service)
    {
        return view('livewire.stock.stock-list', [
            'stocks' => $service->list(Auth::user(), $this->search, $this->statusFilter),
            'statuses' => StockStatus::cases(),
        ]);
    }

    private function resetForm(): void
    {
        $this->reset('showForm', 'editingId', 'name', 'sku', 'description', 'quantity', 'unit', 'price', 'min_stock');
        $this->resetValidation();
    }
}
