<?php
use function Livewire\Volt\{state, mount};
use App\Models\Passenger;

state(['passenger' => null]);

mount(function (Passenger $passenger) {
    // Otorisasi: Mencegah agen melihat penumpang cabang lain
    $user = auth()->user();
    if (!$user->canViewAll()) {
        $originId = $passenger->booking->schedule->route->origin_agent_id ?? null;
        $destId = $passenger->booking->schedule->route->destination_agent_id ?? null;
        if ($originId !== $user->agent_id && $destId !== $user->agent_id) {
            abort(403, 'AKSES DITOLAK: Penumpang ini tidak terkait dengan rute agen Anda.');
        }
    }

    $this->passenger = $passenger->load(['booking.schedule.route.originAgent', 'booking.schedule.route.destinationAgent', 'booking.schedule.bus', 'booking.schedule.driver']);
});

$markAsPaid = function () {
    $this->passenger->booking->update([
        'payment_status' => 'paid',
        'paid_at' => now(),
    ]);

    $this->dispatch('notify', message: 'Status pembayaran Booking berhasil diupdate menjadi Lunas', type: 'success');
};
?>

<div>
    <x-layouts.app title="Detail Penumpang">
        <div class="px-4 pt-6 pb-24 space-y-5 max-w-lg mx-auto">
            {{-- Header --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('passengers.index') }}" wire:navigate class="w-10 h-10 rounded-xl flex items-center justify-center border border-gray-200 bg-white shadow-sm hover:bg-gray-50">
                    <x-heroicon-o-arrow-left class="w-5 h-5 text-gray-600" />
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Detail Penumpang</h1>
                    <div class="flex items-center gap-2">
                        <p class="text-xs text-gray-500">Booking: <span class="font-bold text-gray-800">{{ $passenger->booking->booking_code ?? 'N/A' }}</span></p>
                    </div>
                </div>
            </div>

            {{-- Card Info Status --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white p-4 rounded-2xl shadow-sm border {{ $passenger->booking->status === 'confirmed' ? 'border-emerald-200 bg-emerald-50' : 'border-blue-200 bg-blue-50' }}">
                    <p class="text-xs text-gray-500 font-semibold mb-1">Status Booking</p>
                    <h3 class="font-black {{ $passenger->booking->status === 'confirmed' ? 'text-emerald-700' : 'text-blue-700' }} uppercase">
                        {{ $passenger->booking->status ?? 'Menunggu' }}
                    </h3>
                </div>
                <div class="bg-white p-4 rounded-2xl shadow-sm border {{ $passenger->booking->payment_status === 'paid' ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50' }}">
                    <p class="text-xs text-gray-500 font-semibold mb-1">Status Pembayaran</p>
                    <h3 class="font-black {{ $passenger->booking->payment_status === 'paid' ? 'text-emerald-700' : 'text-red-600' }}">
                        {{ $passenger->booking->payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}
                    </h3>
                </div>
            </div>

            {{-- Detail Penumpang --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
                    <x-heroicon-s-ticket class="w-24 h-24" />
                </div>

                {{-- Data Penumpang di Atas --}}
                <div class="pb-3 border-b border-gray-100">
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Nama Penumpang</p>
                    <p class="text-lg text-gray-900 font-bold uppercase">{{ $passenger->name }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <p class="text-xs font-bold px-2 py-0.5 rounded {{ $passenger->gender == 'L' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700' }}">
                            {{ $passenger->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </p>
                        <p class="text-xs text-emerald-700 font-black px-2 py-0.5 bg-emerald-100 rounded">
                            Nomor Kursi: {{ $passenger->seat_number ?? 'N/A' }}
                        </p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Telepon</p>
                        <p class="text-sm font-bold text-gray-900 flex items-center gap-1"><x-heroicon-o-phone class="w-3.5 h-3.5" /> {{ $passenger->phone ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Tipe Penumpang</p>
                        <p class="text-sm font-bold text-gray-900 uppercase">{{ $passenger->passenger_type ?? 'Dewasa' }}</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 pt-3 border-t border-gray-100">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Rute</p>
                        <p class="text-sm font-bold text-gray-900">{{ $passenger->booking->schedule->route->originAgent->city ?? '-' }} &rarr; {{ $passenger->booking->schedule->route->destinationAgent->city ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Bus & Jadwal</p>
                        <p class="text-sm font-black text-gray-900">{{ $passenger->booking->schedule->bus->name ?? '-' }} ({{ \Carbon\Carbon::parse($passenger->booking->schedule->departure_time)->format('H:i') }})</p>
                    </div>
                </div>
            </div>

            {{-- Tombol Status Aksi --}}
            <div class="space-y-3 print:hidden">
                @if($passenger->booking->payment_status === 'paid')
                <button disabled class="w-full py-4 bg-emerald-500 text-white rounded-2xl font-bold flex items-center justify-center gap-2 opacity-90 cursor-not-allowed">
                    <x-heroicon-s-check-circle class="w-6 h-6" /> PEMBAYARAN: LUNAS
                </button>
                @else
                <button wire:click="markAsPaid" wire:confirm="Anda yakin tagihan keseluruhan tiket ini telah dibayar LUNAS?" class="w-full py-4 bg-red-500 hover:bg-red-600 text-white rounded-2xl font-bold shadow-lg shadow-red-200 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                    <x-heroicon-o-x-circle class="w-6 h-6" /> BELUM LUNAS (Tandai Lunas)
                </button>
                @endif
            </div>

            {{-- Tombol Utilities (WA & Cetak) --}}
            <div class="grid grid-cols-2 gap-3 mt-4 print:hidden">
                @php
                    $waMsg = urlencode("Halo {$passenger->name}, ini adalah E-Tiket keberangkatan Anda.\n\nKode Booking: *" . ($passenger->booking->booking_code) . "*\nRute: " . ($passenger->booking->schedule->route->originAgent->city ?? '-') . " - " . ($passenger->booking->schedule->route->destinationAgent->city ?? '-') . "\nWaktu: " . (\Carbon\Carbon::parse($passenger->booking->schedule->departure_time)->format('H:i')) . " WIB\nKursi: *" . ($passenger->seat_number) . "*");
                    $phone = $passenger->phone ?? ($passenger->booking->booker_phone ?? '');
                    if (str_starts_with($phone, '0')) {
                        $phone = '62' . substr($phone, 1);
                    }
                @endphp
                <a href="https://wa.me/{{ $phone }}?text={{ $waMsg }}" target="_blank" class="flex flex-col items-center justify-center gap-1.5 bg-emerald-50 text-emerald-700 py-3.5 rounded-2xl font-bold border border-emerald-200 shadow-sm hover:bg-emerald-100 transition-colors active:scale-95">
                    <x-heroicon-s-chat-bubble-left-right class="w-6 h-6" />
                    <span class="text-[10px] uppercase tracking-wider font-black">WA Tiket</span>
                </a>
                <a href="{{ route('passengers.print', $passenger) }}" class="flex flex-col items-center justify-center gap-1.5 bg-white text-gray-700 py-3.5 rounded-2xl font-bold border border-gray-200 shadow-sm hover:bg-gray-50 transition-colors active:scale-95">
                    <x-heroicon-s-printer class="w-6 h-6" />
                    <span class="text-[10px] uppercase tracking-wider font-black">Cetak Tiket</span>
                </a>
            </div>

        </div>
    </x-layouts.app>
</div>
