<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Tenant;
use Carbon\Carbon;

new #[Layout('layouts.app')] class extends Component {
    public Tenant $tenant;

    public $startDate;
    public $endDate;

    // Variabel Penampung Data
    public $schedulesCount = 0;
    public $passengersCount = 0;
    public $cargosCount = 0;
    public $billingTotal = 0;
    public $billingPaid = 0;

    public function mount(Tenant $tenant)
    {
        $this->tenant = $tenant;
        // Set default filter tanggal (Awal bulan s/d Akhir bulan ini)
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');

        $this->loadTenantData();
    }

    public function updatedStartDate()
    {
        $this->loadTenantData();
    }

    public function updatedEndDate()
    {
        $this->loadTenantData();
    }

    public function loadTenantData()
    {
        try {
            $startDate = $this->startDate;
            $endDate = $this->endDate;

            // $tenant->run() mengeksekusi kode di dalam database tenant yang bersangkutan
            $stats = $this->tenant->run(function () use ($startDate, $endDate) {
                // Hitung Jadwal di rentang tanggal berdasarkan tanggal keberangkatan
                $schedules = \App\Models\Schedule::whereBetween('departure_date', [$startDate, $endDate])->count();

                // Hitung Penumpang yang berangkat di rentang tanggal
                $passengers = \App\Models\Passenger::whereHas('booking.schedule', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('departure_date', [$startDate, $endDate]);
                })->count();

                // Hitung Cargo berdasarkan jadwal keberangkatan di rentang tanggal
                $cargos = \App\Models\Cargo::whereHas('booking.schedule', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('departure_date', [$startDate, $endDate]);
                })->count();

                // Estimasi Billing berdasarkan total penumpang dan cargo di rentang tanggal keberangkatan
                $tiketTotal = \App\Models\Passenger::whereHas('booking.schedule', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('departure_date', [$startDate, $endDate]);
                })->sum('ticket_price');

                $kargoTotal = \App\Models\Cargo::whereHas('booking.schedule', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('departure_date', [$startDate, $endDate]);
                })->sum('fee');

                // Total Lunas
                $tiketPaid = \App\Models\Passenger::whereHas('booking.schedule', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('departure_date', [$startDate, $endDate]);
                })->whereHas('booking', function ($q) {
                    $q->where('payment_status', 'paid');
                })->sum('ticket_price');

                $kargoPaid = \App\Models\Cargo::whereHas('booking.schedule', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('departure_date', [$startDate, $endDate]);
                })->where('is_paid', true)->sum('fee');

                return [
                    'schedules' => $schedules,
                    'passengers' => $passengers,
                    'cargos' => $cargos,
                    'billing' => $tiketTotal + $kargoTotal,
                    'billingPaid' => $tiketPaid + $kargoPaid,
                ];
            });

            // Terapkan hasil perhitungan dari tenant ke variabel komponen
            $this->schedulesCount = $stats['schedules'];
            $this->passengersCount = $stats['passengers'];
            $this->cargosCount = $stats['cargos'];
            $this->billingTotal = $stats['billing'];
            $this->billingPaid = $stats['billingPaid'];
        } catch (\Exception $e) {
            \Log::error('Central tenant report failed', ['tenant' => $this->tenant->id ?? null, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }
};
?>

<div class="min-h-screen bg-slate-50 py-16">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

        <div class="mb-6">
            <a href="/" class="text-sm font-medium text-[#004a8b] hover:underline">&larr; Kembali</a>
            <h1 class="mt-4 text-3xl font-bold text-slate-900">Detail Laporan Tenant: {{ $tenant->id }}</h1>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/50">

            <!-- Filter Tanggal -->
            <div class="mb-8 flex flex-wrap items-center gap-4 rounded-xl border border-slate-100 bg-slate-50 p-4">
                <div class="w-full flex-1 sm:w-auto">
                    <label for="startDate" class="block text-sm font-medium text-slate-700">Tanggal Awal</label>
                    <input type="date" id="startDate" wire:model="startDate" wire:change="loadTenantData"
                        class="mt-1 block w-full rounded-lg border-slate-300 py-2 shadow-sm focus:border-[#004a8b] focus:ring focus:ring-[#004a8b] focus:ring-opacity-50">
                </div>
                <div class="w-full flex-1 sm:w-auto">
                    <label for="endDate" class="block text-sm font-medium text-slate-700">Tanggal Akhir</label>
                    <input type="date" id="endDate" wire:model="endDate" wire:change="loadTenantData"
                        class="mt-1 block w-full rounded-lg border-slate-300 py-2 shadow-sm focus:border-[#004a8b] focus:ring focus:ring-[#004a8b] focus:ring-opacity-50">
                </div>
            </div>

            <!-- Kartu Statistik (Terupdate otomatis berdasarkan filter tanggal) -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Jumlah Jadwal</p>
                    <p class="mt-2 text-2xl font-bold text-[#004a8b]">{{ number_format($schedulesCount) }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Penumpang Aktif</p>
                    <p class="mt-2 text-2xl font-bold text-green-600">{{ number_format($passengersCount) }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Transaksi Barang</p>
                    <p class="mt-2 text-2xl font-bold text-amber-500">{{ number_format($cargosCount) }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Total Pendapatan</p>
                    <p class="mt-2 text-xl font-bold text-slate-400">Rp
                        {{ number_format($billingTotal, 0, ',', '.') }}</p>

                    <p class="mt-4 text-sm font-medium text-slate-500">Total Lunas</p>
                    <p class="mt-1 text-xl font-bold text-emerald-600">Rp
                        {{ number_format($billingPaid, 0, ',', '.') }}</p>
                </div>
            </div>

        </div>
    </div>
</div>
