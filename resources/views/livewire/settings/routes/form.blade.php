<?php

use function Livewire\Volt\{state, on, rules, computed};
use App\Models\Route;
use App\Models\Agent;

state([
    'isOpen' => false,
    'routeId' => null,

    // Form fields
    'origin_agent_id' => '',
    'destination_agent_id' => '',
    'distance_km' => '',
    'estimated_duration_minutes' => '',
    'base_price' => '',
    'is_active' => true,
]);

$agents = computed(function () {
    return Agent::where('is_active', true)->orderBy('city')->get();
});

rules(function () {
    return [
        'origin_agent_id' => ['required', 'exists:agents,id'],
        'destination_agent_id' => ['required', 'exists:agents,id', 'different:origin_agent_id'],
        'distance_km' => ['nullable', 'numeric', 'min:0'],
        'estimated_duration_minutes' => ['nullable', 'numeric', 'min:0'],
        'base_price' => ['required', 'numeric', 'min:0'],
        'is_active' => ['boolean'],
    ];
});

on([
    'openCreateRoute' => function () {
        $this->reset('routeId', 'origin_agent_id', 'destination_agent_id', 'distance_km', 'estimated_duration_minutes', 'base_price');
        $this->is_active = true;
        $this->resetValidation();
        $this->isOpen = true;
    },
]);

on([
    'openEditRoute' => function ($routeId) {
        $this->resetValidation();
        $route = Route::findOrFail($routeId);

        $this->routeId = $route->id;
        $this->origin_agent_id = $route->origin_agent_id;
        $this->destination_agent_id = $route->destination_agent_id;
        $this->distance_km = $route->distance_km;
        $this->estimated_duration_minutes = $route->estimated_duration_minutes;
        $this->base_price = (float) $route->base_price;
        $this->is_active = $route->is_active;

        $this->isOpen = true;
    },
]);

$closeModal = function () {
    $this->isOpen = false;
};

$save = function () {
    $validated = $this->validate();

    // Pastikan tidak ada duplikat rute (Asal & Tujuan sama)
    $existingRouteQuery = Route::where('origin_agent_id', $this->origin_agent_id)->where('destination_agent_id', $this->destination_agent_id);

    if ($this->routeId) {
        $existingRouteQuery->where('id', '!=', $this->routeId);
    }

    if ($existingRouteQuery->exists()) {
        $this->addError('destination_agent_id', 'Kombinasi rute Asal dan Tujuan ini sudah terdaftar!');
        return;
    }

    if ($this->routeId) {
        Route::findOrFail($this->routeId)->update($validated);
        $this->dispatch('notify', message: 'Rute berhasil diperbarui!', type: 'success');
    } else {
        Route::create($validated);
        $this->dispatch('notify', message: 'Rute berhasil ditambahkan!', type: 'success');
    }

    $this->dispatch('route-saved');
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

            <div x-show="show" x-transition.opacity.duration.300ms @click="close()"
                class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm shadow-2xl">
            </div>

            <div x-show="show" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-8 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-8 sm:scale-95"
                class="relative bg-white w-full sm:max-w-lg rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">

                <div class="w-full flex justify-center pt-3 pb-1 sm:hidden absolute top-0 z-20">
                    <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
                </div>

                <div class="px-6 py-4 border-b border-gray-100 relative z-10 bg-white sm:pt-6 pt-8 shrink-0">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-black text-gray-900">
                                {{ $routeId ? 'Edit Rute' : 'Tambah Rute Baru' }}
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">Tentukan kota/agen Asal dan Tujuan.</p>
                        </div>
                        <button @click="close()"
                            class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors">
                            <x-heroicon-s-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <div class="p-6 overflow-y-auto custom-scrollbar flex-1 relative z-10 bg-white">
                    <form wire:submit="save" id="routeForm" class="space-y-4">

                        {{-- Origin --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Agen Asal Berangkat</label>
                            <select wire:model="origin_agent_id"
                                class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors text-sm">
                                <option value="">-- Pilih Agen Asal --</option>
                                @foreach ($this->agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->city }} - {{ $agent->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('origin_agent_id')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Destination --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Agen Tujuan</label>
                            <select wire:model="destination_agent_id"
                                class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors text-sm">
                                <option value="">-- Pilih Agen Tujuan --</option>
                                @foreach ($this->agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->city }} - {{ $agent->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('destination_agent_id')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Detail Perjalanan --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Jarak (Km)</label>
                                <input type="number" wire:model="distance_km"
                                    class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors text-sm"
                                    placeholder="Misal: 150">
                                @error('distance_km')
                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Estimasi Waktu
                                    (Menit)</label>
                                <input type="number" wire:model="estimated_duration_minutes"
                                    class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors text-sm"
                                    placeholder="Misal: 120">
                                @error('estimated_duration_minutes')
                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Harga Dasar --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Harga Dasar (Rp)</label>
                            <input type="number" wire:model="base_price"
                                class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors text-sm font-black text-teal-700"
                                placeholder="0">
                            @error('base_price')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Status Aktif --}}
                        <div
                            class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50/50 mt-2">
                            <div>
                                <p class="text-sm font-bold text-gray-900">Status Operasional</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">Aktifkan rute ini di aplikasi</p>
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

                <div class="p-4 sm:p-6 border-t border-gray-100 bg-gray-50 relative z-10 shrink-0">
                    <div class="flex gap-3">
                        <button type="button" @click="close()"
                            class="flex-1 py-3 px-4 rounded-xl border border-gray-200 text-gray-700 font-bold text-sm hover:bg-gray-100 active:scale-95 transition-all bg-white text-center">
                            Batal
                        </button>
                        <button type="submit" form="routeForm"
                            class="flex-1 py-3 px-4 rounded-xl text-white font-bold text-sm bg-teal-600 hover:bg-teal-700 active:scale-95 shadow-lg shadow-teal-200 transition-all flex items-center justify-center gap-2">
                            <x-heroicon-s-check-circle class="w-5 h-5" />
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
