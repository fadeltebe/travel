<?php

use function Livewire\Volt\{state, mount, action, rules};
use App\Models\Company;

state([
    'companyId' => null,
    'name' => '',
    'code' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'npwp' => '',
    'billing_mode' => 'centralized',
]);

rules([
    'name' => 'required|string|max:255',
    'code' => 'required|string|max:50',
    'email' => 'nullable|email|max:255',
    'phone' => 'nullable|string|max:20',
    'address' => 'nullable|string',
    'npwp' => 'nullable|string|max:50',
    'billing_mode' => 'required|in:centralized,per_agent',
]);

mount(function () {
    $user = auth()->user();

    // Authorization
    if (!in_array($user->role->value ?? $user->role, ['superadmin', 'owner', 'super_admin'])) {
        abort(403, 'Akses Ditolak: Halaman ini khusus untuk Pimpinan.');
    }

    $company = Company::first();
    if ($company) {
        $this->companyId = $company->id;
        $this->name = $company->name;
        $this->code = $company->code;
        $this->email = $company->email;
        $this->phone = $company->phone;
        $this->address = $company->address;
        $this->npwp = $company->npwp;
        $this->billing_mode = $company->billing_mode;
    }
});

$save = action(function () {
    $validated = $this->validate();

    if ($this->companyId) {
        $company = Company::find($this->companyId);
        $company->update($validated);
    } else {
        Company::create($validated);
    }

    $this->dispatch('notify', message: 'Profil profil dan pengaturan pembayaran berhasil disimpan!', type: 'success');
});
?>

<div>
    <x-layouts.app title="Profil Perusahaan">
        <div class="max-w-3xl mx-auto px-4 py-8">

            <div class="mb-8 flex items-center gap-4">
                <a href="{{ route('settings.index') }}" wire:navigate
                    class="p-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <x-heroicon-o-arrow-left class="w-5 h-5 text-gray-600" />
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Profil Perusahaan</h1>
                    <p class="text-gray-500 text-sm">Atur data kontak dan mode sistem tarif dompet.</p>
                </div>
            </div>

            <form wire:submit="save" class="space-y-6">

                {{-- Mode Tagihan --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
                    <div class="flex items-start gap-4 mb-4">
                        <div
                            class="w-12 h-12 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                            <x-heroicon-s-banknotes class="w-6 h-6" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900">Mode Sistem Kasir / Dompet</h3>
                            <p class="text-sm text-gray-500 mt-1">Tentukan bagaimana cara agen mencetak tiket dan
                                menggunakan mutasi dompet token.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                        <label
                            class="flex items-start p-4 border-2 rounded-xl cursor-pointer transition-all {{ $billing_mode === 'centralized' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <div class="flex-1">
                                <p
                                    class="font-bold {{ $billing_mode === 'centralized' ? 'text-orange-700' : 'text-gray-700' }}">
                                    Terpusat (1 Dompet Bos)</p>
                                <p class="text-xs text-gray-500 mt-1">Semua agen memotong saldo dari Dompet Utama Anda
                                    setiap kali menerbitkan manifest/tiket.</p>
                            </div>
                            <input type="radio" wire:model="billing_mode" value="centralized"
                                class="mt-1 w-5 h-5 text-orange-600 border-gray-300 focus:ring-orange-500">
                        </label>
                        <label
                            class="flex items-start p-4 border-2 rounded-xl cursor-pointer transition-all {{ $billing_mode === 'per_agent' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <div class="flex-1">
                                <p
                                    class="font-bold {{ $billing_mode === 'per_agent' ? 'text-orange-700' : 'text-gray-700' }}">
                                    Mandiri (Per Agen)</p>
                                <p class="text-xs text-gray-500 mt-1">Masing-masing agen memiliki dompet terpisah. Agen
                                    harus top-up mandiri atau disubsidi agar bisa cetak tiket.</p>
                            </div>
                            <input type="radio" wire:model="billing_mode" value="per_agent"
                                class="mt-1 w-5 h-5 text-orange-600 border-gray-300 focus:ring-orange-500">
                        </label>
                    </div>
                </div>

                {{-- Data Umum --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-lg font-bold text-gray-900">Informasi Umum</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div class="col-span-full md:col-span-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Perusahaan*</label>
                            <input type="text" wire:model="name"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('name')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-span-full md:col-span-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Kode / Singkatan*</label>
                            <input type="text" wire:model="code"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('code')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                            <input type="email" wire:model="email"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">No. Telepon / WhatsApp</label>
                            <input type="text" wire:model="phone"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div class="col-span-full">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">NPWP (Opsional)</label>
                            <input type="text" wire:model="npwp"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div class="col-span-full">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat Lengkap</label>
                            <textarea wire:model="address" rows="3"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                    <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition-colors">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </x-layouts.app>
</div>
