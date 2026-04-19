<?php
use function Livewire\Volt\{state, computed, mount}; // 1. Tambahkan 'mount' di sini
use App\Models\Schedule;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Cargo;
use Illuminate\Database\Eloquent\Builder;

state([
    'selectedMonth' => now()->format('m'),
    'selectedYear' => now()->format('Y'),
]);

// ==========================================
// 2. FUNGSI MOUNT SEBAGAI PENJAGA PINTU (KUNCI HALAMAN)
// ==========================================
mount(function () {
    $user = auth()->user();

    // Asumsi: canViewAll() di model User Anda sudah mewakili Super Admin & Owner.
    // Jika Anda menggunakan pengecekan nama role (misal Spatie), kodenya bisa seperti ini:
    // if (!in_array($user->role, ['super_admin', 'owner'])) { ... }

    if (!$user->canViewAll()) {
        abort(403, 'AKSES DITOLAK!');
    }
});

$months = computed(function () {
    return [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember',
    ];
});

$years = computed(function () {
    $currentYear = now()->year;
    return range($currentYear - 2, $currentYear + 1);
});

$metrics = computed(function () {
    $month = $this->selectedMonth;
    $year = $this->selectedYear;

    // Base Queries
    $schedulesQuery = Schedule::query()->whereMonth('departure_date', $month)->whereYear('departure_date', $year);
    $bookingsQuery = Booking::query()->whereHas('schedule', function (Builder $q) use ($month, $year) {
        $q->whereMonth('departure_date', $month)->whereYear('departure_date', $year);
    });
    $passengersQuery = Passenger::query()->whereHas('booking.schedule', function (Builder $q) use ($month, $year) {
        $q->whereMonth('departure_date', $month)->whereYear('departure_date', $year);
    });
    $cargosQuery = Cargo::query()->whereMonth('created_at', $month)->whereYear('created_at', $year);

    // CATATAN: Blok "Apply Role Filters" untuk Agen telah dihapus
    // karena agen sudah diblokir oleh fungsi mount() di atas.
    // Ini membuat query Anda jauh lebih cepat!

    // --- AGGREGATE SCHEDULES ---
    $schedules = clone $schedulesQuery;
    $scheduleStats = [
        'scheduled' => (clone $schedules)->where('status', 'scheduled')->count(),
        'ongoing' => (clone $schedules)->where('status', 'ongoing')->count(),
        'completed' => (clone $schedules)->where('status', 'completed')->count(),
        'cancelled' => (clone $schedules)->where('status', 'cancelled')->count(),
        'total' => (clone $schedules)->count(),
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
        'pending' => (clone $cargos)->where('status', 'pending')->count(),
        'paid' => (clone $cargos)->where('is_paid', true)->count(),
        'unpaid' => (clone $cargos)->where('is_paid', false)->count(),
        'total' => (clone $cargos)->count(),
    ];

    // --- AGGREGATE REVENUE ---
    $bookingsPaid = (clone $bookingsQuery)->where('payment_status', 'paid');
    $passengerRevenue = $bookingsPaid->sum('subtotal_price') + $bookingsPaid->sum('pickup_dropoff_fee');

    $cargosPaid = (clone $cargosQuery)->where('is_paid', true);
    $cargoRevenue = $cargosPaid->sum('fee');

    $revenueStats = [
        'passenger' => $passengerRevenue,
        'cargo' => $cargoRevenue,
        'total' => $passengerRevenue + $cargoRevenue,
    ];

    return [
        'schedules' => $scheduleStats,
        'passengers' => $passengerStats,
        'cargos' => $cargoStats,
        'revenue' => $revenueStats,
    ];
});
?>

<div>
    <x-layouts.app title="Laporan Bulanan">
        <div class="px-4 pb-24 space-y-6">

            {{-- Header, Filter & Revenue Combined --}}
            <div
                class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl p-6 shadow-md text-white flex flex-col relative overflow-hidden mb-6">
                <div class="absolute -right-12 -top-12 opacity-10"><x-heroicon-s-banknotes class="w-56 h-56 text-black" />
                </div>

                <div class="relative z-10">
                    {{-- Judul & Filter (Atas) --}}
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
                        <div>
                            <h1 class="text-xl font-bold font-sans">Laporan & Pendapatan</h1>
                            <p class="text-xs text-emerald-100 mt-0.5">Rekapitulasi operasional berbasis pencarian bulan
                            </p>
                        </div>

                        <div class="flex gap-2">
                            <select wire:model.live="selectedMonth"
                                class="flex-1 sm:w-32 px-4 py-2 bg-white/20 border border-white/30 rounded-xl text-sm font-semibold text-white shadow-sm outline-none focus:ring-2 focus:ring-white/50 backdrop-blur-sm transition-all [&>option]:text-gray-800">
                                @foreach ($this->months as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <select wire:model.live="selectedYear"
                                class="w-24 px-4 py-2 bg-white/20 border border-white/30 rounded-xl text-sm font-semibold text-white shadow-sm outline-none focus:ring-2 focus:ring-white/50 backdrop-blur-sm transition-all [&>option]:text-gray-800">
                                @foreach ($this->years as $yr)
                                    <option value="{{ $yr }}">{{ $yr }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Data Pendapatan (Bawah) --}}
                    <div>
                        <h2 class="text-[10px] font-bold text-emerald-100 uppercase tracking-widest mb-1">Total
                            Pendapatan Bersih (Lunas)</h2>
                        <p class="text-4xl font-black mb-4">Rp
                            {{ number_format($this->metrics['revenue']['total'], 0, ',', '.') }}</p>

                        <div class="flex flex-wrap gap-4 pt-4 border-t border-emerald-400/30">
                            <div class="flex-1 sm:flex-none min-w-[120px]">
                                <p class="text-[10px] font-bold text-emerald-100 uppercase">Dari Penumpang</p>
                                <p class="text-lg font-bold">Rp
                                    {{ number_format($this->metrics['revenue']['passenger'], 0, ',', '.') }}</p>
                            </div>
                            <div class="w-px bg-emerald-400/30 hidden sm:block"></div>
                            <div class="flex-1 sm:flex-none min-w-[120px]">
                                <p class="text-[10px] font-bold text-emerald-100 uppercase">Dari Kargo Barang</p>
                                <p class="text-lg font-bold">Rp
                                    {{ number_format($this->metrics['revenue']['cargo'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 1. Rekap Perjalanan --}}
            <div
                class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col gap-3 relative overflow-hidden">
                <div class="absolute -right-6 -bottom-6 opacity-5"><x-heroicon-s-calendar
                        class="w-32 h-32 text-primary-500" /></div>
                <div class="flex items-center gap-3 relative z-10">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                        style="background: #E3F2FD">
                        <x-heroicon-s-calendar class="w-5 h-5 text-primary-800" />
                    </div>
                    <div>
                        <h2 class="text-xs font-bold text-gray-500 uppercase tracking-widest leading-none mb-1">Rekap
                            Perjalanan</h2>
                        <p class="text-2xl font-black text-gray-800 leading-none">
                            {{ $this->metrics['schedules']['total'] }} <span
                                class="text-xs font-semibold text-gray-400">Jadwal</span></p>
                    </div>
                </div>
                <div
                    class="mt-2 pt-3 border-t border-gray-100 flex flex-wrap items-center gap-2 text-sm font-semibold text-gray-600 relative z-10">
                    <span class="text-gray-500">Terjadwal: <span
                            class="text-gray-800 font-bold">{{ $this->metrics['schedules']['scheduled'] }}</span></span>
                    <span class="text-gray-300">|</span>
                    <span class="text-primary-600">Berjalan: <span
                            class="text-primary-800 font-bold">{{ $this->metrics['schedules']['ongoing'] }}</span></span>
                    <span class="text-gray-300">|</span>
                    <span class="text-emerald-600">Selesai: <span
                            class="text-emerald-800 font-bold">{{ $this->metrics['schedules']['completed'] }}</span></span>
                    <span class="text-gray-300">|</span>
                    <span class="text-red-500">Batal: <span
                            class="text-red-700 font-bold">{{ $this->metrics['schedules']['cancelled'] }}</span></span>
                </div>
            </div>

            {{-- 2. Rekap Penumpang --}}
            <div
                class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col gap-3 relative overflow-hidden mt-4">
                <div class="absolute -right-6 -bottom-6 opacity-5"><x-heroicon-s-users
                        class="w-32 h-32 text-emerald-500" /></div>
                <div class="flex items-center gap-3 relative z-10">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                        style="background: #E8F5E9">
                        <x-heroicon-s-users class="w-5 h-5 text-emerald-800" />
                    </div>
                    <div>
                        <h2 class="text-xs font-bold text-gray-500 uppercase tracking-widest leading-none mb-1">Rekap
                            Penumpang</h2>
                        <p class="text-2xl font-black text-gray-800 leading-none">
                            {{ $this->metrics['passengers']['total'] }} <span
                                class="text-xs font-semibold text-gray-400">Tiket</span></p>
                    </div>
                </div>
                <div
                    class="mt-2 pt-3 border-t border-gray-100 flex flex-wrap items-center gap-2 text-sm font-semibold text-gray-600 relative z-10">
                    <span class="text-emerald-600">Lunas: <span
                            class="text-emerald-800 font-bold">{{ $this->metrics['passengers']['paid'] }}</span></span>
                    <span class="text-gray-300">|</span>
                    <span class="text-red-500">Belum Lunas: <span
                            class="text-red-700 font-bold">{{ $this->metrics['passengers']['unpaid'] }}</span></span>
                </div>
            </div>

            {{-- 3. Rekap Kargo --}}
            <div
                class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col gap-3 relative overflow-hidden mt-4">
                <div class="absolute -right-6 -bottom-6 opacity-5"><x-heroicon-s-cube
                        class="w-32 h-32 text-orange-500" /></div>
                <div class="flex items-center gap-3 relative z-10">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                        style="background: #FFF3E0">
                        <x-heroicon-s-cube class="w-5 h-5 text-orange-800" />
                    </div>
                    <div>
                        <h2 class="text-xs font-bold text-gray-500 uppercase tracking-widest leading-none mb-1">Rekap
                            Kargo</h2>
                        <p class="text-2xl font-black text-gray-800 leading-none">
                            {{ $this->metrics['cargos']['total'] }} <span
                                class="text-xs font-semibold text-gray-400">Resi</span></p>
                    </div>
                </div>
                <div
                    class="mt-2 pt-3 border-t border-gray-100 flex flex-col gap-2 text-sm font-semibold text-gray-600 relative z-10">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-gray-400 uppercase text-[10px] w-16">Fisik:</span>
                        <span class="text-emerald-600">Terambil: <span
                                class="font-bold text-emerald-800">{{ $this->metrics['cargos']['received'] }}</span></span>
                        <span class="text-gray-300">|</span>
                        <span class="text-primary-600">Pending: <span
                                class="font-bold text-primary-800">{{ $this->metrics['cargos']['pending'] }}</span></span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-gray-400 uppercase text-[10px] w-16">Keuangan:</span>
                        <span class="text-emerald-600">Lunas: <span
                                class="font-bold text-emerald-800">{{ $this->metrics['cargos']['paid'] }}</span></span>
                        <span class="text-gray-300">|</span>
                        <span class="text-red-500">Tertunggak: <span
                                class="font-bold text-red-700">{{ $this->metrics['cargos']['unpaid'] }}</span></span>
                    </div>
                </div>
            </div>

            {{-- Floating Back Button (like dashboard / other pages) --}}
            <div class="mt-6 text-center pb-4">
                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-gray-700">
                    <x-heroicon-o-arrow-left class="w-4 h-4" /> Kembali ke Dashboard
                </a>
            </div>

        </div>
    </x-layouts.app>
</div>
