<div class="space-y-4">
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
        <h2 class="font-bold text-gray-900 text-sm uppercase tracking-wider">Data Pengirim</h2>
        <input type="text" wire:model="sender_name" class="w-full px-4 py-3 rounded-xl border-gray-200 text-sm" placeholder="Nama Pengirim">
        <input type="tel" wire:model="sender_phone" class="w-full px-4 py-3 rounded-xl border-gray-200 text-sm" placeholder="Nomor WA Pengirim">
    </div>

    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
        <h2 class="font-bold text-gray-900 text-sm uppercase tracking-wider text-orange-600">Data Penerima</h2>
        <input type="text" wire:model="receiver_name" class="w-full px-4 py-3 rounded-xl border-gray-200 text-sm" placeholder="Nama Penerima">
        <input type="tel" wire:model="receiver_phone" class="w-full px-4 py-3 rounded-xl border-gray-200 text-sm" placeholder="Nomor WA Penerima">
        <textarea wire:model="pickup_address" class="w-full px-4 py-3 rounded-xl border-gray-200 text-sm" placeholder="Alamat Detail (Opsional)"></textarea>
    </div>
    <button wire:click="goStep(3)" class="w-full py-4 bg-orange-500 text-white rounded-2xl font-bold shadow-lg">Lanjut: Detail Barang</button>
</div>
