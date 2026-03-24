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
                    <div class="flex items-center gap-2">
                        <p class="text-xs text-gray-500">Resi: <span class="font-bold text-gray-800">{{ $cargo->tracking_code ?? 'N/A' }}</span></p>
                        <span class="text-gray-300">|</span>
                        <p class="text-[10px] text-gray-400">Ref: {{ $cargo->booking->booking_code ?? '-' }}</p>
                    </div>
                </div>
            </div>


            {{-- Detail Kargo --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
                    <x-heroicon-s-cube class="w-24 h-24" />
                </div>

                {{-- Data Barang di Atas --}}
                <div class="pb-3 border-b border-gray-100">
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Nama Barang / Kemasan</p>
                    <p class="text-lg text-gray-900 font-bold uppercase">{{ $cargo->item_name ?? 'BARANG KARGO' }}</p>
                    <p class="text-sm text-gray-600 leading-snug mt-1">{{ $cargo->description }}</p>
                    <p class="text-xs text-orange-500 font-bold mt-2">{{ $cargo->weight_kg }} Kg &bull; {{ $cargo->quantity }} Koli</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Pengirim</p>
                        <p class="font-bold text-gray-900">{{ $cargo->booking->booker_name }}</p>
                        <p class="text-sm text-gray-600 flex items-center gap-1 mt-0.5"><x-heroicon-o-phone class="w-3.5 h-3.5" /> {{ $cargo->booking->booker_phone ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Penerima</p>
                        <p class="font-bold text-gray-900">{{ $cargo->recipient_name }}</p>
                        <p class="text-sm text-gray-600 flex items-center gap-1 mt-0.5"><x-heroicon-o-phone class="w-3.5 h-3.5" /> {{ $cargo->recipient_phone ?? '-' }}</p>
                    </div>
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
            </div>

            {{-- Tombol Status Aksi --}}
            <div class="space-y-3 print:hidden">
                @if($cargo->is_paid)
                <button disabled class="w-full py-4 bg-emerald-500 text-white rounded-2xl font-bold flex items-center justify-center gap-2 opacity-90 cursor-not-allowed">
                    <x-heroicon-s-check-circle class="w-6 h-6" /> LUNAS
                </button>
                @else
                <button wire:click="markAsPaid" wire:confirm="Anda yakin tagihan telah dibayar dan ingin menandai kargo ini sebagai LUNAS?" class="w-full py-4 bg-red-500 hover:bg-red-600 text-white rounded-2xl font-bold shadow-lg shadow-red-200 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                    <x-heroicon-o-x-circle class="w-6 h-6" /> BELUM LUNAS
                </button>
                @endif

                @if($cargo->status === 'received')
                <button disabled class="w-full py-4 bg-blue-600 text-white rounded-2xl font-bold flex items-center justify-center gap-2 opacity-90 cursor-not-allowed">
                    <x-heroicon-s-check-badge class="w-6 h-6" /> SUDAH DIAMBIL
                </button>
                @else
                <button wire:click="markAsReceived" wire:confirm="Anda yakin barang sudah diambil penerima?" class="w-full py-4 bg-red-500 hover:bg-red-600 text-white rounded-2xl font-bold shadow-lg shadow-red-200 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                    <x-heroicon-o-archive-box-x-mark class="w-6 h-6" /> BELUM DIAMBIL
                </button>
                @endif
            </div>

            {{-- Tombol Utilities (WA & Cetak) --}}
            <div class="grid grid-cols-2 gap-3 mt-4 print:hidden">
                @php
                    $waMsg = urlencode("Halo, ini informasi titipan kargo Anda dari " . ($cargo->originAgent->city ?? 'agen asal') . ".\n\nNomor Resi: *" . $cargo->tracking_code . "*\nBarang: " . ($cargo->item_name ?? 'Paket') . "\nStatus: *" . strtoupper($cargo->status === 'received' ? 'Sudah Diambil' : 'Dalam Proses') . "*\n\nLacak paket Anda di: " . url('/cek-resi') . "?trackingCode=" . $cargo->tracking_code);
                    $phone = $cargo->booking->booker_phone ?? '';
                    if (str_starts_with($phone, '0')) {
                        $phone = '62' . substr($phone, 1);
                    }
                @endphp
                <a href="https://wa.me/{{ $phone }}?text={{ $waMsg }}" target="_blank" class="flex flex-col items-center justify-center gap-1.5 bg-emerald-50 text-emerald-700 py-3.5 rounded-2xl font-bold border border-emerald-200 shadow-sm hover:bg-emerald-100 transition-colors active:scale-95">
                    <x-heroicon-s-chat-bubble-left-right class="w-6 h-6" />
                    <span class="text-[10px] uppercase tracking-wider font-black">WA Pengirim</span>
                </a>
                <a href="{{ route('cargo.print', $cargo->id) }}" class="flex flex-col items-center justify-center gap-1.5 bg-white text-gray-700 py-3.5 rounded-2xl font-bold border border-gray-200 shadow-sm hover:bg-gray-50 transition-colors active:scale-95">
                    <x-heroicon-s-printer class="w-6 h-6" />
                    <span class="text-[10px] uppercase tracking-wider font-black">Cetak Resi</span>
                </a>
            </div>

        </div>
    </x-layouts.app>
</div>
