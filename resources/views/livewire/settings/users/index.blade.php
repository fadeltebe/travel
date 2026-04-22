<?php

use function Livewire\Volt\{state, computed, mount, on};
use App\Models\User;

state([
    'showDeleteModal' => false,
    'deletingUserId' => null,
    'search' => '',
]);

on([
    'user-saved' => function () {
        // Re-render user list
    },
]);

mount(function () {
    $user = auth()->user();
    // Hanya Super Admin yang bisa mengakses user management
    if ($user->role->value !== 'superadmin') {
        abort(403, 'Akses Ditolak: Halaman manajemen pengguna hanya untuk Super Admin.');
    }
});

$stats = computed(function () {
    return [
        'total' => User::count(),
        'active' => User::where('is_active', true)->count(),
        'inactive' => User::where('is_active', false)->count(),
        'superadmin' => User::where('role', 'superadmin')->count(),
        'owner' => User::where('role', 'owner')->count(),
        'admin' => User::where('role', 'admin')->count(),
        'driver' => User::where('role', 'driver')->count(),
    ];
});

$users = computed(function () {
    return User::with('agent')
        ->when(
            $this->search,
            fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%");
            }),
        )
        ->orderBy('created_at', 'desc')
        ->get();
});

$openCreate = function () {
    $this->dispatch('openCreateUser');
};

$openEdit = function ($userId) {
    $this->dispatch('openEditUser', $userId);
};

$confirmDelete = function ($userId) {
    $this->deletingUserId = $userId;
    $this->showDeleteModal = true;
};

$deleteUser = function () {
    if ($this->deletingUserId) {
        $userToDelete = User::findOrFail($this->deletingUserId);

        // Prevent deleting oneself
        if ($userToDelete->id === auth()->id()) {
            $this->dispatch('notify', message: 'Anda tidak bisa menghapus akun sendiri!', type: 'error');
            $this->showDeleteModal = false;
            $this->deletingUserId = null;
            return;
        }

        $userToDelete->delete();
        $this->showDeleteModal = false;
        $this->deletingUserId = null;
        $this->dispatch('notify', message: 'Pengguna berhasil dihapus!', type: 'success');
    }
};
?>

<div>
    <x-layouts.app title="Pengguna & Akses">
        <div class="max-w-3xl mx-auto pb-28">

            {{-- Header Gradient --}}
            <div class="relative overflow-hidden text-white mx-4 mt-2 mb-6 rounded-2xl px-4 pt-5 pb-10"
                style="background: linear-gradient(160deg, #db2777 0%, #be185d 50%, #e11d48 100%);">
                <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full opacity-10" style="background: white;"></div>

                {{-- Judul & Tombol Kembali --}}
                <div class="flex items-center justify-between relative z-10">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('settings.index') }}" wire:navigate
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm border border-white/30 hover:bg-white/30 transition-colors">
                            <x-heroicon-o-arrow-left class="w-5 h-5 text-white" />
                        </a>
                        <div>
                            <h2 class="text-xl font-bold">Pengguna & Akses</h2>
                            <p class="text-[11px] text-pink-100 mt-0.5">Kelola karyawan & hak akses</p>
                        </div>
                    </div>
                </div>

                {{-- Search --}}
                <div class="mt-5 relative z-10">
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Cari nama atau email..."
                            class="w-full pl-10 pr-4 py-3 bg-white rounded-xl text-sm focus:ring-2 focus:ring-white border-0 shadow-sm text-gray-900 focus:outline-none transition-all placeholder-gray-400">
                    </div>
                </div>
            </div>

            {{-- Statistics Cards --}}
            <div class="px-4 -mt-3 mb-6 grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 font-semibold">Total Pengguna</p>
                            <p class="text-2xl font-black text-gray-900 mt-1">{{ $this->stats['total'] }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                            <x-heroicon-o-users class="w-5 h-5 text-blue-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 font-semibold">Aktif</p>
                            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $this->stats['active'] }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-emerald-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 font-semibold">Nonaktif</p>
                            <p class="text-2xl font-black text-red-600 mt-1">{{ $this->stats['inactive'] }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center">
                            <x-heroicon-o-x-circle class="w-5 h-5 text-red-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 font-semibold">Super Admin</p>
                            <p class="text-2xl font-black text-purple-600 mt-1">{{ $this->stats['superadmin'] }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center">
                            <x-heroicon-o-shield-exclamation class="w-5 h-5 text-purple-600" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- User List --}}
            <div class="px-4 -mt-2 space-y-4">
                @forelse ($this->users as $u)
                    <div
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                        <div class="p-4 flex items-start gap-4">
                            {{-- Icon --}}
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 font-bold text-lg uppercase text-white"
                                style="background: {{ $u->role->value === 'superadmin' ? '#7c3aed' : ($u->role->value === 'owner' ? '#0369a1' : ($u->role->value === 'admin' ? '#ea580c' : '#6b7280')) }};">
                                {{ substr($u->name, 0, 1) }}
                            </div>
                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-gray-900 truncate">{{ $u->name }}</h3>
                                    @if ($u->id === auth()->id())
                                        <span
                                            class="text-[10px] font-black bg-amber-50 text-amber-600 px-2 py-0.5 rounded-lg">Anda</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $u->email }}</p>
                                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                    @php
                                        $roleColors = [
                                            'superadmin' => 'bg-purple-50 text-purple-600',
                                            'owner' => 'bg-cyan-50 text-cyan-600',
                                            'admin' => 'bg-orange-50 text-orange-600',
                                            'driver' => 'bg-gray-100 text-gray-600',
                                        ];
                                        $roleColor = $roleColors[$u->role->value] ?? 'bg-gray-50 text-gray-600';
                                    @endphp
                                    <span
                                        class="text-[10px] font-bold {{ $roleColor }} px-2 py-0.5 rounded-lg uppercase">
                                        {{ $u->role->label() ?? $u->role }}
                                    </span>
                                    @if ($u->agent)
                                        <span
                                            class="text-[10px] font-bold bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-lg truncate max-w-[120px]">
                                            📍 {{ $u->agent->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            {{-- Status --}}
                            <div class="flex flex-col items-end gap-2">
                                <span
                                    class="px-2.5 py-1 rounded-full text-[10px] font-black tracking-wide {{ $u->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $u->is_active ? '🟢 AKTIF' : '🔴 NONAKTIF' }}
                                </span>
                                <p class="text-[10px] text-gray-400">
                                    {{ $u->created_at->locale('id')->diffForHumans() }}
                                </p>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="px-4 py-3 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/50">
                            <button wire:click="openEdit({{ $u->id }})"
                                class="px-4 py-2 text-xs font-bold text-blue-600 bg-blue-50 rounded-xl hover:bg-blue-100 active:scale-95 transition-all flex items-center gap-1">
                                <x-heroicon-s-pencil-square class="w-4 h-4" />
                                Edit
                            </button>
                            @if ($u->id !== auth()->id())
                                <button wire:click="confirmDelete({{ $u->id }})"
                                    class="px-4 py-2 text-xs font-bold text-red-600 bg-red-50 rounded-xl hover:bg-red-100 active:scale-95 transition-all flex items-center gap-1">
                                    <x-heroicon-s-trash class="w-4 h-4" />
                                    Hapus
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16">
                        <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                            <x-heroicon-o-users class="w-10 h-10 text-gray-300" />
                        </div>
                        <h3 class="font-bold text-gray-500">Belum ada pengguna</h3>
                        <p class="text-xs text-gray-400 mt-1">Tap tombol + untuk menambah pengguna baru</p>
                    </div>
                @endforelse
            </div>

            {{-- FAB Tambah User --}}
            <button wire:click="openCreate"
                class="fixed right-4 z-40 w-14 h-14 rounded-full text-white flex items-center justify-center shadow-lg active:scale-95 transition-transform border-2 border-white/30"
                style="bottom: calc(72px + env(safe-area-inset-bottom)); background: linear-gradient(135deg, #db2777, #be185d); box-shadow: 0 4px 20px rgba(219,39,119,0.45);"
                title="Tambah Pengguna">
                <x-heroicon-o-plus class="w-7 h-7" />
            </button>
        </div>

        {{-- Livewire component for the Form Modal --}}
        <livewire:settings.user-form />

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
                    <h3 class="text-lg font-black text-gray-900 mb-1">Hapus Pengguna?</h3>
                    <p class="text-sm text-gray-500 mb-6">Pengguna yang dihapus tidak bisa dikembalikan.</p>
                    <div class="flex gap-3">
                        <button wire:click="$set('showDeleteModal', false)"
                            class="flex-1 py-3 rounded-xl border border-gray-200 text-gray-700 font-bold text-sm hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button wire:click="deleteUser"
                            class="flex-1 py-3 rounded-xl bg-red-600 text-white font-bold text-sm shadow-lg shadow-red-200 hover:bg-red-700 active:scale-95 transition-all">
                            Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </x-layouts.app>
</div>
