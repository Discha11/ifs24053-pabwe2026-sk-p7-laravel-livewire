<?php

namespace App\Features\Profile\Livewire;

use App\Features\Profile\Services\ProfileService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Profile extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $username = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public $photo = null;

    public ?string $message = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
    }

    public function saveProfile(ProfileService $service): void
    {
        $user = Auth::user();

        $data = $this->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'alpha_dash', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $service->updateProfile($user, $data);
        $this->message = 'Profil berhasil diperbarui.';
    }

    public function savePassword(ProfileService $service): void
    {
        $this->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $service->updatePassword(Auth::user(), $this->password);
        $this->reset('current_password', 'password', 'password_confirmation');
        $this->message = 'Password berhasil diubah.';
    }

    public function savePhoto(ProfileService $service): void
    {
        $this->validate(['photo' => 'required|image|max:2048']);

        $service->updatePhoto(Auth::user(), $this->photo);
        $this->reset('photo');
        $this->message = 'Foto profil berhasil diubah.';
    }

    public function render()
    {
        return view('livewire.profile.profile', ['user' => Auth::user()->fresh()]);
    }
}
