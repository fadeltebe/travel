<?php

use function Livewire\Volt\{state, computed, on};
use App\Models\Bus;
use App\Models\BusLayout;
use App\Models\BusLayoutSeat;

state([
    'showFormModal' => false,
    'isEdit' => false,
    'editingBusId' => null,

    // Bus form
    'name' => '',
    'plate_number' => '',
    'brand' => '',
    'type' => '',
    'machine_number' => '',
    'chassis_number' => '',
    'is_active' => true,
    'bus_layout_id' => '',
]);

$layouts = computed(fn() => BusLayout::where('is_active', true)->orderBy('name')->get());

$selectedLayoutPreview = computed(function () {
    if (!$this->bus_layout_id) {
        return null;
    }
    return BusLayout::with(['seats' => fn($q) => $q->orderBy('row')->orderBy('column')])->find($this->bus_layout_id);
});

on([
    'openCreateBus' => function () {
        $this->resetFormState();
        $this->showFormModal = true;
    },
]);

on([
    'openEditBus' => function ($busId) {
        $bus = Bus::findOrFail($busId);
        $this->editingBusId = $bus->id;
        $this->isEdit = true;
        $this->name = $bus->name ?? '';
        $this->plate_number = $bus->plate_number;
        $this->brand = $bus->brand ?? '';
        $this->type = $bus->type ?? '';
        $this->machine_number = $bus->machine_number ?? '';
        $this->chassis_number = $bus->chassis_number ?? '';
        $this->is_active = $bus->is_active;
        $this->bus_layout_id = $bus->bus_layout_id ?? '';
        $this->showFormModal = true;
    },
]);

$resetFormState = function () {
    $this->isEdit = false;
    $this->editingBusId = null;
    $this->name = '';
    $this->plate_number = '';
    $this->brand = '';
    $this->type = '';
    $this->machine_number = '';
    $this->chassis_number = '';
    $this->is_active = true;
    $this->bus_layout_id = '';
};

$saveBus = function () {
    $rules = [
        'plate_number' => 'required|string|max:20',
        'name' => 'nullable|string|max:255',
        'brand' => 'nullable|string|max:255',
        'type' => 'nullable|string|max:100',
    ];

    if ($this->isEdit) {
        $rules['plate_number'] .= '|unique:buses,plate_number,' . $this->editingBusId;
    } else {
        $rules['plate_number'] .= '|unique:buses,plate_number';
    }

    $this->validate($rules);

    $layoutId = null;
    $totalSeats = 0;

    if ($this->bus_layout_id) {
        $layoutId = $this->bus_layout_id;
        $totalSeats = BusLayoutSeat::where('bus_layout_id', $layoutId)->where('type', 'passenger')->count();
    }

    $data = [
        'plate_number' => $this->plate_number,
        'brand' => $this->brand ?: null,
        'name' => $this->name ?: null,
        'type' => $this->type ?: null,
        'machine_number' => $this->machine_number ?: null,
        'chassis_number' => $this->chassis_number ?: null,
        'bus_layout_id' => $layoutId,
        'total_seats' => $totalSeats,
        'is_active' => $this->is_active,
    ];

    if ($this->isEdit && $this->editingBusId) {
        Bus::findOrFail($this->editingBusId)->update($data);
        $msg = 'Armada berhasil diperbarui!';
    } else {
        Bus::create($data);
        $msg = 'Armada berhasil ditambahkan!';
    }

    $this->showFormModal = false;
    $this->resetFormState();

    // Refresh parent components
    $this->dispatch('bus-saved');
    $this->dispatch('notify', message: $msg, type: 'success');
};
?>

<div>
    {{-- ADD / EDIT MODAL --}}
    @if ($showFormModal)
        <div x-data x-cloak class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center p-0 sm:p-4">
            {{-- Backdrop --}}
            <div x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                @click="$wire.set('showFormModal', false)" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

            {{-- Content --}}
            <div x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="translate-y-full sm:translate-y-10 sm:scale-95 sm:opacity-0"
                x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100"
                x-transition:leave-end="translate-y-full sm:translate-y-10 sm:scale-95 sm:opacity-0"
                class="relative bg-white w-full max-w-2xl rounded-t-[2.5rem] sm:rounded-3xl shadow-2xl flex flex-col max-h-[90dvh] sm:max-h-[85vh] z-[10000] overflow-hidden overscroll-contain">

                {{-- Handle bar (mobile) --}}
                <div class="sm:hidden w-12 h-1.5 bg-gray-200 rounded-full mx-auto mt-4 mb-2 shrink-0"></div>

                {{-- Header --}}
                <div
                    class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-10 shrink-0">
                    <div>
                        <h3 class="font-black text-gray-900 text-lg">
                            {{ $isEdit ? 'Edit Armada' : 'Tambah Armada Baru' }}
                        </h3>
                        <p class="text-[10px] text-indigo-600 font-bold uppercase tracking-wider mt-0.5">
                            Data bus & layout kursi
                        </p>
                    </div>
                    <button @click="$wire.set('showFormModal', false)"
                        class="p-2 -mr-2 bg-gray-50 text-gray-400 rounded-full hover:bg-gray-100 hover:text-gray-600 transition-colors">
                        <x-heroicon-s-x-mark class="w-6 h-6" />
                    </button>
                </div>

                {{-- Body (Scrollable) --}}
                <div class="flex-1 overflow-y-auto min-h-0 p-6 space-y-8 pb-32 sm:pb-6">

                    {{-- ─── Section 1: Info Bus ─── --}}
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                    <x-heroicon-s-identification class="w-5 h-5" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 text-sm">Informasi Bus</h4>
                                    <p class="text-[11px] text-gray-500">Detail identitas armada</p>
                                </div>
                            </div>

                            {{-- Active toggle --}}
                            <label class="flex items-center gap-2 cursor-pointer">
                                <span
                                    class="text-[11px] font-bold {{ $is_active ? 'text-emerald-600' : 'text-gray-400' }}">Aktif</span>
                                <div class="relative">
                                    <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                    <div
                                        class="w-10 h-5 bg-gray-200 peer-checked:bg-emerald-500 rounded-full transition-colors">
                                    </div>
                                    <div
                                        class="absolute left-[2px] top-[2px] w-4 h-4 bg-white rounded-full shadow-sm transition-transform peer-checked:translate-x-5">
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4 bg-gray-50/50 p-5 rounded-2xl border border-gray-100/50">
                            <div>
                                <label
                                    class="block text-[11px] font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Nama
                                    Bus</label>
                                <input type="text" wire:model="name" placeholder="cth: Harapan 01"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all placeholder-gray-300">
                            </div>
                            <div>
                                <label
                                    class="block text-[11px] font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Plat
                                    Nomor <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="plate_number" placeholder="cth: DN 1234 AB"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all placeholder-gray-300 uppercase">
                                @error('plate_number')
                                    <p class="text-red-500 text-[10px] font-medium mt-1.5 flex items-center gap-1">
                                        <x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-[11px] font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Merek</label>
                                <input type="text" wire:model="brand" placeholder="cth: Toyota, Isuzu"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all placeholder-gray-300">
                            </div>
                            <div>
                                <label
                                    class="block text-[11px] font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Tipe</label>
                                <div class="relative">
                                    <select wire:model="type"
                                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent appearance-none transition-all text-gray-700">
                                        <option value="" class="text-gray-400">Pilih Tipe Bus</option>
                                        <option value="Elf">Elf</option>
                                        <option value="Medium">Medium Bus</option>
                                        <option value="Big Bus">Big Bus</option>
                                    </select>
                                    <div
                                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                                        <x-heroicon-s-chevron-down class="w-4 h-4" />
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label
                                    class="block text-[11px] font-bold text-gray-500 mb-1.5 uppercase tracking-wide">No.
                                    Mesin</label>
                                <input type="text" wire:model="machine_number" placeholder="Opsional"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all placeholder-gray-300 uppercase">
                            </div>
                            <div>
                                <label
                                    class="block text-[11px] font-bold text-gray-500 mb-1.5 uppercase tracking-wide">No.
                                    Rangka</label>
                                <input type="text" wire:model="chassis_number" placeholder="Opsional"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all placeholder-gray-300 uppercase">
                            </div>
                        </div>
                    </div>

                    {{-- ─── Section 2: Layout Kursi ─── --}}
                    <div>
                        <div class="flex items-center justify-between mb-4 mt-2">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                                    <x-heroicon-s-squares-2x2 class="w-5 h-5" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 text-sm">Layout Kursi</h4>
                                    <p class="text-[11px] text-gray-500">Pilih denah kursi armada</p>
                                </div>
                            </div>
                            <a href="{{ route('settings.bus-layouts') }}"
                                class="text-[10px] font-bold text-purple-600 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1"
                                wire:navigate>
                                <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                                Kelola Master
                            </a>
                        </div>

                        <div class="bg-gray-50/50 p-5 rounded-2xl border border-gray-100/50">
                            <div class="relative">
                                <select wire:model.live="bus_layout_id"
                                    class="w-full px-4 py-3 text-sm rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-purple-500 focus:border-transparent appearance-none transition-all text-gray-700 font-medium shadow-sm">
                                    <option value="">-- Tidak ada (Pilih Layout) --</option>
                                    @foreach ($this->layouts as $lo)
                                        <option value="{{ $lo->id }}">
                                            {{ $lo->name }} • {{ $lo->total_seats }} kursi
                                        </option>
                                    @endforeach
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-purple-600">
                                    <x-heroicon-s-chevron-up-down class="w-5 h-5" />
                                </div>
                            </div>

                            {{-- Preview Layout --}}
                            @if ($this->selectedLayoutPreview)
                                @php $preview = $this->selectedLayoutPreview; @endphp
                                <div
                                    class="mt-5 bg-white rounded-xl p-4 border border-purple-100 shadow-[0_2px_10px_-4px_rgba(147,51,234,0.1)]">
                                    <div class="flex items-center justify-between mb-4 border-b border-gray-50 pb-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                            <p class="text-xs font-bold text-gray-600 uppercase tracking-wide">Preview
                                                Denah</p>
                                        </div>
                                        <span
                                            class="text-[10px] font-black text-purple-700 bg-purple-100 px-2.5 py-1 rounded-md tracking-wide">
                                            {{ $preview->total_seats }} KURSI
                                        </span>
                                    </div>
                                    <div class="flex justify-center">
                                        <div
                                            class="bg-gray-50 p-3 rounded-2xl border border-gray-200/60 inline-block overflow-x-auto max-w-full">
                                            <div class="grid gap-2"
                                                style="grid-template-columns: repeat({{ $preview->total_columns }}, minmax(0, 1fr));">
                                                @foreach ($preview->seats as $seat)
                                                    <div
                                                        class="w-10 h-10 rounded-xl flex items-center justify-center text-[11px] font-bold shadow-sm transition-transform hover:scale-105 cursor-default
                                                        {{ $seat->type === 'passenger' ? 'bg-white border-2 border-emerald-500 text-emerald-700' : '' }}
                                                        {{ $seat->type === 'aisle' ? 'bg-transparent text-transparent' : '' }}
                                                        {{ $seat->type === 'driver' ? 'bg-indigo-600 text-white shadow-indigo-200' : '' }}
                                                        {{ $seat->type === 'door' ? 'bg-amber-100 border-2 border-amber-400 text-amber-600' : '' }}">
                                                        @if ($seat->type === 'passenger')
                                                            {{ $seat->seat_number }}
                                                        @elseif($seat->type === 'driver')
                                                            <x-heroicon-s-user class="w-5 h-5" />
                                                        @elseif($seat->type === 'door')
                                                            <x-heroicon-s-arrow-right-on-rectangle class="w-4 h-4" />
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="mt-4 flex flex-wrap justify-center gap-4 text-[10px] font-medium text-gray-400 border-t border-gray-50 pt-3">
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-3 h-3 rounded bg-white border-2 border-emerald-500"></div>
                                            Kursi Penumpang
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-3 h-3 rounded bg-indigo-600"></div> Supir
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-3 h-3 rounded bg-amber-100 border-2 border-amber-400"></div>
                                            Pintu
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Footer (Sticky) --}}
                <div
                    class="p-5 bg-white border-t border-gray-100 shrink-0 flex gap-3 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.02)]">
                    <button type="button" @click="$wire.set('showFormModal', false)"
                        class="flex-1 py-3.5 rounded-xl bg-gray-50 text-gray-600 font-bold text-sm hover:bg-gray-100 transition-colors focus:ring-2 focus:ring-gray-200 focus:outline-none">
                        Batal
                    </button>
                    <button type="button" @click="$wire.saveBus()"
                        class="flex-1 py-3.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-200 hover:bg-indigo-700 active:scale-95 transition-all focus:ring-2 focus:ring-indigo-500 focus:outline-none flex items-center justify-center gap-2">
                        <x-heroicon-s-check-circle class="w-5 h-5" />
                        {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Bus Baru' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
