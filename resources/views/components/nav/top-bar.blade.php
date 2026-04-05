@props(['title' => 'Dashboard'])

<header class="fixed top-0 inset-x-0 z-40 text-white h-14 bg-blue-600 rounded-b-2xl shadow-sm border border-blue-700">
    <div class="flex items-center justify-between h-full px-4">

        {{-- Logo + Title --}}
        <div class="flex items-center gap-2">
            <div class="flex flex-col leading-none">
                <span class="text-xs text-blue-200">Sulteng</span>
                <span class="text-sm font-bold tracking-tight">Express</span>
            </div>
            <div class="w-px h-6 bg-blue-400 mx-1"></div>
            <span class="text-sm font-medium text-blue-100">{{ $title }}</span>
        </div>

        {{-- Right Actions --}}
        <div class="flex items-center gap-3">

            @auth
                {{-- Token/Balance Display --}}
                <a href="{{ route('wallets.index') }}"
                    class="flex items-center gap-2 px-3 py-1.5 bg-white/10 hover:bg-white/20 rounded-full border border-white/20 transition-all group">
                    <x-heroicon-s-ticket class="w-4 h-4 text-blue-200 group-hover:text-white" />
                    <span class="text-xs font-bold text-white">
                        {{ \App\Helpers\WalletHelper::formatBalance() }}
                    </span>
                    <x-heroicon-s-plus-circle class="w-3.5 h-3.5 text-blue-300 group-hover:text-white" />
                </a>

                {{-- Notification --}}
                <button class="relative">
                    <x-heroicon-o-bell class="w-6 h-6 text-blue-100" />
                    <span
                        class="absolute -top-1 -right-1 w-4 h-4 rounded-full
                                 bg-accent-500 text-white text-[10px] font-bold
                                 flex items-center justify-center">
                        3
                    </span>
                </button>
            @endauth

            {{-- Avatar + Dropdown --}}
            @auth
                <div class="relative" x-data="{ open: false }">

                    {{-- Avatar Button --}}
                    <button @click="open = !open"
                        class="w-8 h-8 rounded-full border-2 border-accent-400
                               bg-accent-600 flex items-center justify-center
                               text-sm font-bold text-white
                               active:scale-95 transition-transform">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </button>

                    {{-- Dropdown --}}
                    <div x-show="open" @click.outside="open = false" x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                        class="absolute right-0 top-11 w-56 bg-white rounded-2xl shadow-xl
                            border border-gray-100 py-2 z-50"
                        style="display: none;">

                        {{-- User Info --}}
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-800 truncate">
                                {{ auth()->user()->name }}
                            </p>
                            <p class="text-xs text-gray-400 truncate mt-0.5">
                                {{ auth()->user()->email }}
                            </p>
                            <span
                                class="inline-block mt-1.5 text-[10px] font-semibold
                                     px-2 py-0.5 rounded-full text-white"
                                style="background: #F57C00;">
                                {{ auth()->user()->role->label() }}
                            </span>
                        </div>

                        {{-- Menu Items --}}
                        <div class="py-1">
                            <a href="{{ route('wallets.index') }}"
                                class="flex items-center gap-3 px-4 py-2.5
                                  text-sm text-gray-700 hover:bg-gray-50
                                  active:bg-gray-100 transition-colors">
                                <x-heroicon-o-wallet class="w-4 h-4 text-gray-400" />
                                Dompet & Billing
                            </a>

                            <a href="{{ route('profile.edit') }}"
                                class="flex items-center gap-3 px-4 py-2.5
                                  text-sm text-gray-700 hover:bg-gray-50
                                  active:bg-gray-100 transition-colors">
                                <x-heroicon-o-user-circle class="w-4 h-4 text-gray-400" />
                                Profil Saya
                            </a>

                            <a href="#"
                                class="flex items-center gap-3 px-4 py-2.5
                                  text-sm text-gray-700 hover:bg-gray-50
                                  active:bg-gray-100 transition-colors">
                                <x-heroicon-o-cog-6-tooth class="w-4 h-4 text-gray-400" />
                                Pengaturan
                            </a>
                        </div>

                        {{-- Logout --}}
                        <div class="border-t border-gray-100 pt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-3 px-4 py-2.5
                                           text-sm text-red-600 hover:bg-red-50
                                           active:bg-red-100 transition-colors">
                                    <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                                    Keluar
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            @endauth

        </div>
    </div>
</header>
