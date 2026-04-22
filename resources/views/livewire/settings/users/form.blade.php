<?php

use function Livewire\Volt\{state, on, rules, with};
use App\Models\User;
use App\Models\Agent;
use App\Enums\Role;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Hash;

state([
    'isOpen' => false,
    'userId' => null,

    // Form fields
    'name' => '',
    'email' => '',
    'password' => '',
    'role' => '',
    'agent_id' => null,
    'is_active' => true,
]);

$agents = function () {
    return Agent::where('is_active', true)->orderBy('name')->get();
};

with(fn() => ['agents' => $this->agents()]);

rules(function () {
    return [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $this->userId],
        'password' => $this->userId ? ['nullable', 'min:8'] : ['required', 'min:8'],
        'role' => ['required', new Enum(Role::class)],
        'agent_id' => [
            'nullable',
            // If role is admin, agent_id should be required
            function ($attribute, $value, $fail) {
                if ($this->role === 'admin' && empty($value)) {
                    $fail('Cabang/Agen wajib diisi untuk role Admin.');
                }
            },
        ],
        'is_active' => ['boolean'],
    ];
});

on([
    'openCreateUser' => function () {
        $this->reset('userId', 'name', 'email', 'password', 'role', 'agent_id');
        $this->is_active = true;
        $this->resetValidation();
        $this->isOpen = true;
    },
]);

on([
    'openEditUser' => function ($userId) {
        $this->resetValidation();
        $user = User::findOrFail($userId);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role->value ?? $user->role;
        $this->agent_id = $user->agent_id;
        $this->is_active = $user->is_active;
        $this->password = '';

        $this->isOpen = true;
    },
]);

$closeModal = function () {
    $this->isOpen = false;
};

$save = function () {
    $validated = $this->validate();

    // If role is not admin, ensure agent_id is null
    if ($this->role !== 'admin') {
        $validated['agent_id'] = null;
    }

    if (!empty($validated['password'])) {
        // Assume model handles hashing via casts, or we can explicity hash:
        $validated['password'] = Hash::make($validated['password']);
    } else {
        unset($validated['password']);
    }

    if ($this->userId) {
        User::findOrFail($this->userId)->update($validated);
        $this->dispatch('notify', message: 'Pengguna berhasil diperbarui!', type: 'success');
    } else {
        User::create($validated);
        $this->dispatch('notify', message: 'Pengguna berhasil ditambahkan!', type: 'success');
    }

    $this->dispatch('user-saved');
    $this->isOpen = false;
};
?>

<div>
    @if ($isOpen)
        <div class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center p-0 sm:p-4"
            x-data="{
                show: false,
                init() {
                    setTimeout(() => this.show = true, 10);
                    document.body.style.overflow = 'hidden';
                },
                close() {
                    this.show = false;
                    setTimeout(() => $wire.closeModal(), 300);
                    document.body.style.overflow = '';
                }
            }" x-on:keydown.escape.window="close()">

            {{-- Backdrop --}}
            <div x-show="show" x-transition.opacity.duration.300ms @click="close()"
                class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm shadow-2xl">
            </div>

            {{-- Modal Panel --}}
            <div x-show="show" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-8 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-8 sm:scale-95"
                class="relative bg-white w-full sm:max-w-lg rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">

                {{-- Handle untuk mobile swipe --}}
                <div class="w-full flex justify-center pt-3 pb-1 sm:hidden absolute top-0 z-20">
                    <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
                </div>

                {{-- Header Modal --}}
                <div class="px-6 py-4 border-b border-gray-100 relative z-10 bg-white sm:pt-6 pt-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-black text-gray-900">
                                {{ $userId ? 'Edit Pengguna' : 'Tambah Pengguna' }}
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">Lengkapi form identitas & hak akses user.</p>
                        </div>
                        <button @click="close()"
                            class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors">
                            <x-heroicon-s-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                {{-- Body Modal (Scrollable) --}}
                <div class="p-6 overflow-y-auto custom-scrollbar flex-1 relative z-10 bg-white">
                    <form wire:submit="save" id="userForm" class="space-y-5">

                        {{-- Name --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Nama Lengkap</label>
                            <input type="text" wire:model="name"
                                class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors text-sm"
                                placeholder="Contoh: Budi Santoso">
                            @error('name')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Alamat Email</label>
                            <input type="email" wire:model="email"
                                class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors text-sm"
                                placeholder="Contoh: budi@travel.com">
                            @error('email')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Password
                                {{ $userId ? '(Kosongkan jika tidak diubah)' : '' }}</label>
                            <input type="password" wire:model="password"
                                class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors text-sm"
                                placeholder="Minimal 8 karakter">
                            @error('password')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Role --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Peran / Hak Akses</label>
                            <div class="relative">
                                <select wire:model.live="role"
                                    class="w-full pl-4 pr-10 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors text-sm appearance-none">
                                    <option value="">Pilih Hak Akses...</option>
                                    @foreach (App\Enums\Role::cases() as $r)
                                        <option value="{{ $r->value }}">{{ $r->label() }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <x-heroicon-s-chevron-down class="w-4 h-4 text-gray-400" />
                                </div>
                            </div>
                            @error('role')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Agent (Only required if admin) --}}
                        @if ($role === 'admin')
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Cabang / Agen</label>
                                <div class="relative">
                                    <select wire:model="agent_id"
                                        class="w-full pl-4 pr-10 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors text-sm appearance-none">
                                        <option value="">Pilih Cabang / Agen...</option>
                                        @foreach ($agents as $agent)
                                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <x-heroicon-s-chevron-down class="w-4 h-4 text-gray-400" />
                                    </div>
                                </div>
                                @error('agent_id')
                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        {{-- Status Aktif --}}
                        <div
                            class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50/50 mt-2">
                            <div>
                                <p class="text-sm font-bold text-gray-900">Status Akun</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">Aktifkan untuk memberikan akses sistem</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500">
                                </div>
                            </label>
                        </div>
                    </form>
                </div>

                {{-- Footer Modal --}}
                <div class="p-4 sm:p-6 border-t border-gray-100 bg-gray-50 relative z-10 shrink-0">
                    <div class="flex gap-3">
                        <button type="button" @click="close()"
                            class="flex-1 py-3 px-4 rounded-xl border border-gray-200 text-gray-700 font-bold text-sm hover:bg-gray-100 active:scale-95 transition-all bg-white text-center">
                            Batal
                        </button>
                        <button type="submit" form="userForm"
                            class="flex-1 py-3 px-4 rounded-xl text-white font-bold text-sm bg-pink-600 hover:bg-pink-700 active:scale-95 shadow-lg shadow-pink-200 transition-all flex items-center justify-center gap-2">
                            <x-heroicon-s-check-circle class="w-5 h-5" />
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
