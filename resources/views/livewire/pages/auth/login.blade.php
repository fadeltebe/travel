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
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="min-h-screen bg-white flex flex-col items-center">
    <!-- Top Image Section (Using your uploaded travel bus & cargo image) -->
    <!-- Gambarnya memiliki border melengkung bergelombang di bawah secara bawaan -->
    <div class="w-full max-w-md relative">
        <img src="{{ asset('images/hero-login.webp') }}" alt="Travel & Cargo Illustration" class="w-full object-cover">
    </div>

    <!-- Login Form Section -->
    <div class="w-full max-w-md px-6 py-6 flex flex-col bg-white">
        <h2 class="text-[#004a8b] text-3xl font-extrabold mb-6 tracking-tight">Login</h2>
        
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form wire:submit="login" class="flex flex-col">
            <!-- Box Input (Username/Email & Password) -->
            <div class="bg-white rounded-[14px] border border-gray-200 shadow-sm overflow-hidden mb-4">
                
                <!-- Email -->
                <div class="relative group border-b border-gray-200">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                        <!-- Icon User -->
                        <svg class="w-6 h-6 text-[#004a8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <input wire:model="form.email" id="email" type="email" required autofocus
                        class="pl-14 block w-full bg-transparent px-0 py-4 text-[15px] font-semibold text-gray-800 placeholder-gray-400 !border-none !outline-none !shadow-none !ring-0 focus:!border-none focus:!outline-none focus:!shadow-none focus:!ring-0"
                        placeholder="Alamat Email" />
                </div>

                <!-- Password -->
                <div x-data="{ show: false }" class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                        <!-- Icon Lock -->
                        <svg class="w-6 h-6 text-[#004a8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <input x-bind:type="show ? 'text' : 'password'" wire:model="form.password" id="password" required
                        class="pl-14 pr-12 block w-full bg-transparent px-0 py-4 text-[15px] font-semibold text-gray-800 placeholder-gray-400 !border-none !outline-none !shadow-none !ring-0 focus:!border-none focus:!outline-none focus:!shadow-none focus:!ring-0"
                        placeholder="Password" />
                        
                    <button type="button" @click="show = !show" class="absolute right-0 top-0 bottom-0 pr-5 flex items-center text-[#004a8b] focus:outline-none focus:ring-0">
                        <!-- Eye Icon -->
                        <svg x-show="!show" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <svg x-show="show" style="display:none;" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Error Messages -->
            @if($errors->has('form.email') || $errors->has('form.password'))
                <div class="px-2 mb-2">
                    <x-input-error :messages="$errors->get('form.email')" class="mt-1" />
                    <x-input-error :messages="$errors->get('form.password')" class="mt-1" />
                </div>
            @endif

            <!-- Forgot Password Link -->
            <div class="flex justify-end mb-8">
                <a href="{{ route('password.request') }}" wire:navigate class="text-sm font-bold text-[#004a8b] hover:text-blue-800 underline decoration-2 underline-offset-4 decoration-[#004a8b]/30 hover:decoration-[#004a8b]">
                    Lupa Username/Password?
                </a>
            </div>

            <div class="pt-2 pb-12 flex w-full">
                <!-- Login Button -->
                <!-- x-data handles the dynamic blue color exactly like BRIMO (light blue if empty, dark blue if filled) -->
                <button type="submit" 
                    x-data="{ 
                        get isFilled() { 
                            return @entangle('form.email').live != '' && @entangle('form.password').live != ''; 
                        } 
                    }"
                    x-bind:class="isFilled ? 'bg-[#1572c4] hover:bg-[#004a8b] text-white shadow-lg' : 'bg-[#b8d4ee] text-white'"
                    class="w-full flex justify-center items-center py-4 rounded-[14px] text-lg font-bold transition-all duration-300 active:scale-[0.98]" 
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="login">Login</span>
                    <span wire:loading wire:target="login" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
