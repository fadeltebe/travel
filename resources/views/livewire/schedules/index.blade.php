<?php
use function Livewire\Volt\{state, computed};
use App\Models\Schedule;

state([
    'filterYear'   => now()->year,
    'filterMonth'  => now()->month,
    'filterStatus' => '', // Isi: '', 'departure', atau 'arrival'
]);

$schedules = computed(function () {
    $user = auth()->user();

    return Schedule::query()
        ->with(['route.originAgent', 'route.destinationAgent', 'bus', 'driver'])
        
        ->addSelect(['total_passengers_sum' => \App\Models\Passenger::query()
            ->selectRaw('COUNT(*)')
            ->whereNull('passengers.deleted_at')
            ->whereIn('passengers.booking_id', function ($q) {
                $q->select('id')->from('bookings')
                  ->whereColumn('bookings.schedule_id', 'schedules.id')
                  ->whereNull('bookings.deleted_at');
            })
        ])
        ->withSum('bookings as total_cargo_sum', 'total_cargo')
        ->withSum('bookings as total_ticket_revenue', 'total_price')
        ->withSum('bookings as total_cargo_revenue', 'cargo_fee')

        // Filter Hak Akses & Arah Perjalanan (MODIFIKASI DI SINI)
        ->where(function($query) use ($user) {
            $query->whereHas('route', function ($q) use ($user) {
                if ($this->filterStatus === 'departure') {
                    // Filter: Hanya yang berangkat dari agen saya
                    $q->where('origin_agent_id', $user->agent_id);
                } elseif ($this->filterStatus === 'arrival') {
                    // Filter: Hanya yang menuju ke agen saya
                    $q->where('destination_agent_id', $user->agent_id);
                } else {
                    // Semua: Asal ATAU Tujuan adalah agen saya (Logika lama)
                    $q->where('origin_agent_id', $user->agent_id)
                      ->orWhere('destination_agent_id', $user->agent_id);
                }
            });
        })

        ->whereYear('departure_date', (int) $this->filterYear)
        ->whereMonth('departure_date', (int) $this->filterMonth)
        // Hapus filter status lama: ->when($this->filterStatus, ...) 

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
        <div class="relative overflow-hidden text-white mx-4 rounded-2xl px-4 pt-5 pb-10" style="background: linear-gradient(160deg, #0D47A1 0%, #1565C0 50%, #1976D2 100%);">
            <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full opacity-10" style="background: white;"></div>

            {{-- Judul & Tombol Tambah --}}
            <div class="flex items-center justify-between relative z-10">
                <h2 class="text-2xl font-bold">Jadwal Keberangkatan</h2>

                {{-- Tombol Tambah --}}
                <a href="{{ route('schedules.create') }}" wire:navigate class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/40 backdrop-blur-sm hover:bg-white/60 transition-colors">
                    <x-heroicon-o-plus class="w-6 h-6 text-white" />
                </a>
            </div>


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
            <div class="bg-white rounded-2xl p-2 shadow-sm border border-gray-100">
                <div class="grid grid-cols-3 gap-2">
                    @foreach([
                    '' => 'Semua',
                    'departure' => 'Keberangkatan',
                    'arrival' => 'Kedatangan',
                    ] as $value => $label)
                    <button wire:click="$set('filterStatus', '{{ $value }}')" class="text-[11px] font-bold py-2.5 mt-3 rounded-xl transition-all duration-200
                {{ $filterStatus === $value
                    ? 'bg-blue-600 text-white shadow-md' 
                    : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Schedules List (mobile-first) --}}
            <div class="space-y-3">
                @forelse($this->schedules as $schedule)
                @php
                // Logika penentuan arah perjalanan berdasarkan agen yang login
                $userAgentId = auth()->user()->agent_id;
                $isDeparture = $schedule->route->origin_agent_id == $userAgentId;
                $isSuperAdmin = auth()->user()->canViewAll();

                $statusConfig = [
                'scheduled' => ['class' => 'bg-yellow-500', 'label' => 'Dijadwalkan', 'animate' => false],
                'ongoing' => ['class' => 'bg-emerald-500', 'label' => 'Diperjalanan', 'animate' => true],
                'completed' => ['class' => 'bg-blue-500', 'label' => 'Tiba', 'animate' => false],
                'cancelled' => ['class' => 'bg-red-500', 'label' => 'Dibatalkan', 'animate' => false],
                ];
                $status = $statusConfig[$schedule->status] ?? $statusConfig['scheduled'];
                @endphp

                <a href="{{ route('schedules.show', $schedule) }}" class="block bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md active:bg-gray-50 transition-all overflow-hidden relative">

                    {{-- Badge Arah --}}
                    @if(!$isSuperAdmin)
                    <div class="absolute top-0 right-0 flex flex-col items-end">
                        {{-- Biru untuk Keluar, Emerald untuk Masuk --}}
                        <span class="text-[10px] font-bold px-3 py-1 rounded-bl-lg text-white {{ $isDeparture ? 'bg-blue-600' : 'bg-emerald-600' }}">
                            {{ $isDeparture ? 'KEBERANGKATAN' : 'KEDATANGAN' }}
                        </span>

                        {{-- Status Operasional (Tetap menggunakan status asli database) --}}
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-bl-md text-[9px] font-bold text-white shadow-sm {{ $status['class'] }}">
                            @if($status['animate'])
                            <span class="w-1 h-1 rounded-full bg-white animate-ping"></span>
                            @endif
                            {{ strtoupper($status['label']) }}
                        </span>
                    </div>
                    @endif

                    {{-- Baris 1: Ikon & Rute --}}
                    <div class="flex items-start justify-between gap-2 p-4 pb-2">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isDeparture ? 'bg-blue-50' : 'bg-emerald-50' }}">
                                @if($isDeparture)
                                <x-heroicon-o-paper-airplane class="w-5 h-5 text-blue-600 rotate-45" />
                                @else
                                <x-heroicon-o-home-modern class="w-5 h-5 text-emerald-600" />
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-900 leading-tight">
                                    {{ $schedule->route->originAgent->city }} → {{ $schedule->route->destinationAgent->city }}
                                </p>
                                <p class="text-[11px] text-orange-500 font-bold mt-1 flex items-center gap-1">
                                    <x-heroicon-o-calendar class="w-3 h-3" />
                                    {{ $schedule->departure_date->locale('id')->translatedFormat('d F Y') }}
                                    <span class="text-gray-300 mx-1">|</span>
                                    <x-heroicon-o-clock class="w-3 h-3" />
                                    {{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}
                                </p>
                            </div>
                        </div>

                        {{-- Space kosong di kanan karena status sudah pindah ke absolute top-right --}}
                        <div class="w-16"></div>
                    </div>

                    {{-- Baris 2: Info Bus & Pendapatan --}}
                    <div class="px-4 pb-2 flex items-center justify-between">
                        <div class="text-[11px] text-gray-500 italic">
                            {{ $schedule->bus->name ?? 'N/A' }} ({{ $schedule->bus->plate_number ?? '' }})
                        </div>
                        <div class="flex items-center gap-2">
                            <!-- <span class="text-[10px] text-gray-400 uppercase font-semibold">Estimasi:</span> -->

                        </div>
                    </div>

                    {{-- Baris 3: Footer Stats --}}
                    <div class="px-4 pb-3 pt-2 flex items-center justify-between gap-2 border-t border-gray-50 mt-1 bg-gray-50/50">
                        <div class="flex items-center gap-3 text-[11px]">
                            <span class="flex items-center gap-1 text-gray-600">
                                <x-heroicon-o-users class="w-3.5 h-3.5" />
                                <b class="text-gray-900">{{ (int) $schedule->total_passengers_sum }}</b> Pnp
                            </span>
                            <span class="flex items-center gap-1 text-gray-600">
                                <x-heroicon-o-cube class="w-3.5 h-3.5" />
                                <b class="text-gray-900">{{ (int) $schedule->total_cargo_sum }}</b> Barang
                            </span>
                            <span class="flex items-center gap-1 {{ ($schedule->available_seats - $schedule->total_passengers_sum) <= 2 ? 'text-red-600' : 'text-emerald-600' }} font-bold">
                                {{ $schedule->available_seats - $schedule->total_passengers_sum }} Kursi Sisa
                            </span>
                        </div>

                        <span class="text-xl font-black text-orange-500">
                            Rp{{ number_format(($schedule->total_ticket_revenue ?? 0) + ($schedule->total_cargo_revenue ?? 0), 0, ',', '.') }}
                        </span>
                    </div>
                </a>
                @empty
                <div class="bg-white rounded-xl p-8 shadow-sm border border-dashed border-gray-300 text-center">
                    <x-heroicon-o-calendar-days class="w-10 h-10 text-gray-300 mx-auto mb-3" />
                    <p class="text-gray-500 font-bold text-sm">Tidak Ada Jadwal</p>
                    <p class="text-xs text-gray-400">Silakan sesuaikan filter atau buat jadwal baru.</p>
                </div>
                @endforelse
            </div>

        </div>

        <!-- {{-- FAB Tambah Jadwal (kanan bawah, orange) --}}
        <a href="{{ route('schedules.create') }}" class="fixed right-4 z-40 w-14 h-14 rounded-full text-white flex items-center justify-center shadow-lg active:scale-95 transition-transform border-2 border-white/30" style="bottom: calc(72px + env(safe-area-inset-bottom)); background: linear-gradient(135deg, #F57C00, #FF9800); box-shadow: 0 4px 20px rgba(245,124,0,0.45);" title="Tambah jadwal">
            <x-heroicon-o-plus class="w-7 h-7" />
        </a> -->


    </x-layouts.app>
</div>