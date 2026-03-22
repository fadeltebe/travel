<div class="space-y-4">
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <label class="block text-sm font-bold text-gray-800 mb-3">Pilih Jadwal Keberangkatan Bus</label>
        <div class="space-y-3 overflow-y-auto pr-2 custom-scrollbar" style="max-height: 400px;">
            @forelse($schedules as $schedule)
            <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all {{ $schedule_id == $schedule->id ? 'border-blue-500 bg-blue-50' : 'border-gray-100 bg-gray-50' }}">
                <input type="radio" wire:model.live="schedule_id" value="{{ $schedule->id }}" class="sr-only">
                <div class="flex justify-between items-start">
                    <span class="text-sm font-bold text-gray-900">{{ $schedule->route->originAgent->city }} → {{ $schedule->route->destinationAgent->city }}</span>
                    <span class="text-[10px] px-2 py-0.5 bg-orange-500 text-white rounded font-bold uppercase">{{ $schedule->bus->name }}</span>
                </div>
                <div class="mt-2 flex items-center gap-3 text-[10px] text-gray-500 font-medium">
                    <span class="flex items-center gap-1"><x-heroicon-o-calendar class="w-3.5 h-3.5" /> {{ $schedule->departure_date->format('d M Y') }}</span>
                    <span class="flex items-center gap-1"><x-heroicon-o-clock class="w-3.5 h-3.5" /> {{ substr($schedule->departure_time, 0, 5) }}</span>
                </div>
            </label>
            @empty
            <p class="text-center text-sm text-gray-500 py-10">Jadwal tidak tersedia.</p>
            @endforelse
        </div>
    </div>
    <button wire:click="goStep(2)" class="w-full py-4 bg-orange-500 text-white rounded-2xl font-bold shadow-lg">Lanjut: Data Pengirim</button>
</div>
