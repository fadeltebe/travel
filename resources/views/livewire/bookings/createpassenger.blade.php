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
    'selecting_for_index' => null,
    'selected_seats'      => [],

    // State untuk modal form penumpang
    'showPassengerModal'    => false,
    'editingPassengerIndex' => null, // null = tambah baru, angka = edit
    'form_name'             => '',
    'form_gender'           => 'male',
    'form_passenger_type'   => 'dewasa',
    'form_id_card_number'   => '',
    'form_phone'            => '',
    'form_pickup_address'   => '',
    'form_dropoff_address'  => '',
    'form_need_pickup'      => false,
    'form_need_dropoff'     => false,
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
    // Ambil data detail kursi berdasarkan nomor kursi
    $seat = $this->busSeats->where('seat_number', $seatNumber)->first();

    // 1. VALIDASI BARU: Cek apakah kursi ada dan tipenya 'passenger'
    // Jika tipenya 'driver', 'toilet', atau 'aisle', maka abaikan (return)
    if (!$seat || $seat->type !== 'passenger') {
        $this->dispatch('notify', message: 'Hanya kursi penumpang yang dapat dipilih', type: 'error');
        return;
    }

    // 2. Cek apakah kursi sudah ada di database (sudah dibooking orang lain)
    if (in_array($seatNumber, $this->bookedSeats)) return;

    // 3. Cek apakah kursi sudah dipilih oleh penumpang lain di form yang sama
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
        ->addSelect(['total_passengers_sum' => \App\Models\Passenger::query()
            ->selectRaw('COUNT(*)')
            ->whereNull('passengers.deleted_at')
            ->whereIn('passengers.booking_id', function ($q) {
                $q->select('id')->from('bookings')
                  ->whereColumn('bookings.schedule_id', 'schedules.id')
                  ->whereNull('bookings.deleted_at');
            })
        ])
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
    // 1. Lewati validasi jika user menekan tombol "Kembali"
    if ($to < $this->step) {
        $this->step = $to;
        return;
    }
    
    // VALIDASI STEP 2: Sebelum lanjut ke Pengisian Data Penumpang (Step 3)
    if ($to === 2) {
        $this->validate([
            'schedule_id' => 'required|exists:schedules,id',
        ], [], ['schedule_id' => 'Jadwal']);
    }

    // VALIDASI STEP 3: Sebelum lanjut ke Detail Penumpang & Kursi
    if ($to === 3) {
        $this->validate([
            'booker_name'  => 'required|string|max:255|min:3',
            'booker_phone' => 'required|string|max:50',
            'agent_id'     => 'required|exists:agents,id',
        ], [], [
            'booker_name'  => 'nama pemesan',
            'booker_phone' => 'telepon pemesan',
            'agent_id'     => 'agen',
        ]);

        // LOGIKA SINKRONISASI PEMESAN -> PENUMPANG
        if ($this->booker_is_passenger) {
            // Pastikan array passengers memiliki index 0
            if (!isset($this->passengers[0])) {
                $this->passengers[] = [
                    'name' => '', 'gender' => 'male', 'passenger_type' => 'dewasa',
                    'id_card_number' => '', 'phone' => '', 'seat_number' => null,
                    'pickup_address' => '', 'dropoff_address' => '',
                    'need_pickup' => false, 'need_dropoff' => false, 'is_booker' => true,
                ];
            }
            // Paksa index pertama mengikuti data pemesan
            $this->passengers[0]['name'] = $this->booker_name;
            $this->passengers[0]['phone'] = $this->booker_phone;
            $this->passengers[0]['is_booker'] = true;
        } else {
            // Jika toggle mati dan penumpang pertama adalah "is_booker", bersihkan datanya
            if (isset($this->passengers[0]) && ($this->passengers[0]['is_booker'] ?? false)) {
                $this->passengers[0]['name'] = '';
                $this->passengers[0]['phone'] = '';
                $this->passengers[0]['is_booker'] = false;
            }
            
            // Jika kosong, berikan satu slot kosong (tanpa modal)
            if (count($this->passengers) === 0) {
                $this->passengers[] = [
                    'name' => '', 'gender' => 'male', 'passenger_type' => 'dewasa',
                    'id_card_number' => '', 'phone' => '', 'seat_number' => null,
                    'pickup_address' => '', 'dropoff_address' => '',
                    'need_pickup' => false, 'need_dropoff' => false, 'is_booker' => false,
                ];
            }
        }
    }

    // VALIDASI STEP 4: Sebelum lanjut ke Pembayaran (CEK KELENGKAPAN DATA)
    if ($to === 4) {
        $this->validate([
            'passengers' => 'required|array|min:1',
            'passengers.*.name' => 'required|string|min:3',
            'passengers.*.seat_number' => 'required|string', // WAJIB PILIH KURSI
        ], [
            'passengers.*.name.required' => 'Nama penumpang harus diisi.',
            'passengers.*.name.min' => 'Nama penumpang ke-:index+1 minimal 3 karakter.',
            'passengers.*.seat_number.required' => 'Penumpang ke-:index+1 belum memilih kursi.',
        ]);
    }

    // Jika semua validasi di atas lolos, update step
    $this->step = $to;
    $this->dispatch('scroll-to-top');
};
$resetPassengerForm = function () {
    $this->form_name = '';
    $this->form_gender = 'male';
    $this->form_passenger_type = 'dewasa';
    $this->form_id_card_number = '';
    $this->form_phone = '';
    $this->form_pickup_address = '';
    $this->form_dropoff_address = '';
    $this->form_need_pickup = false;
    $this->form_need_dropoff = false;
    $this->editingPassengerIndex = null;
};

$addPassenger = function () {
    $this->resetPassengerForm();
    $this->showPassengerModal = true;
};

$editPassenger = function ($index) {
    $p = $this->passengers[$index];
    $this->editingPassengerIndex = $index;
    $this->form_name = $p['name'] ?? '';
    $this->form_gender = $p['gender'] ?? 'male';
    $this->form_passenger_type = $p['passenger_type'] ?? 'dewasa';
    $this->form_id_card_number = $p['id_card_number'] ?? '';
    $this->form_phone = $p['phone'] ?? '';
    $this->form_pickup_address = $p['pickup_address'] ?? '';
    $this->form_dropoff_address = $p['dropoff_address'] ?? '';
    $this->form_need_pickup = (bool) ($p['need_pickup'] ?? false);
    $this->form_need_dropoff = (bool) ($p['need_dropoff'] ?? false);
    $this->showPassengerModal = true;
};

$savePassenger = function () {
    $this->validate([
        'form_name'           => 'required|string|min:3|max:255',
        'form_gender'         => 'required|in:male,female',
        'form_passenger_type' => 'required|in:balita,anak-anak,dewasa',
    ], [
        'form_name.required' => 'Nama penumpang harus diisi.',
        'form_name.min'      => 'Nama penumpang minimal 3 karakter.',
    ]);

    $data = [
        'name'            => $this->form_name,
        'gender'          => $this->form_gender,
        'passenger_type'  => $this->form_passenger_type,
        'id_card_number'  => $this->form_id_card_number,
        'phone'           => $this->form_phone,
        'pickup_address'  => $this->form_pickup_address,
        'dropoff_address' => $this->form_dropoff_address,
        'need_pickup'     => $this->form_need_pickup,
        'need_dropoff'    => $this->form_need_dropoff,
        'is_booker'       => false,
        'seat_number'     => null,
    ];

    if ($this->editingPassengerIndex !== null) {
        // Update: pertahankan seat_number dan is_booker yang lama
        $data['seat_number'] = $this->passengers[$this->editingPassengerIndex]['seat_number'] ?? null;
        $data['is_booker'] = $this->passengers[$this->editingPassengerIndex]['is_booker'] ?? false;
        $this->passengers[$this->editingPassengerIndex] = $data;
    } else {
        $this->passengers[] = $data;
    }

    $this->showPassengerModal = false;
    $this->resetPassengerForm();
};

$closePassengerModal = function () {
    $this->showPassengerModal = false;
    $this->resetPassengerForm();
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
                'name'            => $p['name'],
                'gender'          => $p['gender'] ?? 'male',
                'passenger_type'  => $p['passenger_type'] ?? 'dewasa',
                'phone'           => $p['phone'] ?? null,
                'id_card_number'  => $p['id_card_number'] ?? null,
                'is_booker'       => (bool) ($p['is_booker'] ?? false),
                'seat_number'     => $p['seat_number'] ?? null,
                'pickup_address'  => $p['pickup_address'] ?? null,
                'dropoff_address' => $p['dropoff_address'] ?? null,
                'need_pickup'     => (bool) ($p['need_pickup'] ?? false),
                'need_dropoff'    => (bool) ($p['need_dropoff'] ?? false),
            ]);
        }
    });

    session()->flash('success', 'Booking berhasil disimpan.');
    return $this->redirect(route('schedules.index'), navigate: true);
};

$toggleSeat = function ($seatNumber) {
    if (!$seatNumber) return;

    $seat = $this->busSeats->where('seat_number', $seatNumber)->first();

    // VALIDASI: Hanya tipe passenger yang bisa diproses
    if (!$seat || $seat->type !== 'passenger') return;

    // Jika kursi sudah terisi orang lain (di DB), abaikan
    if (in_array($seatNumber, $this->bookedSeats)) return;

    if (in_array($seatNumber, $this->selected_seats)) {
        $this->selected_seats = array_diff($this->selected_seats, [$seatNumber]);
    } else {
        if (count($this->selected_seats) < count($this->passengers)) {
            $this->selected_seats[] = $seatNumber;
        } else {
            $this->dispatch('notify', message: 'Jumlah kursi sudah sesuai', type: 'warning');
        }
    }

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