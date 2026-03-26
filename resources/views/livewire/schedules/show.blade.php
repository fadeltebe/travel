<?php
use function Livewire\Volt\{state, computed};
use App\Models\Schedule;

state([
    'activeTab' => 'details',
    'scheduleModel' => function () {
        $param = request()->route('schedule');
        $schedule = ($param instanceof Schedule) ? $param : Schedule::with(['route.originAgent', 'route.destinationAgent', 'bus', 'driver'])->findOrFail($param);
        
        // Otorisasi: Mencegah agen melihat jadwal cabang lain
        $user = auth()->user();
        if (!$user->canViewAll() && $schedule->route->origin_agent_id !== $user->agent_id && $schedule->route->destination_agent_id !== $user->agent_id) {
            abort(403, 'AKSES DITOLAK: Jadwal ini tidak terdaftar untuk agen Anda.');
        }

        $schedule->loadMissing(['route.originAgent', 'route.destinationAgent', 'bus', 'driver']);
        return $schedule;
    },
]);

$passengers = computed(function () {
    return \App\Models\Passenger::whereHas('booking', function ($q) {
        $q->where('schedule_id', $this->scheduleModel->id);
    })->with('booking')->get();
});

$cargos = computed(function () {
    return \App\Models\Cargo::whereHas('booking', function ($q) {
        $q->where('schedule_id', $this->scheduleModel->id);
    })->with('booking')->get();
});
?>

<div>
    <x-layouts.app title="Detail Jadwal">
        <div class="px-4 pt-0 pb-24 space-y-6">

            @if(session('success'))
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
            @endif

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

            {{-- Tabs --}}
            <div class="flex items-center p-1.5 bg-gray-200/50 rounded-2xl mb-4 mt-2">
                <button wire:click="$set('activeTab', 'details')" class="{{ $activeTab == 'details' ? 'bg-white shadow text-gray-900 pointer-events-none' : 'text-gray-500 hover:text-gray-700' }} flex-1 flex flex-col items-center justify-center gap-1.5 py-2.5 text-xs font-bold rounded-xl transition-all">
                    <x-heroicon-s-calendar class="w-5 h-5 {{ $activeTab == 'details' ? 'text-primary-600' : '' }}" /> Detail
                </button>
                <button wire:click="$set('activeTab', 'passengers')" class="{{ $activeTab == 'passengers' ? 'bg-white shadow text-gray-900 pointer-events-none' : 'text-gray-500 hover:text-gray-700' }} flex-1 flex flex-col items-center justify-center gap-1.5 py-2.5 text-xs font-bold rounded-xl transition-all relative">
                    <x-heroicon-s-users class="w-5 h-5 {{ $activeTab == 'passengers' ? 'text-emerald-600' : '' }}" /> Penumpang
                    @if($this->passengers->count() > 0)
                    <span class="absolute top-1.5 right-2 inline-flex items-center justify-center min-w-[16px] h-4 text-[9px] font-black text-white bg-emerald-500 rounded-full px-1 shadow-sm">
                        {{ $this->passengers->count() }}
                    </span>
                    @endif
                </button>
                <button wire:click="$set('activeTab', 'cargos')" class="{{ $activeTab == 'cargos' ? 'bg-white shadow text-gray-900 pointer-events-none' : 'text-gray-500 hover:text-gray-700' }} flex-1 flex flex-col items-center justify-center gap-1.5 py-2.5 text-xs font-bold rounded-xl transition-all relative">
                    <x-heroicon-s-cube class="w-5 h-5 {{ $activeTab == 'cargos' ? 'text-orange-500' : '' }}" /> Kargo
                    @if($this->cargos->count() > 0)
                    <span class="absolute top-1.5 right-2 inline-flex items-center justify-center min-w-[16px] h-4 text-[9px] font-black text-white bg-orange-500 rounded-full px-1 shadow-sm">
                        {{ $this->cargos->count() }}
                    </span>
                    @endif
                </button>
            </div>

            @if($activeTab === 'details')
            {{-- Tombol Laporan Perjalanan (Manifest) --}}
            <a href="{{ route('schedules.manifest', $scheduleModel) }}" target="_blank" class="flex items-center justify-center gap-2 w-full py-3.5 mb-4 rounded-xl text-sm font-bold text-white shadow-sm hover:opacity-90 active:scale-[0.98] transition-all" style="background: linear-gradient(135deg, #10B981, #059669);">
                <x-heroicon-s-printer class="w-5 h-5"/>
                Cetak Laporan Perjalanan
            </a>

            {{-- Route Card (BRImo / theme style) --}}
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #1d4ed8, #6366f1);">
                            <x-heroicon-o-map-pin class="w-4 h-4 text-white" />
                        </div>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Rute</span>
                    </div>

                    {{-- Status Badge Pindahan --}}
                    @if($scheduleModel->status === 'scheduled' || $scheduleModel->status === 'ongoing')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase" style="background: rgba(16, 185, 129, 0.12); color: #059669;">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Aktif
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase bg-gray-100 text-gray-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                        Nonaktif
                    </span>
                    @endif
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

            <!-- {{-- Actions --}}
            <div class="flex gap-3 pt-2">
                <a href="{{ route('schedules.index') }}" class="flex-1 py-3 rounded-xl border border-gray-200 text-center text-sm font-semibold text-gray-600 hover:bg-gray-50 active:scale-[0.98] transition-all">
                    Kembali ke Daftar
                </a>
                <a href="{{ route('schedules.edit', $scheduleModel) }}" class="flex-1 py-3 rounded-xl text-center text-sm font-semibold text-white
          hover:opacity-90 active:scale-[0.98] transition-all" style="background: linear-gradient(160deg, #0D47A1 0%, #1565C0 50%, #1976D2 100%);">
                    Edit Jadwal
                </a>
            </div> -->

            @elseif($activeTab === 'passengers')
                <div class="space-y-3 mt-4">
                    @forelse($this->passengers as $passenger)
                    <a href="{{ route('passengers.show', $passenger) }}" class="block bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all overflow-hidden relative">
                        <div class="flex items-start gap-3 p-3 pb-2 pr-24">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-emerald-50 border border-emerald-100">
                                <x-heroicon-s-user class="w-5 h-5 text-emerald-600" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-900 leading-tight">
                                    {{ $passenger->name }}
                                </p>
                                <p class="text-[10px] text-gray-500 font-semibold mt-0.5 flex items-center gap-1">
                                    <x-heroicon-o-device-phone-mobile class="w-3 h-3" />
                                    {{ $passenger->phone ?? '-' }}
                                </p>
                            </div>
                        </div>
                        <div class="absolute top-0 right-0 flex flex-col items-end">
                            <span class="text-[9px] font-bold px-3 py-1 rounded-bl-lg text-white shadow-sm {{ $passenger->booking->payment_status === 'paid' ? 'bg-emerald-500' : 'bg-red-500' }}">
                                {{ $passenger->booking->payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}
                            </span>
                        </div>
                        <div class="px-3 pb-2 flex items-center justify-between text-[10px] border-t border-gray-50 pt-2 bg-gray-50/50">
                            <div class="text-gray-600 truncate mr-2 font-bold flex items-center gap-1">
                                <x-heroicon-o-ticket class="w-3 h-3 text-emerald-600" />
                                Booking: {{ $passenger->booking->booking_code ?? '-' }}
                            </div>
                            <div class="flex items-center gap-1 text-emerald-700 font-black shrink-0 px-2 py-0.5 bg-emerald-100 rounded-md">
                                KURSI: {{ $passenger->seat_number ?? 'N/A' }}
                            </div>
                        </div>
                    </a>
                    @empty
                    <div class="text-center py-10 bg-white rounded-2xl shadow-sm border border-gray-100">
                        <x-heroicon-o-users class="w-8 h-8 text-gray-300 mx-auto" />
                        <p class="text-gray-500 font-semibold text-sm mt-2">Tidak ada data penumpang di rute ini.</p>
                    </div>
                    @endforelse
                </div>

            @elseif($activeTab === 'cargos')
                <div class="space-y-3 mt-4">
                    @forelse($this->cargos as $cargo)
                    <a href="{{ route('cargo.show', $cargo) }}" class="block bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all overflow-hidden relative">
                        <div class="absolute top-0 right-0 flex flex-col items-end">
                            <span class="text-[9px] font-bold px-3 py-1 rounded-bl-lg text-white shadow-sm {{ $cargo->status === 'received' ? 'bg-emerald-600' : 'bg-blue-600' }}">
                                {{ $cargo->status === 'received' ? 'SUDAH DIAMBIL' : 'BELUM DIAMBIL' }}
                            </span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-bl-md text-[8px] font-bold text-white shadow-sm {{ $cargo->is_paid ? 'bg-emerald-500' : 'bg-red-500' }}">
                                {{ $cargo->is_paid ? 'LUNAS' : 'BELUM LUNAS' }}
                            </span>
                        </div>

                        <div class="flex items-start gap-3 p-3 pb-2 pr-24">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 bg-orange-50">
                                <x-heroicon-o-cube class="w-4 h-4 text-orange-600" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-gray-900 leading-tight">
                                    {{ $cargo->description }} ({{ $cargo->weight_kg }}Kg)
                                </p>
                                <p class="text-[10px] text-gray-500 font-semibold mt-0.5 flex items-center gap-1">
                                    Resi: <span class="font-black text-gray-800">{{ $cargo->tracking_code ?? 'N/A' }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="px-3 pb-2 pt-2 flex items-center justify-between gap-2 border-t border-gray-50 mt-1 bg-gray-50/50">
                            <div class="flex items-center gap-1 text-[10px] font-bold px-2 py-1 rounded bg-orange-100 text-orange-700">
                                Biaya: Rp{{ number_format($cargo->fee, 0, ',', '.') }}
                            </div>
                            <div class="flex items-center gap-1 text-[10px] text-gray-500 shrink-0">
                                <x-heroicon-o-user class="w-3 h-3" /> {{ $cargo->recipient_name }}
                            </div>
                        </div>
                    </a>
                    @empty
                    <div class="text-center py-10 bg-white rounded-2xl shadow-sm border border-gray-100">
                        <x-heroicon-o-cube class="w-8 h-8 text-gray-300 mx-auto" />
                        <p class="text-gray-500 font-semibold text-sm mt-2">Tidak ada data kargo di rute ini.</p>
                    </div>
                    @endforelse
                </div>
            @endif

            {{-- FAB Edit Jadwal (kanan bawah, orange) --}}
            <a href="{{ route('schedules.edit', $scheduleModel) }}" class="fixed right-4 z-40 w-14 h-14 rounded-full text-white flex items-center justify-center shadow-lg active:scale-95 transition-transform border-2 border-white/30" style="bottom: calc(72px + env(safe-area-inset-bottom)); background: linear-gradient(135deg, #F57C00, #FF9800); box-shadow: 0 4px 20px rgba(245,124,0,0.45);" title="Edit jadwal">
                <x-heroicon-o-pencil class="w-7 h-7" />
            </a>

        </div>
    </x-layouts.app>
</div>