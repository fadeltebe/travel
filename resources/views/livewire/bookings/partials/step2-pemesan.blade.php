{{-- Step 2: Informasi Pemesan --}}
<div class="space-y-4">
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
        <h2 class="font-bold text-gray-900">Informasi Pemesan</h2>
        <div>
            <label class="text-xs font-semibold text-gray-500 uppercase ml-1">Nama Lengkap</label>
            <input type="text" wire:model.live.debounce.300ms="booker_name" class="w-full mt-1 px-4 py-3 rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500" placeholder="Nama Pemesan">
            @error('booker_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="text-xs font-semibold text-gray-500 uppercase ml-1">WhatsApp</label>
            <input type="tel" wire:model.live.debounce.300ms="booker_phone" class="w-full mt-1 px-4 py-3 rounded-xl border-gray-200 focus:ring-blue-500" placeholder="0812xxxx">
            @error('booker_phone') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center justify-between p-3 bg-blue-50/50 rounded-xl border border-blue-100">
            <span class="text-sm font-bold text-blue-900">Pemesan ikut berangkat?</span>
            <button type="button" wire:click="$toggle('booker_is_passenger')" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $this->booker_is_passenger ? 'bg-blue-600' : 'bg-gray-300' }}">
                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $this->booker_is_passenger ? 'translate-x-6' : 'translate-x-1' }}"></span>
            </button>
        </div>

        @if($this->can_manage_all_agents)
        <div class="relative">
            <select wire:model="agent_id" class="w-full mt-1 px-4 py-3 rounded-xl border-gray-200 bg-white">
                <option value="">-- Pilih Cabang Agen --</option>
                @foreach($this->agents as $agent)
                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                @endforeach
            </select>
        </div>
        @else
        <!-- <div class="mt-1 px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-between">
            <span class="text-sm font-bold text-gray-700">{{ auth()->user()->agent->name ?? 'Internal Pusat' }}</span>
            <x-heroicon-m-lock-closed class="w-4 h-4 text-gray-400" />
        </div> -->
        <input type="hidden" wire:model="agent_id">
        @endif
    </div>
    <button wire:click="goStep(3)" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-bold shadow-lg active:scale-95 transition-transform">
        Lanjut ke Daftar Penumpang
    </button>
</div>
