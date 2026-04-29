<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {};
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
        </div>
    </div>
</div>
