<div class="space-y-4">
    <div class="flex justify-between items-center px-1">
        <h2 class="font-bold text-gray-900">Daftar Barang</h2>
        <button wire:click="addItem" class="text-orange-500 text-sm font-bold flex items-center gap-1">
            <x-heroicon-o-plus-circle class="w-5 h-5" /> Tambah
        </button>
    </div>

    @foreach($items as $index => $item)
    <div wire:key="cargo-item-{{ $index }}" class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-3 relative">
        @if(count($items) > 1)
        <button wire:click="removeItem({{ $index }})" class="absolute top-4 right-4 text-red-400"><x-heroicon-o-trash class="w-5 h-5" /></button>
        @endif
        <div class="space-y-2 mt-1">
            <input type="text" autofocus wire:model="items.{{ $index }}.item_name" class="w-full px-4 py-2 rounded-lg border-gray-200 text-sm font-bold text-gray-900" placeholder="Nama Barang / Kemasan (contoh: Dos Coklat)">
            <input type="text" wire:model="items.{{ $index }}.description" class="w-full px-4 py-2 rounded-lg border-gray-200 text-sm text-gray-600" placeholder="Keterangan Isi (contoh: Pakaian, Elektronik, Makanan, dll)">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase">Berat (Kg)</label>
                <input type="number" wire:model="items.{{ $index }}.weight" class="w-full px-4 py-2 rounded-lg border-gray-200 text-sm">
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase">Biaya Kirim (Rp)</label>
                <input type="number" wire:model="items.{{ $index }}.price" class="w-full px-4 py-2 rounded-lg border-gray-200 text-sm font-black text-orange-500">
            </div>
        </div>
        <div class="mt-2">
            <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Foto Barang (Opsional)</label>
            <div class="flex items-center gap-3">
                <div wire:ignore class="w-full">
                    <input type="file" wire:model="photos.{{ $index }}" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
                </div>
                <div wire:loading wire:target="photos.{{ $index }}">
                    <svg class="animate-spin h-6 w-6 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                </div>
                @if(isset($photos[$index]) && !is_string($photos[$index]))
                    <div class="w-10 h-10 shrink-0 rounded-lg overflow-hidden border border-gray-200" wire:loading.remove wire:target="photos.{{ $index }}">
                        <img src="{{ $photos[$index]->temporaryUrl() }}" class="w-full h-full object-cover">
                    </div>
                @endif
            </div>
            @error('photos.'.$index) <span class="text-xs text-red-500">{{ $message }}</span> @enderror
        </div>
    </div>
    @endforeach
    <button wire:click="goStep(4)" wire:loading.attr="disabled" class="w-full py-4 bg-orange-500 text-white rounded-2xl font-bold shadow-lg disabled:opacity-50">Lanjut: Pembayaran</button>
</div>
