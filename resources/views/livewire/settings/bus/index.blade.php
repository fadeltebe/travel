<?php

use function Livewire\Volt\{state, computed, mount, on};
use App\Models\Bus;

state([
    'showDeleteModal' => false,
    'deletingBusId' => null,
    'search' => '',
]);

on([
    'bus-saved' => function () {
        // This will implicitly cause a component re-render so the bus list is updated.
    },
]);

mount(function () {
    $user = auth()->user();
    if (!in_array($user->role->value ?? $user->role, ['superadmin', 'owner', 'super_admin'])) {
        abort(403, 'Akses Ditolak');
    }
});

$buses = computed(function () {
    return Bus::with(['busLayout.seats' => fn($q) => $q->orderBy('row')->orderBy('column')])
        ->when(
            $this->search,
            fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")->orWhere('plate_number', 'like', "%{$this->search}%");
            }),
        )
        ->orderBy('created_at', 'desc')
        ->get();
});

$openCreate = function () {
    $this->dispatch('openCreateBus');
};

$openEdit = function ($busId) {
    $this->dispatch('openEditBus', $busId);
};

$confirmDelete = function ($busId) {
    $this->deletingBusId = $busId;
    $this->showDeleteModal = true;
};

$deleteBus = function () {
    if ($this->deletingBusId) {
        Bus::findOrFail($this->deletingBusId)->delete();
        $this->showDeleteModal = false;
        $this->deletingBusId = null;
        $this->dispatch('notify', message: 'Armada berhasil dihapus!', type: 'success');
    }
};
?>

<div>
    <x-layouts.app title="Manajemen Armada">
        <div class="max-w-3xl mx-auto pb-28">

            {{-- Header Gradient --}}
            <div class="relative overflow-hidden text-white mx-4 mt-2 mb-6 rounded-2xl px-4 pt-5 pb-10"
                style="background: linear-gradient(160deg, #4338ca 0%, #4f46e5 50%, #6366f1 100%);">
                <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full opacity-10" style="background: white;"></div>

                {{-- Judul & Tombol Kembali --}}
                <div class="flex items-center justify-between relative z-10">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('settings.index') }}" wire:navigate
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm border border-white/30 hover:bg-white/30 transition-colors">
                            <x-heroicon-o-arrow-left class="w-5 h-5 text-white" />
                        </a>
                        <div>
                            <h2 class="text-xl font-bold">Manajemen Armada</h2>
                            <p class="text-[11px] text-indigo-100 mt-0.5">Kelola bus & layout kursi</p>
                        </div>
                    </div>
                </div>

                {{-- Search --}}
                <div class="mt-5 relative z-10">
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Cari nama bus atau plat..."
                            class="w-full pl-10 pr-4 py-3 bg-white rounded-xl text-sm focus:ring-2 focus:ring-white border-0 shadow-sm text-gray-900 focus:outline-none transition-all placeholder-gray-400">
                    </div>
                </div>
            </div>

            {{-- Bus List --}}
            <div class="px-4 -mt-4 space-y-4">
                @forelse ($this->buses as $bus)
                    <div
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                        <div class="p-4 flex items-start gap-4">
                            {{-- Icon --}}
                            <div
                                class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                                <x-heroicon-s-truck class="w-6 h-6" />
                            </div>
                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-900 truncate">
                                    {{ $bus->name ?: 'Bus ' . $bus->plate_number }}</h3>
                                <p class="text-xs text-gray-500 mt-0.5 font-mono">{{ $bus->plate_number }}</p>
                                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                    @if ($bus->brand)
                                        <span
                                            class="text-[10px] font-bold bg-gray-100 text-gray-600 px-2 py-0.5 rounded-lg">{{ $bus->brand }}</span>
                                    @endif
                                    @if ($bus->type)
                                        <span
                                            class="text-[10px] font-bold bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-lg">{{ $bus->type }}</span>
                                    @endif
                                </div>
                            </div>
                            {{-- Status --}}
                            <span
                                class="px-2.5 py-1 rounded-full text-[10px] font-black tracking-wide {{ $bus->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                {{ $bus->is_active ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                        </div>

                        {{-- Layout Info --}}
                        @if ($bus->busLayout)
                            <div
                                class="px-4 bg-gray-50/80 border-t border-gray-100 flex items-center gap-2 justify-between">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-squares-2x2 class="w-4 h-4 text-purple-500" />
                                    <span class="text-xs font-bold text-gray-700">{{ $bus->busLayout->name }}</span>
                                    <span class="text-xs text-gray-400">·</span>
                                    <span class="text-xs font-black text-purple-600">{{ $bus->total_seats }}
                                        kursi</span>
                                </div>

                                <div class="px-4 py-3 border-t border-gray-100 flex justify-end gap-2">
                                    <button wire:click="openEdit({{ $bus->id }})"
                                        class="px-4 py-2 text-xs font-bold text-blue-600 bg-blue-50 rounded-xl hover:bg-blue-100 active:scale-95 transition-all">
                                        Edit
                                    </button>
                                    <button wire:click="confirmDelete({{ $bus->id }})"
                                        class="px-4 py-2 text-xs font-bold text-red-600 bg-red-50 rounded-xl hover:bg-red-100 active:scale-95 transition-all">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="px-4 py-2.5 bg-amber-50/60 border-t border-amber-100">
                                <p class="text-[10px] font-bold text-amber-600 flex items-center gap-1.5">
                                    <x-heroicon-s-exclamation-triangle class="w-3.5 h-3.5" />
                                    Belum ada layout kursi
                                </p>
                            </div>
                        @endif

                        {{-- Actions --}}

                    </div>
                @empty
                    <div class="text-center py-16">
                        <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                            <x-heroicon-o-truck class="w-10 h-10 text-gray-300" />
                        </div>
                        <h3 class="font-bold text-gray-500">Belum ada armada</h3>
                        <p class="text-xs text-gray-400 mt-1">Tap tombol + untuk menambah bus baru</p>
                    </div>
                @endforelse
            </div>

            {{-- FAB Tambah Bus --}}
            <button wire:click="openCreate"
                class="fixed right-4 z-40 w-14 h-14 rounded-full text-white flex items-center justify-center shadow-lg active:scale-95 transition-transform border-2 border-white/30"
                style="bottom: calc(72px + env(safe-area-inset-bottom)); background: linear-gradient(135deg, #4338ca, #4f46e5); box-shadow: 0 4px 20px rgba(79,70,229,0.45);"
                title="Tambah bus">
                <x-heroicon-o-plus class="w-7 h-7" />
            </button>
        </div>

        {{-- Livewire component for the Form Modal --}}
        <livewire:settings.bus.form />

        {{-- ═══════════════════════════════════════════ --}}
        {{-- DELETE CONFIRMATION MODAL                  --}}
        {{-- ═══════════════════════════════════════════ --}}
        @if ($showDeleteModal)
            <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4" x-data x-cloak>
                <div @click="$wire.set('showDeleteModal', false)" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm">
                </div>
                <div class="relative bg-white rounded-3xl shadow-2xl p-6 max-w-sm w-full z-10 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                        <x-heroicon-s-trash class="w-8 h-8 text-red-500" />
                    </div>
                    <h3 class="text-lg font-black text-gray-900 mb-1">Hapus Armada?</h3>
                    <p class="text-sm text-gray-500 mb-6">Bus yang dihapus tidak bisa dikembalikan.</p>
                    <div class="flex gap-3">
                        <button wire:click="$set('showDeleteModal', false)"
                            class="flex-1 py-3 rounded-xl border border-gray-200 text-gray-700 font-bold text-sm hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button wire:click="deleteBus"
                            class="flex-1 py-3 rounded-xl bg-red-600 text-white font-bold text-sm shadow-lg shadow-red-200 hover:bg-red-700 active:scale-95 transition-all">
                            Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </x-layouts.app>
</div>
