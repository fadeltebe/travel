<?php
use function Livewire\Volt\{state, computed, usesPagination};
use App\Models\Schedule;

usesPagination();

state([
    'filterYear' => now()->year,
    'filterMonth' => now()->month,
    'filterStatus' => '', // Isi: '', 'departure', atau 'arrival'
]);

$schedules = computed(function () {
    $user = auth()->user();

    return Schedule::query()
        ->with(['route.originAgent', 'route.destinationAgent', 'bus', 'driver'])

        ->addSelect([
            'total_passengers_sum' => \App\Models\Passenger::query()
                ->selectRaw('COUNT(*)')
                ->whereNull('passengers.deleted_at')
                ->whereIn('passengers.booking_id', function ($q) {
                    $q->select('id')->from('bookings')->whereColumn('bookings.schedule_id', 'schedules.id')->whereNull('bookings.deleted_at');
                }),
        ])
        ->withSum('bookings as total_cargo_sum', 'total_cargo')
        ->withSum('bookings as total_ticket_revenue', 'total_price')

        // Filter Hak Akses & Arah Perjalanan - HANYA berlaku untuk Admin, bukan Owner/SuperAdmin/Driver
        ->when(!$user->canViewAll() && !$user->isDriver(), function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->whereHas('route', function ($route) use ($user) {
                    if ($this->filterStatus === 'departure') {
                        // Filter: Hanya yang berangkat dari agen saya
                        $route->where('origin_agent_id', $user->agent_id);
                    } elseif ($this->filterStatus === 'arrival') {
                        // Filter: Hanya yang menuju ke agen saya
                        $route->where('destination_agent_id', $user->agent_id);
                    } else {
                        // Semua: Asal ATAU Tujuan adalah agen saya
                        $route->where('origin_agent_id', $user->agent_id)->orWhere('destination_agent_id', $user->agent_id);
                    }
                });
            });
        })

        ->whereYear('departure_date', (int) $this->filterYear)
        ->whereMonth('departure_date', (int) $this->filterMonth)

        ->orderBy('departure_date', 'asc')
        ->orderBy('departure_time', 'asc')
        ->cursorPaginate(15);
});
?>

<div>
    <x-layouts.app title="Jadwal">

        <style>
            @keyframes pulse {

                0%,
                100% {
                    opacity: 1;
                }

                50% {
                    opacity: 0.5;
                }
            }
        </style>

        {{-- Header --}}
        <div class="relative overflow-hidden text-white mx-4 rounded-2xl px-4 pt-5 pb-10"
            style="background: linear-gradient(160deg, #0D47A1 0%, #1565C0 50%, #1976D2 100%);">
            <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full opacity-10" style="background: white;"></div>

            {{-- Judul & Tombol Tambah --}}
            <div class="flex items-center justify-between relative z-10">
                <h2 class="text-2xl font-bold">Jadwal Keberangkatan</h2>

                {{-- Tombol Tambah --}}
                {{-- <a href="{{ route('schedules.create') }}" wire:navigate class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/40 backdrop-blur-sm hover:bg-white/60 transition-colors">
                    <x-heroicon-o-plus class="w-6 h-6 text-white" />
                </a> --}}
            </div>

            {{-- Filter Bulan & Tahun --}}
            <div class="mt-4 grid grid-cols-2 gap-3">
                <div>

                    <select id="filterMonth" wire:model.live="filterMonth"
                        class="w-full px-3 py-2 rounded-lg border border-blue-200 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent text-gray-700">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">
                                {{ \Carbon\Carbon::create()->month($m)->locale('id')->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <select id="filterYear" wire:model.live="filterYear"
                        class="w-full px-3 py-2 rounded-lg border border-blue-200 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent text-gray-700">
                        @foreach (range(now()->year - 2, now()->year + 1) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="px-4 -mt-4 space-y-4 pb-24">

            {{-- Status Filter - Hanya tampil untuk admin agen, bukan driver --}}
            @if (!auth()->user()->isDriver())
            <div class="bg-white rounded-2xl p-2 shadow-sm border border-gray-100">
                <div class="grid grid-cols-3 gap-2">
                    @foreach ([
        '' => 'Semua',
        'departure' => 'Keberangkatan',
        'arrival' => 'Kedatangan',
    ] as $value => $label)
                        <button wire:click="$set('filterStatus', '{{ $value }}')"
                            class="text-[11px] font-bold py-2.5 mt-3 rounded-xl transition-all duration-200
                {{ $filterStatus === $value
                    ? 'bg-blue-600 text-white shadow-md'
                    : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Schedules List (mobile-first) --}}
            <div class="space-y-3">
                @forelse($this->schedules as $schedule)
                    <x-card.schedule-card :schedule="$schedule" />
                @empty
                    <div class="bg-white rounded-xl p-8 shadow-sm border border-dashed border-gray-300 text-center">
                        <x-heroicon-o-calendar-days class="w-10 h-10 text-gray-300 mx-auto mb-3" />
                        <p class="text-gray-500 font-bold text-sm">Tidak Ada Jadwal</p>
                        <p class="text-xs text-gray-400">Silakan sesuaikan filter atau buat jadwal baru.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $this->schedules->links() }}
            </div>

        </div>

        <!-- {{-- FAB Tambah Jadwal (kanan bawah, orange) --}}
        <a href="{{ route('schedules.create') }}" class="fixed right-4 z-40 w-14 h-14 rounded-full text-white flex items-center justify-center shadow-lg active:scale-95 transition-transform border-2 border-white/30" style="bottom: calc(72px + env(safe-area-inset-bottom)); background: linear-gradient(135deg, #F57C00, #FF9800); box-shadow: 0 4px 20px rgba(245,124,0,0.45);" title="Tambah jadwal">
            <x-heroicon-o-plus class="w-7 h-7" />
        </a> -->

    </x-layouts.app>
</div>
