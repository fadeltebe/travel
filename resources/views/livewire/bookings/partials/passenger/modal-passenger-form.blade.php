{{-- Modal Form Tambah/Edit Penumpang --}}
@if($this->showPassengerModal)
<template x-teleport="body">
    <div class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center overflow-hidden" role="dialog" aria-modal="true"
         x-data="{ show: true }" x-show="show"
         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        {{-- Backdrop --}}
        <div wire:click="closePassengerModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-md"></div>

        {{-- Content --}}
        <div class="relative bg-white w-full max-w-lg rounded-t-[2.5rem] sm:rounded-3xl shadow-2xl flex flex-col h-[90vh] sm:h-auto sm:max-h-[90vh] overflow-hidden z-[10000]"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-y-full sm:translate-y-10 sm:scale-95 sm:opacity-0"
             x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100">

            {{-- Handle bar Mobile --}}
            <div class="sm:hidden w-12 h-1.5 bg-gray-300 rounded-full mx-auto mt-4 mb-2 shrink-0"></div>

            {{-- Header --}}
            <div class="px-6 py-4 border-b flex justify-between items-center bg-white sticky top-0 shrink-0">
                <div>
                    <h3 class="font-black text-gray-900 text-lg">
                        {{ $this->editingPassengerIndex !== null ? 'Edit Penumpang' : 'Tambah Penumpang' }}
                    </h3>
                    <p class="text-[10px] text-blue-600 font-bold uppercase tracking-wider">
                        Lengkapi data penumpang
                    </p>
                </div>
                <button wire:click="closePassengerModal" class="p-2 bg-gray-100 text-gray-500 rounded-full hover:bg-gray-200 transition-colors">
                    <x-heroicon-s-x-mark class="w-6 h-6" />
                </button>
            </div>

            {{-- Body --}}
            <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4">

                {{-- Nama --}}
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase ml-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.lazy="form_name" class="w-full mt-1 px-4 py-3 rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500" placeholder="Nama penumpang">
                    @error('form_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Gender --}}
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase ml-1">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-2 mt-1">
                        <button type="button" wire:click="$set('form_gender', 'male')" class="py-3 text-sm font-bold rounded-xl border-2 transition-all flex items-center justify-center gap-2 {{ $this->form_gender === 'male' ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-gray-100 text-gray-400' }}">
                            <x-heroicon-o-user class="w-4 h-4" /> Laki-laki
                        </button>
                        <button type="button" wire:click="$set('form_gender', 'female')" class="py-3 text-sm font-bold rounded-xl border-2 transition-all flex items-center justify-center gap-2 {{ $this->form_gender === 'female' ? 'border-pink-600 bg-pink-50 text-pink-600' : 'border-gray-100 text-gray-400' }}">
                            <x-heroicon-o-user class="w-4 h-4" /> Perempuan
                        </button>
                    </div>
                    @error('form_gender') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Kategori Usia --}}
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase ml-1">Kategori Usia <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-3 gap-2 mt-1">
                        @foreach ($this->passengerTypeOptions as $val => $label)
                        @php
                            $activeColors = [
                                'dewasa' => 'border-green-600 bg-green-50 text-green-600',
                                'balita' => 'border-pink-600 bg-pink-50 text-pink-600',
                                'bayi' => 'border-blue-600 bg-blue-50 text-blue-600',
                            ];
                        @endphp
                        <button type="button" wire:click="$set('form_passenger_type', '{{ $val }}')" class="py-3 text-xs font-bold rounded-xl border-2 transition-all {{ $this->form_passenger_type === $val ? $activeColors[$val] : 'border-gray-100 text-gray-400' }}">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                    @error('form_passenger_type') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- NIK & Phone --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase ml-1">NIK / KTP</label>
                        <input type="text" wire:model.lazy="form_id_card_number" class="w-full mt-1 px-4 py-3 rounded-xl border-gray-200 focus:ring-blue-500" placeholder="Opsional">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase ml-1">No. HP</label>
                        <input type="tel" wire:model.lazy="form_phone" class="w-full mt-1 px-4 py-3 rounded-xl border-gray-200 focus:ring-blue-500" placeholder="Opsional">
                    </div>
                </div>

                {{-- Harga Tiket --}}
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase ml-1">Harga Tiket <span class="text-red-500">*</span></label>
                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500 font-bold">Rp</span>
                        <input type="number" wire:model.lazy="form_ticket_price" class="w-full pl-10 pr-4 py-3 rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500" placeholder="Misal: 150000">
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1 ml-1">Isi 0 untuk penumpang bayi/balita.</p>
                    @error('form_ticket_price') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Jemput --}}
                <div class="p-3 bg-emerald-50/50 rounded-xl border border-emerald-100 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-emerald-900">Perlu dijemput?</span>
                        <button type="button" wire:click="$toggle('form_need_pickup')" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $this->form_need_pickup ? 'bg-emerald-600' : 'bg-gray-300' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $this->form_need_pickup ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </div>
                    @if($this->form_need_pickup)
                    <div>
                        <label class="text-xs font-semibold text-gray-500 ml-1">Alamat Jemput</label>
                        <textarea wire:model.lazy="form_pickup_address" rows="2" class="w-full mt-1 px-4 py-2 rounded-xl border-gray-200 focus:ring-emerald-500 text-sm" placeholder="Masukkan alamat jemput..."></textarea>
                    </div>
                    @endif
                </div>

                {{-- Antar --}}
                <div class="p-3 bg-purple-50/50 rounded-xl border border-purple-100 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-purple-900">Perlu diantar?</span>
                        <button type="button" wire:click="$toggle('form_need_dropoff')" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $this->form_need_dropoff ? 'bg-purple-600' : 'bg-gray-300' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $this->form_need_dropoff ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </div>
                    @if($this->form_need_dropoff)
                    <div>
                        <label class="text-xs font-semibold text-gray-500 ml-1">Alamat Antar</label>
                        <textarea wire:model.lazy="form_dropoff_address" rows="2" class="w-full mt-1 px-4 py-2 rounded-xl border-gray-200 focus:ring-purple-500 text-sm" placeholder="Masukkan alamat tujuan..."></textarea>
                    </div>
                    @endif
                </div>

            </div>

            {{-- Footer --}}
            <div class="p-4 bg-white border-t shrink-0">
                <button wire:click="savePassenger" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-bold shadow-lg active:scale-[0.98] transition-transform flex items-center justify-center gap-2">
                    <x-heroicon-o-check class="w-5 h-5" />
                    {{ $this->editingPassengerIndex !== null ? 'Simpan Perubahan' : 'Tambah Penumpang' }}
                </button>
            </div>
        </div>
    </div>
</template>
@endif
