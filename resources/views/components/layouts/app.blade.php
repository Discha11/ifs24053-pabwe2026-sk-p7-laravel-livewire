<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Stock Barang') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800 antialiased">
@auth
    <div class="flex min-h-screen">
        <aside class="w-60 bg-slate-900 text-slate-200 p-5 flex flex-col">
            <div class="text-lg font-bold text-white mb-6">📦 Stock Barang</div>
            <nav class="space-y-1 flex-1">
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.users') }}" class="block rounded px-3 py-2 hover:bg-slate-800">Daftar User</a>
                @else
                    <a href="{{ route('stocks.index') }}" class="block rounded px-3 py-2 hover:bg-slate-800">Stok Saya</a>
                @endif
                <a href="{{ route('profile') }}" class="block rounded px-3 py-2 hover:bg-slate-800">Profil</a>
            </nav>
            <div class="border-t border-slate-700 pt-4 text-sm">
                <div class="font-semibold text-white">{{ auth()->user()->name }}</div>
                <div class="text-slate-400 mb-3">{{ '@'.auth()->user()->username }} · {{ auth()->user()->role }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded bg-slate-700 px-3 py-2 hover:bg-slate-600">Keluar</button>
                </form>
            </div>
        </aside>
        <main class="flex-1 p-6 overflow-x-auto">{{ $slot }}</main>
    </div>
@else
    <main class="min-h-screen flex items-center justify-center p-6">{{ $slot }}</main>
@endauth
</body>
</html>
