<?php
use function Livewire\Volt\{state, computed};
use App\Models\Schedule;

state([
    'activeTab' => 'details',
    'scheduleModel' => function () {
        $param = request()->route('schedule');
        $schedule = $param instanceof Schedule ? $param : Schedule::with(['route.originAgent', 'route.destinationAgent', 'bus', 'driver'])->findOrFail($param);

        // Otorisasi: Mencegah agen melihat jadwal cabang lain
        $user = auth()->user();
        if (!$user->canViewAll() && $schedule->route->origin_agent_id !== $user->agent_id && $schedule->route->destination_agent_id !== $user->agent_id) {
            abort(403, 'AKSES DITOLAK: Jadwal ini tidak terdaftar untuk agen Anda.');
        }

        $schedule->loadMissing(['route.originAgent', 'route.destinationAgent', 'bus', 'driver']);
        return $schedule;
    },
]);

$passengers = computed(function () {
    return \App\Models\Passenger::whereHas('booking', function ($q) {
        $q->where('schedule_id', $this->scheduleModel->id);
    })
        ->with('booking')
        ->get();
});

$cargos = computed(function () {
    return \App\Models\Cargo::whereHas('booking', function ($q) {
        $q->where('schedule_id', $this->scheduleModel->id);
    })
        ->with('booking')
        ->get();
});
?>

<div>
    <x-layouts.app title="Detail Jadwal">
        <div class="px-4 pt-0 pb-24 space-y-6">

            @if (session('success'))
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Back + Header --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('schedules.index') }}"
                    class="w-9 h-9 rounded-xl flex items-center justify-center border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 active:scale-95 transition-all shadow-sm">
                    <x-heroicon-o-arrow-left class="w-5 h-5" />
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Detail Jadwal</h1>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $scheduleModel->departure_date->format('d M Y') }} ·
                        {{ \Carbon\Carbon::parse($scheduleModel->departure_time)->format('H:i') }}</p>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="flex items-center p-1.5 bg-gray-200/50 rounded-2xl mb-4 mt-2">
                <button wire:click="$set('activeTab', 'details')"
                    class="{{ $activeTab == 'details' ? 'bg-white shadow text-gray-900 pointer-events-none' : 'text-gray-500 hover:text-gray-700' }} flex-1 flex flex-col items-center justify-center gap-1.5 py-2.5 text-xs font-bold rounded-xl transition-all">
                    <x-heroicon-s-calendar class="w-5 h-5 {{ $activeTab == 'details' ? 'text-primary-600' : '' }}" />
                    Detail
                </button>
                <button wire:click="$set('activeTab', 'passengers')"
                    class="{{ $activeTab == 'passengers' ? 'bg-white shadow text-gray-900 pointer-events-none' : 'text-gray-500 hover:text-gray-700' }} flex-1 flex flex-col items-center justify-center gap-1.5 py-2.5 text-xs font-bold rounded-xl transition-all relative">
                    <x-heroicon-s-users class="w-5 h-5 {{ $activeTab == 'passengers' ? 'text-emerald-600' : '' }}" />
                    Penumpang
                    @if ($this->passengers->count() > 0)
                        <span
                            class="absolute top-1.5 right-2 inline-flex items-center justify-center min-w-[16px] h-4 text-[9px] font-black text-white bg-emerald-500 rounded-full px-1 shadow-sm">
                            {{ $this->passengers->count() }}
                        </span>
                    @endif
                </button>
                <button wire:click="$set('activeTab', 'cargos')"
                    class="{{ $activeTab == 'cargos' ? 'bg-white shadow text-gray-900 pointer-events-none' : 'text-gray-500 hover:text-gray-700' }} flex-1 flex flex-col items-center justify-center gap-1.5 py-2.5 text-xs font-bold rounded-xl transition-all relative">
                    <x-heroicon-s-cube class="w-5 h-5 {{ $activeTab == 'cargos' ? 'text-orange-500' : '' }}" /> Kargo
                    @if ($this->cargos->count() > 0)
                        <span
                            class="absolute top-1.5 right-2 inline-flex items-center justify-center min-w-[16px] h-4 text-[9px] font-black text-white bg-orange-500 rounded-full px-1 shadow-sm">
                            {{ $this->cargos->count() }}
                        </span>
                    @endif
                </button>
            </div>

            @if ($activeTab === 'details')
                {{-- Tombol Laporan Perjalanan (Manifest) --}}
                <a href="{{ route('schedules.manifest', $scheduleModel) }}" target="_blank"
                    class="flex items-center justify-center gap-2 w-full py-3.5 mb-4 rounded-xl text-sm font-bold text-white shadow-sm hover:opacity-90 active:scale-[0.98] transition-all"
                    style="background: linear-gradient(135deg, #10B981, #059669);">
                    <x-heroicon-s-printer class="w-5 h-5" />
                    Cetak Laporan Perjalanan
                </a>

                {{-- Ringkasan Informasi Terpadu --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    {{-- Header Status --}}
                    <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between"
                        style="background: linear-gradient(135deg, #f8fafc, #f1f5f9);">
                        <div class="flex items-center gap-2">
                            <x-heroicon-s-map class="w-4 h-4 text-primary-600" />
                            <span class="text-[11px] font-bold text-gray-800 uppercase tracking-widest">Informasi
                                Perjalanan</span>
                        </div>
                        @if ($scheduleModel->status === 'scheduled' || $scheduleModel->status === 'ongoing')
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase"
                                style="background: rgba(16, 185, 129, 0.12); color: #059669;">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase bg-gray-100 text-gray-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Nonaktif
                            </span>
                        @endif
                    </div>

                    <div class="p-5 space-y-5">
                        {{-- Rute --}}
                        <div class="flex items-center gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Berangkat
                                </p>
                                <p class="text-base font-bold text-gray-800 leading-tight">
                                    {{ $scheduleModel->route->originAgent->city ?? 'N/A' }}</p>
                                <p class="text-[11px] text-gray-500 mt-0.5 truncate">
                                    {{ $scheduleModel->route->originAgent->name ?? '-' }}</p>
                                <p class="text-lg font-black text-primary-600 mt-2 leading-none">
                                    {{ \Carbon\Carbon::parse($scheduleModel->departure_time)->format('H:i') }}</p>
                                <p class="text-[10px] font-semibold text-gray-400 mt-0.5">
                                    {{ $scheduleModel->departure_date->format('d M y') }}</p>
                            </div>

                            <div class="flex flex-col items-center px-1">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center bg-primary-50 mb-1">
                                    <x-heroicon-s-arrow-right class="w-4 h-4 text-primary-600" />
                                </div>
                                @if ($scheduleModel->route->distance_km)
                                    <span
                                        class="text-[10px] font-bold text-gray-400">{{ $scheduleModel->route->distance_km }}
                                        km</span>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0 text-right">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tiba (Est)
                                </p>
                                <p class="text-base font-bold text-gray-800 leading-tight">
                                    {{ $scheduleModel->route->destinationAgent->city ?? 'N/A' }}</p>
                                <p class="text-[11px] text-gray-500 mt-0.5 truncate">
                                    {{ $scheduleModel->route->destinationAgent->name ?? '-' }}</p>
                                <p class="text-lg font-black text-emerald-600 mt-2 leading-none">
                                    {{ $scheduleModel->arrival_time ? \Carbon\Carbon::parse($scheduleModel->arrival_time)->format('H:i') : '-' }}
                                </p>
                                <p class="text-[10px] font-semibold text-gray-400 mt-0.5">
                                    {{ $scheduleModel->arrival_date ? $scheduleModel->arrival_date->format('d M y') : '-' }}
                                </p>
                            </div>
                        </div>

                        {{-- Data Armada, Supir, Harga, Kursi --}}
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-dashed border-gray-200">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase mb-1 flex items-center gap-1">
                                    <x-heroicon-s-truck class="w-3 h-3" /> Armada
                                </p>
                                <p class="text-sm font-bold text-gray-800 leading-tight">
                                    {{ $scheduleModel->bus->name ?? 'N/A' }}
                                    <br>
                                    <span
                                        class="text-xs font-normal text-gray-500">({{ $scheduleModel->bus->plate_number ?? '-' }})</span>
                                </p>
                                <p class="text-[11px] text-gray-500 mt-1">Supir: <span
                                        class="font-semibold text-gray-800">{{ $scheduleModel->driver->name ?? 'Belum Ditentukan' }}</span>
                                </p>
                            </div>
                            <div class="text-right">
                                <p
                                    class="text-[10px] font-bold text-gray-400 uppercase mb-1 flex items-center justify-end gap-1">
                                    Biaya & Kursi <x-heroicon-s-ticket class="w-3 h-3" /></p>
                                <p class="text-sm font-black text-primary-700 leading-tight">Rp
                                    {{ number_format($scheduleModel->price, 0, ',', '.') }}<span
                                        class="text-[10px] font-normal text-gray-500">/kursi</span></p>
                                <p class="text-[11px] text-gray-500 mt-1">Sisa Kursi: <span
                                        class="font-bold {{ $scheduleModel->available_seats <= 2 ? 'text-red-500' : 'text-emerald-600' }}">{{ $scheduleModel->available_seats }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- {{-- Actions --}}
            <div class="flex gap-3 pt-2">
                <a href="{{ route('schedules.index') }}" class="flex-1 py-3 rounded-xl border border-gray-200 text-center text-sm font-semibold text-gray-600 hover:bg-gray-50 active:scale-[0.98] transition-all">
                    Kembali ke Daftar
                </a>
                <a href="{{ route('schedules.edit', $scheduleModel) }}" class="flex-1 py-3 rounded-xl text-center text-sm font-semibold text-white
          hover:opacity-90 active:scale-[0.98] transition-all" style="background: linear-gradient(160deg, #0D47A1 0%, #1565C0 50%, #1976D2 100%);">
                    Edit Jadwal
                </a>
            </div> -->
            @elseif($activeTab === 'passengers')
                <div class="space-y-3 mt-4">
                    @forelse($this->passengers as $passenger)
                        <a href="{{ route('passengers.show', $passenger) }}"
                            class="block bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all overflow-hidden relative">

                            {{-- Baris 1: Avatar, Nama, HP --}}
                            <div class="flex items-start gap-3 p-3 pb-2 pr-24">
                                <div
                                    class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-emerald-50 border border-emerald-100">
                                    <x-heroicon-s-user class="w-5 h-5 text-emerald-600" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-gray-900 leading-tight">
                                        {{ $passenger->name }}
                                    </p>
                                    <p class="text-[10px] text-gray-500 font-semibold mt-0.5 flex items-center gap-1">
                                        <x-heroicon-o-device-phone-mobile class="w-3 h-3" />
                                        {{ $passenger->phone ?? '-' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Status Lunas/Belum Lunas di Pojok Kanan Atas --}}
                            <div class="absolute top-0 right-0 flex flex-col items-end">
                                <span
                                    class="text-[9px] font-bold px-3 py-1 rounded-bl-lg text-white shadow-sm {{ $passenger->booking->payment_status === 'paid' ? 'bg-emerald-500' : 'bg-red-500' }}">
                                    {{ $passenger->booking->payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}
                                </span>
                            </div>

                            {{-- Baris 2: Booking, Harga Tiket & Kursi --}}
                            <div
                                class="px-3 pb-2 flex items-center justify-between text-[10px] border-t border-gray-50 pt-2 bg-gray-50/50">
                                <div class="text-gray-600 truncate mr-2 font-bold flex items-center gap-1">
                                    <x-heroicon-o-ticket class="w-3 h-3 text-emerald-600" />
                                    {{ $passenger->booking->booking_code ?? '-' }}
                                </div>

                                {{-- PERUBAHAN DI SINI: Harga dan Kursi dikelompokkan --}}
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="font-black text-orange-500 text-xs">
                                        {{-- CATATAN: Sesuaikan variabel ini dengan struktur database Anda --}}
                                        Rp{{ number_format($passenger->booking->total_price ?? 0, 0, ',', '.') }}
                                    </span>
                                    <div
                                        class="flex items-center gap-1 text-emerald-700 font-black px-2 py-0.5 bg-emerald-100 rounded-md">
                                        KURSI: {{ $passenger->seat_number ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>

                        </a>
                    @empty
                        <div class="text-center py-10 bg-white rounded-2xl shadow-sm border border-gray-100">
                            <x-heroicon-o-users class="w-8 h-8 text-gray-300 mx-auto" />
                            <p class="text-gray-500 font-semibold text-sm mt-2">Tidak ada data penumpang di rute ini.
                            </p>
                        </div>
                    @endforelse
                </div>
            @elseif($activeTab === 'cargos')
                <div class="space-y-3 mt-4">
                    @forelse($this->cargos as $cargo)
                        {{-- PANGGIL KOMPONEN DI SINI --}}
                        <x-card.cargo-card :cargo="$cargo" />

                    @empty
                        <div class="bg-white rounded-xl p-8 shadow-sm border border-dashed border-gray-300 text-center">
                            <x-heroicon-o-cube class="w-10 h-10 text-gray-300 mx-auto mb-3" />
                            <p class="text-gray-500 font-bold text-sm">Tidak Ada Data Cargo</p>
                        </div>
                    @endforelse
                </div>
            @endif

            {{-- FAB Edit Jadwal (kanan bawah, orange) --}}
            <a href="{{ route('schedules.edit', $scheduleModel) }}"
                class="fixed right-4 z-40 w-14 h-14 rounded-full text-white flex items-center justify-center shadow-lg active:scale-95 transition-transform border-2 border-white/30"
                style="bottom: calc(72px + env(safe-area-inset-bottom)); background: linear-gradient(135deg, #F57C00, #FF9800); box-shadow: 0 4px 20px rgba(245,124,0,0.45);"
                title="Edit jadwal">
                <x-heroicon-o-pencil class="w-7 h-7" />
            </a>

        </div>
    </x-layouts.app>
</div>
