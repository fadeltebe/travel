<?php
use function Livewire\Volt\{state, computed};
use App\Models\Schedule;
use Carbon\Carbon;

state([
    'filterYear'  => fn() => now()->year,
    'filterMonth' => fn() => (int) now()->format('n'), // 1-12
]);

$schedules = computed(function () {
    $date = Carbon::createFromDate($this->filterYear, $this->filterMonth, 1);
    return Schedule::query()
        ->with('route.originAgent', 'route.destinationAgent', 'bus')
        ->withSum('bookings', 'total_passengers')
        ->withSum('bookings', 'total_cargo')
        ->whereYear('departure_date', $date->year)
        ->whereMonth('departure_date', $date->month)
        ->orderBy('departure_date')
        ->orderBy('departure_time')
        ->get();
});
?>

<div>
    <x-layouts.app title="Jadwal">
        <div class="px-4 pt-0 pb-24 space-y-6">

            {{-- Header --}}
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Daftar Jadwal</h1>
            </div>

            {{-- Filter Bulan (bahasa Indonesia) --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="filterMonth" class="block text-xs font-medium text-gray-500 mb-1">Bulan</label>
                    <select id="filterMonth" wire:model.live="filterMonth" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white">
                        @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->locale('id')->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label for="filterYear" class="block text-xs font-medium text-gray-500 mb-1">Tahun</label>
                    <select id="filterYear" wire:model.live="filterYear" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white">
                        @foreach(range(now()->year - 2, now()->year + 1) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Schedules List (mobile-first) --}}
            <div class="space-y-3">
                @forelse($this->schedules as $schedule)
                <a href="{{ route('schedules.show', $schedule) }}" class="block bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md active:bg-gray-50 transition-all overflow-hidden">
                    {{-- Baris 1: Rute + Edit --}}
                    <div class="flex items-start justify-between gap-2 p-4 pb-2">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #1d4ed8, #6366f1);">
                                <x-heroicon-o-map-pin class="w-5 h-5 text-white" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-800 leading-tight">{{ $schedule->route->originAgent->city ?? 'N/A' }} → {{ $schedule->route->destinationAgent->city ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $schedule->bus->name ?? 'N/A' }} · {{ $schedule->bus->plate_number ?? '' }}</p>
                            </div>
                        </div>
                        <button type="button" onclick="event.preventDefault(); event.stopPropagation();" class="p-2 -m-2 rounded-lg hover:bg-orange-50 active:bg-orange-100 transition-colors flex-shrink-0" title="Edit jadwal">
                            <x-heroicon-o-pencil-square class="w-5 h-5 text-accent-600" />
                        </button>
                    </div>

                    {{-- Baris 2: Waktu, tanggal, harga, kursi --}}
                    <div class="px-4 pb-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">
                        <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}</span>
                        <span class="text-gray-400 whitespace-nowrap">{{ $schedule->departure_date->locale('id')->translatedFormat('d F') }}</span>
                        <span class="font-bold text-primary-600">Rp {{ number_format($schedule->price, 0, ',', '.') }}</span>
                        <span class="text-emerald-600 font-semibold">{{ $schedule->available_seats }} kursi</span>
                    </div>

                    {{-- Baris 3: Penumpang | Barang + Status --}}
                    <div class="px-4 pb-2 pt-2 flex flex-wrap items-center justify-between gap-2 border-t border-gray-50 mt-1">
                        <div class="flex items-center gap-3 text-xs text-gray-600">
                            <span class="inline-flex items-center gap-1.5">
                                <x-heroicon-o-users class="w-4 h-4 text-gray-400" />
                                <span class="font-semibold text-gray-800">{{ (int) ($schedule->bookings_sum_total_passengers ?? 0) }}</span> penumpang
                            </span>
                            <span class="text-gray-200">|</span>
                            <span class="inline-flex items-center gap-1.5">
                                <x-heroicon-o-cube class="w-4 h-4 text-gray-400" />
                                <span class="font-semibold text-gray-800">{{ (int) ($schedule->bookings_sum_total_cargo ?? 0) }}</span> barang
                            </span>
                        </div>
                        @if($schedule->status === 'active')
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium" style="background: rgba(16, 185, 129, 0.12); color: #059669;">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Aktif
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                            Nonaktif
                        </span>
                        @endif
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
    </x-layouts.app>
</div>