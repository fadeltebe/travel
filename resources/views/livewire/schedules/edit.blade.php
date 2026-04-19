<?php
use function Livewire\Volt\{state, mount};
use App\Models\Schedule;

state([
    'scheduleId' => null,
    'routeLabel' => '',
    'departure_date' => '',
]);

mount(function (Schedule $schedule) {
    $this->scheduleId     = $schedule->id;
    $this->routeLabel     = ($schedule->route->originAgent->city ?? 'N/A')
                          . ' → '
                          . ($schedule->route->destinationAgent->city ?? 'N/A');
    $this->departure_date = $schedule->departure_date->format('d M Y');
});
?>

<div>
    <x-layouts.app title="Edit Jadwal">
        <div class="px-4 pt-6 pb-24 space-y-6">

            {{-- Header --}}
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Jadwal</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $routeLabel }}
                    <span class="text-gray-400 mx-1">•</span>
                    {{ $departure_date }}
                </p>
            </div>

            {{-- Form --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <livewire:schedules.form :schedule="$scheduleId" />
            </div>

        </div>
    </x-layouts.app>
</div>