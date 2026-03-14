<?php
use function Livewire\Volt\{state};
state([]);
?>

<div>
    <x-layouts.app title="Dashboard">
        <div class="p-4 space-y-4 pb-24">

            {{-- Welcome Card --}}
            <div class="relative overflow-hidden rounded-2xl p-5 text-white" style="background: linear-gradient(135deg, #1d4ed8 0%, #6366f1 100%);">

                {{-- Decorative circles --}}
                <div class="absolute -top-6 -right-6 w-32 h-32 rounded-full opacity-20" style="background: rgba(255,255,255,0.3)"></div>
                <div class="absolute -bottom-8 -right-12 w-40 h-40 rounded-full opacity-10" style="background: rgba(255,255,255,0.4)"></div>

                <div class="relative z-10">
                    <p class="text-blue-200 text-sm">Selamat datang,</p>
                    <h2 class="text-2xl font-bold mt-0.5">
                        {{ auth()->user()->name }}
                    </h2>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="bg-white/20 text-white text-xs font-medium px-2.5 py-1 rounded-full">
                            {{ auth()->user()->role->label() }}
                        </span>
                        <span class="text-blue-200 text-xs">
                            {{ now()->isoFormat('dddd, D MMMM Y') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Stats Grid --}}
            <div class="grid grid-cols-2 gap-3">

                {{-- Jadwal --}}
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-medium text-gray-400">Jadwal Hari Ini</p>
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #dbeafe, #bfdbfe)">
                            <x-heroicon-o-calendar-days class="w-4 h-4 text-blue-600" />
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-gray-800">0</p>
                    <p class="text-xs text-gray-400 mt-1">jadwal aktif</p>
                </div>

                {{-- Booking --}}
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-medium text-gray-400">Total Booking</p>
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0)">
                            <x-heroicon-o-ticket class="w-4 h-4 text-emerald-600" />
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-gray-800">0</p>
                    <p class="text-xs text-gray-400 mt-1">bulan ini</p>
                </div>

                {{-- Penumpang --}}
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-medium text-gray-400">Penumpang</p>
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ede9fe, #ddd6fe)">
                            <x-heroicon-o-users class="w-4 h-4 text-violet-600" />
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-gray-800">0</p>
                    <p class="text-xs text-gray-400 mt-1">hari ini</p>
                </div>

                {{-- Cargo --}}
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-medium text-gray-400">Cargo</p>
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #fef3c7, #fde68a)">
                            <x-heroicon-o-cube class="w-4 h-4 text-amber-600" />
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-gray-800">0</p>
                    <p class="text-xs text-gray-400 mt-1">pengiriman</p>
                </div>

            </div>

            {{-- Quick Access --}}
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                    Akses Cepat
                </p>
                <div class="grid grid-cols-3 gap-3">

                    <a href="{{ route('schedules.index') }}" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100
                          flex flex-col items-center gap-2 active:scale-95 transition-transform">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #1d4ed8, #6366f1)">
                            <x-heroicon-o-calendar-days class="w-5 h-5 text-white" />
                        </div>
                        <span class="text-xs font-medium text-gray-600 text-center">Jadwal</span>
                    </a>

                    <a href="{{ route('agents.index') }}" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100
                          flex flex-col items-center gap-2 active:scale-95 transition-transform">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #059669, #10b981)">
                            <x-heroicon-o-building-office class="w-5 h-5 text-white" />
                        </div>
                        <span class="text-xs font-medium text-gray-600 text-center">Agen</span>
                    </a>

                    <a href="{{ route('cargo.index') }}" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100
                          flex flex-col items-center gap-2 active:scale-95 transition-transform">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #d97706, #f59e0b)">
                            <x-heroicon-o-cube class="w-5 h-5 text-white" />
                        </div>
                        <span class="text-xs font-medium text-gray-600 text-center">Cargo</span>
                    </a>

                </div>
            </div>

        </div>
    </x-layouts.app>
</div>