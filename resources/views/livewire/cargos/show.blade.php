<?php
use function Livewire\Volt\{state, mount};
use App\Models\Cargo;

state(['cargo' => null]);

mount(function (Cargo $cargo) {
    // Memastikan relasi termuat
    $this->cargo = $cargo->load(['booking', 'originAgent', 'destinationAgent']);
});

$markAsPaid = function () {
    $this->cargo->update([
        'is_paid' => true,
        'payment_status' => 'paid',
        'paid_at' => now(),
    ]);

    // Update booking if necessary
    $this->cargo->booking->update([
        'payment_status' => 'paid'
    ]);

    $this->dispatch('notify', message: 'Status pembayaran berhasil diupdate menjadi Lunas', type: 'success');
};

$markAsReceived = function () {
    $this->cargo->update([
        'status' => 'received',
    ]);
    
    $this->dispatch('notify', message: 'Status kargo berhasil diupdate menjadi Sudah Diambil', type: 'success');
};
?>

<div>
    <x-layouts.app title="Detail Cargo">
        <div class="px-4 pt-6 pb-24 space-y-5 max-w-lg mx-auto">
            {{-- Header --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('cargo.index') }}" wire:navigate class="w-10 h-10 rounded-xl flex items-center justify-center border border-gray-200 bg-white shadow-sm hover:bg-gray-50">
                    <x-heroicon-o-arrow-left class="w-5 h-5 text-gray-600" />
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Detail Cargo</h1>
                    <p class="text-xs text-gray-500">{{ $cargo->booking->booking_code ?? 'N/A' }}</p>
                </div>
            </div>

            {{-- Card Info Status --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white p-4 rounded-2xl shadow-sm border {{ $cargo->status === 'received' ? 'border-emerald-200 bg-emerald-50' : 'border-blue-200 bg-blue-50' }}">
                    <p class="text-xs text-gray-500 font-semibold mb-1">Status Pengambilan</p>
                    <h3 class="font-black {{ $cargo->status === 'received' ? 'text-emerald-700' : 'text-blue-700' }}">
                        {{ $cargo->status === 'received' ? 'SUDAH DIAMBIL' : 'BELUM DIAMBIL' }}
                    </h3>
                </div>
                <div class="bg-white p-4 rounded-2xl shadow-sm border {{ $cargo->is_paid ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50' }}">
                    <p class="text-xs text-gray-500 font-semibold mb-1">Status Pembayaran</p>
                    <h3 class="font-black {{ $cargo->is_paid ? 'text-emerald-700' : 'text-red-600' }}">
                        {{ $cargo->is_paid ? 'LUNAS' : 'BELUM LUNAS' }}
                    </h3>
                </div>
            </div>

            {{-- Detail Kargo --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
                    <x-heroicon-s-cube class="w-24 h-24" />
                </div>
                
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Penerima</p>
                    <p class="font-bold text-gray-900">{{ $cargo->recipient_name }}</p>
                    <p class="text-sm text-gray-600 flex items-center gap-1 mt-0.5"><x-heroicon-o-phone class="w-3.5 h-3.5" /> {{ $cargo->recipient_phone ?? '-' }}</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4 pt-3 border-t border-gray-100">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Rute</p>
                        <p class="text-sm font-bold text-gray-900">{{ $cargo->originAgent->city ?? '-' }} &rarr; {{ $cargo->destinationAgent->city ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Biaya</p>
                        <p class="text-sm font-black text-orange-500">Rp{{ number_format($cargo->fee, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100">
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Barang</p>
                    <p class="text-sm text-gray-800 font-semibold">{{ $cargo->description }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $cargo->weight_kg }} Kg &bull; {{ $cargo->quantity }} Koli</p>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="space-y-3">
                @if(!$cargo->is_paid)
                <button wire:click="markAsPaid" wire:confirm="Anda yakin ingin menandai kargo ini sebagai LUNAS?" class="w-full py-4 bg-emerald-500 text-white rounded-2xl font-bold shadow-lg shadow-emerald-200 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                    <x-heroicon-o-banknotes class="w-6 h-6" /> Tandai Lunas
                </button>
                @endif

                @if($cargo->status !== 'received')
                <button wire:click="markAsReceived" wire:confirm="Anda yakin barang sudah diambil penerima?" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-bold shadow-lg shadow-blue-200 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                    <x-heroicon-o-check-badge class="w-6 h-6" /> Tandai Sudah Diambil
                </button>
                @endif
            </div>

        </div>
    </x-layouts.app>
</div>
