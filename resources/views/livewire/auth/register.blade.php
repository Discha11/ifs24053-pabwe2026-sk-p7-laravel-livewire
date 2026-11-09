<div class="w-full max-w-sm rounded-lg bg-white p-8 shadow">
    <h1 class="mb-6 text-2xl font-bold">Daftar</h1>
    <form wire:submit="submit" class="space-y-4">
        @foreach ([['name','Nama','text'],['username','Username','text'],['email','Email','email'],['password','Password','password'],['password_confirmation','Konfirmasi Password','password']] as [$field,$label,$type])
            <div>
                <label for="register-{{ $field }}" class="mb-1 block text-sm font-medium">{{ $label }}</label>
                <input id="register-{{ $field }}" type="{{ $type }}" wire:model="{{ $field }}"
                       class="w-full rounded border border-slate-300 px-3 py-2">
                @error($field) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        @endforeach
        <button id="register-submit" type="submit"
                class="w-full rounded bg-slate-900 px-4 py-2 font-medium text-white hover:bg-slate-700">Daftar</button>
    </form>
    <p class="mt-4 text-center text-sm">Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-600 underline">Masuk</a></p>
</div>
