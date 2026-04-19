@props(['schedule'])

@php
    $user = auth()->user();
    // Logika penentuan arah perjalanan berdasarkan agen yang login
    $userAgentId = $user->agent_id;
    $isDeparture = $schedule->route->origin_agent_id == $userAgentId;
    $isSuperAdmin = $user->canViewAll();
    $isDriver = $user->isDriver();

    $statusConfig = [
        'scheduled' => ['class' => 'bg-yellow-500', 'label' => 'Dijadwalkan', 'animate' => false],
        'ongoing' => ['class' => 'bg-emerald-500', 'label' => 'Diperjalanan', 'animate' => true],
        'completed' => ['class' => 'bg-blue-500', 'label' => 'Tiba', 'animate' => false],
        'cancelled' => ['class' => 'bg-red-500', 'label' => 'Dibatalkan', 'animate' => false],
    ];
    $status = $statusConfig[$schedule->status] ?? $statusConfig['scheduled'];
@endphp

<a href="{{ route('schedules.show', $schedule) }}"
    class="block bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md active:bg-gray-50 transition-all overflow-hidden relative">

    {{-- Area Top Right: Status & Badge Arah --}}
    <div class="absolute top-0 right-0 flex flex-col items-end">
        {{-- Status Operasional (Muncul untuk semua role) --}}
        <span class="inline-flex items-center gap-1 px-3 py-1 text-[9px] font-bold text-white shadow-sm rounded-bl-lg {{ $status['class'] }}">
            @if ($status['animate'])
                <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping mt-px"></span>
            @endif
            {{ strtoupper($status['label']) }}
        </span>

        @if (!$isSuperAdmin && !$isDriver)
            {{-- Badge Arah: Biru untuk Keluar, Emerald untuk Masuk --}}
            <span class="text-[8px] font-bold px-2 py-0.5 text-white shadow-sm rounded-bl-md {{ $isDeparture ? 'bg-blue-600' : 'bg-emerald-600' }}">
                {{ $isDeparture ? 'KEBERANGKATAN' : 'KEDATANGAN' }}
            </span>
        @endif
    </div>

    {{-- Baris 1: Ikon & Rute --}}
    <div class="flex items-start justify-between gap-2 p-4 pb-2">
        <div class="flex items-center gap-3 min-w-0 flex-1">
            <div
                class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isDeparture ? 'bg-blue-50' : 'bg-emerald-50' }}">
                @if ($isDeparture)
                    <x-heroicon-o-arrow-up-right class="w-5 h-5 text-blue-600" />
                @else
                    <x-heroicon-o-arrow-down-left class="w-5 h-5 text-emerald-600" />
                @endif
            </div>
            <div class="min-w-0">
                <p class="text-sm font-bold text-gray-900 leading-tight">
                    {{ $schedule->route->originAgent->city }} →
                    {{ $schedule->route->destinationAgent->city }}
                </p>
                <p class="text-[11px] text-orange-500 font-bold mt-1 flex items-center gap-1">
                    <x-heroicon-o-calendar class="w-3 h-3" />
                    {{ $schedule->departure_date->toIndoDayDate() }}
                    <span class="text-gray-300 mx-1">|</span>
                    <x-heroicon-o-clock class="w-3 h-3" />
                    {{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}
                </p>
            </div>
        </div>
        <div class="w-16"></div>
    </div>

    {{-- Baris 2: Info Bus & Pendapatan --}}
    <div class="px-4 pb-2 flex items-center justify-between">
        <div class="text-[11px] text-gray-500 italic">
            {{ $schedule->bus->name ?? 'N/A' }} ({{ $schedule->bus->plate_number ?? '' }})
        </div>
        <div class="flex items-center gap-2">
        </div>
    </div>

    {{-- Baris 3: Footer Stats --}}
    <div
        class="px-4 pb-3 pt-2 flex items-center justify-between gap-2 border-t border-gray-50 mt-1 bg-gray-50/50">
        <div class="flex items-center gap-3 text-[11px]">
            <span class="flex items-center gap-1 text-gray-600">
                <x-heroicon-o-users class="w-3.5 h-3.5" />
                <b class="text-gray-900">{{ (int) $schedule->total_passengers_sum }}</b> Pnp
            </span>
            <span class="flex items-center gap-1 text-gray-600">
                <x-heroicon-o-cube class="w-3.5 h-3.5" />
                <b class="text-gray-900">{{ (int) $schedule->total_cargo_sum }}</b> Pkt
            </span>
            <span
                class="flex items-center font-bold gap-1 {{ $schedule->available_seats - $schedule->total_passengers_sum <= 2 ? 'text-red-600' : 'text-emerald-600' }}">
                {{ $schedule->available_seats - $schedule->total_passengers_sum }} Kursi
            </span>
        </div>

        <span class="font-black text-emerald-600">
            Rp{{ number_format($schedule->total_ticket_revenue ?? 0, 0, ',', '.') }}
        </span>
    </div>
</a>
