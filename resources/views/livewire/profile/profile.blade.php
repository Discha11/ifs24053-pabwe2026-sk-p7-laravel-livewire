<div class="mx-auto max-w-2xl space-y-6">
    <h1 class="text-2xl font-bold">Profil</h1>

    @if ($message)
        <div class="rounded bg-green-100 px-4 py-2 text-sm text-green-800">{{ $message }}</div>
    @endif

    <section class="flex items-center gap-4 rounded bg-white p-6 shadow-sm">
        @if ($user->profilePhotoUrl())
            <img src="{{ $user->profilePhotoUrl() }}" alt="Foto profil" class="h-20 w-20 rounded-full object-cover">
        @else
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-200 text-2xl">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
        @endif
        <form wire:submit="savePhoto" class="space-y-2 text-sm">
            <input type="file" wire:model="photo" accept="image/*" aria-label="Pilih foto profil">
            @error('photo') <p class="text-red-600">{{ $message }}</p> @enderror
            <button type="submit" class="rounded bg-blue-600 px-3 py-1 text-white">Ubah foto</button>
        </form>
    </section>

    <form wire:submit="saveProfile" class="space-y-3 rounded bg-white p-6 shadow-sm">
        <h2 class="font-semibold">Data diri</h2>
        @foreach ([['name','Nama','text'],['username','Username','text'],['email','Email','email']] as [$f,$l,$t])
            <div>
                <label for="profile-{{ $f }}" class="mb-1 block text-sm font-medium">{{ $l }}</label>
                <input id="profile-{{ $f }}" type="{{ $t }}" wire:model="{{ $f }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
                @error($f) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        @endforeach
        <button type="submit" class="rounded bg-slate-900 px-3 py-2 text-sm text-white">Simpan</button>
    </form>

    <form wire:submit="savePassword" class="space-y-3 rounded bg-white p-6 shadow-sm">
        <h2 class="font-semibold">Ubah password</h2>
        @foreach ([['current_password','Password saat ini'],['password','Password baru'],['password_confirmation','Konfirmasi password baru']] as [$f,$l])
            <div>
                <label for="profile-{{ $f }}" class="mb-1 block text-sm font-medium">{{ $l }}</label>
                <input id="profile-{{ $f }}" type="password" wire:model="{{ $f }}" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
                @error($f) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        @endforeach
        <button type="submit" class="rounded bg-slate-900 px-3 py-2 text-sm text-white">Ubah password</button>
    </form>
</div>
