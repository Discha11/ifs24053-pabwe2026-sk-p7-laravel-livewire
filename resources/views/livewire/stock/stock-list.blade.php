<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Daftar Stok</h1>
        <div class="flex flex-wrap gap-2">
            <button wire:click="openCreate" class="rounded bg-slate-900 px-3 py-2 text-sm text-white">+ Tambah</button>
            <button wire:click="openAi" class="rounded bg-purple-600 px-3 py-2 text-sm text-white">✨ Generate AI</button>
            <button wire:click="exportExcel" class="rounded bg-green-800 px-3 py-2 text-sm text-white">Export Excel</button>
        </div>
    </div>

    @if ($message)
        <div class="rounded bg-green-100 px-4 py-2 text-sm text-green-800">{{ $message }}</div>
    @endif

    <form wire:submit="import" class="flex flex-wrap items-center gap-2 rounded bg-white p-3 text-sm shadow-sm">
        <span class="font-medium">Import Excel:</span>
        <input type="file" wire:model="importFile" accept=".xlsx,.xls" aria-label="Pilih file Excel untuk diimpor">
        <button type="submit" class="rounded bg-blue-600 px-3 py-1 text-white">Import</button>
        @error('importFile') <span class="text-red-600">{{ $message }}</span> @enderror
        <span class="text-slate-600">Kolom: name, sku, qty, unit, price, min_stock, status, description</span>
    </form>

    <div class="flex flex-wrap gap-2">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama / SKU…" aria-label="Cari nama atau SKU"
               class="w-64 rounded border border-slate-300 px-3 py-2 text-sm">
        <select wire:model.live="statusFilter" aria-label="Filter status stok" class="rounded border border-slate-300 px-3 py-2 text-sm">
            <option value="">Semua status</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="overflow-x-auto rounded bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-600">
            <tr>
                <th class="px-4 py-3">Nama</th><th class="px-4 py-3">SKU</th>
                <th class="px-4 py-3">Qty</th><th class="px-4 py-3">Harga</th>
                <th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y">
            @forelse ($stocks as $stock)
                <tr wire:key="stock-{{ $stock->id }}">
                    <td class="px-4 py-3 font-medium">{{ $stock->name }}</td>
                    <td class="px-4 py-3">{{ $stock->sku ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <button wire:click="adjust({{ $stock->id }}, -1)" aria-label="Kurangi stok {{ $stock->name }}" class="h-6 w-6 rounded bg-slate-200">−</button>
                            <span>{{ $stock->quantity }} {{ $stock->unit }}</span>
                            <button wire:click="adjust({{ $stock->id }}, 1)" aria-label="Tambah stok {{ $stock->name }}" class="h-6 w-6 rounded bg-slate-200">+</button>
                        </div>
                    </td>
                    <td class="px-4 py-3">{{ $stock->price !== null ? 'Rp '.number_format((float) $stock->price, 0, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded-full px-2 py-1 text-xs {{ $stock->status->badgeClass() }}">{{ $stock->status->label() }}</span>
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('stocks.show', $stock) }}" class="text-blue-600">Detail</a>
                        <button wire:click="openEdit({{ $stock->id }})" class="text-amber-800">Edit</button>
                        <button wire:click="delete({{ $stock->id }})" wire:confirm="Hapus stok ini?" class="text-red-600">Hapus</button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-slate-600">Belum ada stok.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $stocks->links() }}

    @if ($showForm)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4">
            <form wire:submit="save" class="max-h-full w-full max-w-lg space-y-3 overflow-y-auto rounded bg-white p-6 shadow-lg">
                <h2 class="text-lg font-bold">{{ $editingId ? 'Edit Stok' : 'Tambah Stok' }}</h2>
                @include('livewire.stock.partials.fields', ['markdown' => false])
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="closeForm" class="rounded bg-slate-200 px-3 py-2 text-sm">Batal</button>
                    <button type="submit" class="rounded bg-slate-900 px-3 py-2 text-sm text-white">Simpan</button>
                </div>
            </form>
        </div>
    @endif

    @if ($showAi)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4">
            <div class="max-h-full w-full max-w-2xl space-y-3 overflow-y-auto rounded bg-white p-6 shadow-lg">
                <h2 class="text-lg font-bold">Generate Item Stok dengan AI</h2>
                <form wire:submit="generateAi" class="flex gap-2">
                    <input type="text" wire:model="aiTopic" placeholder="mis. perlengkapan kantor, bahan kue…" aria-label="Topik item stok untuk AI"
                           class="flex-1 rounded border border-slate-300 px-3 py-2 text-sm">
                    <button type="submit" class="rounded bg-purple-600 px-3 py-2 text-sm text-white">
                        <span wire:loading.remove wire:target="generateAi">Generate</span>
                        <span wire:loading wire:target="generateAi">Memproses…</span>
                    </button>
                </form>
                @error('aiTopic') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                @if ($aiError) <p class="text-sm text-red-600">{{ $aiError }}</p> @endif

                @if ($aiItems)
                    <div class="divide-y rounded border">
                        @foreach ($aiItems as $i => $item)
                            <label class="flex items-start gap-3 p-3 text-sm" wire:key="ai-{{ $i }}">
                                <input type="checkbox" wire:model="aiSelected" value="{{ $i }}" class="mt-1">
                                <span>
                                    <span class="font-semibold">{{ $item['name'] }}</span>
                                    <span class="text-slate-600">· {{ $item['sku'] ?? '-' }} · {{ $item['quantity'] }} {{ $item['unit'] }}
                                        @if ($item['price']) · Rp {{ number_format($item['price'], 0, ',', '.') }} @endif
                                        · min {{ $item['min_stock'] }}</span>
                                    <span class="block text-slate-600">{{ $item['description'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="closeAi" class="rounded bg-slate-200 px-3 py-2 text-sm">Tutup</button>
                    @if ($aiItems)
                        <button type="button" wire:click="saveAi" class="rounded bg-slate-900 px-3 py-2 text-sm text-white">Simpan yang dipilih</button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
