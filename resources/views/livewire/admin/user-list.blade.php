<div class="space-y-4">
    <h1 class="text-2xl font-bold">Daftar User</h1>
    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama / username…"
           class="w-64 rounded border border-slate-300 px-3 py-2 text-sm">

    <div class="overflow-x-auto rounded bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
            <tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Username</th><th class="px-4 py-3">Email</th>
                <th class="px-4 py-3">Jumlah Stok</th><th class="px-4 py-3 text-right">Aksi</th></tr>
            </thead>
            <tbody class="divide-y">
            @forelse ($users as $user)
                <tr wire:key="user-{{ $user->id }}">
                    <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                    <td class="px-4 py-3">{{ $user->username }}</td>
                    <td class="px-4 py-3">{{ $user->email }}</td>
                    <td class="px-4 py-3">{{ $user->stocks_count }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.users.stocks', $user) }}" class="text-blue-600">Lihat stok</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada user.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</div>
