{{-- Step 3: Daftar Penumpang --}}
<div class="space-y-4" x-data="{ openModal: false }" x-on:open-seat-modal.window="openModal = true" x-on:close-seat-modal.window="openModal = false">
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-gray-900">Daftar Penumpang</h2>
            <button wire:click="addPassenger" class="text-blue-600 text-sm font-bold flex items-center gap-1">
                <x-heroicon-o-plus-circle class="w-5 h-5" /> Tambah
            </button>
        </div>

        @forelse($this->passengers as $index => $passenger)
        <div class="p-4 rounded-xl border border-gray-200 space-y-2 relative {{ ($passenger['is_booker'] ?? false) ? 'bg-blue-50/30 border-blue-200' : '' }}">

            {{-- Badge Pemesan --}}
            @if($passenger['is_booker'] ?? false)
            <div class="flex items-center gap-1.5 text-[10px] bg-blue-600 text-white px-2 py-0.5 rounded-md font-bold uppercase w-fit">
                <x-heroicon-s-user class="w-3 h-3" /> Pemesan
            </div>
            @endif

            {{-- Info Penumpang --}}
            <div class="flex justify-between items-start">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Penumpang #{{ $index + 1 }}</span>
                        @php
                            $typeColors = ['dewasa' => 'bg-green-100 text-green-700', 'anak-anak' => 'bg-yellow-100 text-yellow-700', 'balita' => 'bg-pink-100 text-pink-700'];
                        @endphp
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded {{ $typeColors[$passenger['passenger_type'] ?? 'dewasa'] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($passenger['passenger_type'] ?? 'dewasa') }}
                        </span>
                    </div>
                    <p class="text-sm font-bold text-gray-900 mt-1 truncate">{{ $passenger['name'] ?? '-' }}</p>
                    <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                        <span class="flex items-center gap-1">
                            <x-heroicon-o-user class="w-3 h-3" />
                            {{ ($passenger['gender'] ?? 'male') === 'male' ? 'Laki-laki' : 'Perempuan' }}
                        </span>
                        @if(!empty($passenger['phone']))
                        <span class="flex items-center gap-1">
                            <x-heroicon-o-phone class="w-3 h-3" />
                            {{ $passenger['phone'] }}
                        </span>
                        @endif
                    </div>

                    {{-- Pickup/Dropoff Info --}}
                    @if(!empty($passenger['need_pickup']) || !empty($passenger['need_dropoff']))
                    <div class="flex flex-wrap gap-1.5 mt-1.5">
                        @if(!empty($passenger['need_pickup']))
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 flex items-center gap-0.5">
                            <x-heroicon-o-arrow-up-on-square class="w-2.5 h-2.5" /> Jemput
                        </span>
                        @endif
                        @if(!empty($passenger['need_dropoff']))
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-purple-100 text-purple-700 flex items-center gap-0.5">
                            <x-heroicon-o-arrow-down-on-square class="w-2.5 h-2.5" /> Antar
                        </span>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Aksi: Kursi + Edit + Hapus --}}
                <div class="flex items-center gap-1.5 ml-2 shrink-0">
                    <button type="button" wire:click="openSeatModal({{ $index }})" class="flex items-center gap-1 px-2.5 py-1.5 bg-orange-100 text-orange-600 rounded-lg text-xs font-bold active:scale-95 transition-transform">
                        <x-heroicon-s-stop class="w-3 h-3" />
                        {{ $passenger['seat_number'] ?? 'Kursi' }}
                    </button>
                    <button wire:click="editPassenger({{ $index }})" class="p-1.5 bg-gray-100 text-gray-500 rounded-lg hover:bg-blue-100 hover:text-blue-600 transition-colors">
                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                    </button>
                    @if(count($this->passengers) > 1 && !($passenger['is_booker'] ?? false))
                    <button wire:click="removePassenger({{ $index }})" class="p-1.5 bg-gray-100 text-gray-500 rounded-lg hover:bg-red-100 hover:text-red-600 transition-colors">
                        <x-heroicon-o-trash class="w-4 h-4" />
                    </button>
                    @endif
                </div>
            </div>

            <input type="hidden" wire:model="passengers.{{ $index }}.seat_number">
        </div>
        @empty
        <div class="text-center py-8">
            <x-heroicon-o-user-group class="w-10 h-10 text-gray-300 mx-auto" />
            <p class="text-sm text-gray-400 mt-2">Belum ada penumpang</p>
            <button wire:click="addPassenger" class="mt-3 text-blue-600 text-sm font-bold">+ Tambah Penumpang</button>
        </div>
        @endforelse
    </div>

    {{-- Modal Form Penumpang --}}
    @include('livewire.bookings.partials.modal-passenger-form')

    {{-- Modal Pilih Kursi --}}
    @include('livewire.bookings.partials.modal-seat-picker')

    {{-- Error Messages --}}
    @if ($errors->has('passengers.*'))
    <div class="mt-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl">
        <div class="flex">
            <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-red-500" />
            <div class="ml-3">
                <p class="text-sm text-red-700 font-bold">Data belum lengkap:</p>
                <ul class="list-disc list-inside text-xs text-red-600 mt-1">
                    @foreach ($errors->all() as $error)
                    @if(str_contains($error, 'penumpang'))
                    <li>{{ $error }}</li>
                    @endif
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <button wire:click="goStep(4)" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-bold shadow-lg active:scale-95 transition-transform">
        Lanjut ke Pembayaran
    </button>
</div>
