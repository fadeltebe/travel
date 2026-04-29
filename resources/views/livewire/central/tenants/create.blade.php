<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Tenant;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Stancl\Tenancy\Database\Models\Domain;

new #[Layout('layouts.app')] class extends Component {
    public string $tenantId = '';
    public string $name = '';
    public string $domain = '';
    public bool $creating = false;

    protected array $rules = [
        'tenantId' => 'required|alpha_dash|max:50|unique:tenants,id',
        'name' => 'nullable|string|max:255',
        'domain' => 'required|string|max:255|unique:domains,domain',
    ];

    public function createTenant(): void
    {
        $this->tenantId = Str::of($this->tenantId)->trim()->slug('_')->toString();
        $this->domain = Str::of($this->domain)->trim()->lower()->replace('http://', '')->replace('https://', '')->replaceMatches('/\/+$/', '')->toString();

        $validated = $this->validate();
        $this->creating = true;

        try {
            $tenant = Tenant::create(['id' => $validated['tenantId']]);
            $tenant->domains()->create(['domain' => $validated['domain']]);

            $this->dispatch('notify', message: 'Tenant baru berhasil dibuat dan database otomatis disiapkan.', type: 'success');
            $this->redirect(route('central.dashboard', absolute: false), navigate: true);
        } catch (\Throwable $exception) {
            Log::error('Gagal membuat tenant central', [
                'message' => $exception->getMessage(),
                'tenantId' => $this->tenantId,
                'domain' => $this->domain,
            ]);

            $this->dispatch('notify', message: 'Gagal membuat tenant: ' . $exception->getMessage(), type: 'error');
        } finally {
            $this->creating = false;
        }
    }
};
?>

<div>
    <x-layouts.app title="Buat Tenant Baru">
        <div class="max-w-3xl mx-auto px-4 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-slate-900">Buat Tenant Baru</h1>
                <p class="mt-2 text-slate-600">Tambahkan tenant dan buat database otomatis untuk tenant tersebut.</p>
            </div>

            <form wire:submit="createTenant" class="space-y-6 bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
                <div>
                    <label for="tenantId" class="block text-sm font-semibold text-slate-700">Tenant ID</label>
                    <input id="tenantId" wire:model.defer="tenantId" type="text" required
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        placeholder="contoh: travelku" />
                    <p class="mt-2 text-xs text-slate-500">Tenant ID akan digunakan sebagai identifier database tenant.
                    </p>
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700">Nama Tenant
                        (opsional)</label>
                    <input id="name" wire:model.defer="name" type="text"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        placeholder="contoh: TravelKu" />
                </div>

                <div>
                    <label for="domain" class="block text-sm font-semibold text-slate-700">Domain Tenant</label>
                    <input id="domain" wire:model.defer="domain" type="text" required
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        placeholder="contoh: travelku.com atau travelku.travel.test" />
                    <p class="mt-2 text-xs text-slate-500">Domain ini akan dipakai untuk mengakses tenant. Bisa
                        subdomain atau custom domain.</p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('central.dashboard') }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                        Kembali ke Dashboard
                    </a>
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800"
                        @if ($creating) disabled @endif>
                        {{ $creating ? 'Membuat...' : 'Buat Tenant' }}
                    </button>
                </div>
            </form>
        </div>
    </x-layouts.app>
</div>
