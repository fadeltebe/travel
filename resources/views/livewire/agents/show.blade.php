<?php

use function Livewire\Volt\{state, computed};
use App\Models\Agent;

state([
    'agentModel' => function () {
        $param = request()->route('agent');
        $agent =
            $param instanceof Agent
                ? $param
                : Agent::withCount(['users', 'originRoutes', 'destinationRoutes', 'bookings', 'originCargos', 'destinationCargos'])
                    ->where(function ($query) use ($param) {
                        $query->where('id', $param)->orWhere('code', $param);
                    })
                    ->firstOrFail();

        $user = auth()->user();
        if (!in_array($user->role->value ?? $user->role, ['superadmin', 'owner', 'super_admin'])) {
            abort(403, 'Akses Ditolak');
        }

        return $agent;
    },
]);

$transactionCount = computed(function () {
    return $this->agentModel->bookings_count + $this->agentModel->origin_cargos_count + $this->agentModel->destination_cargos_count;
});
?>

<div>
    <x-layouts.app title="Detail Agen">
        <div class="px-4 pt-6 pb-24 space-y-6">

            <div class="flex items-center gap-3">
                <a href="{{ route('agents.index') }}"
                    class="w-9 h-9 rounded-xl flex items-center justify-center border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 active:scale-95 transition-all shadow-sm">
                    <x-heroicon-o-arrow-left class="w-5 h-5" />
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Detail Agen</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Lihat informasi detail untuk {{ $agentModel->name }}.</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 bg-slate-50 border-b border-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">{{ $agentModel->name }}</h2>
                            <p class="text-sm text-slate-500 mt-1">Kode: <span
                                    class="font-semibold text-slate-700">{{ $agentModel->code }}</span></p>
                        </div>
                        <span
                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-black tracking-wide {{ $agentModel->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ $agentModel->is_active ? 'AKTIF' : 'NONAKTIF' }}
                        </span>
                    </div>
                </div>

                <div class="p-6 space-y-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Kota</p>
                            <p class="mt-2 text-lg font-bold text-slate-900">{{ $agentModel->city ?? '-' }}</p>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Komisi</p>
                            <p class="mt-2 text-lg font-bold text-slate-900">
                                {{ number_format($agentModel->commission_rate, 2) }}%</p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-3xl border border-slate-200 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Telepon</p>
                            <p class="mt-2 text-sm text-slate-700">{{ $agentModel->phone ?? '-' }}</p>
                        </div>
                        <div class="rounded-3xl border border-slate-200 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Email</p>
                            <p class="mt-2 text-sm text-slate-700">{{ $agentModel->email ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 p-4 bg-slate-50">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Alamat</p>
                        <p class="mt-2 text-sm text-slate-700">{{ $agentModel->address ?? '-' }}</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-3xl border border-slate-200 p-4 text-center">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Pengguna</p>
                            <p class="mt-3 text-2xl font-bold text-slate-900">{{ $agentModel->users_count }}</p>
                        </div>
                        <div class="rounded-3xl border border-slate-200 p-4 text-center">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Rute Asal</p>
                            <p class="mt-3 text-2xl font-bold text-slate-900">{{ $agentModel->origin_routes_count }}
                            </p>
                        </div>
                        <div class="rounded-3xl border border-slate-200 p-4 text-center">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Rute Tujuan</p>
                            <p class="mt-3 text-2xl font-bold text-slate-900">
                                {{ $agentModel->destination_routes_count }}</p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-3xl border border-slate-200 p-4 text-center bg-white">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Total Transaksi</p>
                            <p class="mt-3 text-2xl font-bold text-slate-900">{{ $transactionCount }}</p>
                        </div>
                        <div class="rounded-3xl border border-slate-200 p-4 text-center bg-white">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Total Booking</p>
                            <p class="mt-3 text-2xl font-bold text-slate-900">{{ $agentModel->bookings_count }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-layouts.app>
</div>
