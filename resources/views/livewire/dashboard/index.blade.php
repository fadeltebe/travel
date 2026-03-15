<?php
use function Livewire\Volt\{state,computed};
use App\Models\Schedule;
state([
'filterDate' => now()->format('Y-m-d'),
]);

$schedules = computed(function () {
    return Schedule::query()
        ->with('route.originAgent', 'route.destinationAgent', 'bus', 'driver')
        ->latest()
        ->get();
});

?>

<div>
    <div class="overflow-x-hidden">
        <x-layouts.app title="Dashboard">

            {{-- Header Wave (BRImo style) --}}
            <div class="relative text-white mx-4 rounded-2xl px-4 pt-5 pb-12 mt-0" style="background: linear-gradient(160deg, #0D47A1 0%, #1565C0 50%, #1976D2 100%);">

                {{-- Decorative circle --}}
                <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full opacity-10" style="background: white;"></div>

                <p class="text-blue-200 text-sm">Selamat datang,</p>
                <h2 class="text-2xl font-bold">{{ auth()->user()->name }}</h2>

                <div class="flex items-center gap-2 mt-2">
                    <span class="text-xs font-semibold px-3 py-1 rounded-full" style="background: #F57C00; color: white;">
                        {{ auth()->user()->role->label() }}
                    </span>
                    <span class="text-blue-200 text-xs">
                        {{ now()->isoFormat('dddd, D MMM Y') }}
                    </span>
                </div>
            </div>

            {{-- Content --}}
            <div class="px-4 -mt-6 space-y-5 pb-4">

                {{-- Quick Actions --}}
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                    <div class="grid grid-cols-4 gap-2 mt-6">

                        <a href="{{ route('schedules.index') }}" class="flex flex-col items-center gap-2 active:scale-90 transition-transform">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background: #E3F2FD">
                                <x-heroicon-o-calendar-days class="w-6 h-6 text-primary-800" />
                            </div>
                            <span class="text-[11px] font-medium text-gray-600 text-center leading-tight">
                                Jadwal
                            </span>
                        </a>

                        <a href="{{ route('agents.index') }}" class="flex flex-col items-center gap-2 active:scale-90 transition-transform">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background: #FFF3E0">
                                <x-heroicon-o-building-office class="w-6 h-6 text-accent-700" />
                            </div>
                            <span class="text-[11px] font-medium text-gray-600 text-center leading-tight">
                                Agen
                            </span>
                        </a>

                        <a href="{{ route('cargo.index') }}" class="flex flex-col items-center gap-2 active:scale-90 transition-transform">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background: #E8F5E9">
                                <x-heroicon-o-cube class="w-6 h-6 text-green-700" />
                            </div>
                            <span class="text-[11px] font-medium text-gray-600 text-center leading-tight">
                                Cargo
                            </span>
                        </a>

                        <a href="#" class="flex flex-col items-center gap-2 active:scale-90 transition-transform">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background: #F3E5F5">
                                <x-heroicon-o-chart-bar class="w-6 h-6 text-purple-700" />
                            </div>
                            <span class="text-[11px] font-medium text-gray-600 text-center leading-tight">
                                Laporan
                            </span>
                        </a>

                    </div>
                </div>

                {{-- Stats --}}
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                        Ringkasan Hari Ini
                    </p>
                    <div class="grid grid-cols-2 gap-3">

                        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background: #E3F2FD">
                                    <x-heroicon-o-calendar-days class="w-4 h-4 text-primary-800" />
                                </div>
                                <div>
                                    <p class="text-[11px] text-gray-400">Jadwal</p>
                                    <p class="text-xl font-bold text-gray-800 leading-tight">{{ $this->schedules->count() }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background: #FFF3E0">
                                    <x-heroicon-o-ticket class="w-4 h-4 text-accent-700" />
                                </div>
                                <div>
                                    <p class="text-[11px] text-gray-400">Booking</p>
                                    <p class="text-xl font-bold text-gray-800 leading-tight">0</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background: #E8F5E9">
                                    <x-heroicon-o-users class="w-4 h-4 text-green-700" />
                                </div>
                                <div>
                                    <p class="text-[11px] text-gray-400">Penumpang</p>
                                    <p class="text-xl font-bold text-gray-800 leading-tight">0</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background: #F3E5F5">
                                    <x-heroicon-o-cube class="w-4 h-4 text-purple-700" />
                                </div>
                                <div>
                                    <p class="text-[11px] text-gray-400">Cargo</p>
                                    <p class="text-xl font-bold text-gray-800 leading-tight">0</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </x-layouts.app>
    </div>