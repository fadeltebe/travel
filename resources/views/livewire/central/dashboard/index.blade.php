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

<div class="min-h-screen bg-slate-50 py-16">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl border border-slate-200 bg-white p-10 shadow-xl shadow-slate-200/50">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900">Central Dashboard</h1>
                    <p class="mt-2 text-slate-600">Ringkasan tenant dan manajemen sistem.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="{{ route('central.tenants.create') }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                        Buat Tenant Baru
                    </a>
                    <button wire:click="logout"
                        class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800">
                        Logout
                    </button>
                </div>
            </div>

            <div class="mt-10 grid gap-6 sm:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                    <p class="text-sm uppercase tracking-[0.2em] text-slate-400">Tenant Terdaftar</p>
                    <p class="mt-4 text-4xl font-bold text-slate-900">{{ $this->tenantCount }}</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                    <p class="text-sm uppercase tracking-[0.2em] text-slate-400">Host Central</p>
                    <p class="mt-4 text-4xl font-bold text-slate-900">{{ request()->getHost() }}</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                    <p class="text-sm uppercase tracking-[0.2em] text-slate-400">Status</p>
                    <p class="mt-4 text-4xl font-bold text-slate-900">Active</p>
                </div>
            </div>

            <div class="mt-10 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div
                    class="border-b border-slate-200 bg-slate-50 px-6 py-4 text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">
                    Tenant Terdaftar
                </div>
                <div class="divide-y divide-slate-200">
                    @forelse ($this->tenants as $tenant)
                        <div class="px-6 py-4 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $tenant->id }}</p>
                                <p class="text-sm text-slate-500">{{ $tenant->domains->pluck('domain')->join(', ') }}
                                </p>
                            </div>
                            <p class="mt-3 text-sm text-slate-500 sm:mt-0">
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
</div>
