<?php
use function Livewire\Volt\{state};
use App\Models\Schedule;

state([
    'scheduleModel' => function () {
        $param = request()->route('schedule');
        if ($param instanceof Schedule) {
            $param->load(['route.originAgent', 'route.destinationAgent', 'bus', 'driver']);
            return $param;
        }
        return Schedule::with(['route.originAgent', 'route.destinationAgent', 'bus', 'driver'])->findOrFail($param);
    },
]);
?>

<div>
    <x-layouts.app title="Detail Jadwal">
        <div class="px-4 pt-0 pb-24 space-y-6">

            {{-- Back + Header --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('schedules.index') }}" class="w-9 h-9 rounded-xl flex items-center justify-center border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 active:scale-95 transition-all shadow-sm">
                    <x-heroicon-o-arrow-left class="w-5 h-5" />
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Detail Jadwal</h1>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $scheduleModel->departure_date->format('d M Y') }} · {{ \Carbon\Carbon::parse($scheduleModel->departure_time)->format('H:i') }}</p>
                </div>
            </div>

            {{-- Status Badge --}}
            <div>
                @if($scheduleModel->status === 'active')
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium" style="background: rgba(16, 185, 129, 0.12); color: #059669;">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Aktif
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-600">
                    <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                    Nonaktif
                </span>
                @endif
            </div>

            {{-- Route Card (BRImo / theme style) --}}
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #1d4ed8, #6366f1);">
                        <x-heroicon-o-map-pin class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Rute</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-800">{{ $scheduleModel->route->originAgent->city ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $scheduleModel->route->originAgent->name ?? '-' }}</p>
                    </div>
                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center bg-primary-50">
                        <x-heroicon-o-arrow-right class="w-5 h-5 text-primary-600" />
                    </div>
                    <div class="flex-1 min-w-0 text-right">
                        <p class="text-sm font-bold text-gray-800">{{ $scheduleModel->route->destinationAgent->city ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $scheduleModel->route->destinationAgent->name ?? '-' }}</p>
                    </div>
                </div>
                @if($scheduleModel->route->distance_km || $scheduleModel->route->estimated_duration_minutes)
                <div class="flex gap-4 mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500">
                    @if($scheduleModel->route->distance_km)
                    <span class="flex items-center gap-1">
                        <x-heroicon-o-arrow-path class="w-3.5 h-3.5" /> {{ $scheduleModel->route->distance_km }} km
                    </span>
                    @endif
                    @if($scheduleModel->route->estimated_duration_minutes)
                    <span class="flex items-center gap-1">
                        <x-heroicon-o-clock class="w-3.5 h-3.5" /> ± {{ $scheduleModel->route->estimated_duration_minutes }} menit
                    </span>
                    @endif
                </div>
                @endif
            </div>

            {{-- Waktu Keberangkatan & Tiba --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center bg-primary-50">
                            <x-heroicon-o-play-circle class="w-4 h-4 text-primary-600" />
                        </div>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Berangkat</span>
                    </div>
                    <p class="text-lg font-bold text-gray-800">{{ \Carbon\Carbon::parse($scheduleModel->departure_time)->format('H:i') }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $scheduleModel->departure_date->format('d M Y') }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center bg-emerald-50">
                            <x-heroicon-o-flag class="w-4 h-4 text-emerald-600" />
                        </div>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tiba</span>
                    </div>
                    <p class="text-lg font-bold text-gray-800">{{ $scheduleModel->arrival_time ? \Carbon\Carbon::parse($scheduleModel->arrival_time)->format('H:i') : '-' }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $scheduleModel->arrival_date ? $scheduleModel->arrival_date->format('d M Y') : '-' }}</p>
                </div>
            </div>

            {{-- Bus & Supir --}}
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 space-y-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background: #FFF3E0;">
                        <x-heroicon-o-truck class="w-4 h-4 text-accent-700" />
                    </div>
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Bus</span>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-xs text-gray-400">Nama</p>
                        <p class="font-semibold text-gray-800">{{ $scheduleModel->bus->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Plat</p>
                        <p class="font-semibold text-gray-800">{{ $scheduleModel->bus->plate_number ?? 'N/A' }}</p>
                    </div>
                    @if($scheduleModel->bus->type ?? null)
                    <div>
                        <p class="text-xs text-gray-400">Tipe</p>
                        <p class="font-semibold text-gray-800">{{ $scheduleModel->bus->type }}</p>
                    </div>
                    @endif
                </div>
                @if($scheduleModel->driver)
                <div class="pt-3 border-t border-gray-100">
                    <p class="text-xs text-gray-400 mb-1">Supir</p>
                    <p class="font-semibold text-gray-800">{{ $scheduleModel->driver->name }}</p>
                </div>
                @endif
            </div>

            {{-- Harga & Kursi --}}
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center bg-primary-50">
                        <x-heroicon-o-banknotes class="w-4 h-4 text-primary-600" />
                    </div>
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tarif & Ketersediaan</span>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-xs text-gray-400">Harga per kursi</p>
                        <p class="text-2xl font-bold" style="color: #1565C0;">Rp {{ number_format($scheduleModel->price, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400">Kursi tersedia</p>
                        <p class="text-xl font-bold text-emerald-600">{{ $scheduleModel->available_seats }} kursi</p>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 pt-2">
                <a href="{{ route('schedules.index') }}" class="flex-1 py-3 rounded-xl border border-gray-200 text-center text-sm font-semibold text-gray-600 hover:bg-gray-50 active:scale-[0.98] transition-all">
                    Kembali ke Daftar
                </a>
                <button type="button" class="flex-1 py-3 rounded-xl text-center text-sm font-semibold text-white hover:opacity-90 active:scale-[0.98] transition-all" style="background: linear-gradient(160deg, #0D47A1 0%, #1565C0 50%, #1976D2 100%);">
                    Edit Jadwal
                </button>
            </div>

        </div>
    </x-layouts.app>
</div>
