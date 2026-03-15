<?php
use function Livewire\Volt\{state, computed};

// State untuk form input sementara
state([
    'description' => '',
    'weight'      => '',
    'fee'         => '',
    'recipient'   => '',
    'items'       => [] // Array untuk menampung data sebelum masuk database
]);

// Fungsi untuk menambah barang ke list (Belum simpan ke DB)
$addItem = function () {
    $this->validate([
        'description' => 'required|string',
        'fee'         => 'required|numeric',
    ]);

    $this->items[] = [
        'description' => $this->description,
        'weight_kg'   => $this->weight ?: 0,
        'fee'         => $this->fee,
        'recipient'   => $this->recipient,
    ];

    // Reset input setelah tambah
    $this->reset(['description', 'weight', 'fee', 'recipient']);
    
    // Auto-focus kembali ke input pertama (butuh sedikit Alpine.js)
    $this->dispatch('item-added');
};

// Fungsi untuk menghapus item dari list sementara
$removeItem = function ($index) {
    unset($this->items[$index]);
    $this->items = array_values($this->items); // Reset index array
};

// Computed property untuk total harga sementara
$totalTemporary = computed(function () {
    return collect($this->items)->sum('fee');
});
?>

<div x-data="{ focusInput() { $refs.desc.focus() } }" x-on:item-added.window="focusInput()">

    {{-- Input Area (Sticky Mobile) --}}
    <div class="bg-white p-4 shadow-md border-b sticky top-0 z-10">
        <div class="grid grid-cols-2 gap-2">
            <div class="col-span-2">
                <input x-ref="desc" wire:model="description" type="text" placeholder="Deskripsi Barang (Contoh: Dus Baju)" class="w-full border-gray-300 rounded-lg text-sm focus:ring-primary-500">
            </div>
            <div>
                <input wire:model="weight" type="number" placeholder="Berat (kg)" class="w-full border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <input wire:model="fee" wire:keydown.enter="addItem" type="number" placeholder="Harga (Rp)" class="w-full border-gray-300 rounded-lg text-sm font-bold text-primary-600">
            </div>
        </div>
        <button wire:click="addItem" class="w-full mt-3 bg-primary-600 text-white py-2 rounded-lg font-bold text-sm active:bg-primary-700">
            + Tambah ke Daftar
        </button>
    </div>

    {{-- List Barang Yang Akan Diinput --}}
    <div class="p-4 space-y-3 mb-24">
        <h3 class="text-xs font-bold text-gray-500 uppercase">Daftar Barang ({{ count($items) }})</h3>

        @forelse($items as $index => $item)
        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-xl border border-gray-200">
            <div>
                <p class="text-sm font-bold text-gray-800">{{ $item['description'] }}</p>
                <p class="text-xs text-gray-500">{{ $item['weight_kg'] }}kg · Rp {{ number_format($item['fee'], 0, ',', '.') }}</p>
            </div>
            <button wire:click="removeItem({{ $index }})" class="text-red-500 p-2">
                <x-heroicon-o-trash class="w-5 h-5" />
            </button>
        </div>
        @empty
        <p class="text-center text-gray-400 text-sm py-10">Belum ada barang ditambahkan</p>
        @endforelse
    </div>

    {{-- Footer Summary --}}
    @if(count($items) > 0)
    <div class="fixed bottom-0 left-0 right-0 bg-white p-4 border-t shadow-[0_-4px_10px_rgba(0,0,0,0.05)] flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 leading-none">Total Sementara</p>
            <p class="text-lg font-black text-primary-600">Rp {{ number_format($this->totalTemporary, 0, ',', '.') }}</p>
        </div>
        <button class="bg-emerald-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-emerald-200 active:scale-95 transition-transform">
            Simpan Booking
        </button>
    </div>
    @endif
</div>