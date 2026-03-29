<?php
use function Livewire\Volt\{state, mount, computed}; // Tambahkan computed
use App\Models\Cargo;
use Illuminate\Support\Facades\Auth;

state(['cargo' => null]);

mount(function (Cargo $cargo) {
    $user = Auth::user();
    if (!$user->canViewAll() && $cargo->origin_agent_id !== $user->agent_id && $cargo->destination_agent_id !== $user->agent_id) {
        abort(403, 'AKSES DITOLAK: Kargo ini bukan dari atau menuju agen Anda.');
    }

    $this->cargo = $cargo->load(['booking', 'originAgent', 'destinationAgent']);
});

// ==========================================
// 1. LOGIKA OTORISASI (COMPUTED PROPERTIES)
// ==========================================

// Cek hak akses untuk mengubah status "Sudah Diambil"
$canUpdateReceived = computed(function () {
    $user = Auth::user();
    if ($user->canViewAll()) {
        return true;
    } // Superadmin bebas

    // Hanya Agen Tujuan yang bisa merubah status barang diambil
    return $user->agent_id === $this->cargo->destination_agent_id;
});

// Cek hak akses untuk mengubah status "Lunas"
$canUpdatePayment = computed(function () {
    $user = Auth::user();
    if ($user->canViewAll()) {
        return true;
    } // Superadmin bebas

    // Ambil tipe pembayaran dari database (Sesuaikan dengan nama kolom/enum Anda, misal: 'paid_origin' atau 'cod')
    $paymentType = $this->cargo->payment_type ?? 'paid_origin';

    // Jika bayar di asal (Cash di loket pengirim)
    if (in_array($paymentType, ['paid_origin', 'cash'])) {
        return $user->agent_id === $this->cargo->origin_agent_id;
    }

    // Jika bayar di tujuan (COD)
    if (in_array($paymentType, ['paid_destination', 'cod'])) {
        return $user->agent_id === $this->cargo->destination_agent_id;
    }

    return false;
});

// ==========================================
// 2. AKSI EKSEKUSI (DILENGKAPI KEAMANAN)
// ==========================================

$markAsPaid = function () {
    // Validasi Keamanan Lapis Backend
    if (!$this->canUpdatePayment) {
        abort(403, 'Akses Ditolak: Anda tidak berwenang memproses pembayaran untuk tipe transaksi ini.');
    }

    $this->cargo->update([
        'is_paid' => true,
        'payment_status' => 'paid',
        'paid_at' => now(),
    ]);

    $this->cargo->booking->update([
        'payment_status' => 'paid',
    ]);

    $this->dispatch('notify', message: 'Status pembayaran berhasil diupdate menjadi Lunas', type: 'success');
};

$markAsReceived = function () {
    // Validasi Keamanan Lapis Backend
    if (!$this->canUpdateReceived) {
        abort(403, 'Akses Ditolak: Hanya agen tujuan yang berhak menyerahkan paket ke penerima.');
    }

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
                <a href="{{ route('cargo.index') }}" wire:navigate
                    class="w-10 h-10 rounded-xl flex items-center justify-center border border-gray-200 bg-white shadow-sm hover:bg-gray-50">
                    <x-heroicon-o-arrow-left class="w-5 h-5 text-gray-600" />
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Detail Cargo</h1>
                    <div class="flex items-center gap-2">
                        <p class="text-xs text-gray-500">Resi: <span
                                class="font-bold text-gray-800">{{ $cargo->tracking_code ?? 'N/A' }}</span></p>
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
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Nama Barang / Kemasan
                    </p>
                    <p class="text-lg text-gray-900 font-bold uppercase">{{ $cargo->item_name ?? 'BARANG KARGO' }}</p>
                    <p class="text-sm text-gray-600 leading-snug mt-1">{{ $cargo->description }}</p>
                    <p class="text-xs text-orange-500 font-bold mt-2">{{ $cargo->weight_kg }} Kg &bull;
                        {{ $cargo->quantity }} Koli</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Pengirim</p>
                        <p class="font-bold text-gray-900">{{ $cargo->booking->booker_name }}</p>
                        <p class="text-sm text-gray-600 flex items-center gap-1 mt-0.5"><x-heroicon-o-phone
                                class="w-3.5 h-3.5" /> {{ $cargo->booking->booker_phone ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Penerima</p>
                        <p class="font-bold text-gray-900">{{ $cargo->recipient_name }}</p>
                        <p class="text-sm text-gray-600 flex items-center gap-1 mt-0.5"><x-heroicon-o-phone
                                class="w-3.5 h-3.5" /> {{ $cargo->recipient_phone ?? '-' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-3 border-t border-gray-100">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Rute</p>
                        <p class="text-sm font-bold text-gray-900">{{ $cargo->originAgent->city ?? '-' }} &rarr;
                            {{ $cargo->destinationAgent->city ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Biaya</p>
                        <p class="text-sm font-black text-orange-500">Rp{{ number_format($cargo->fee, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Tombol Status Aksi --}}
            <div class="space-y-3 print:hidden">

                {{-- 1. BLOK STATUS PEMBAYARAN --}}
                @if ($cargo->is_paid)
                    <button disabled
                        class="w-full py-4 bg-emerald-500 text-white rounded-2xl font-bold flex items-center justify-center gap-2 opacity-90 cursor-not-allowed">
                        <x-heroicon-s-check-circle class="w-6 h-6" /> LUNAS
                    </button>
                @else
                    @if ($this->canUpdatePayment)
                        {{-- Jika Agen Berhak Memproses Pembayaran --}}
                        <button wire:click="markAsPaid"
                            wire:confirm="Anda yakin tagihan telah dibayar dan ingin menandai kargo ini sebagai LUNAS?"
                            class="w-full py-4 bg-red-500 hover:bg-red-600 text-white rounded-2xl font-bold shadow-lg shadow-red-200 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                            <x-heroicon-o-x-circle class="w-6 h-6" /> BELUM LUNAS
                        </button>
                    @else
                        {{-- Jika Agen TIDAK Berhak (Misal Agen Asal lihat paket COD) --}}
                        <button disabled
                            class="w-full py-4 bg-gray-100 text-gray-400 border border-gray-200 rounded-2xl font-bold flex items-center justify-center gap-2 cursor-not-allowed">
                            <x-heroicon-o-lock-closed class="w-5 h-5" /> BELUM LUNAS
                        </button>
                    @endif
                @endif

                {{-- 2. BLOK STATUS PENGAMBILAN BARANG --}}
                @if ($cargo->status === 'received')
                    <button disabled
                        class="w-full py-4 bg-blue-600 text-white rounded-2xl font-bold flex items-center justify-center gap-2 opacity-90 cursor-not-allowed">
                        <x-heroicon-s-check-badge class="w-6 h-6" /> SUDAH DIAMBIL
                    </button>
                @else
                    @if ($this->canUpdateReceived)
                        {{-- Jika Agen Berhak (Agen Tujuan) --}}
                        <button wire:click="markAsReceived" wire:confirm="Anda yakin barang sudah diambil penerima?"
                            class="w-full py-4 bg-red-500 hover:bg-red-600 text-white rounded-2xl font-bold shadow-lg shadow-red-200 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                            <x-heroicon-o-archive-box-x-mark class="w-6 h-6" /> BELUM DIAMBIL
                        </button>
                    @else
                        {{-- Jika Agen TIDAK Berhak (Misal Agen Asal) --}}
                        <button disabled
                            class="w-full py-4 bg-gray-100 text-gray-400 border border-gray-200 rounded-2xl font-bold flex items-center justify-center gap-2 cursor-not-allowed">
                            <x-heroicon-o-lock-closed class="w-5 h-5" /> BELUM DIAMBIL
                        </button>
                    @endif
                @endif

            </div>

            {{-- Tombol Utilities (WA & Cetak) --}}
            <div class="grid grid-cols-2 gap-3 mt-4 print:hidden">
                @php
                    $waMsg = urlencode(
                        'Halo, ini informasi titipan kargo Anda dari ' .
                            ($cargo->originAgent->city ?? 'agen asal') .
                            ".\n\nNomor Resi: *" .
                            $cargo->tracking_code .
                            "*\nBarang: " .
                            ($cargo->item_name ?? 'Paket') .
                            "\nStatus: *" .
                            strtoupper($cargo->status === 'received' ? 'Sudah Diambil' : 'Dalam Proses') .
                            "*\n\nLacak paket Anda di: " .
                            url('/cek-resi') .
                            '?trackingCode=' .
                            $cargo->tracking_code,
                    );
                    $phone = $cargo->booking->booker_phone ?? '';
                    if (str_starts_with($phone, '0')) {
                        $phone = '62' . substr($phone, 1);
                    }
                @endphp
                <a href="https://wa.me/{{ $phone }}?text={{ $waMsg }}" target="_blank"
                    class="flex flex-col items-center justify-center gap-1.5 bg-emerald-50 text-emerald-700 py-3.5 rounded-2xl font-bold border border-emerald-200 shadow-sm hover:bg-emerald-100 transition-colors active:scale-95">
                    <x-heroicon-s-chat-bubble-left-right class="w-6 h-6" />
                    <span class="text-[10px] uppercase tracking-wider font-black">WA Pengirim</span>
                </a>
                <a href="{{ route('cargo.print', $cargo) }}"
                    class="flex flex-col items-center justify-center gap-1.5 bg-white text-gray-700 py-3.5 rounded-2xl font-bold border border-gray-200 shadow-sm hover:bg-gray-50 transition-colors active:scale-95">
                    <x-heroicon-s-printer class="w-6 h-6" />
                    <span class="text-[10px] uppercase tracking-wider font-black">Cetak Resi</span>
                </a>
            </div>

        </div>
    </x-layouts.app>
</div>
