        <div class="space-y-4">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
                <h2 class="font-bold text-gray-900 border-b pb-2 text-center uppercase tracking-wider">Rincian Transaksi</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tiket ({{ count($this->passengers) }}x)</span>
                        <span class="font-medium text-gray-900">Rp{{ number_format($this->subtotalPrice, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center gap-4">
                        <span class="text-gray-500">Ongkir Paket</span>
                        <input type="number" wire:model.live="cargo_fee" class="w-32 text-right border-gray-200 rounded-lg text-sm bg-gray-50">
                    </div>
                    <div class="flex justify-between items-center gap-4 border-b pb-2">
                        <span class="text-gray-500 text-xs">Jemput/Antar</span>
                        <input type="number" wire:model.live="pickup_dropoff_fee" class="w-32 text-right border-gray-200 rounded-lg text-sm bg-gray-50">
                    </div>
                    <div class="flex justify-between pt-2 font-black text-xl">
                        <span class="text-gray-900">TOTAL</span>
                        <span class="text-blue-600">Rp{{ number_format($this->totalPrice, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
                <label class="text-xs font-bold text-gray-500 uppercase">Metode Pembayaran</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach(['cash' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS'] as $val => $label)
                    <button type="button" wire:click="$set('payment_method', '{{ $val }}')" class="py-2 text-xs font-bold rounded-lg border-2 transition-all {{ $this->payment_method === $val ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-gray-100 text-gray-400' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>

                <label class="text-xs font-bold text-gray-500 uppercase">Status Pembayaran</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" wire:click="$set('payment_status', 'pending')" class="py-2 text-xs font-bold rounded-lg border-2 transition-all {{ $this->payment_status === 'pending' ? 'border-yellow-600 bg-yellow-50 text-yellow-600' : 'border-yellow-100 text-yellow-400' }}">
                        Pending
                    </button>
                    <button type="button" wire:click="$set('payment_status', 'paid')" class="py-2 text-xs font-bold rounded-lg border-2 transition-all {{ $this->payment_status === 'paid' ? 'border-green-600 bg-green-50 text-green-600' : 'border-gray-100 text-gray-400' }}">
                        Lunas
                    </button>
                </div>


                <button wire:click="save" class="w-full py-4 bg-green-600 text-white rounded-2xl font-bold shadow-lg shadow-green-100 active:scale-95 transition-transform flex items-center justify-center gap-2">
                    <x-heroicon-o-check-badge class="w-6 h-6" /> Konfirmasi & Simpan
                </button>
            </div>
