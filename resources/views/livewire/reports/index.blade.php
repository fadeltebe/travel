<?php
use function Livewire\Volt\{state, computed};
use App\Models\Schedule;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Cargo;
use Illuminate\Database\Eloquent\Builder;

state([
    'selectedMonth' => now()->format('m'),
    'selectedYear' => now()->format('Y'),
]);

$months = computed(function() {
    return [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
});

$years = computed(function() {
    $currentYear = now()->year;
    return range($currentYear - 2, $currentYear + 1);
});

$metrics = computed(function () {
    $user = auth()->user();
    $month = $this->selectedMonth;
    $year = $this->selectedYear;

    // Base Queries
    $schedulesQuery = Schedule::query()->whereMonth('departure_date', $month)->whereYear('departure_date', $year);
    $bookingsQuery = Booking::query()->whereHas('schedule', function(Builder $q) use ($month, $year) {
        $q->whereMonth('departure_date', $month)->whereYear('departure_date', $year);
    });
    $passengersQuery = Passenger::query()->whereHas('booking.schedule', function(Builder $q) use ($month, $year) {
        $q->whereMonth('departure_date', $month)->whereYear('departure_date', $year);
    });
    $cargosQuery = Cargo::query()->whereMonth('created_at', $month)->whereYear('created_at', $year);

    // Apply Role Filters
    if (!$user->canViewAll()) {
        $agentId = $user->agent_id;
        $schedulesQuery->whereHas('route', function(Builder $q) use ($agentId) {
            $q->where('origin_agent_id', $agentId)->orWhere('destination_agent_id', $agentId);
        });
        $bookingsQuery->where('agent_id', $agentId);
        $passengersQuery->whereHas('booking', function (Builder $q) use ($agentId) {
            $q->where('agent_id', $agentId);
        });
        $cargosQuery->where(function ($q) use ($agentId) {
            $q->where('origin_agent_id', $agentId)->orWhere('destination_agent_id', $agentId);
        });
    }

    // --- AGGREGATE SCHEDULES ---
    $schedules = clone $schedulesQuery;
    $scheduleStats = [
        'scheduled' => (clone $schedules)->where('status', 'scheduled')->count(),
        'ongoing'   => (clone $schedules)->where('status', 'ongoing')->count(),
        'completed' => (clone $schedules)->where('status', 'completed')->count(),
        'cancelled' => (clone $schedules)->where('status', 'cancelled')->count(),
        'total'     => (clone $schedules)->count(),
    ];

    // --- AGGREGATE PASSENGERS ---
    $passengers = clone $passengersQuery;
    $passengersPaid = (clone $passengers)->whereHas('booking', fn($q) => $q->where('payment_status', 'paid'))->count();
    $passengersUnpaid = (clone $passengers)->whereHas('booking', fn($q) => $q->where('payment_status', '!=', 'paid'))->count();
    $passengerStats = [
        'paid' => $passengersPaid,
        'unpaid' => $passengersUnpaid,
        'total' => $passengersPaid + $passengersUnpaid,
    ];

    // --- AGGREGATE CARGOS ---
    $cargos = clone $cargosQuery;
    $cargoStats = [
        'received' => (clone $cargos)->where('status', 'received')->count(),
        'pending'  => (clone $cargos)->where('status', 'pending')->count(),
        'paid'     => (clone $cargos)->where('is_paid', true)->count(),
        'unpaid'   => (clone $cargos)->where('is_paid', false)->count(),
        'total'    => (clone $cargos)->count(),
    ];

    return [
        'schedules' => $scheduleStats,
        'passengers' => $passengerStats,
        'cargos' => $cargoStats
    ];
});
?>

<div>
    <x-layouts.app title="Laporan Bulanan">
        <div class="px-4 pt-6 pb-24 space-y-6">

            {{-- Header & Filter --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 mb-6">
                <div class="flex flex-col gap-4">
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Laporan Keuangan & Operasional</h1>
                        <p class="text-xs text-gray-500 mt-1">Rekapitulasi data operasional berbasis bulan</p>
                    </div>
                    
                    <div class="flex gap-3">
                        <select wire:model.live="selectedMonth" class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                            @foreach($this->months as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="selectedYear" class="w-28 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all">
                            @foreach($this->years as $yr)
                                <option value="{{ $yr }}">{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- 1. Rekap Perjalanan --}}
            <div>
                <h2 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-md bg-blue-100 flex items-center justify-center text-blue-600"><x-heroicon-s-calendar class="w-3.5 h-3.5"/></div>
                    REKAP PERJALANAN / JADWAL
                </h2>
                <div class="grid grid-cols-1 gap-3">
                    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex justify-between items-center">
                        <span class="text-xs font-semibold text-gray-500">Total Keberangkatan Bulan Ini</span>
                        <span class="text-xl font-black text-gray-800">{{ $this->metrics['schedules']['total'] }} <span class="text-xs font-semibold text-gray-400">Jadwal</span></span>
                    </div>
                </div>
                <div class="grid grid-cols-4 gap-2 mt-3 text-center">
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100 flex flex-col items-center justify-center">
                        <p class="text-[10px] font-bold text-gray-500 uppercase leading-tight">Terjadwal</p>
                        <p class="text-lg font-black text-gray-800 mt-1">{{ $this->metrics['schedules']['scheduled'] }}</p>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-3 border border-blue-100 flex flex-col items-center justify-center">
                        <p class="text-[10px] font-bold text-blue-600 uppercase leading-tight">Berjalan</p>
                        <p class="text-lg font-black text-blue-800 mt-1">{{ $this->metrics['schedules']['ongoing'] }}</p>
                    </div>
                    <div class="bg-emerald-50 rounded-xl p-3 border border-emerald-100 flex flex-col items-center justify-center">
                        <p class="text-[10px] font-bold text-emerald-600 uppercase leading-tight">Selesai</p>
                        <p class="text-lg font-black text-emerald-800 mt-1">{{ $this->metrics['schedules']['completed'] }}</p>
                    </div>
                    <div class="bg-red-50 rounded-xl p-3 border border-red-100 flex flex-col items-center justify-center">
                        <p class="text-[10px] font-bold text-red-600 uppercase leading-tight">Batal</p>
                        <p class="text-lg font-black text-red-800 mt-1">{{ $this->metrics['schedules']['cancelled'] }}</p>
                    </div>
                </div>
            </div>

            {{-- 2. Rekap Penumpang --}}
            <div class="mt-8">
                <h2 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-md bg-emerald-100 flex items-center justify-center text-emerald-600"><x-heroicon-s-users class="w-3.5 h-3.5"/></div>
                    REKAP KEUANGAN PENUMPANG
                </h2>
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm col-span-1 flex flex-col justify-center">
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Total Tiket</span>
                        <span class="text-2xl font-black text-gray-800 mt-1">{{ $this->metrics['passengers']['total'] }}</span>
                    </div>
                    <div class="bg-white p-4 rounded-2xl border border-emerald-100 shadow-sm col-span-2 relative overflow-hidden">
                        <div class="absolute -right-4 -bottom-4 opacity-10"><x-heroicon-s-banknotes class="w-24 h-24 text-emerald-500"/></div>
                        <div class="relative z-10">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[10px] font-bold text-emerald-600 uppercase">Sudah Lunas</span>
                                <span class="text-lg font-black text-emerald-700">{{ $this->metrics['passengers']['paid'] }} Org</span>
                            </div>
                            <div class="w-full bg-emerald-100 rounded-full h-1.5 mb-3">
                                @php $pctPaidPass = $this->metrics['passengers']['total'] > 0 ? ($this->metrics['passengers']['paid'] / $this->metrics['passengers']['total'] * 100) : 0; @endphp
                                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $pctPaidPass }}%"></div>
                            </div>
                            
                            <div class="flex justify-between items-center mt-2 pt-2 border-t border-gray-100">
                                <span class="text-[10px] font-bold text-red-500 uppercase">Belum Lunas</span>
                                <span class="text-sm font-black text-red-600">{{ $this->metrics['passengers']['unpaid'] }} Org</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Rekap Kargo --}}
            <div class="mt-8">
                <h2 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-md bg-orange-100 flex items-center justify-center text-orange-600"><x-heroicon-s-cube class="w-3.5 h-3.5"/></div>
                    REKAP KEUANGAN KARGO
                </h2>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                    <div class="flex justify-between items-end mb-4">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Total Paket Dikelola</p>
                            <p class="text-3xl font-black text-gray-800 mt-1">{{ $this->metrics['cargos']['total'] }} <span class="text-sm text-gray-400 font-semibold">Resi</span></p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 pt-3 border-t border-gray-100">
                        {{-- Status Pengambilan --}}
                        <div>
                            <p class="text-[10px] font-bold text-gray-500 uppercase mb-2">Status Fisik</p>
                            <div class="flex justify-between items-center bg-gray-50 px-3 py-2.5 rounded-lg mb-2 border border-gray-100">
                                <span class="text-[11px] font-semibold text-emerald-600">Terambil</span>
                                <span class="text-base font-black text-gray-800">{{ $this->metrics['cargos']['received'] }}</span>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 px-3 py-2.5 rounded-lg border border-gray-100">
                                <span class="text-[11px] font-semibold text-blue-600">Pending</span>
                                <span class="text-base font-black text-gray-800">{{ $this->metrics['cargos']['pending'] }}</span>
                            </div>
                        </div>
                        
                        {{-- Status Pembayaran --}}
                        <div>
                            <p class="text-[10px] font-bold text-gray-500 uppercase mb-2">Keuangan (COD/Tunai)</p>
                            <div class="flex justify-between items-center bg-emerald-50 px-3 py-2.5 rounded-lg mb-2 border border-emerald-100">
                                <span class="text-[11px] font-bold text-emerald-700">Lunas</span>
                                <span class="text-base font-black text-emerald-800">{{ $this->metrics['cargos']['paid'] }}</span>
                            </div>
                            <div class="flex justify-between items-center bg-red-50 px-3 py-2.5 rounded-lg border border-red-100">
                                <span class="text-[11px] font-bold text-red-600">Tertunggak</span>
                                <span class="text-base font-black text-red-700">{{ $this->metrics['cargos']['unpaid'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Floating Back Button (like dashboard / other pages) --}}
            <div class="mt-6 text-center pb-4">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-gray-700">
                    <x-heroicon-o-arrow-left class="w-4 h-4"/> Kembali ke Dashboard
                </a>
            </div>

        </div>
    </x-layouts.app>
</div>
