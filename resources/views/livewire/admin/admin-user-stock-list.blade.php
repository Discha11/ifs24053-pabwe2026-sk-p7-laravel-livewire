<div class="space-y-4">
    <a href="{{ route('admin.users') }}" class="text-sm text-blue-600">← Kembali</a>
    <h1 class="text-2xl font-bold">Stok milik {{ $user->name }} <span class="text-sm font-normal text-slate-600">(hanya baca)</span></h1>

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
            <tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">SKU</th><th class="px-4 py-3">Qty</th>
                <th class="px-4 py-3">Harga</th><th class="px-4 py-3">Status</th></tr>
            </thead>
            <tbody class="divide-y">
            @forelse ($stocks as $stock)
                <tr wire:key="stock-{{ $stock->id }}">
                    <td class="px-4 py-3 font-medium">{{ $stock->name }}</td>
                    <td class="px-4 py-3">{{ $stock->sku ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $stock->quantity }} {{ $stock->unit }}</td>
                    <td class="px-4 py-3">{{ $stock->price !== null ? 'Rp '.number_format((float) $stock->price, 0, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs {{ $stock->status->badgeClass() }}">{{ $stock->status->label() }}</span></td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-600">User ini belum punya stok.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $stocks->links() }}
</div>
