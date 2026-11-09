<?php

namespace App\Features\Auth\Livewire;

use App\Features\Auth\Actions\LoginUserAction;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Login extends Component
{
    public string $login = '';

    public string $password = '';

    public bool $remember = false;

    public function submit(LoginUserAction $action)
    {
        $this->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        if (! $action->execute($this->login, $this->password, $this->remember)) {
            $this->addError('login', 'Username/email atau password salah.');

            return null;
        }

        session()->regenerate();

        return $this->redirect(Auth::user()->isAdmin() ? route('admin.users') : route('stocks.index'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
