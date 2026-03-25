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

            {{-- ========== MODAL FORM PENUMPANG ========== --}}
            @if($this->showPassengerModal)
            <template x-teleport="body">
                <div class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center overflow-hidden" role="dialog" aria-modal="true"
                     x-data="{ show: true }" x-show="show"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                    {{-- Backdrop --}}
                    <div wire:click="closePassengerModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-md"></div>

                    {{-- Content --}}
                    <div class="relative bg-white w-full max-w-lg rounded-t-[2.5rem] sm:rounded-3xl shadow-2xl flex flex-col h-[90vh] sm:h-auto sm:max-h-[90vh] overflow-hidden z-[10000]"
                         x-transition:enter="transition ease-out duration-300 transform"
                         x-transition:enter-start="translate-y-full sm:translate-y-10 sm:scale-95 sm:opacity-0"
                         x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100">

                        {{-- Handle bar Mobile --}}
                        <div class="sm:hidden w-12 h-1.5 bg-gray-300 rounded-full mx-auto mt-4 mb-2 shrink-0"></div>

                        {{-- Header --}}
                        <div class="px-6 py-4 border-b flex justify-between items-center bg-white sticky top-0 shrink-0">
                            <div>
                                <h3 class="font-black text-gray-900 text-lg">
                                    {{ $this->editingPassengerIndex !== null ? 'Edit Penumpang' : 'Tambah Penumpang' }}
                                </h3>
                                <p class="text-[10px] text-blue-600 font-bold uppercase tracking-wider">
                                    Lengkapi data penumpang
                                </p>
                            </div>
                            <button wire:click="closePassengerModal" class="p-2 bg-gray-100 text-gray-500 rounded-full hover:bg-gray-200 transition-colors">
                                <x-heroicon-s-x-mark class="w-6 h-6" />
                            </button>
                        </div>

                        {{-- Body --}}
                        <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4">

                            {{-- Nama --}}
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase ml-1">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="form_name" class="w-full mt-1 px-4 py-3 rounded-xl border-gray-200 focus:ring-blue-500 focus:border-blue-500" placeholder="Nama penumpang">
                                @error('form_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Gender --}}
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase ml-1">Jenis Kelamin <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-2 gap-2 mt-1">
                                    <button type="button" wire:click="$set('form_gender', 'male')" class="py-3 text-sm font-bold rounded-xl border-2 transition-all flex items-center justify-center gap-2 {{ $this->form_gender === 'male' ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-gray-100 text-gray-400' }}">
                                        <x-heroicon-o-user class="w-4 h-4" /> Laki-laki
                                    </button>
                                    <button type="button" wire:click="$set('form_gender', 'female')" class="py-3 text-sm font-bold rounded-xl border-2 transition-all flex items-center justify-center gap-2 {{ $this->form_gender === 'female' ? 'border-pink-600 bg-pink-50 text-pink-600' : 'border-gray-100 text-gray-400' }}">
                                        <x-heroicon-o-user class="w-4 h-4" /> Perempuan
                                    </button>
                                </div>
                                @error('form_gender') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Kategori Usia --}}
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase ml-1">Kategori Usia <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-3 gap-2 mt-1">
                                    @foreach(['dewasa' => 'Dewasa', 'anak-anak' => 'Anak-anak', 'balita' => 'Balita'] as $val => $label)
                                    @php
                                        $activeColors = ['dewasa' => 'border-green-600 bg-green-50 text-green-600', 'anak-anak' => 'border-yellow-600 bg-yellow-50 text-yellow-600', 'balita' => 'border-pink-600 bg-pink-50 text-pink-600'];
                                    @endphp
                                    <button type="button" wire:click="$set('form_passenger_type', '{{ $val }}')" class="py-3 text-xs font-bold rounded-xl border-2 transition-all {{ $this->form_passenger_type === $val ? $activeColors[$val] : 'border-gray-100 text-gray-400' }}">
                                        {{ $label }}
                                    </button>
                                    @endforeach
                                </div>
                                @error('form_passenger_type') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- NIK & Phone --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase ml-1">NIK / KTP</label>
                                    <input type="text" wire:model="form_id_card_number" class="w-full mt-1 px-4 py-3 rounded-xl border-gray-200 focus:ring-blue-500" placeholder="Opsional">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase ml-1">No. HP</label>
                                    <input type="tel" wire:model="form_phone" class="w-full mt-1 px-4 py-3 rounded-xl border-gray-200 focus:ring-blue-500" placeholder="Opsional">
                                </div>
                            </div>

                            {{-- Jemput --}}
                            <div class="p-3 bg-emerald-50/50 rounded-xl border border-emerald-100 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-emerald-900">Perlu dijemput?</span>
                                    <button type="button" wire:click="$toggle('form_need_pickup')" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $this->form_need_pickup ? 'bg-emerald-600' : 'bg-gray-300' }}">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $this->form_need_pickup ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </div>
                                @if($this->form_need_pickup)
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 ml-1">Alamat Jemput</label>
                                    <textarea wire:model="form_pickup_address" rows="2" class="w-full mt-1 px-4 py-2 rounded-xl border-gray-200 focus:ring-emerald-500 text-sm" placeholder="Masukkan alamat jemput..."></textarea>
                                </div>
                                @endif
                            </div>

                            {{-- Antar --}}
                            <div class="p-3 bg-purple-50/50 rounded-xl border border-purple-100 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-purple-900">Perlu diantar?</span>
                                    <button type="button" wire:click="$toggle('form_need_dropoff')" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $this->form_need_dropoff ? 'bg-purple-600' : 'bg-gray-300' }}">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $this->form_need_dropoff ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </div>
                                @if($this->form_need_dropoff)
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 ml-1">Alamat Antar</label>
                                    <textarea wire:model="form_dropoff_address" rows="2" class="w-full mt-1 px-4 py-2 rounded-xl border-gray-200 focus:ring-purple-500 text-sm" placeholder="Masukkan alamat tujuan..."></textarea>
                                </div>
                                @endif
                            </div>

                        </div>

                        {{-- Footer --}}
                        <div class="p-4 bg-white border-t shrink-0">
                            <button wire:click="savePassenger" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-bold shadow-lg active:scale-[0.98] transition-transform flex items-center justify-center gap-2">
                                <x-heroicon-o-check class="w-5 h-5" />
                                {{ $this->editingPassengerIndex !== null ? 'Simpan Perubahan' : 'Tambah Penumpang' }}
                            </button>
                        </div>
                    </div>
                </div>
            </template>
            @endif

            {{-- ========== MODAL PILIH KURSI (existing) ========== --}}
            <template x-teleport="body">
                <div x-show="openModal" x-cloak class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center overflow-hidden" role="dialog" aria-modal="true">

                    {{-- Backdrop dengan Blur --}}
                    <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="openModal = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-md">
                    </div>

                    {{-- Content Modal --}}
                    <div x-show="openModal" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-y-full sm:translate-y-10 sm:scale-95 sm:opacity-0" x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100" x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100" x-transition:leave-end="translate-y-full sm:translate-y-10 sm:scale-95 sm:opacity-0" class="relative bg-white w-full max-w-lg rounded-t-[2.5rem] sm:rounded-3xl shadow-2xl flex flex-col h-[90vh] sm:h-auto sm:max-h-[90vh] overflow-hidden z-[10000]">

                        {{-- Handle bar untuk Mobile --}}
                        <div class="sm:hidden w-12 h-1.5 bg-gray-300 rounded-full mx-auto mt-4 mb-2 shrink-0"></div>

                        {{-- Header Modal --}}
                        <div class="px-6 py-4 border-b flex justify-between items-center bg-white sticky top-0 shrink-0">
                            <div>
                                <h3 class="font-black text-gray-900 text-lg">Pilih Kursi</h3>
                                <p class="text-[10px] text-orange-600 font-bold uppercase tracking-wider">
                                    Penumpang #{{ ($this->selecting_for_index ?? 0) + 1 }}
                                </p>
                            </div>
                            <button @click="openModal = false" class="p-2 bg-gray-100 text-gray-500 rounded-full hover:bg-gray-200 transition-colors">
                                <x-heroicon-s-x-mark class="w-6 h-6" />
                            </button>
                        </div>

                        {{-- Body Modal (Scrollable) --}}
                        <div class="p-6 overflow-y-auto custom-scrollbar flex-1 bg-gray-50/50">

                            {{-- Info Legend --}}
                            <div class="grid grid-cols-4 gap-2 bg-white p-3 rounded-2xl border border-gray-100 shadow-sm mb-6">
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
                                <div class="grid gap-3" style="grid-template-columns: repeat({{ $totalColumns }}, minmax(0, 1fr));">
                                    @foreach($this->busSeats as $seat)
                                    @php
                                    $isBooked = in_array($seat->seat_number, $this->bookedSeats);
                                    $allSelectedInForm = collect($this->passengers)->pluck('seat_number')->filter()->toArray();
                                    $isSelectedByOthers = in_array($seat->seat_number, $allSelectedInForm);
                                    $isMyCurrentSeat = ($this->passengers[$this->selecting_for_index]['seat_number'] ?? null) === $seat->seat_number;
                                    $isNotPassengerType = $seat->type !== 'passenger';
                                    $isUnavailable = $isBooked || ($isSelectedByOthers && !$isMyCurrentSeat) || $isNotPassengerType;
                                    @endphp

                                    @if($seat->type === 'aisle')
                                    <div class="w-full aspect-square flex items-center justify-center">
                                        <div class="w-1.5 h-1.5 bg-gray-200 rounded-full"></div>
                                    </div>
                                    @else
                                    <button type="button" wire:click="selectSeat('{{ $seat->seat_number }}')" @disabled($isUnavailable) class="relative w-full aspect-square rounded-xl flex items-center justify-center text-xs font-black transition-all duration-200 active:scale-90
                {{ $isBooked ? 'bg-gray-400 text-white cursor-not-allowed' : '' }}
                {{ ($isSelectedByOthers && !$isMyCurrentSeat) ? 'bg-red-500 text-white cursor-not-allowed shadow-lg shadow-red-100' : '' }}
                {{ $isMyCurrentSeat ? 'bg-blue-600 text-white ring-4 ring-blue-100 z-10' : '' }}
                {{ !$isUnavailable && !$isMyCurrentSeat ? 'bg-white text-gray-700 border-2 border-gray-100 hover:border-blue-300 shadow-sm' : '' }}
                {{ $isNotPassengerType && !$isBooked ? 'bg-gray-100 text-gray-400 cursor-not-allowed opacity-50' : '' }}">

                                        @if($seat->type === 'driver')
                                        <x-heroicon-s-user class="w-5 h-5 opacity-30" />
                                        @else
                                        {{ $seat->seat_number }}
                                        @endif

                                        @if($isMyCurrentSeat)
                                        <div class="absolute -top-1 -right-1 w-3 h-3 bg-orange-500 rounded-full border-2 border-white shadow-sm animate-pulse"></div>
                                        @endif
                                    </button>
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Footer Modal --}}
                        <div class="p-4 bg-white border-t shrink-0">
                            <button @click="openModal = false" class="w-full py-4 bg-gray-900 text-white rounded-2xl font-bold shadow-lg active:scale-[0.98] transition-transform">
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
