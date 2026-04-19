<?php
use function Livewire\Volt\{state, mount};
use App\Models\Passenger;

state(['passenger' => null, 'showDeleteModal' => false]);

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

$confirmDelete = function () {
    $this->showDeleteModal = true;
};

$deletePassenger = function () {
    $user = auth()->user();
    if (!in_array($user->role->value ?? $user->role, ['superadmin', 'owner', 'super_admin'])) {
        $this->dispatch('notify', message: 'Akses Ditolak: Hanya Super Admin dan Owner yang bisa melakukan ini.', type: 'error');
        $this->showDeleteModal = false;
        return;
    }

    $this->passenger->delete();
    // Opsional jika mau bookingnya dihapus (jangan hapus booking di level ini kalau tiket bisa kolektif, cukup penumpangnya saja)
    
    session()->flash('success', 'Data penumpang berhasil dihapus!');
    $this->redirect(route('passengers.index'), navigate: true);
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
                        <p class="text-xs font-bold px-2 py-0.5 rounded {{ $passenger->gender === 'male' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700' }}">
                            {{ $passenger->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}
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
                        <p class="text-sm font-bold text-gray-900 uppercase">{{ ucfirst($passenger->passenger_type ?? 'dewasa') }}</p>
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
                <div class="flex flex-col gap-3">
                    <a href="{{ route('passengers.print', $passenger) }}" class="flex-1 flex flex-col items-center justify-center gap-1.5 bg-white text-gray-700 py-3.5 rounded-2xl font-bold border border-gray-200 shadow-sm hover:bg-gray-50 transition-colors active:scale-95">
                        <x-heroicon-s-printer class="w-6 h-6" />
                        <span class="text-[10px] uppercase tracking-wider font-black">Cetak Tiket</span>
                    </a>
                </div>
            </div>

            @if(in_array(auth()->user()->role->value ?? auth()->user()->role, ['superadmin', 'owner', 'super_admin']))
            <button wire:click="confirmDelete" class="w-full mt-3 py-3.5 rounded-xl text-center text-sm font-semibold text-red-600 bg-red-50 border border-red-100 hover:bg-red-100 active:scale-[0.98] transition-all print:hidden flex items-center justify-center gap-2">
                <x-heroicon-s-trash class="w-4 h-4" /> Hapus Tiket Penumpang
            </button>
            @endif

        </div>

        {{-- DELETE CONFIRMATION MODAL --}}
        @if ($showDeleteModal)
            <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4" x-data x-cloak>
                <div @click="$wire.set('showDeleteModal', false)"
                    class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
                <div class="relative bg-white rounded-3xl shadow-2xl p-6 max-w-sm w-full z-10 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                        <x-heroicon-s-trash class="w-8 h-8 text-red-500" />
                    </div>
                    <h3 class="text-lg font-black text-gray-900 mb-1">Hapus Data Penumpang?</h3>
                    <p class="text-xs text-gray-500 mb-6 px-2">Data akan tersembunyi (soft delete). Aksi ini akan mempengaruhi jumlah manifest pada jadwal keberangkatan ini.</p>
                    <div class="flex gap-3">
                        <button wire:click="$set('showDeleteModal', false)"
                            class="flex-1 py-3 rounded-xl border border-gray-200 text-gray-700 font-bold text-sm hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button wire:click="deletePassenger"
                            class="flex-1 py-3 rounded-xl bg-red-600 text-white font-bold text-sm shadow-lg shadow-red-200 hover:bg-red-700 active:scale-95 transition-all">
                            Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </x-layouts.app>
</div>
