<?php

use function Livewire\Volt\{state, computed, mount};

mount(function () {
    $user = auth()->user();

    // Authorization: Hanya Owner / Super Admin yang boleh akses pengaturan
    if (!in_array($user->role->value ?? $user->role, ['superadmin', 'owner', 'super_admin'])) {
        abort(403, 'Akses Ditolak: Halaman ini khusus untuk Pimpinan.');
    }
});

$menus = computed(function () {
    return [
        [
            'title' => 'Profil Perusahaan',
            'desc' => 'Informasi umum, logo, kontak & NPWP.',
            'icon' => 'heroicon-o-building-office-2',
            'route' => '#',
            'color' => 'text-blue-600',
            'bg' => 'bg-blue-50',
        ],
        [
            'title' => 'Cabang & Agen',
            'desc' => 'Kelola data agen, alamat, & komisi.',
            'icon' => 'heroicon-o-map-pin',
            'route' => route('agents.index'),
            'color' => 'text-emerald-600',
            'bg' => 'bg-emerald-50',
        ],
        [
            'title' => 'Manajemen Armada',
            'desc' => 'Data bus, plat nomor, dan fasilitas.',
            'icon' => 'heroicon-o-truck',
            'route' => '#',
            'color' => 'text-indigo-600',
            'bg' => 'bg-indigo-50',
        ],
        [
            'title' => 'Layout Kursi Bus',
            'desc' => 'Atur denah tempat duduk armada.',
            'icon' => 'heroicon-o-squares-2x2',
            'route' => '#',
            'color' => 'text-purple-600',
            'bg' => 'bg-purple-50',
        ],
        [
            'title' => 'Pengguna & Akses',
            'desc' => 'Karyawan, supir, dan hak akses sistem.',
            'icon' => 'heroicon-o-users',
            'route' => '#',
            'color' => 'text-pink-600',
            'bg' => 'bg-pink-50',
        ],
        [
            'title' => 'Sistem Token & Dompet',
            'desc' => 'Atur saldo agen & metode tagihan bos.',
            'icon' => 'heroicon-o-wallet',
            'route' => route('wallets.index'),
            'color' => 'text-orange-600',
            'bg' => 'bg-orange-50',
        ],
    ];
});
?>

<div>
    <x-layouts.app title="Pengaturan Sistem">

        {{-- Header Background --}}
        <div class="relative overflow-hidden text-white mx-4 rounded-2xl px-5 pt-6 pb-10 mb-6"
            style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
            <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full opacity-5 bg-white"></div>
            <div class="absolute -bottom-8 -left-8 w-32 h-32 rounded-full opacity-5 bg-white"></div>

            <div class="relative z-10">
                <h1 class="text-2xl font-bold tracking-wide">Pengaturan</h1>
                <p class="text-sm text-gray-400 mt-1">Pusat kendali aplikasi & perusahaan.</p>
            </div>
        </div>

        {{-- Menu List (Mobile First) --}}
        <div class="px-4 -mt-3 space-y-3 pb-24 relative z-20">
            @foreach ($this->menus as $menu)
                <a href="{{ $menu['route'] }}" wire:navigate.hover
                    class="flex items-center gap-4 bg-white p-4 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md active:scale-[0.98] transition-all relative overflow-hidden group">

                    {{-- Icon Container --}}
                    <div
                        class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 transition-colors {{ $menu['bg'] }} {{ $menu['color'] }}">
                        <x-dynamic-component :component="$menu['icon']" class="w-6 h-6" />
                    </div>

                    {{-- Text Info --}}
                    <div class="flex-1 min-w-0">
                        <h3
                            class="text-sm font-bold text-gray-900 group-hover:text-primary-600 transition-colors truncate">
                            {{ $menu['title'] }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5 truncate pr-4">
                            {{ $menu['desc'] }}
                        </p>
                    </div>

                    {{-- Arrow --}}
                    <x-heroicon-s-chevron-right
                        class="w-5 h-5 text-gray-300 absolute right-4 group-hover:text-gray-400 transition-colors" />
                </a>
            @endforeach

            {{-- Logout Shortcut/Info System --}}
            <div class="mt-6 pt-4 border-t border-dashed border-gray-200">
                <p class="text-center text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                    Travel Management System 1.0 <br>
                    &copy; {{ date('Y') }} All Rights Reserved
                </p>
            </div>
        </div>

    </x-layouts.app>
</div>
