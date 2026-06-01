<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public int $tenantCount;
    public $tenants;

    public function mount(): void
    {
        $this->tenantCount = Tenant::count();
        $this->tenants = Tenant::with('domains')->get();
    }

    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        $this->redirect(route('central.home', absolute: false), navigate: true);
    }
};
?>

<div class="min-h-screen bg-slate-50 py-8 sm:py-16">
    <div class="mx-auto max-w-6xl pb-40 sm:pb-28 px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-10 shadow-xl shadow-slate-200/50">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Central Dashboard</h1>
                    <p class="mt-2 text-slate-600">Ringkasan tenant dan manajemen sistem.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="{{ url('/tenants/create') }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                        Buat Tenant Baru
                    </a>
                    <button wire:click="logout"
                        class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800">
                        Logout
                    </button>
                </div>
            </div>

            <div class="mt-8 sm:mt-10 grid gap-4 sm:gap-6 sm:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                    <p class="text-sm uppercase tracking-[0.2em] text-slate-400">Tenant Terdaftar</p>
                    <p class="mt-4 text-3xl sm:text-4xl font-bold text-slate-900">{{ $this->tenantCount }}</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6 overflow-hidden">
                    <p class="text-sm uppercase tracking-[0.2em] text-slate-400">Host Central</p>
                    <p class="mt-4 text-3xl sm:text-4xl font-bold text-slate-900 truncate" title="{{ request()->getHost() }}">{{ request()->getHost() }}</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                    <p class="text-sm uppercase tracking-[0.2em] text-slate-400">Status</p>
                    <p class="mt-4 text-3xl sm:text-4xl font-bold text-slate-900">Active</p>
                </div>
            </div>

            <div class="mt-8 sm:mt-10 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div
                    class="border-b border-slate-200 bg-slate-50 px-6 py-4 text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">
                    Tenant Terdaftar
                </div>
                <div class="divide-y divide-slate-200">
                    @forelse ($this->tenants as $tenant)
                        <div class="px-6 py-4 sm:flex sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900 truncate">{{ $tenant->id }}</p>
                                <p class="text-sm text-slate-500 break-words">{{ $tenant->domains->pluck('domain')->join(', ') }}
                                </p>
                            </div>
                            <p class="mt-3 text-sm text-slate-500 sm:mt-0 whitespace-nowrap">
                                {{ $tenant->created_at?->format('d M Y') ?? '-' }}</p>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-slate-500">
                            Belum ada tenant terdaftar.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div
        class="fixed inset-x-0 bottom-0 z-50 border-t border-slate-200 bg-white/95 backdrop-blur-lg px-4 py-4 shadow-xl">
        <div class="mx-auto flex max-w-6xl flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Quick Action</p>
                <p class="mt-1 text-sm font-semibold text-slate-900">Lihat daftar tenant dan akses detail tenant</p>
            </div>
            <a href="{{ url('/tenants') }}"
                class="inline-flex items-center justify-center rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/10 hover:bg-emerald-600 transition">
                Daftar Tenant
            </a>
        </div>
    </div>
