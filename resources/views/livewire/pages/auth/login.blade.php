<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>
<div>
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2 tracking-tight">Selamat Datang</h2>
        {{-- <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Sign in to manage your travels</p> --}}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-6">
        <!-- Email Address -->
        <div class="space-y-1">
            <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                Alamat Email
            </label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors duration-200"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                </div>
                <input wire:model="form.email" id="email" type="email" name="email" required autofocus
                    autocomplete="username"
                    class="pl-10 block w-full rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 sm:text-sm py-3 transition-all duration-200"
                    placeholder="you@example.com" />
            </div>
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="space-y-1">
            <div class="flex items-center justify-between">
                <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Password
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate
                        class="text-xs font-bold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 transition-colors">
                        Forgot password?
                    </a>
                @endif
            </div>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors duration-200"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <input wire:model="form.password" id="password" type="password" name="password" required
                    autocomplete="current-password"
                    class="pl-10 block w-full rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 sm:text-sm py-3 transition-all duration-200"
                    placeholder="••••••••" />
            </div>
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        {{-- <div class="flex items-center pt-1">
            <input wire:model="form.remember" id="remember" type="checkbox" 
                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 transition-colors shadow-sm">
            <label for="remember" class="ml-2 block text-sm font-medium text-gray-700 dark:text-gray-300 select-none">
                Remember me
            </label>
        </div> --}}

        <div class="pt-2">
            <button type="submit"
                class="w-full flex justify-center items-center py-3.5 px-4 border border-transparent rounded-2xl shadow-lg shadow-indigo-500/30 text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0"
                wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="login">Log In</span>
                <span wire:loading wire:target="login" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Signing in...
                </span>
            </button>
        </div>

        {{-- @if (Route::has('register'))
            <div class="mt-6 text-center text-sm pt-2 border-t border-gray-100 dark:border-gray-800">
                <span class="text-gray-600 dark:text-gray-400 font-medium">Don't have an account?</span>
                <a href="{{ route('register') }}" wire:navigate class="font-bold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 transition-colors ml-1">
                    Sign up
                </a>
            </div>
        @endif --}}
    </form>
</div>
