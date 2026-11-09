<?php

namespace App\Features\Stock\Services;

use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StockService
{
    public const RULES = [
        'name' => 'required|string|max:255',
        'sku' => 'nullable|string|max:100',
        'description' => 'nullable|string',
        'quantity' => 'required|integer|min:0',
        'unit' => 'required|string|max:20',
        'price' => 'nullable|numeric|min:0',
        'min_stock' => 'required|integer|min:0',
    ];

    public function __construct(
        private readonly StockRepositoryInterface $repository,
        private readonly StockExcelService $excel,
    ) {}

    public function list(User $user, string $search = '', string $status = '', int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginateForUser($user->id, $search, $status, $perPage);
    }

    public function create(User $user, array $data): Stock
    {
        return $this->repository->create($user->id, $this->prepare($data));
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function createMany(User $user, array $rows): int
    {
        foreach ($rows as $row) {
            $this->create($user, $row);
        }

        return count($rows);
    }

    public function update(Stock $stock, array $data): Stock
    {
        return $this->repository->update($stock, $this->prepare($data));
    }

    public function delete(Stock $stock): void
    {
        if ($stock->cover_image_path) {
            Storage::disk('public')->delete($stock->cover_image_path);
        }

        $this->repository->delete($stock);
    }

    /**
     * Tambah/kurangi quantity (tidak boleh di bawah 0) dan sinkronkan status.
     */
    public function adjustQuantity(Stock $stock, int $delta): Stock
    {
        $quantity = max(0, $stock->quantity + $delta);

        return $this->repository->update($stock, [
            'quantity' => $quantity,
            'status' => StockStatus::fromQuantity($quantity, $stock->min_stock),
        ]);
    }

    public function uploadCover(Stock $stock, UploadedFile $file): Stock
    {
        if ($stock->cover_image_path) {
            Storage::disk('public')->delete($stock->cover_image_path);
        }

        return $this->repository->update($stock, [
            'cover_image_path' => $file->store('stock-covers', 'public'),
        ]);
    }

    public function export(User $user): string
    {
        return $this->excel->export($this->repository->allForUser($user->id));
    }

    public function import(User $user, string $path): int
    {
        return $this->createMany($user, $this->excel->parse($path));
    }

    private function prepare(array $data): array
    {
        $quantity = max(0, (int) ($data['quantity'] ?? 0));
        $minStock = max(0, (int) ($data['min_stock'] ?? 5));

        return [
            'name' => trim((string) $data['name']),
            'sku' => ($data['sku'] ?? '') === '' ? null : $data['sku'],
            'description' => ($data['description'] ?? '') === '' ? null : $data['description'],
            'quantity' => $quantity,
            'unit' => ($data['unit'] ?? '') === '' ? 'pcs' : $data['unit'],
            'price' => ($data['price'] ?? '') === '' ? null : $data['price'],
            'min_stock' => $minStock,
            'status' => StockStatus::fromQuantity($quantity, $minStock),
        ];
    }
}
