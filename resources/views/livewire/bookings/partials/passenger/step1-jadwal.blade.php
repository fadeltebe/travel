        <div class="flex flex-col h-[63vh]">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col min-h-0">
                <label class="block text-sm font-bold text-gray-800 mb-3 flex-none">Pilih Jadwal</label>
                <div class="space-y-3 overflow-y-auto pr-2 custom-scrollbar" style="max-height: 400px;">
                    @forelse($this->schedules as $schedule)
                    <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all {{ $this->schedule_id == $schedule->id ? 'border-blue-500 bg-blue-50' : 'border-gray-100 bg-gray-50' }}">
                        <input type="radio" wire:model.live="schedule_id" value="{{ $schedule->id }}" class="sr-only">
                        <div class="flex justify-between items-start">
                            <span class="text-sm font-bold text-gray-900">{{ $schedule->route->originAgent->city }} → {{ $schedule->route->destinationAgent->city }}</span>
                            <span class="text-sm font-black text-orange-500">Rp{{ number_format($schedule->price, 0, ',', '.') }}</span>
                        </div>
                        <div class="mt-2 flex items-center gap-3 text-xs text-gray-500">
                            <span class="flex items-center gap-1"><x-heroicon-o-calendar class="w-3.5 h-3.5" /> {{ $schedule->departure_date->locale('id')->translatedFormat('d F Y') }}</span>
                            <span class="flex items-center gap-1"><x-heroicon-o-clock class="w-3.5 h-3.5" /> {{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}</span>
                            <span class="text-emerald-600 font-semibold">{{ $schedule->available_seats - $schedule->total_passengers_sum }} kursi</span>
                        </div>
                    </label>
                    @empty
                    <p class="text-center text-sm text-gray-500 py-10">Tidak ada jadwal tersedia.</p>
                    @endforelse
                </div>
            </div>
            <div class="mt-4 pt-2 bg-white/80 backdrop-blur-sm sticky bottom-0">
                @if(!$this->hasEnoughToken)
                    <div class="p-3 bg-red-50 text-red-600 rounded-xl border border-red-100 text-xs font-medium text-center mb-3">
                        <x-heroicon-s-exclamation-triangle class="w-5 h-5 inline-block mb-1" /> <br>
                        Saldo Token / Dompet Poin tidak mencukupi untuk memproses {{ max(1, count($this->passengers)) }} tiket. <br>
                        Harap isi ulang (Top-Up) token.
                    </div>
                @endif
                <button wire:click="goStep(2)" 
                    @disabled(!$this->hasEnoughToken)
                    class="w-full py-4 rounded-2xl font-bold shadow-lg active:scale-95 transition-transform disabled:opacity-50 disabled:cursor-not-allowed
                    {{ $this->hasEnoughToken ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-500' }}">
                    Lanjut ke Data Pemesan
                </button>
            </div>
        </div>
