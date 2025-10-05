<?php

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Features;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string')]
    public string $username = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        $user = $this->validateCredentials();

        if (Features::canManageTwoFactorAuthentication() && $user->hasEnabledTwoFactorAuthentication()) {
            Session::put([
                'login.id' => $user->getKey(),
                'login.remember' => $this->remember,
            ]);

            $this->redirect(route('two-factor.login'), navigate: true);

            return;
        }

        Auth::login($user, $this->remember);

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Validate the user's credentials.
     */
    protected function validateCredentials(): User
    {
        $user = Auth::getProvider()->retrieveByCredentials(['username' => $this->username, 'password' => $this->password]);

        if (!$user || !Auth::getProvider()->validateCredentials($user, ['password' => $this->password])) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'username' => __('auth.failed'),
            ]);
        }

        return $user;
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->username) . '|' . request()->ip());
    }
}; ?>

<div class="min-h-screen flex flex-row">
    <aside class="flex flex-col justify-end bg-purple-dark w-[520px] py-6 px-8">
        <div class="flex flex-col gap-4">
            <img src="/images/logo.png" alt="Logo Pondok Pesantren Azmania Ponorogo" class="w-[100px] h-auto">
            <h1 class="text-5xl font-bold text-white max-w-[420px]">Sistem Jadwal Pondok Azmania Ponorogo</h1>
        </div>
    </aside>
    <aside class="flex flex-col justify-center pl-20 pr-10">
        <div class="mb-6">
            <h2 class="text-4xl mb-4 font-extrabold">Halo</h2>
            <p class="text-gray-600">Silahkan lakukan login untuk mengakses sistem</p>
        </div>
        <form wire:submit="login" class="flex flex-col gap-5">
            <flux:input wire:model="username" label="Username" type="text" size="lg" required autofocus
                autocomplete="username" :placeholder="__('Username')" />
            <div x-data="{ show: false }">
                <flux:input wire:model="password" :label="__('Password')" x-bind:type="show ? 'text' : 'password'"
                    size="lg" required autocomplete="current-password" :placeholder="__('Password')">

                    <x-slot name="iconTrailing">
                        <flux:button size="sm" variant="subtle" icon="eye-slash" x-show="show" class="-mr-1"
                            type="button" x-on:click="show = false" />

                            <flux:button size="sm" variant="subtle" icon="eye" x-show="!show" class="-mr-1"
                            type="button" x-on:click="show = true" />
                    </x-slot>
                </flux:input>
            </div>
            <flux:button type="submit" wire:action="login" class="!bg-primary !text-white w-fit mt-4 !px-6">
        </form>
        Login
        </flux:button>
    </aside>
</div>
