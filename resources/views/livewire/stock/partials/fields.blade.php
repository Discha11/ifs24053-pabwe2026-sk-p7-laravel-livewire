@php
    $fields = [
        ['name', 'Nama', 'text'],
        ['sku', 'SKU', 'text'],
        ['quantity', 'Quantity', 'number'],
        ['unit', 'Satuan', 'text'],
        ['price', 'Harga', 'number'],
        ['min_stock', 'Stok minimum', 'number'],
    ];
@endphp
@foreach ($fields as [$field, $label, $type])
    <div>
        <label for="stock-field-{{ $field }}" class="mb-1 block text-sm font-medium">{{ $label }}</label>
        <input id="stock-field-{{ $field }}" type="{{ $type }}" wire:model="{{ $field }}" @if($type === 'number') step="any" min="0" @endif
               class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
        @error($field) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
@endforeach
@unless ($markdown)
    <div>
        <label for="stock-field-description" class="mb-1 block text-sm font-medium">Deskripsi (Markdown)</label>
        <textarea id="stock-field-description" wire:model="description" rows="4" class="w-full rounded border border-slate-300 px-3 py-2 text-sm"></textarea>
        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
@endunless
<p class="text-xs text-slate-600">Status dihitung otomatis dari quantity dan stok minimum.</p>
