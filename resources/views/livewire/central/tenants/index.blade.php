<?php

use App\Models\Tenant;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'tenants' => Tenant::with('domains')->orderBy('created_at', 'desc')->get(),
        ];
    }
};
?>

<div class="min-h-screen bg-slate-50 py-16">
    <div class="mx-auto max-w-6xl pb-28 px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl border border-slate-200 bg-white p-10 shadow-xl shadow-slate-200/50">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900">Daftar Tenant</h1>
                    <p class="mt-2 text-slate-600">Lihat semua tenant yang terdaftar dan buka detail setiap tenant.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="{{ route('central.dashboard') }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800">
                        Kembali ke Dashboard
                    </a>
                    <a href="{{ route('central.tenants.create') }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                        Buat Tenant Baru
                    </a>
                </div>
            </div>

            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($tenants as $tenant)
                    <a href="{{ route('central.tenants.show', $tenant->id) }}"
                        class="group block overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-[#004a8b] hover:shadow-lg">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Tenant</p>
                                <h2 class="mt-2 text-xl font-bold text-slate-900">{{ $tenant->id }}</h2>
                            </div>
                            <span
                                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Detail</span>
                        </div>
                        <div class="mt-4 text-sm text-slate-500">
                            @if ($tenant->domains->isNotEmpty())
                                {{ $tenant->domains->pluck('domain')->join(', ') }}
                            @else
                                Belum ada domain terdaftar.
                            @endif
                        </div>
                        <p class="mt-4 text-sm font-semibold text-[#004a8b]">Lihat Detail &rarr;</p>
                    </a>
                @empty
                    <div
                        class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-slate-500">
                        Belum ada tenant terdaftar.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
