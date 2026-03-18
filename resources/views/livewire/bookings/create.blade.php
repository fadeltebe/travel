<?php
use function Livewire\Volt\{state, computed, updated};
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Schedule;
use App\Models\Agent;
use App\Models\Customer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

state([
    'step'                => 1,
    'schedule_id'         => '',
    'agent_id'            => fn() => (auth()->user()->agent_id ?? ''),
    'user_id'             => fn() => auth()->id(),
    'customer_id'         => '',
    'booker_name'         => '',
    'booker_phone'        => '',
    'booker_email'        => '',
    'booker_is_passenger' => true,
    'passengers'          => [],
    'total_cargo'         => 0,
    'cargo_fee'           => 0,
    'cargo_cod_fee'       => 0,
    'pickup_dropoff_fee'  => 0,
    'payment_status'      => 'pending',
    'payment_method'      => 'cash',
    'status'              => 'confirmed',
    'notes'               => '',
    'can_manage_all_agents' => fn() => auth()->user()->canViewAll(),
    'selecting_for_index' => null, // Menyimpan indeks penumpang yang sedang pilih kursi
    'selected_seats'      => [],   // Cache kursi yang sedang dipilih di sesi ini
]);

// --- Lifecycle Hooks (Watchers) ---

// Sinkronisasi Nama Pemesan ke Penumpang indeks 0 jika toggle aktif
updated(['booker_name' => function ($value) {
    if ($this->booker_is_passenger && isset($this->passengers[0])) {
        $this->passengers[0]['name'] = $value;
    }
}]);

// Sinkronisasi HP Pemesan ke Penumpang indeks 0 jika toggle aktif
updated(['booker_phone' => function ($value) {
    if ($this->booker_is_passenger && isset($this->passengers[0])) {
        $this->passengers[0]['phone'] = $value;
    }
}]);

// Ambil layout kursi berdasarkan jadwal yang dipilih
$busSeats = computed(function () {
    $schedule = $this->selectedSchedule;
    if (!$schedule || !$schedule->bus || !$schedule->bus->busLayout) return collect();

    return $schedule->bus->busLayout->seats()
        ->orderBy('row')
        ->orderBy('column')
        ->get();
});

// Ambil daftar kursi yang SUDAH terpesan (di database)
$bookedSeats = computed(function () {
    if (!$this->schedule_id) return [];

    return Passenger::whereHas('booking', function ($q) {
        $q->where('schedule_id', $this->schedule_id)
          ->where('status', '!=', 'cancelled');
    })
    ->pluck('seat_number')
    ->toArray();
});

// Logika pilih kursi
$selectSeat = function ($seatNumber) {
    // 1. Cek apakah kursi sudah ada di database
    if (in_array($seatNumber, $this->bookedSeats)) return;

    // 2. Cek apakah kursi sudah dipilih oleh penumpang lain di form yang sama
    // (Kecuali jika itu adalah kursi milik penumpang yang sedang aktif ini sendiri)
    $currentSeat = $this->passengers[$this->selecting_for_index]['seat_number'] ?? null;
    $allSelected = collect($this->passengers)->pluck('seat_number')->filter()->toArray();
    
    if (in_array($seatNumber, $allSelected) && $seatNumber !== $currentSeat) {
        $this->dispatch('notify', message: 'Kursi ini sudah dipilih penumpang lain', type: 'warning');
        return;
    }

    // Masukkan ke data penumpang yang sedang aktif
    $this->passengers[$this->selecting_for_index]['seat_number'] = $seatNumber;

    // Update cache visual
    $this->selected_seats = collect($this->passengers)->pluck('seat_number')->filter()->toArray();

    $this->dispatch('close-seat-modal');
};

// Action untuk membuka modal pilih kursi
$openSeatModal = function ($index) {
    $this->selecting_for_index = $index;
    $this->dispatch('open-seat-modal');
};

// --- Computed Properties ---

$schedules = computed(function () {
    $user = auth()->user();
    
    return Schedule::query()
        // 1. Ambil kolom yang diperlukan saja untuk mengurangi beban memori
        ->select('id', 'route_id', 'price', 'departure_date', 'departure_time', 'available_seats')
        ->with([
            'route' => function($q) {
                // Hanya ambil data kota, jangan ambil seluruh kolom agen
                $q->select('id', 'origin_agent_id', 'destination_agent_id');
            },
            'route.originAgent:id,city', // Eager loading spesifik kolom
            'route.destinationAgent:id,city'
        ])
        ->withSum('bookings as total_passengers_sum', 'total_passengers')
        ->where('departure_date', '>=', now()->toDateString())
        ->whereIn('status', ['active', 'scheduled'])
        ->when(!$user->canViewAll(), function ($query) use ($user) {
            $query->whereHas('route', function ($q) use ($user) {
                $q->where('origin_agent_id', $user->agent_id);
            });
        })
        ->orderBy('departure_date', 'asc')
        ->orderBy('departure_time', 'asc')
        ->get();
});

$agents = computed(fn() => Agent::where('is_active', true)->orderBy('name')->get());

// Gunakan find() sederhana saja untuk selectedSchedule agar tidak berat
$selectedSchedule = computed(function () {
    if (!$this->schedule_id) return null;
    return Schedule::select('id', 'price', 'bus_id')
        ->with('bus.busLayout')
        ->find($this->schedule_id);
});

$subtotalPrice = computed(function () {
    $schedule = $this->selectedSchedule;
    $count = count($this->passengers);
    if (!$schedule || $count === 0) return 0;
    return $schedule->price * $count;
});

$totalPrice = computed(function () {
    return (float) $this->subtotalPrice
        + (float) ($this->cargo_fee ?: 0)
        + (float) ($this->pickup_dropoff_fee ?: 0);
});

// --- Actions ---

$goStep = function ($to) {
    if ($to === 2) {
        $this->validate(['schedule_id' => 'required|exists:schedules,id'], [], ['schedule_id' => 'jadwal']);
    }
    
    if ($to === 3) {
        $this->validate([
            'booker_name'  => 'required|string|max:255',
            'booker_phone' => 'required|string|max:50',
            'agent_id'     => 'required|exists:agents,id',
        ], [], [
            'booker_name'  => 'nama pemesan',
            'booker_phone' => 'telepon pemesan',
            'agent_id'     => 'agen',
        ]);

        // Logika Penumpang Otomatis Berdasarkan Toggle
        if ($this->booker_is_passenger) {
            // Pasang data pemesan di index pertama
            $this->passengers[0] = [
                'name'           => $this->booker_name,
                'phone'          => $this->booker_phone,
                'id_card_number' => $this->passengers[0]['id_card_number'] ?? '',
                'is_booker'      => true,
            ];
        } else {
            // Jika toggle mati, hapus data pemesan dari daftar penumpang jika ada
            if (isset($this->passengers[0]) && ($this->passengers[0]['is_booker'] ?? false)) {
                array_shift($this->passengers);
            }
            
            // Jika kosong setelah dihapus, berikan form kosong
            if (count($this->passengers) === 0) {
                $this->addPassenger();
            }
        }
    }
    $this->step = $to;
    $this->dispatch('scroll-to-top');
};

$addPassenger = function () {
    $this->passengers[] = ['name' => '', 'phone' => '', 'id_card_number' => '', 'is_booker' => false];
};

$removePassenger = function ($index) {
    unset($this->passengers[$index]);
    $this->passengers = array_values($this->passengers);
};

$save = function () {
    if (!auth()->user()->canViewAll()) {
        $this->agent_id = auth()->user()->agent_id;
    }

    $this->validate([
        'payment_status' => 'required|in:pending,paid,refunded',
        'status'         => 'required|in:confirmed,cancelled,completed',
        'agent_id'       => 'required|exists:agents,id',
        'schedule_id'    => 'required|exists:schedules,id',
        'passengers.*.name' => 'required|string|max:255',
    ]);

    DB::transaction(function () {
        $bookingCode = 'BK-' . strtoupper(Str::random(8));

        $booking = Booking::create([
            'booking_code'        => $bookingCode,
            'schedule_id'         => $this->schedule_id,
            'agent_id'            => $this->agent_id,
            'user_id'             => auth()->id(),
            'customer_id'         => $this->customer_id ?: null,
            'booker_name'         => $this->booker_name,
            'booker_phone'        => $this->booker_phone,
            'booker_email'        => $this->booker_email ?: null,
            'total_passengers'    => count($this->passengers),
            'total_cargo'         => (int) $this->total_cargo,
            'subtotal_price'      => $this->subtotalPrice,
            'cargo_fee'           => $this->cargo_fee ?: 0,
            'cargo_cod_fee'       => $this->cargo_cod_fee ?: 0,
            'pickup_dropoff_fee'  => $this->pickup_dropoff_fee ?: 0,
            'total_price'         => $this->totalPrice,
            'payment_status'      => $this->payment_status,
            'payment_method'      => $this->payment_method,
            'status'              => $this->status,
            'notes'               => $this->notes,
        ]);

        foreach ($this->passengers as $p) {
            $booking->passengers()->create([
                'name'           => $p['name'],
                'phone'          => $p['phone'] ?? null,
                'id_card_number' => $p['id_card_number'] ?? null,
                'is_booker'      => (bool) ($p['is_booker'] ?? false),
            ]);
        }
    });

    session()->flash('success', 'Booking berhasil disimpan.');
    return $this->redirect(route('schedules.index'), navigate: true);
};

$toggleSeat = function ($seatNumber) {
    if (!$seatNumber) return;

    // Jika kursi sudah terisi orang lain, abaikan
    if (in_array($seatNumber, $this->bookedSeats)) return;

    if (in_array($seatNumber, $this->selected_seats)) {
        // Hapus jika diklik lagi (unselect)
        $this->selected_seats = array_diff($this->selected_seats, [$seatNumber]);
    } else {
        // Tambahkan jika jumlah kursi belum melebihi jumlah penumpang yang didaftarkan
        if (count($this->selected_seats) < count($this->passengers)) {
            $this->selected_seats[] = $seatNumber;
        } else {
            $this->dispatch('notify', message: 'Jumlah kursi sudah sesuai dengan jumlah penumpang', type: 'warning');
        }
    }

    // Sinkronisasi nomor kursi ke array passengers secara otomatis
    foreach ($this->passengers as $index => $passenger) {
        $this->passengers[$index]['seat_number'] = $this->selected_seats[$index] ?? null;
    }
};
?>

<div x-on:scroll-to-top.window="window.scrollTo(0,0)">
    <div class="px-4 pt-6 pb-24 space-y-5 max-w-lg mx-auto">

        {{-- Header & Progress --}}
        <div class="flex items-center gap-3">
            @if($this->step > 1)
            <button type="button" wire:click="goStep({{ $this->step - 1 }})" class="w-10 h-10 rounded-xl flex items-center justify-center border border-gray-200 bg-white shadow-sm active:scale-90 transition-transform">
                <x-heroicon-o-arrow-left class="w-5 h-5 text-gray-600" />
            </button>
            @else
            <a href="{{ route('schedules.index') }}" wire:navigate class="w-10 h-10 rounded-xl flex items-center justify-center border border-gray-200 bg-white shadow-sm">
                <x-heroicon-o-arrow-left class="w-5 h-5 text-gray-600" />
            </a>
            @endif
            <div>
                <h1 class="text-xl font-bold text-gray-900">Buat Booking</h1>
                <p class="text-xs text-gray-500">Langkah {{ $this->step }} dari 4</p>
            </div>
        </div>

        <div class="flex gap-2">
            @foreach([1,2,3,4] as $s)
            <div class="flex-1 h-1.5 rounded-full {{ $this->step >= $s ? 'bg-blue-600' : 'bg-gray-200' }}"></div>
            @endforeach
        </div>

        {{-- STEP 1: JADWAL --}}
        @if($this->step === 1)
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
                <button wire:click="goStep(2)" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-bold shadow-lg active:scale-95 transition-transform">
                    Lanjut ke Data Pemesan
                </button>
            </div>
        </div>
        @endif

        {{-- STEP 2: PEMESAN --}}
        @if($this->step === 2)
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
        @endif

        {{-- STEP 3: PENUMPANG --}}
        @if($this->step === 3)
        <div class="space-y-4" x-data="{ openModal: false }" x-on:open-seat-modal.window="openModal = true" x-on:close-seat-modal.window="openModal = false">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
                <div class="flex justify-between items-center">
                    <h2 class="font-bold text-gray-900">Daftar Penumpang</h2>
                    <button wire:click="addPassenger" class="text-blue-600 text-sm font-bold flex items-center gap-1">
                        <x-heroicon-o-plus-circle class="w-5 h-5" /> Tambah
                    </button>
                </div>

                @foreach($this->passengers as $index => $passenger)
                <div class="p-4 rounded-xl border border-gray-200 space-y-3 relative {{ ($passenger['is_booker'] ?? false) ? 'bg-blue-50/30 border-blue-200' : '' }}">

                    @if(count($this->passengers) > 1 && !($passenger['is_booker'] ?? false))
                    <button wire:click="removePassenger({{ $index }})" class="absolute top-2 right-2 text-red-400 hover:text-red-600">
                        <x-heroicon-o-x-circle class="w-5 h-5" />
                    </button>
                    @endif

                    @if($passenger['is_booker'] ?? false)
                    <div class="flex items-center gap-1.5 text-[10px] bg-blue-600 text-white px-2 py-0.5 rounded-md font-bold uppercase w-fit">
                        <x-heroicon-s-user class="w-3 h-3" /> Data Pemesan
                    </div>
                    @endif

                    {{-- Tombol Pilih Kursi --}}
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Penumpang #{{ $index + 1 }}</span>
                        <button type="button" wire:click="openSeatModal({{ $index }})" class="flex items-center gap-1 px-3 py-1.5 bg-orange-100 text-orange-600 rounded-lg text-xs font-bold active:scale-95 transition-transform">
                            <x-heroicon-s-stop class="w-3 h-3" />
                            {{ $passenger['seat_number'] ?? 'Pilih Kursi' }}
                        </button>
                    </div>

                    <div>
                        <input type="text" wire:model="passengers.{{ $index }}.name" class="w-full px-4 py-2 rounded-lg border-gray-200 text-sm" placeholder="Nama Penumpang">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" wire:model="passengers.{{ $index }}.phone" class="w-full px-4 py-2 rounded-lg border-gray-200 text-xs" placeholder="No. HP" {{ ($passenger['is_booker'] ?? false) ? 'readonly' : '' }}>
                            <input type="text" wire:model="passengers.{{ $index }}.id_card_number" class="w-full px-4 py-2 rounded-lg border-gray-200 text-xs" placeholder="NIK">
                        </div>
                    </div>
                    {{-- Input Hidden untuk simpan nomor kursi --}}
                    <input type="hidden" wire:model="passengers.{{ $index }}.seat_number">
                </div>
                @endforeach
            </div>

            {{-- MODAL LAYOUT KURSI (Fixed & Responsive) --}}
            <div x-show="openModal" x-cloak class="fixed inset-0 z-[99] flex items-end sm:items-center justify-center overflow-hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">

                {{-- Backdrop dengan Blur --}}
                <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="openModal = false" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity">
                </div>

                {{-- Content Modal --}}
                <div x-show="openModal" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-y-full sm:translate-y-10 sm:scale-95 sm:opacity-0" x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100" x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100" x-transition:leave-end="translate-y-full sm:translate-y-10 sm:scale-95 sm:opacity-0" class="relative bg-white w-full max-w-lg rounded-t-[2.5rem] sm:rounded-3xl shadow-2xl flex flex-col max-h-[95vh] sm:max-h-[90vh] overflow-hidden">

                    {{-- Handle bar untuk Mobile (Visual Only) --}}
                    <div class="sm:hidden w-12 h-1.5 bg-gray-300 rounded-full mx-auto mt-4 mb-2"></div>

                    {{-- Header Modal --}}
                    <div class="px-6 py-4 border-b flex justify-between items-center bg-white sticky top-0 z-10">
                        <div>
                            <h3 class="font-black text-gray-900 text-lg">Pilih Kursi</h3>
                            <p class="text-[10px] text-orange-600 font-bold uppercase tracking-wider">Penumpang #{{ $this->selecting_for_index + 1 }}</p>
                        </div>
                        <button @click="openModal = false" class="p-2 bg-gray-100 text-gray-500 rounded-full hover:bg-gray-200 transition-colors">
                            <x-heroicon-s-x-mark class="w-6 h-6" />
                        </button>
                    </div>

                    {{-- Body Modal (Scrollable) --}}
                    <div class="p-6 overflow-y-auto custom-scrollbar flex-1 bg-gray-50/50">

                        {{-- Info Legend --}}
                        <div class="flex justify-between items-center bg-white p-3 rounded-2xl border border-gray-100 shadow-sm mb-6">
                            <div class="flex flex-col items-center gap-1">
                                <div class="w-4 h-4 bg-white border border-gray-200 rounded"></div>
                                <span class="text-[8px] font-bold text-gray-400">Tersedia</span>
                            </div>
                            <div class="flex flex-col items-center gap-1">
                                <div class="w-4 h-4 bg-gray-400 rounded"></div>
                                <span class="text-[8px] font-bold text-gray-400">Terisi</span>
                            </div>
                            <div class="flex flex-col items-center gap-1">
                                <div class="w-4 h-4 bg-red-500 rounded"></div>
                                <span class="text-[8px] font-bold text-gray-400">Dipilih (Lain)</span>
                            </div>
                            <div class="flex flex-col items-center gap-1">
                                <div class="w-4 h-4 bg-blue-600 rounded"></div>
                                <span class="text-[8px] font-bold text-gray-400">Pilihan Anda</span>
                            </div>
                        </div>

                        {{-- Visual Bus Layout --}}
                        <div class="bg-white p-6 rounded-[2rem] border-2 border-gray-100 shadow-inner relative overflow-hidden">
                            {{-- Ornamen Dashboard --}}
                            <!-- <div class="flex justify-between items-end mb-10 pb-4 border-b-2 border-dashed border-gray-100">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-12 h-8 bg-gray-200 rounded-t-lg flex items-center justify-center">
                                        <div class="w-6 h-6 rounded-full border-4 border-gray-400 border-t-gray-600 animate-spin-slow"></div>
                                    </div>
                                    <span class="text-[9px] font-black text-gray-400 uppercase">SOPIR</span>
                                </div>
                                <div class="text-right">
                                    <div class="w-14 h-6 bg-blue-50 text-blue-400 rounded-full flex items-center justify-center text-[8px] font-black tracking-tighter border border-blue-100 mb-2">PINTU MASUK</div>
                                </div>
                            </div> -->

                            {{-- Grid Kursi --}}
                            <div class="grid gap-3" style="grid-template-columns: repeat({{ $this->selectedSchedule?->bus?->busLayout?->total_columns ?? 4 }}, minmax(0, 1fr));">
                                @foreach($this->busSeats as $seat)
                                @php
                                // 1. Kursi sudah dibayar/dipesan orang lain (Database)
                                $isBooked = in_array($seat->seat_number, $this->bookedSeats);

                                // 2. Kursi sedang dipilih oleh penumpang dalam transaksi ini (State)
                                $allSelectedInForm = collect($this->passengers)->pluck('seat_number')->filter()->toArray();
                                $isSelectedByOthers = in_array($seat->seat_number, $allSelectedInForm);

                                // 3. Kursi yang spesifik milik penumpang yang sedang dibuka modalnya
                                $isMyCurrentSeat = ($this->passengers[$this->selecting_for_index]['seat_number'] ?? null) === $seat->seat_number;

                                // Gabungkan status: Tidak tersedia jika sudah di DB atau sudah dipilih penumpang lain
                                $isUnavailable = $isBooked || ($isSelectedByOthers && !$isMyCurrentSeat);
                                @endphp

                                @if($seat->type === 'aisle')
                                <div class="w-full aspect-square flex items-center justify-center">
                                    <div class="w-1 h-1 bg-gray-200 rounded-full"></div>
                                </div>
                                @else
                                <button type="button" wire:click="selectSeat('{{ $seat->seat_number }}')" @disabled($isUnavailable) class="relative w-full aspect-square rounded-2xl flex items-center justify-center text-xs font-black transition-all duration-200 active:scale-90
            {{ $isBooked ? 'bg-gray-400 text-white cursor-not-allowed' : '' }}
            {{ ($isSelectedByOthers && !$isMyCurrentSeat) ? 'bg-red-500 text-white cursor-not-allowed shadow-lg shadow-red-200' : '' }}
            {{ $isMyCurrentSeat ? 'bg-blue-600 text-white ring-4 ring-blue-100 shadow-xl z-10' : '' }}
            {{ !$isUnavailable && !$isMyCurrentSeat ? 'bg-white text-gray-700 border-2 border-gray-100 hover:border-blue-300 shadow-sm' : '' }}">

                                    {{ $seat->seat_number }}

                                    @if($isMyCurrentSeat)
                                    <div class="absolute -top-1 -right-1 w-3 h-3 bg-orange-500 rounded-full border-2 border-white"></div>
                                    @endif
                                </button>
                                @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button wire:click="goStep(4)" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-bold shadow-lg active:scale-95 transition-transform">
                Lanjut ke Pembayaran
            </button>
        </div>
        @endif

        {{-- STEP 4: KONFIRMASI & BAYAR --}}
        @if($this->step === 4)
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
            @endif
        </div>
    </div>