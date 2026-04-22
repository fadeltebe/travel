<?php

use function Livewire\Volt\{state, computed, mount, on};
use App\Models\Route;

state([
    'showDeleteModal' => false,
    'deletingRouteId' => null,
    'search' => '',
]);

on([
    'route-saved' => function () {
        // Re-render
    },
]);

mount(function () {
    $user = auth()->user();
    if (!in_array($user->role->value ?? $user->role, ['superadmin', 'owner', 'super_admin'])) {
        abort(403, 'Akses Ditolak');
    }
});

$routes = computed(function () {
    return Route::with(['originAgent', 'destinationAgent'])
        ->when(
            $this->search,
            fn($q) => $q
                ->whereHas('originAgent', function ($sq) {
                    $sq->where('city', 'like', "%{$this->search}%")->orWhere('name', 'like', "%{$this->search}%");
                })
                ->orWhereHas('destinationAgent', function ($sq) {
                    $sq->where('city', 'like', "%{$this->search}%")->orWhere('name', 'like', "%{$this->search}%");
                }),
        )
        ->orderBy('created_at', 'desc')
        ->get();
});

$openCreate = function () {
    $this->dispatch('openCreateRoute');
};

$openEdit = function ($routeId) {
    $this->dispatch('openEditRoute', $routeId);
};

$confirmDelete = function ($routeId) {
    $this->deletingRouteId = $routeId;
    $this->showDeleteModal = true;
};

$deleteRoute = function () {
    if ($this->deletingRouteId) {
        $route = Route::withCount(['schedules'])->findOrFail($this->deletingRouteId);

        if ($route->schedules_count > 0) {
            $this->dispatch('notify', message: 'Tida bisa menghapus Rute karena memiliki jadwal terkait!', type: 'error');
            $this->showDeleteModal = false;
            $this->deletingRouteId = null;
            return;
        }

        $route->delete();
        $this->showDeleteModal = false;
        $this->deletingRouteId = null;
        $this->dispatch('notify', message: 'Rute berhasil dihapus!', type: 'success');
    }
};
?>

<div>
    <x-layouts.app title="Master Rute">
        <div class="max-w-3xl mx-auto pb-28">

            {{-- Header Gradient --}}
            <div class="relative overflow-hidden text-white mx-4 mt-2 mb-6 rounded-2xl px-4 pt-5 pb-10"
                style="background: linear-gradient(160deg, #0d9488 0%, #0f766e 50%, #115e59 100%);">
                <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full opacity-10" style="background: white;"></div>

                {{-- Judul & Tombol Kembali --}}
                <div class="flex items-center justify-between relative z-10">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('settings.index') }}" wire:navigate
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm border border-white/30 hover:bg-white/30 transition-colors">
                            <x-heroicon-o-arrow-left class="w-5 h-5 text-white" />
                        </a>
                        <div>
                            <h2 class="text-xl font-bold">Master Rute</h2>
                            <p class="text-[11px] text-teal-100 mt-0.5">Kelola Rute Perjalanan Bus</p>
                        </div>
                    </div>
                </div>

                {{-- Search --}}
                <div class="mt-5 relative z-10">
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Cari rute, kota asal atau tujuan..."
                            class="w-full pl-10 pr-4 py-3 bg-white rounded-xl text-sm focus:ring-2 focus:ring-white border-0 shadow-sm text-gray-900 focus:outline-none transition-all placeholder-gray-400">
                    </div>
                </div>
            </div>

            {{-- Routes List --}}
            <div class="px-4 -mt-4 space-y-4">
                @forelse ($this->routes as $r)
                    <div
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                        <div class="p-4 flex items-start gap-4">
                            {{-- Icon --}}
                            <div
                                class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                                <x-heroicon-o-arrows-right-left class="w-6 h-6" />
                            </div>
                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-900 truncate">
                                    {{ $r->originAgent->city ?? 'N/A' }} &rarr;
                                    {{ $r->destinationAgent->city ?? 'N/A' }}
                                </h3>
                                <p class="text-[11px] text-gray-500 mt-0.5">
                                    {{ $r->originAgent->name ?? '-' }} ke {{ $r->destinationAgent->name ?? '-' }}
                                </p>
                                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                    <span
                                        class="text-[10px] font-bold bg-teal-50 text-teal-600 px-2 py-0.5 rounded-lg flex items-center gap-1">
                                        <x-heroicon-s-currency-dollar class="w-3 h-3" />
                                        Rp {{ number_format($r->base_price, 0, ',', '.') }}
                                    </span>
                                    <span
                                        class="text-[10px] font-bold bg-gray-100 text-gray-600 px-2 py-0.5 rounded-lg">
                                        {{ $r->distance_km ?? 0 }} km
                                    </span>
                                </div>
                            </div>
                            {{-- Status --}}
                            <span
                                class="px-2.5 py-1 rounded-full text-[10px] font-black tracking-wide {{ $r->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                {{ $r->is_active ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                        </div>

                        {{-- Actions --}}
                        <div class="px-4 py-3 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/50">
                            <button wire:click="openEdit({{ $r->id }})"
                                class="px-4 py-2 text-xs font-bold text-blue-600 bg-blue-50 rounded-xl hover:bg-blue-100 active:scale-95 transition-all">
                                Edit
                            </button>
                            <button wire:click="confirmDelete({{ $r->id }})"
                                class="px-4 py-2 text-xs font-bold text-red-600 bg-red-50 rounded-xl hover:bg-red-100 active:scale-95 transition-all">
                                Hapus
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16">
                        <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                            <x-heroicon-o-arrows-right-left class="w-10 h-10 text-gray-300" />
                        </div>
                        <h3 class="font-bold text-gray-500">Belum ada rute</h3>
                        <p class="text-xs text-gray-400 mt-1">Tap tombol + untuk membuat rute baru</p>
                    </div>
                @endforelse
            </div>

            {{-- FAB Tambah --}}
            <button wire:click="openCreate"
                class="fixed right-4 z-40 w-14 h-14 rounded-full text-white flex items-center justify-center shadow-lg active:scale-95 transition-transform border-2 border-white/30"
                style="bottom: calc(72px + env(safe-area-inset-bottom)); background: linear-gradient(135deg, #0d9488, #0f766e); box-shadow: 0 4px 20px rgba(13,148,136,0.45);"
                title="Tambah Rute">
                <x-heroicon-o-plus class="w-7 h-7" />
            </button>
        </div>

        {{-- Component Form --}}
        <livewire:settings.routes.form />

        {{-- CONFIRMATION MODAL --}}
        @if ($showDeleteModal)
            <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4" x-data x-cloak>
                <div @click="$wire.set('showDeleteModal', false)" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm">
                </div>
                <div class="relative bg-white rounded-3xl shadow-2xl p-6 max-w-sm w-full z-10 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                        <x-heroicon-s-trash class="w-8 h-8 text-red-500" />
                    </div>
                    <h3 class="text-lg font-black text-gray-900 mb-1">Hapus Rute?</h3>
                    <p class="text-sm text-gray-500 mb-6">Data ini akan disembunyikan (soft delete).</p>
                    <div class="flex gap-3">
                        <button wire:click="$set('showDeleteModal', false)"
                            class="flex-1 py-3 rounded-xl border border-gray-200 text-gray-700 font-bold text-sm hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button wire:click="deleteRoute"
                            class="flex-1 py-3 rounded-xl bg-red-600 text-white font-bold text-sm shadow-lg shadow-red-200 hover:bg-red-700 active:scale-95 transition-all">
                            Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </x-layouts.app>
</div>
