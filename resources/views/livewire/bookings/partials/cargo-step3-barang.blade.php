<div class="space-y-4">
    <div class="flex justify-between items-center px-1">
        <h2 class="font-bold text-gray-900">Daftar Barang</h2>
        <button wire:click="addItem" class="text-orange-500 text-sm font-bold flex items-center gap-1">
            <x-heroicon-o-plus-circle class="w-5 h-5" /> Tambah
        </button>
    </div>

    @foreach($items as $index => $item)
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-3 relative">
        @if(count($items) > 1)
        <button wire:click="removeItem({{ $index }})" class="absolute top-4 right-4 text-red-400"><x-heroicon-o-trash class="w-5 h-5" /></button>
        @endif
        <input type="text" wire:model="items.{{ $index }}.description" class="w-full px-4 py-2 rounded-lg border-gray-200 text-sm font-bold" placeholder="Deskripsi Barang">
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
    </div>
    @endforeach
    <button wire:click="goStep(4)" class="w-full py-4 bg-orange-500 text-white rounded-2xl font-bold shadow-lg">Lanjut: Pembayaran</button>
</div>
