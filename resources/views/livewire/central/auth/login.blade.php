<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth-mobile')] class extends Component {
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();
        $this->redirect(route('central.dashboard', absolute: false), navigate: true);
    }
};
?>

<div class="min-h-[100dvh] bg-white flex flex-col items-center">
    <div class="w-full max-w-md relative">
        <img src="{{ global_asset('images/hero-login.webp') }}" alt="Travel Illustration" class="w-full object-cover">
    </div>

    <div
        class="w-full max-w-md px-6 py-6 flex flex-col bg-white -mt-10 rounded-t-[32px] shadow-xl shadow-slate-200/80 flex-1">
        <h2 class="text-[#004a8b] text-3xl font-extrabold mb-6 tracking-tight">Central Portal Login</h2>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form wire:submit="login" class="flex flex-col flex-1">
            <div class="bg-white rounded-[14px] border border-gray-200 shadow-sm overflow-hidden mb-4">
                <div class="relative group border-b border-gray-200">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                        <svg class="w-6 h-6 text-[#004a8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <input id="email" wire:model="form.email" type="email" required autofocus
                        class="pl-14 block w-full bg-transparent px-0 py-4 text-[15px] font-semibold text-gray-800 placeholder-gray-400 outline-none"
                        placeholder="Alamat Email" />
                </div>

                <div x-data="{ show: false }" class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                        <svg class="w-6 h-6 text-[#004a8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                            </path>
                        </svg>
                    </div>
                    <input x-bind:type="show ? 'text' : 'password'" wire:model="form.password" id="password" required
                        class="pl-14 pr-12 block w-full bg-transparent px-0 py-4 text-[15px] font-semibold text-gray-800 placeholder-gray-400 outline-none"
                        placeholder="Password" />

                    <button type="button" @click="show = !show"
                        class="absolute right-0 top-0 bottom-0 pr-5 flex items-center text-[#004a8b] focus:outline-none">
                        <svg x-show="!show" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                            </path>
                        </svg>
                        <svg x-show="show" style="display:none;" class="w-6 h-6" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="pt-2 pb-12 flex w-full mt-auto">
                <button type="submit"
                    class="w-full rounded-[14px] bg-[#1572c4] py-4 text-lg font-bold text-white transition hover:bg-[#004a8b]">
                    Login
                </button>
            </div>
        </form>
    </div>
</div>
