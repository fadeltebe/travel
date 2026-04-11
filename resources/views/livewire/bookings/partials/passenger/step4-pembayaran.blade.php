        <div class="space-y-4">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
                <h2 class="font-bold text-gray-900 border-b pb-2 text-center uppercase tracking-wider">Rincian Transaksi
                </h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tiket ({{ count($this->passengers) }}x)</span>
                        <span
                            class="font-medium text-gray-900">Rp{{ number_format($this->subtotalPrice, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center gap-4">
                        <span class="text-gray-500">Ongkir Paket</span>
                        <input type="number" wire:model.live="cargo_fee"
                            class="w-32 text-right border-gray-200 rounded-lg text-sm bg-gray-50">
                    </div>
                    <div class="flex justify-between items-center gap-4 border-b pb-2">
                        <span class="text-gray-500 text-xs">Jemput/Antar</span>
                        <input type="number" wire:model.live="pickup_dropoff_fee"
                            class="w-32 text-right border-gray-200 rounded-lg text-sm bg-gray-50">
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
                    @foreach (['cash' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS'] as $val => $label)
                        <button type="button" wire:click="$set('payment_method', '{{ $val }}')"
                            class="py-2 text-xs font-bold rounded-lg border-2 transition-all {{ $this->payment_method === $val ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-gray-100 text-gray-400' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <label class="text-xs font-bold text-gray-500 uppercase">Status Pembayaran</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" wire:click="$set('payment_status', 'pending')"
                        class="py-2 text-xs font-bold rounded-lg border-2 transition-all {{ $this->payment_status === 'pending' ? 'border-yellow-600 bg-yellow-50 text-yellow-600' : 'border-yellow-100 text-yellow-400' }}">
                        Pending
                    </button>
                    <button type="button" wire:click="$set('payment_status', 'paid')"
                        class="py-2 text-xs font-bold rounded-lg border-2 transition-all {{ $this->payment_status === 'paid' ? 'border-green-600 bg-green-50 text-green-600' : 'border-gray-100 text-gray-400' }}">
                        Lunas
                    </button>
                </div>

                @if(!$this->hasEnoughToken)
                    <div class="p-3 bg-red-50 text-red-600 rounded-xl border border-red-100 text-xs font-medium text-center">
                        <x-heroicon-s-exclamation-triangle class="w-5 h-5 inline-block mb-1" /> <br>
                        Saldo Token / Dompet Poin tidak mencukupi untuk memproses {{ count($this->passengers) }} tiket. <br>
                        Harap isi ulang (Top-Up) token.
                    </div>
                @endif

                <button wire:click="save"
                    wire:loading.attr="disabled"
                    wire:loading.class="!opacity-50 !cursor-not-allowed !active:scale-100"
                    wire:target="save"
                    @disabled(!$this->hasEnoughToken)
                    class="w-full py-4 rounded-2xl font-bold shadow-lg flex items-center justify-center gap-2 transition-transform disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100 active:scale-95
                    {{ $this->hasEnoughToken ? 'bg-green-600 text-white shadow-green-100' : 'bg-gray-300 text-gray-500 shadow-none' }}">
                    <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                        <x-heroicon-o-check-badge class="w-6 h-6" /> Konfirmasi & Simpan
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Menyimpan
                    </span>
                </button>
            </div>
