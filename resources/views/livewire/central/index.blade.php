<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Tenant;

new #[Layout('layouts.app')] class extends Component {
    public function with(): array
    {
        // Mengambil semua data tenant berserta relasi domainnya
        return [
            'tenants' => Tenant::with('domains')->get(),
        ];
    }
};
?>

<div class="min-h-screen bg-slate-50 py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl border border-slate-200 bg-white p-10 shadow-xl shadow-slate-200/50">
            <h1 class="text-4xl font-bold text-slate-900">SaaS Travel Management</h1>
            <p class="mt-4 text-lg text-slate-600">Central portal untuk daftar tenant dan manajemen sistem.</p>

            <div class="mt-10 grid gap-6 sm:grid-cols-2">
                <a href="{{ route('central.login') }}"
                    class="rounded-2xl bg-[#004a8b] px-6 py-5 text-center text-white shadow-lg shadow-slate-800/10 transition hover:bg-[#00376b]">
                    <span class="block text-xl font-semibold">Login Portal Central</span>
                    <span class="block text-sm text-slate-200">Akses admin / owner</span>
                </a>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-6 py-5">
                    <h2 class="text-xl font-semibold text-slate-900">Petunjuk</h2>
                    <p class="mt-3 text-slate-600">Gunakan halaman ini untuk mengelola tenant. Untuk masuk ke aplikasi
                        tenant, gunakan subdomain tenant masing-masing.</p>
                </div>
            </div>

            <div class="mt-12 border-t border-slate-200 pt-10">
                <h2 class="text-2xl font-bold text-slate-900">Daftar Tenant Aktif</h2>
                <p class="mt-1 text-sm text-slate-500">Pilih tenant di bawah ini untuk melihat detail data statistik
                    operasional dan tagihan (billing).</p>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-2">
                    @forelse($tenants as $tenant)
                        <a href="{{ route('central.tenants.show', $tenant->id) }}"
                            class="group block rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-[#004a8b] hover:shadow-md">
                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-[#004a8b]">{{ $tenant->id }}
                            </h3>
                            <div class="mt-2">
                                @foreach ($tenant->domains as $domain)
                                    <span
                                        class="inline-block rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">{{ $domain->domain }}</span>
                                @endforeach
                            </div>
                            <p class="mt-4 text-sm font-medium text-[#004a8b]">Lihat Detail &rarr;</p>
                        </a>
                    @empty
                        <div
                            class="col-span-full rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-500">
                            Belum ada tenant yang terdaftar.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
