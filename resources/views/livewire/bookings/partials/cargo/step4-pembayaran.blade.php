<div class="space-y-4">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-4 text-center">
        <span class="text-xs font-bold text-gray-400 uppercase">Total Tagihan</span>
        <h2 class="text-4xl font-black text-orange-500">Rp{{ number_format($this->totalBill, 0, ',', '.') }}</h2>
    </div>

    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
        <div class="grid grid-cols-2 gap-2">
            <button wire:click="$set('payment_type', 'origin')" class="py-3 text-xs font-bold rounded-xl border-2 {{ $payment_type == 'origin' ? 'border-orange-500 bg-blue-50 text-orange-500' : 'border-gray-50 text-gray-400' }}">Bayar di Sini</button>
            <button wire:click="$set('payment_type', 'destination')" class="py-3 text-xs font-bold rounded-xl border-2 {{ $payment_type == 'destination' ? 'border-orange-600 bg-orange-50 text-orange-600' : 'border-gray-50 text-gray-400' }}">Bayar di Tujuan (COD)</button>
        </div>

        <div class="grid grid-cols-3 gap-2">
            @foreach(['cash' => 'Tunai', 'transfer' => 'TF', 'qris' => 'QRIS'] as $v => $l)
            <button wire:click="$set('payment_method', '{{ $v }}')" class="py-2 text-[10px] font-bold rounded-lg border {{ $payment_method == $v ? 'bg-gray-900 text-white' : 'bg-gray-50 text-gray-400' }}">{{ $l }}</button>
            @endforeach
        </div>

        <button wire:click="save" class="w-full py-4 bg-green-600 text-white rounded-2xl font-bold shadow-lg flex items-center justify-center gap-2">
            <x-heroicon-o-check-badge class="w-6 h-6" /> Simpan & Selesai
        </button>
    </div>
</div>
