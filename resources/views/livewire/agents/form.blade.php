<?php

use function Livewire\Volt\{state, on, rules};
use App\Models\Agent;
use Illuminate\Support\Str;

state([
    'isOpen' => false,
    'agentId' => null,
    
    // Form fields
    'name' => '',
    'code' => '',
    'city' => '',
    'address' => '',
    'phone' => '',
    'email' => '',
    'commission_rate' => 0,
    'is_active' => true,
]);

rules(function () {
    return [
        'name' => ['required', 'string', 'max:255'],
        'code' => ['required', 'string', 'max:50', 'unique:agents,code,' . $this->agentId],
        'city' => ['required', 'string', 'max:255'],
        'address' => ['nullable', 'string'],
        'phone' => ['nullable', 'string', 'max:255'],
        'email' => ['nullable', 'email', 'max:255', 'unique:agents,email,' . $this->agentId],
        'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        'is_active' => ['boolean']
    ];
});

on(['openCreateAgent' => function () {
    $this->reset('agentId', 'name', 'code', 'city', 'address', 'phone', 'email', 'commission_rate');
    $this->is_active = true;
    $this->resetValidation();
    $this->isOpen = true;
}]);

on(['openEditAgent' => function ($agentId) {
    $this->resetValidation();
    $agent = Agent::findOrFail($agentId);
    
    $this->agentId = $agent->id;
    $this->name = $agent->name;
    $this->code = $agent->code;
    $this->city = $agent->city;
    $this->address = $agent->address;
    $this->phone = $agent->phone;
    $this->email = $agent->email;
    $this->commission_rate = $agent->commission_rate;
    $this->is_active = $agent->is_active;
    
    $this->isOpen = true;
}]);

$closeModal = function () {
    $this->isOpen = false;
};

$save = function () {
    $validated = $this->validate();

    // Generate slug and formatting
    $validated['slug'] = Str::slug($validated['name']);
    $validated['code'] = strtoupper($validated['code']);

    if ($this->agentId) {
        Agent::findOrFail($this->agentId)->update($validated);
        $this->dispatch('notify', message: 'Agen berhasil diperbarui!', type: 'success');
    } else {
        Agent::create($validated);
        $this->dispatch('notify', message: 'Agen berhasil ditambahkan!', type: 'success');
    }

    $this->dispatch('agent-saved');
    $this->isOpen = false;
};
?>

<div>
    @if ($isOpen)
        <div class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center p-0 sm:p-4" x-data="{
            show: false,
            init() { 
                setTimeout(() => this.show = true, 10); 
                document.body.style.overflow = 'hidden';
            },
            close() { 
                this.show = false; 
                setTimeout(() => $wire.closeModal(), 300); 
                document.body.style.overflow = '';
            }
        }" x-on:keydown.escape.window="close()">
            
            {{-- Backdrop --}}
            <div 
                x-show="show" 
                x-transition.opacity.duration.300ms
                @click="close()"
                class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm shadow-2xl">
            </div>

            {{-- Modal Panel --}}
            <div 
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-8 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-8 sm:scale-95"
                class="relative bg-white w-full sm:max-w-lg rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                
                {{-- Handle untuk mobile swipe --}}
                <div class="w-full flex justify-center pt-3 pb-1 sm:hidden absolute top-0 z-20">
                    <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
                </div>

                {{-- Header Modal --}}
                <div class="px-6 py-4 border-b border-gray-100 relative z-10 bg-white sm:pt-6 pt-8 shrink-0">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-black text-gray-900">
                                {{ $agentId ? 'Edit Agen' : 'Tambah Agen Baru' }}
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">Lengkapi profil agensi / cabang.</p>
                        </div>
                        <button @click="close()" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors">
                            <x-heroicon-s-x-mark class="w-5 h-5"/>
                        </button>
                    </div>
                </div>

                {{-- Body Modal (Scrollable) --}}
                <div class="p-6 overflow-y-auto custom-scrollbar flex-1 relative z-10 bg-white">
                    <form wire:submit="save" id="agentForm" class="space-y-4">
                        
                        {{-- Nama & Kode --}}
                        <div class="grid grid-cols-3 gap-3">
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Nama Agen</label>
                                <input type="text" wire:model="name" class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors text-sm" placeholder="Contoh: Agen Palu">
                                @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Kode Agen</label>
                                <input type="text" wire:model.blur="code" class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors text-sm uppercase" placeholder="PLU-01">
                                @error('code') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- City --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Kota Operasional</label>
                            <input type="text" wire:model="city" class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors text-sm" placeholder="Contoh: Palu">
                            @error('city') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            {{-- Phone --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">No. Telepon / WA</label>
                                <input type="text" wire:model="phone" class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors text-sm" placeholder="08...">
                                @error('phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            {{-- Email --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Email (Opsional)</label>
                                <input type="email" wire:model="email" class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors text-sm" placeholder="agen@travel.com">
                                @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Address --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Alamat Lengkap</label>
                            <textarea wire:model="address" rows="2" class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors text-sm" placeholder="Jalan..."></textarea>
                            @error('address') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Commission Rate --}}
                        <div class="p-4 bg-amber-50 rounded-xl border border-amber-100">
                            <label class="block text-xs font-bold text-amber-900 mb-1.5 flex justify-between">
                                <span>Persentase Diskon/Komisi (%)</span>
                                <span>{{ $commission_rate }}%</span>
                            </label>
                            <input type="range" wire:model.live="commission_rate" min="0" max="100" step="0.5" class="w-full mt-2 accent-amber-500">
                            @error('commission_rate') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Status Aktif --}}
                        <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50/50 mt-2">
                            <div>
                                <p class="text-sm font-bold text-gray-900">Status Operasional</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">Aktifkan agar kantor bisa beroperasi</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>
                    </form>
                </div>

                {{-- Footer Modal --}}
                <div class="p-4 sm:p-6 border-t border-gray-100 bg-gray-50 relative z-10 shrink-0">
                    <div class="flex gap-3">
                        <button type="button" @click="close()" class="flex-1 py-3 px-4 rounded-xl border border-gray-200 text-gray-700 font-bold text-sm hover:bg-gray-100 active:scale-95 transition-all bg-white text-center">
                            Batal
                        </button>
                        <button type="submit" form="agentForm" class="flex-1 py-3 px-4 rounded-xl text-white font-bold text-sm bg-emerald-600 hover:bg-emerald-700 active:scale-95 shadow-lg shadow-emerald-200 transition-all flex items-center justify-center gap-2">
                            <x-heroicon-s-check-circle class="w-5 h-5" />
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
