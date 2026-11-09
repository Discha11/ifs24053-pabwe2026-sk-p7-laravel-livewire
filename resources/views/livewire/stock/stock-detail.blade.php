<div class="mx-auto max-w-3xl space-y-4">
    @assets
    <link rel="stylesheet" href="https://uicdn.toast.com/editor/latest/toastui-editor.min.css">
    <script src="https://uicdn.toast.com/editor/latest/toastui-editor-all.min.js"></script>
    @endassets

    <a href="{{ route('stocks.index') }}" class="text-sm text-blue-600">← Kembali</a>

    @if ($message)
        <div class="rounded bg-green-100 px-4 py-2 text-sm text-green-800">{{ $message }}</div>
    @endif

    <div class="overflow-hidden rounded bg-white shadow-sm">
        @if ($stock->coverUrl())
            <img src="{{ $stock->coverUrl() }}" alt="Cover {{ $stock->name }}" class="h-56 w-full object-cover">
        @endif

        <div class="space-y-4 p-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold">{{ $stock->name }}</h1>
                    <p class="text-sm text-slate-500">SKU: {{ $stock->sku ?? '-' }}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs {{ $stock->status->badgeClass() }}">{{ $stock->status->label() }}</span>
            </div>

            <div class="flex items-center gap-3">
                <button wire:click="adjust(-1)" class="h-8 w-8 rounded bg-slate-200">−</button>
                <span class="text-lg font-semibold">{{ $stock->quantity }} {{ $stock->unit }}</span>
                <button wire:click="adjust(1)" class="h-8 w-8 rounded bg-slate-200">+</button>
                <span class="text-sm text-slate-500">(min {{ $stock->min_stock }})</span>
            </div>

            <p>Harga: <strong>{{ $stock->price !== null ? 'Rp '.number_format((float) $stock->price, 0, ',', '.') : '-' }}</strong></p>

            @if (! $editing)
                <div class="prose max-w-none text-sm">
                    {!! \Illuminate\Support\Str::markdown($stock->description ?? '*Belum ada deskripsi.*', ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                </div>
                <button wire:click="startEdit" class="rounded bg-slate-900 px-3 py-2 text-sm text-white">Edit</button>
            @endif
        </div>
    </div>

    <form wire:submit="uploadCover" class="flex flex-wrap items-center gap-2 rounded bg-white p-4 text-sm shadow-sm">
        <span class="font-medium">Cover:</span>
        <input type="file" wire:model="cover" accept="image/*">
        <button type="submit" class="rounded bg-blue-600 px-3 py-1 text-white">Unggah</button>
        @error('cover') <span class="text-red-600">{{ $message }}</span> @enderror
    </form>

    @if ($editing)
        <form wire:submit="save" class="space-y-3 rounded bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">Edit Stok</h2>
            @include('livewire.stock.partials.fields', ['markdown' => true])
            <div>
                <label class="mb-1 block text-sm font-medium">Deskripsi (Markdown)</label>
                <div wire:ignore><div id="stock-editor"></div></div>
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" wire:click="cancelEdit" class="rounded bg-slate-200 px-3 py-2 text-sm">Batal</button>
                <button type="submit" class="rounded bg-slate-900 px-3 py-2 text-sm text-white">Simpan</button>
            </div>
        </form>

        @script
        <script>
            const editor = new toastui.Editor({
                el: document.querySelector('#stock-editor'),
                height: '320px',
                initialEditType: 'markdown',
                previewStyle: 'tab',
                initialValue: $wire.description ?? '',
            });
            editor.on('change', () => { $wire.description = editor.getMarkdown(); });
        </script>
        @endscript
    @endif
</div>
