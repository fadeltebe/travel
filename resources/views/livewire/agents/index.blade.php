<?php

use function Livewire\Volt\{state, computed, mount, on};
use App\Models\Agent;

state([
    'showDeleteModal' => false,
    'deletingAgentId' => null,
    'search' => '',
]);

on(['agent-saved' => function () {
    // Re-render list
}]);

mount(function () {
    $user = auth()->user();
    if (!in_array($user->role->value ?? $user->role, ['superadmin', 'owner', 'super_admin'])) {
        abort(403, 'Akses Ditolak');
    }
});

$agents = computed(function () {
    return Agent::query()
        ->when($this->search, fn($q) => $q->where(function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('city', 'like', "%{$this->search}%")
              ->orWhere('code', 'like', "%{$this->search}%");
        }))
        ->orderBy('created_at', 'desc')
        ->get();
});

$openCreate = function () {
    $this->dispatch('openCreateAgent');
};

$openEdit = function ($agentId) {
    $this->dispatch('openEditAgent', $agentId);
};

$confirmDelete = function ($agentId) {
    $this->deletingAgentId = $agentId;
    $this->showDeleteModal = true;
};

$deleteAgent = function () {
    if ($this->deletingAgentId) {
        $agent = Agent::withCount(['users', 'originRoutes', 'destinationRoutes', 'bookings', 'originCargos'])->findOrFail($this->deletingAgentId);
        
        // Prevent deleting if it has related active data
        if ($agent->users_count > 0 || $agent->origin_routes_count > 0 || $agent->destination_routes_count > 0 || $agent->bookings_count > 0 || $agent->origin_cargos_count > 0) {
            $this->dispatch('notify', message: 'Tidak bisa menghapus agen yang memiliki data relasi (karyawan, rute, atau pesanan)!', type: 'error');
            $this->showDeleteModal = false;
            $this->deletingAgentId = null;
            return;
        }

        $agent->delete();
        $this->showDeleteModal = false;
        $this->deletingAgentId = null;
        $this->dispatch('notify', message: 'Cabang/Agen berhasil dihapus!', type: 'success');
    }
};
?>

<div>
    <x-layouts.app title="Manajemen Cabang & Agen">
        <div class="max-w-3xl mx-auto pb-28">

            {{-- Header Gradient --}}
            <div class="relative overflow-hidden text-white mx-4 mt-2 mb-6 rounded-2xl px-4 pt-5 pb-10"
                style="background: linear-gradient(160deg, #10b981 0%, #059669 50%, #047857 100%);">
                <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full opacity-10" style="background: white;"></div>

                {{-- Judul & Tombol Kembali --}}
                <div class="flex items-center justify-between relative z-10">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('settings.index') }}" wire:navigate
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm border border-white/30 hover:bg-white/30 transition-colors">
                            <x-heroicon-o-arrow-left class="w-5 h-5 text-white" />
                        </a>
                        <div>
                            <h2 class="text-xl font-bold">Cabang & Agen</h2>
                            <p class="text-[11px] text-emerald-100 mt-0.5">Kelola agen dan komisi per rute</p>
                        </div>
                    </div>
                </div>

                {{-- Search --}}
                <div class="mt-5 relative z-10">
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, kota atau kode..."
                            class="w-full pl-10 pr-4 py-3 bg-white rounded-xl text-sm focus:ring-2 focus:ring-emerald-50 focus:border-0 shadow-sm text-gray-900 focus:outline-none transition-all placeholder-gray-400">
                    </div>
                </div>
            </div>

            {{-- Agent List --}}
            <div class="px-4 -mt-4 space-y-4">
                @forelse ($this->agents as $agent)
                    <div
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                        <div class="p-4 flex items-start gap-4">
                            {{-- Icon --}}
                            <div
                                class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                <x-heroicon-o-map-pin class="w-6 h-6" />
                            </div>
                            
                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-900 truncate flex items-center gap-2">
                                    {{ $agent->name }}
                                </h3>
                                <div class="flex items-center gap-1.5 mt-0.5 text-xs text-gray-500">
                                    <span class="font-mono text-[10px] bg-gray-100 px-1.5 py-0.5 rounded text-gray-600">{{ $agent->code }}</span>
                                    <span>&bull;</span>
                                    <span class="truncate">{{ $agent->city }}</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                    <span class="text-[10px] font-bold bg-amber-50 text-amber-600 px-2 py-0.5 rounded-lg flex items-center gap-1">
                                        <x-heroicon-s-currency-dollar class="w-3 h-3" />
                                        Diskon: {{ number_format($agent->commission_rate, 2) }}%
                                    </span>
                                </div>
                            </div>

                            {{-- Status --}}
                            <span
                                class="px-2.5 py-1 rounded-full text-[10px] font-black tracking-wide shrink-0 {{ $agent->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                {{ $agent->is_active ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                        </div>
                        
                        @if($agent->address || $agent->phone)
                        <div class="px-4 py-2.5 bg-gray-50/80 border-t border-gray-100 flex flex-col gap-1">
                            @if($agent->phone)
                                <div class="flex items-center gap-2 text-xs text-gray-600">
                                    <x-heroicon-s-phone class="w-3.5 h-3.5 text-gray-400" />
                                    {{ $agent->phone }}
                                </div>
                            @endif
                            @if($agent->address)
                                <div class="flex items-start gap-2 text-xs text-gray-500 line-clamp-1 truncate">
                                    <x-heroicon-s-map class="w-3.5 h-3.5 text-gray-400 shrink-0 mt-0.5" />
                                    {{ $agent->address }}
                                </div>
                            @endif
                        </div>
                        @endif

                        {{-- Actions --}}
                        <div class="px-4 py-3 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/40">
                            <button wire:click="openEdit({{ $agent->id }})"
                                class="px-4 py-2 text-xs font-bold text-blue-600 bg-blue-50 rounded-xl hover:bg-blue-100 active:scale-95 transition-all">
                                Edit
                            </button>
                            <button wire:click="confirmDelete({{ $agent->id }})"
                                class="px-4 py-2 text-xs font-bold text-red-600 bg-red-50 rounded-xl hover:bg-red-100 active:scale-95 transition-all">
                                Hapus
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16">
                        <div
                            class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                            <x-heroicon-o-map-pin class="w-10 h-10 text-gray-300" />
                        </div>
                        <h3 class="font-bold text-gray-500">Belum ada agen/cabang</h3>
                        <p class="text-xs text-gray-400 mt-1">Tap tombol + untuk menambah agen baru</p>
                    </div>
                @endforelse
            </div>

            {{-- FAB Tambah Agent --}}
            <button wire:click="openCreate" class="fixed right-4 z-40 w-14 h-14 rounded-full text-white flex items-center justify-center shadow-lg active:scale-95 transition-transform border-2 border-white/30" style="bottom: calc(72px + env(safe-area-inset-bottom)); background: linear-gradient(135deg, #10b981, #047857); box-shadow: 0 4px 20px rgba(16,185,129,0.45);" title="Tambah Agen">
                <x-heroicon-o-plus class="w-7 h-7" />
            </button>
        </div>

        {{-- Livewire component for the Form Modal --}}
        <livewire:agents.form />

        {{-- ═══════════════════════════════════════════ --}}
        {{-- DELETE CONFIRMATION MODAL                  --}}
        {{-- ═══════════════════════════════════════════ --}}
        @if ($showDeleteModal)
            <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4" x-data x-cloak>
                <div @click="$wire.set('showDeleteModal', false)"
                    class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
                <div class="relative bg-white rounded-3xl shadow-2xl p-6 max-w-sm w-full z-10 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                        <x-heroicon-s-trash class="w-8 h-8 text-red-500" />
                    </div>
                    <h3 class="text-lg font-black text-gray-900 mb-1">Hapus Agen?</h3>
                    <p class="text-sm text-gray-500 mb-6">Data agen akan dihapus sementara (soft delete).</p>
                    <div class="flex gap-3">
                        <button wire:click="$set('showDeleteModal', false)"
                            class="flex-1 py-3 rounded-xl border border-gray-200 text-gray-700 font-bold text-sm hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button wire:click="deleteAgent"
                            class="flex-1 py-3 rounded-xl bg-red-600 text-white font-bold text-sm shadow-lg shadow-red-200 hover:bg-red-700 active:scale-95 transition-all">
                            Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </x-layouts.app>
</div>