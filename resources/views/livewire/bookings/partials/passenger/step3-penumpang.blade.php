        <div class="space-y-4" x-data="{ openModal: false }" x-on:open-seat-modal.window="openModal = true"
            x-on:close-seat-modal.window="openModal = false">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
                <div class="flex justify-between items-center">
                    <h2 class="font-bold text-gray-900">Daftar Penumpang</h2>
                    <button wire:click="addPassenger" class="text-blue-600 text-sm font-bold flex items-center gap-1">
                        <x-heroicon-o-plus-circle class="w-5 h-5" /> Tambah
                    </button>
                </div>

                @forelse($this->passengers as $index => $passenger)
                    <div
                        class="p-4 rounded-xl border border-gray-200 space-y-2 relative {{ $passenger['is_booker'] ?? false ? 'bg-blue-50/30 border-blue-200' : '' }}">

                        {{-- Badge Pemesan --}}
                        @if ($passenger['is_booker'] ?? false)
                            <div
                                class="flex items-center gap-1.5 text-[10px] bg-blue-600 text-white px-2 py-0.5 rounded-md font-bold uppercase w-fit">
                                <x-heroicon-s-user class="w-3 h-3" /> Pemesan
                            </div>
                        @endif

                        {{-- Info Penumpang --}}
                        <div class="flex justify-between items-start">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase">Penumpang
                                        #{{ $index + 1 }}</span>
                                    <span
                                        class="text-[9px] font-bold px-1.5 py-0.5 rounded {{ $this->passengerTypeBadgeClasses[$passenger['passenger_type'] ?? 'dewasa'] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ $this->passengerTypeOptions[$passenger['passenger_type'] ?? 'dewasa'] ?? ucfirst($passenger['passenger_type'] ?? 'dewasa') }}
                                    </span>
                                    {{-- Menampilkan Harga Tiket yang diinput agen --}}
                                    <span
                                        class="text-[9px] font-black px-1.5 py-0.5 rounded bg-orange-100 text-orange-700 ml-1">
                                        Rp {{ number_format($passenger['ticket_price'] ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                                <p class="text-sm font-bold text-gray-900 mt-1 truncate">{{ $passenger['name'] ?? '-' }}
                                </p>
                                <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-user class="w-3 h-3" />
                                        {{ $this->genderOptions[$passenger['gender'] ?? 'male'] ?? 'Laki-laki' }}
                                    </span>
                                    @if (!empty($passenger['phone']))
                                        <span class="flex items-center gap-1">
                                            <x-heroicon-o-phone class="w-3 h-3" />
                                            {{ $passenger['phone'] }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Pickup/Dropoff Info --}}
                                @if (!empty($passenger['need_pickup']) || !empty($passenger['need_dropoff']))
                                    <div class="flex flex-wrap gap-1.5 mt-1.5">
                                        @if (!empty($passenger['need_pickup']))
                                            <span
                                                class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 flex items-center gap-0.5">
                                                <x-heroicon-o-arrow-up-on-square class="w-2.5 h-2.5" /> Jemput
                                            </span>
                                        @endif
                                        @if (!empty($passenger['need_dropoff']))
                                            <span
                                                class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-purple-100 text-purple-700 flex items-center gap-0.5">
                                                <x-heroicon-o-arrow-down-on-square class="w-2.5 h-2.5" /> Antar
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            {{-- Aksi: Kursi + Edit + Hapus --}}
                            <div class="flex items-center gap-1.5 ml-2 shrink-0">
                                <button type="button" wire:click="openSeatModal({{ $index }})"
                                    class="flex items-center gap-1 px-2.5 py-1.5 bg-orange-100 text-orange-600 rounded-lg text-xs font-bold active:scale-95 transition-transform">
                                    <x-heroicon-s-stop class="w-3 h-3" />
                                    {{ $passenger['seat_number'] ?? 'Kursi' }}
                                </button>
                                <button wire:click="editPassenger({{ $index }})"
                                    class="p-1.5 bg-gray-100 text-gray-500 rounded-lg hover:bg-blue-100 hover:text-blue-600 transition-colors">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                </button>
                                @if (count($this->passengers) > 1 && !($passenger['is_booker'] ?? false))
                                    <button wire:click="removePassenger({{ $index }})"
                                        class="p-1.5 bg-gray-100 text-gray-500 rounded-lg hover:bg-red-100 hover:text-red-600 transition-colors">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <x-heroicon-o-user-group class="w-10 h-10 text-gray-300 mx-auto" />
                        <p class="text-sm text-gray-400 mt-2">Belum ada penumpang</p>
                        <button wire:click="addPassenger" class="mt-3 text-blue-600 text-sm font-bold">+ Tambah
                            Penumpang</button>
                    </div>
                @endforelse
            </div>

            @include('livewire.bookings.partials.passenger.modal-passenger-form')

            {{-- ========== MODAL PILIH KURSI (existing) ========== --}}
            <template x-teleport="body">
                <div x-show="openModal" x-cloak
                    class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center overflow-hidden"
                    role="dialog" aria-modal="true">

                    {{-- Backdrop dengan Blur --}}
                    <div x-show="openModal" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0" @click="openModal = false"
                        class="fixed inset-0 bg-gray-900/60 backdrop-blur-md">
                    </div>

                    {{-- Content Modal --}}
                    <div x-show="openModal" x-transition:enter="transition ease-out duration-300 transform"
                        x-transition:enter-start="translate-y-full sm:translate-y-10 sm:scale-95 sm:opacity-0"
                        x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100"
                        x-transition:leave="transition ease-in duration-200 transform"
                        x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100"
                        x-transition:leave-end="translate-y-full sm:translate-y-10 sm:scale-95 sm:opacity-0"
                        class="relative bg-white w-full max-w-lg rounded-t-[2.5rem] sm:rounded-3xl shadow-2xl flex flex-col h-[90vh] sm:h-auto sm:max-h-[90vh] overflow-hidden z-[10000]">

                        {{-- Handle bar untuk Mobile --}}
                        <div class="sm:hidden w-12 h-1.5 bg-gray-300 rounded-full mx-auto mt-4 mb-2 shrink-0"></div>

                        {{-- Header Modal --}}
                        <div
                            class="px-6 py-4 border-b flex justify-between items-center bg-white sticky top-0 shrink-0">
                            <div>
                                <h3 class="font-black text-gray-900 text-lg">Pilih Kursi</h3>
                                <p class="text-[10px] text-orange-600 font-bold uppercase tracking-wider">
                                    Penumpang #{{ ($this->selecting_for_index ?? 0) + 1 }}
                                </p>
                            </div>
                            <button @click="openModal = false"
                                class="p-2 bg-gray-100 text-gray-500 rounded-full hover:bg-gray-200 transition-colors">
                                <x-heroicon-s-x-mark class="w-6 h-6" />
                            </button>
                        </div>

                        {{-- Body Modal (Scrollable) --}}
                        <div class="p-6 overflow-y-auto custom-scrollbar flex-1 bg-gray-50/50">

                            {{-- Info Legend --}}
                            <div
                                class="grid grid-cols-4 gap-2 bg-white p-3 rounded-2xl border border-gray-100 shadow-sm mb-6">
                                <div class="flex flex-col items-center gap-1 text-center">
                                    <div class="w-4 h-4 bg-white border border-gray-200 rounded shadow-sm"></div>
                                    <span class="text-[8px] font-bold text-gray-400 uppercase">Tersedia</span>
                                </div>
                                <div class="flex flex-col items-center gap-1 text-center">
                                    <div class="w-4 h-4 bg-gray-400 rounded shadow-sm"></div>
                                    <span class="text-[8px] font-bold text-gray-400 uppercase">Terisi</span>
                                </div>
                                <div class="flex flex-col items-center gap-1 text-center">
                                    <div class="w-4 h-4 bg-red-500 rounded shadow-sm"></div>
                                    <span class="text-[8px] font-bold text-gray-400 uppercase">Dipilih</span>
                                </div>
                                <div class="flex flex-col items-center gap-1 text-center">
                                    <div class="w-4 h-4 bg-blue-600 rounded shadow-sm"></div>
                                    <span class="text-[8px] font-bold text-gray-400 uppercase">Anda</span>
                                </div>
                            </div>

                            {{-- Visual Bus Layout --}}
                            <div class="bg-white p-6 rounded-[2rem] border-2 border-gray-100 shadow-inner relative">

                                @php
                                    $totalColumns = $this->selectedSchedule?->bus?->busLayout?->total_columns ?? 4;
                                @endphp

                                {{-- Grid Kursi --}}
                                <div class="grid gap-3"
                                    style="grid-template-columns: repeat({{ $totalColumns }}, minmax(0, 1fr));">
                                    @foreach ($this->busSeats as $seat)
                                        @php
                                            $isBooked = in_array($seat->seat_number, $this->bookedSeats);
                                            $allSelectedInForm = collect($this->passengers)
                                                ->pluck('seat_number')
                                                ->filter()
                                                ->toArray();
                                            $isSelectedByOthers = in_array($seat->seat_number, $allSelectedInForm);
                                            $isMyCurrentSeat =
                                                ($this->passengers[$this->selecting_for_index]['seat_number'] ??
                                                    null) ===
                                                $seat->seat_number;
                                            $isNotPassengerType = $seat->type !== 'passenger';
                                            $isUnavailable =
                                                $isBooked ||
                                                ($isSelectedByOthers && !$isMyCurrentSeat) ||
                                                $isNotPassengerType;
                                        @endphp

                                        @if ($seat->type === 'aisle')
                                            <div class="w-full aspect-square flex items-center justify-center">
                                                <div class="w-1.5 h-1.5 bg-gray-200 rounded-full"></div>
                                            </div>
                                        @else
                                            <button type="button"
                                                wire:click="selectSeat('{{ $seat->seat_number }}')"
                                                @disabled($isUnavailable)
                                                class="relative w-full aspect-square rounded-xl flex items-center justify-center text-xs font-black transition-all duration-200 active:scale-90
                {{ $isBooked ? 'bg-gray-400 text-white cursor-not-allowed' : '' }}
                {{ $isSelectedByOthers && !$isMyCurrentSeat ? 'bg-red-500 text-white cursor-not-allowed shadow-lg shadow-red-100' : '' }}
                {{ $isMyCurrentSeat ? 'bg-blue-600 text-white ring-4 ring-blue-100 z-10' : '' }}
                {{ !$isUnavailable && !$isMyCurrentSeat ? 'bg-white text-gray-700 border-2 border-gray-100 hover:border-blue-300 shadow-sm' : '' }}
                {{ $isNotPassengerType && !$isBooked ? 'bg-gray-100 text-gray-400 cursor-not-allowed opacity-50' : '' }}">

                                                @if ($seat->type === 'driver')
                                                    <x-heroicon-s-user class="w-5 h-5 opacity-30" />
                                                @else
                                                    {{ $seat->seat_number }}
                                                @endif

                                                @if ($isMyCurrentSeat)
                                                    <div
                                                        class="absolute -top-1 -right-1 w-3 h-3 bg-orange-500 rounded-full border-2 border-white shadow-sm animate-pulse">
                                                    </div>
                                                @endif
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Footer Modal --}}
                        <div class="p-4 bg-white border-t shrink-0">
                            <button @click="openModal = false"
                                class="w-full py-4 bg-gray-900 text-white rounded-2xl font-bold shadow-lg active:scale-[0.98] transition-transform">
                                Selesai Pilih
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            @if ($errors->has('passengers.*'))
                <div class="mt-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl">
                    <div class="flex">
                        <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-red-500" />
                        <div class="ml-3">
                            <p class="text-sm text-red-700 font-bold">Data belum lengkap:</p>
                            <ul class="list-disc list-inside text-xs text-red-600 mt-1">
                                @foreach ($errors->all() as $error)
                                    @if (str_contains($error, 'penumpang'))
                                        <li>{{ $error }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <button wire:click="goStep(4)"
                class="w-full py-4 bg-blue-600 text-white rounded-2xl font-bold shadow-lg active:scale-95 transition-transform">
                Lanjut ke Pembayaran
            </button>
        </div>
