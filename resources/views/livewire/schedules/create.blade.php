<?php
use function Livewire\Volt\{state};
use App\Models\Schedule;

state([]);
?>

<div>
    <x-layouts.app title="Buat Jadwal">
        <div class="px-4 pt-6 pb-24 space-y-6">

            {{-- Header --}}
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Buat Jadwal Baru</h1>
                <p class="text-sm text-gray-500 mt-1">Tambahkan jadwal perjalanan baru ke sistem</p>
            </div>

            {{-- Form Container --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <livewire:schedules.form />
            </div>

        </div>
    </x-layouts.app>
</div>