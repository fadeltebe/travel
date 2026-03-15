<?php
use function Livewire\Volt\{state, computed};
use App\Models\Schedule;

state([
    'filterYear'   => now()->year,
    'filterMonth'  => now()->month,
    'filterStatus' => '',
]);

$schedules = computed(function () {
    return Schedule::query()
        ->with('route.originAgent', 'route.destinationAgent', 'bus', 'driver')
        ->withSum('bookings as total_passengers_sum', 'total_passengers')
        ->withSum('bookings as total_cargo_sum', 'total_cargo')
        ->withSum('bookings as total_ticket_revenue', 'total_price') // Total dari tiket
        ->withSum('bookings as total_cargo_revenue', 'cargo_fee')    // Total dari biaya kargo
        ->whereYear('departure_date', (int) $this->filterYear)
        ->whereMonth('departure_date', (int) $this->filterMonth)
        ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
        ->orderBy('departure_date', 'desc')
        ->orderBy('departure_time', 'desc')
        ->get();
});
?>

<div>
    <x-layouts.app title="Jadwal">

        <style>
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
</style>


        {{-- Header --}}
        <div class="relative overflow-hidden text-white mx-4 rounded-2xl px-4 pt-5 pb-10 mt-3" style="background: linear-gradient(160deg, #0D47A1 0%, #1565C0 50%, #1976D2 100%);">
            <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full opacity-10" style="background: white;"></div>

            <h2 class="text-2xl font-bold">Jadwal Keberangkatan</h2>

            {{-- Filter Bulan & Tahun --}}
            <div class="mt-4 grid grid-cols-2 gap-3">
                <div>

                    <select id="filterMonth" wire:model.live="filterMonth" class="w-full px-3 py-2 rounded-lg border border-blue-200 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent text-gray-700">
                        @for($m = 1; $m <= 12; $m++) <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->locale('id')->translatedFormat('F') }}</option>
                            @endfor
                    </select>
                </div>
                <div>
                    <select id="filterYear" wire:model.live="filterYear" class="w-full px-3 py-2 rounded-lg border border-blue-200 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent text-gray-700">
                        @foreach(range(now()->year - 2, now()->year + 1) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="px-4 -mt-4 space-y-4 pb-24">

            {{-- Status Filter --}}
            <div class="bg-white rounded-2xl p-3 shadow-sm border border-gray-100">
                <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-none">

                    {{-- Status Filter --}}
                    <div class="flex gap-2 overflow-x-auto mt-3 pb-1 scrollbar-none">
                        @foreach([
                        '' => 'Semua',
                        'scheduled' => 'Dijadwalkan',
                        'ongoing' => 'Diperjalanan',
                        'completed' => 'Tiba',
                        'cancelled' => 'Dibatalkan',
                        ] as $value => $label)
                        <button wire:click="$set('filterStatus', '{{ $value }}')" class="shrink-0 text-xs font-medium px-3 py-1.5 rounded-full
                                   transition-colors
                                   {{ $filterStatus === $value
                                       ? 'bg-primary-800 text-white'
                                       : 'bg-gray-100 text-gray-500' }}">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>

                </div>
            </div>

            {{-- Schedules List (mobile-first) --}}
            <div class="space-y-3">
                @forelse($this->schedules as $schedule)
                <a href="{{ route('schedules.show', $schedule) }}" class="block bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md active:bg-gray-50 transition-all overflow-hidden">
                    {{-- Baris 1: Rute --}}
                    <div class="flex items-start justify-between gap-2 p-4 pb-2">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #1d4ed8, #6366f1);">
                                <x-heroicon-o-map-pin class="w-5 h-5 text-white" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-800 leading-tight">{{ $schedule->route->originAgent->city ?? 'N/A' }} → {{ $schedule->route->destinationAgent->city ?? 'N/A' }} <span class="text-orange-400 whitespace-nowrap">{{ $schedule->departure_date->locale('id')->translatedFormat('d F Y') }}
                                        <span class="text-gray-400 whitespace-nowrap">|</span>
                                    </span> {{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $schedule->bus->name ?? 'N/A' }} · {{ $schedule->bus->plate_number ?? '' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Baris 2: Waktu, tanggal, harga, kursi --}}
                    <div class="px-4 pb-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">

                        {{-- Total Pendapatan (Penumpang + Barang) --}}
                        <span class="font-bold text-primary-600">
                            Rp {{ number_format(($schedule->total_ticket_revenue ?? 0) + ($schedule->total_cargo_revenue ?? 0), 0, ',', '.') }}
                        </span>

                        <span class="text-emerald-600 font-semibold">
                            {{ $schedule->available_seats - $schedule->total_passengers_sum }} kursi
                        </span>
                    </div>

                    {{-- Baris 3: Penumpang | Barang + Status --}}
                    <div class="px-4 pb-3 pt-2 flex flex-wrap items-center justify-between gap-2 border-t border-gray-50 mt-1">

                        {{-- Penumpang & Barang --}}
                        <div class="flex items-center gap-3 text-xs text-gray-600">
                            <span class="inline-flex items-center gap-1.5">
                                <x-heroicon-o-users class="w-4 h-4 text-gray-400" />
                                <span class="font-semibold text-gray-800">
                                    {{ (int) $schedule->total_passengers_sum }}
                                </span>
                                penumpang
                            </span>
                            <span class="text-gray-200">|</span>
                            <span class="inline-flex items-center gap-1.5">
                                <x-heroicon-o-cube class="w-4 h-4 text-gray-400" />
                                {{-- Barang --}}
                                <span class="font-semibold text-gray-800">
                                    {{ (int) $schedule->total_cargo_sum }}
                                </span>
                                barang
                            </span>
                        </div>

                        {{-- Status Badge --}}
                        @php
                        $statusConfig = [
                        'scheduled' => ['class' => 'bg-gray-500', 'label' => 'Dijadwalkan', 'animate' => false],
                        'ongoing' => ['class' => 'bg-emerald-500', 'label' => 'Diperjalanan', 'animate' => true],
                        'completed' => ['class' => 'bg-blue-500', 'label' => 'Tiba', 'animate' => false],
                        'cancelled' => ['class' => 'bg-red-500', 'label' => 'Dibatalkan', 'animate' => false],
                        ];
                        $status = $statusConfig[$schedule->status] ?? $statusConfig['scheduled'];
                        @endphp

                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full
             text-xs font-medium text-white {{ $status['class'] }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-white/70
                 {{ $status['animate'] ? 'animate-pulse' : '' }}">
                            </span>
                            {{ $status['label'] }}
                        </span>

                    </div>
                </a>
                @empty
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 text-center">
                    <div class="flex justify-center mb-3">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center" style="background: rgba(229, 231, 235, 0.5);">
                            <x-heroicon-o-calendar-days class="w-6 h-6 text-gray-400" />
                        </div>
                    </div>
                    <p class="text-gray-500 font-semibold text-sm">Belum ada jadwal</p>
                    <p class="text-xs text-gray-400 mt-0.5">Mulai buat jadwal untuk memulai operasional</p>
                </div>
                @endforelse
            </div>

        </div>

        {{-- FAB Tambah Jadwal (kanan bawah, orange) --}}
        <a href="{{ route('schedules.create') }}" class="fixed right-4 z-40 w-14 h-14 rounded-full text-white flex items-center justify-center shadow-lg active:scale-95 transition-transform border-2 border-white/30" style="bottom: calc(72px + env(safe-area-inset-bottom)); background: linear-gradient(135deg, #F57C00, #FF9800); box-shadow: 0 4px 20px rgba(245,124,0,0.45);" title="Tambah jadwal">
            <x-heroicon-o-plus class="w-7 h-7" />
        </a>


    </x-layouts.app>
</div>