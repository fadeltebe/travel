<?php
use function Livewire\Volt\{state, computed};
use App\Models\Schedule;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Cargo;
use Illuminate\Database\Eloquent\Builder;

state([
    'filterDate' => now()->format('Y-m-d'),
]);

$metrics = computed(function () {
    $user = auth()->user();
    $date = $this->filterDate;

    // 1. Jadwal Berangkat Hari Ini
    $schedulesQuery = Schedule::whereDate('departure_date', $date);

    // 2. Booking Terjadwal Hari Ini
    $bookingsQuery = Booking::whereHas('schedule', function (Builder $q) use ($date) {
        $q->whereDate('departure_date', $date);
    });

    // 3. Penumpang Berangkat Hari Ini
    $passengersQuery = Passenger::whereHas('booking.schedule', function (Builder $q) use ($date) {
        $q->whereDate('departure_date', $date);
    });

    // 4. Cargo Diterima Hari Ini
    $cargosQuery = Cargo::whereDate('created_at', $date);

    // Terapkan Filter Berdasarkan Role
    if (!$user->canViewAll()) {
        if ($user->isDriver()) {
            // Driver: Hanya melihat data dari jadwal yang dia sopiri
            $schedulesQuery->where('driver_id', $user->id);

            $bookingsQuery->whereHas('schedule', function (Builder $q) use ($user) {
                $q->where('driver_id', $user->id);
            });

            $passengersQuery->whereHas('booking.schedule', function (Builder $q) use ($user) {
                $q->where('driver_id', $user->id);
            });

            $cargosQuery->whereHas('booking.schedule', function (Builder $q) use ($user) {
                $q->where('driver_id', $user->id);
            });
        } else {
            // Admin Agen: Filter berdasarkan agent_id
            $agentId = $user->agent_id;

            $schedulesQuery->whereHas('route', function (Builder $q) use ($agentId) {
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
    }

    return [
        'schedules' => $schedulesQuery->count(),
        'bookings' => $bookingsQuery->count(),
        'passengers' => $passengersQuery->count(),
        'cargos' => $cargosQuery->count(),
    ];
});

?>

<div>
    <div class="overflow-x-hidden">
        <x-layouts.app title="Dashboard">

            {{-- Header --}}
            <div class="relative text-white mx-4 rounded-2xl px-4 pt-5 pb-12 mt-0"
                style="background: linear-gradient(160deg, #0D47A1 0%, #1565C0 50%, #1976D2 100%);">

                {{-- Decorative circle --}}
                <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full opacity-10" style="background: white;"></div>

                <p class="text-blue-200 text-sm">Selamat datang,</p>
                <h2 class="text-2xl font-bold">{{ auth()->user()->name }}</h2>

                <div class="flex items-center gap-2 mt-2">
                    <span class="text-xs font-semibold px-3 py-1 rounded-full"
                        style="background: #F57C00; color: white;">
                        {{ auth()->user()->role->label() }}
                    </span>
                    <span class="text-blue-200 text-xs">
                        {{ now()->locale('id')->translatedFormat('d F Y') }}
                    </span>
                </div>
            </div>

            {{-- Content --}}
            <div class="px-4 -mt-6 space-y-5 pb-4">

                @if (auth()->user()->canViewAll())
                    {{-- Quick Actions --}}
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                        <div class="grid grid-cols-4 gap-2 mt-6">

                            <a href="{{ route('billings.index') }}"
                                class="flex flex-col items-center gap-2 active:scale-90 transition-transform">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center"
                                    style="background: #E3F2FD">
                                    <x-heroicon-o-wallet class="w-6 h-6 text-primary-800" />
                                </div>
                                <span class="text-[11px] font-medium text-gray-600 text-center leading-tight">
                                    Token
                                </span>
                            </a>

                            <a href="{{ route('agents.index') }}"
                                class="flex flex-col items-center gap-2 active:scale-90 transition-transform">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center"
                                    style="background: #FFF3E0">
                                    <x-heroicon-o-building-office class="w-6 h-6 text-accent-700" />
                                </div>
                                <span class="text-[11px] font-medium text-gray-600 text-center leading-tight">
                                    Agen
                                </span>
                            </a>

                            <a href="{{ route('reports.index') }}"
                                class="flex flex-col items-center gap-2 active:scale-90 transition-transform">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center"
                                    style="background: #F3E5F5">
                                    <x-heroicon-o-chart-bar class="w-6 h-6 text-purple-700" />
                                </div>
                                <span class="text-[11px] font-medium text-gray-600 text-center leading-tight">
                                    Laporan
                                </span>
                            </a>

                            <a href="{{ route('settings.index') }}"
                                class="flex flex-col items-center gap-2 active:scale-90 transition-transform">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center"
                                    style="background: #E8F5E9">
                                    <x-heroicon-o-cog-8-tooth class="w-6 h-6 text-green-700" />
                                </div>
                                <span class="text-[11px] font-medium text-gray-600 text-center leading-tight">
                                    Pengaturan
                                </span>
                            </a>

                        </div>
                    </div>
                @elseif (auth()->user()->isDriver())
                    {{-- Driver: Tidak ada quick actions khusus, tampilkan info singkat --}}
                @elseif (auth()->user()->agent_id)
                    {{-- Quick Actions for Agent --}}
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                        <div class="flex gap-4 mt-2">

                            <a href="{{ route('agent.reports.index') }}"
                                class="flex flex-col items-center gap-2 active:scale-90 transition-transform">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center"
                                    style="background: #E8EAF6">
                                    <x-heroicon-o-chart-bar class="w-6 h-6 text-indigo-700" />
                                </div>
                                <span class="text-[11px] font-medium text-gray-600 text-center leading-tight">
                                    Laporan Agen
                                </span>
                            </a>

                        </div>
                    </div>
                @endif

                {{-- Stats --}}
                <div>
                    {{-- Header Ringkasan & Filter Tanggal --}}
                    <div class="flex justify-between items-center mb-3 mt-8">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                            @if ($filterDate === now()->format('Y-m-d'))
                                Ringkasan Hari Ini
                            @else
                                Ringkasan:
                                {{ \Carbon\Carbon::parse($filterDate)->toIndoDate() }}
                            @endif
                        </p>

                        {{-- Tombol Filter Kalender --}}
                        <input type="date" wire:model.live="filterDate"
                            class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-600 bg-white shadow-sm cursor-pointer">
                    </div>

                    <div class="grid grid-cols-2 gap-3">

                        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                                    style="background: #E3F2FD">
                                    <x-heroicon-o-calendar-days class="w-4 h-4 text-primary-800" />
                                </div>
                                <div>
                                    <p class="text-[11px] text-gray-400">Jadwal</p>
                                    <p class="text-xl font-bold text-gray-800 leading-tight">
                                        {{ $this->metrics['schedules'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                                    style="background: #FFF3E0">
                                    <x-heroicon-o-ticket class="w-4 h-4 text-accent-700" />
                                </div>
                                <div>
                                    <p class="text-[11px] text-gray-400">Booking</p>
                                    <p class="text-xl font-bold text-gray-800 leading-tight">
                                        {{ $this->metrics['bookings'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                                    style="background: #E8F5E9">
                                    <x-heroicon-o-users class="w-4 h-4 text-green-700" />
                                </div>
                                <div>
                                    <p class="text-[11px] text-gray-400">Penumpang</p>
                                    <p class="text-xl font-bold text-gray-800 leading-tight">
                                        {{ $this->metrics['passengers'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                                    style="background: #F3E5F5">
                                    <x-heroicon-o-cube class="w-4 h-4 text-purple-700" />
                                </div>
                                <div>
                                    <p class="text-[11px] text-gray-400">Cargo</p>
                                    <p class="text-xl font-bold text-gray-800 leading-tight">
                                        {{ $this->metrics['cargos'] }}</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </x-layouts.app>
    </div>
