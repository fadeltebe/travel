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
    'step' => 1,
    'schedule_id' => '',
    'agent_id' => fn() => auth()->user()->agent_id ?? '',
    'user_id' => fn() => auth()->id(),
    'customer_id' => '',
    'booker_name' => '',
    'booker_phone' => '',
    'booker_email' => '',
    'booker_is_passenger' => true,
    'passengers' => [],
    'total_cargo' => 0,
    'cargo_fee' => 0,
    'cargo_cod_fee' => 0,
    'pickup_dropoff_fee' => 0,
    'payment_status' => 'pending',
    'payment_method' => 'cash',
    'status' => 'confirmed',
    'notes' => '',
    'can_manage_all_agents' => fn() => auth()->user()->canViewAll(),
    'selecting_for_index' => null,
    'selected_seats' => [],

    // State untuk modal form penumpang
    'showPassengerModal' => false,
    'editingPassengerIndex' => null, // null = tambah baru, angka = edit
    'form_name' => '',
    'form_gender' => 'male',
    'form_passenger_type' => 'dewasa',
    'form_ticket_price' => '',
    'form_id_card_number' => '',
    'form_phone' => '',
    'form_pickup_address' => '',
    'form_dropoff_address' => '',
    'form_need_pickup' => false,
    'form_need_dropoff' => false,
]);

// --- Lifecycle Hooks (Watchers) ---

// Sinkronisasi Nama Pemesan ke Penumpang indeks 0 jika toggle aktif
updated([
    'booker_name' => function ($value) {
        if ($this->booker_is_passenger && isset($this->passengers[0])) {
            $this->passengers[0]['name'] = $value;
        }
    },
]);

// Sinkronisasi HP Pemesan ke Penumpang indeks 0 jika toggle aktif
updated([
    'booker_phone' => function ($value) {
        if ($this->booker_is_passenger && isset($this->passengers[0])) {
            $this->passengers[0]['phone'] = $value;
        }
    },
]);

// Ambil layout kursi berdasarkan jadwal yang dipilih
$busSeats = computed(function () {
    $schedule = $this->selectedSchedule;
    if (!$schedule || !$schedule->bus || !$schedule->bus->busLayout) {
        return collect();
    }

    return $schedule->bus->busLayout->seats()->orderBy('row')->orderBy('column')->get();
});

// Ambil daftar kursi yang SUDAH terpesan (di database)
$bookedSeats = computed(function () {
    if (!$this->schedule_id) {
        return [];
    }

    return Passenger::whereHas('booking', function ($q) {
        $q->where('schedule_id', $this->schedule_id)->where('status', '!=', 'cancelled');
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
    if (in_array($seatNumber, $this->bookedSeats)) {
        return;
    }

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
            'route' => function ($q) {
                // Hanya ambil data kota, jangan ambil seluruh kolom agen
                $q->select('id', 'origin_agent_id', 'destination_agent_id');
            },
            'route.originAgent:id,city', // Eager loading spesifik kolom
            'route.destinationAgent:id,city',
        ])
        ->addSelect([
            'total_passengers_sum' => \App\Models\Passenger::query()
                ->selectRaw('COUNT(*)')
                ->whereNull('passengers.deleted_at')
                ->whereIn('passengers.booking_id', function ($q) {
                    $q->select('id')->from('bookings')->whereColumn('bookings.schedule_id', 'schedules.id')->whereNull('bookings.deleted_at');
                }),
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
    if (!$this->schedule_id) {
        return null;
    }
    return Schedule::select('id', 'price', 'bus_id')->with('bus.busLayout')->find($this->schedule_id);
});

$subtotalPrice = computed(function () {
    $schedule = $this->selectedSchedule;
    $count = count($this->passengers);
    if (!$schedule || $count === 0) {
        return 0;
    }
    return $schedule->price * $count;
});

$totalPrice = computed(function () {
    return (float) $this->subtotalPrice + (float) ($this->cargo_fee ?: 0) + (float) ($this->pickup_dropoff_fee ?: 0);
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
        $this->validate(
            [
                'schedule_id' => 'required|exists:schedules,id',
            ],
            [],
            ['schedule_id' => 'Jadwal'],
        );
    }

    // VALIDASI STEP 3: Sebelum lanjut ke Detail Penumpang & Kursi
    if ($to === 3) {
        $this->validate(
            [
                'booker_name' => 'required|string|max:255|min:3',
                'booker_phone' => 'required|string|max:50',
                'agent_id' => 'required|exists:agents,id',
            ],
            [],
            [
                'booker_name' => 'nama pemesan',
                'booker_phone' => 'telepon pemesan',
                'agent_id' => 'agen',
            ],
        );

        // LOGIKA SINKRONISASI PEMESAN -> PENUMPANG
        if ($this->booker_is_passenger) {
            // Pastikan array passengers memiliki index 0
            if (!isset($this->passengers[0])) {
                $this->passengers[] = [
                    'name' => '',
                    'gender' => 'male',
                    'passenger_type' => 'dewasa',
                    'id_card_number' => '',
                    'phone' => '',
                    'seat_number' => null,
                    'pickup_address' => '',
                    'dropoff_address' => '',
                    'need_pickup' => false,
                    'need_dropoff' => false,
                    'is_booker' => true,
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
                    'name' => '',
                    'gender' => 'male',
                    'passenger_type' => 'dewasa',
                    'id_card_number' => '',
                    'phone' => '',
                    'seat_number' => null,
                    'pickup_address' => '',
                    'dropoff_address' => '',
                    'need_pickup' => false,
                    'need_dropoff' => false,
                    'is_booker' => false,
                ];
            }
        }
    }

    // VALIDASI STEP 4: Sebelum lanjut ke Pembayaran (CEK KELENGKAPAN DATA)
    if ($to === 4) {
        $this->validate(
            [
                'passengers' => 'required|array|min:1',
                'passengers.*.name' => 'required|string|min:3',
                'passengers.*.seat_number' => 'required|string', // WAJIB PILIH KURSI
            ],
            [
                'passengers.*.name.required' => 'Nama penumpang harus diisi.',
                'passengers.*.name.min' => 'Nama penumpang ke-:index+1 minimal 3 karakter.',
                'passengers.*.seat_number.required' => 'Penumpang ke-:index+1 belum memilih kursi.',
            ],
        );
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
    $this->validate(
        [
            'form_name' => 'required|string|min:3|max:255',
            'form_gender' => 'required|in:male,female',
            'form_passenger_type' => 'required|in:balita,anak-anak,dewasa',
        ],
        [
            'form_name.required' => 'Nama penumpang harus diisi.',
            'form_name.min' => 'Nama penumpang minimal 3 karakter.',
        ],
    );

    $data = [
        'name' => $this->form_name,
        'gender' => $this->form_gender,
        'passenger_type' => $this->form_passenger_type,
        'id_card_number' => $this->form_id_card_number,
        'ticket_price' => (float) $this->form_ticket_price ?: 0,
        'phone' => $this->form_phone,
        'pickup_address' => $this->form_pickup_address,
        'dropoff_address' => $this->form_dropoff_address,
        'need_pickup' => $this->form_need_pickup,
        'need_dropoff' => $this->form_need_dropoff,
        'is_booker' => false,
        'seat_number' => null,
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
        'status' => 'required|in:confirmed,cancelled,completed',
        'agent_id' => 'required|exists:agents,id',
        'schedule_id' => 'required|exists:schedules,id',
        'passengers.*.name' => 'required|string|max:255',
    ]);

    DB::transaction(function () {
        $bookingCode = 'BK-' . strtoupper(\Illuminate\Support\Str::random(8));

        $booking = Booking::create([
            'booking_code' => $bookingCode,
            'schedule_id' => $this->schedule_id,
            'agent_id' => $this->agent_id,
            'user_id' => auth()->id(),
            'customer_id' => $this->customer_id ?: null,
            'booker_name' => $this->booker_name,
            'booker_phone' => $this->booker_phone,
            'booker_email' => $this->booker_email ?: null,
            'total_passengers' => count($this->passengers),
            'total_cargo' => (int) $this->total_cargo,
            'subtotal_price' => $this->subtotalPrice,
            'cargo_fee' => $this->cargo_fee ?: 0,
            'cargo_cod_fee' => $this->cargo_cod_fee ?: 0,
            'pickup_dropoff_fee' => $this->pickup_dropoff_fee ?: 0,
            'total_price' => $this->totalPrice,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'notes' => $this->notes,
        ]);

        // Ambil data harga dasar dari Jadwal untuk diisi ke tabel penumpang
        $schedule = \App\Models\Schedule::find($this->schedule_id);
        $basePrice = $schedule ? $schedule->price : 0;

        foreach ($this->passengers as $p) {
            $passengerType = $p['passenger_type'] ?? 'dewasa';

            // Hitung harga tiket berdasarkan usia
            $ticketPrice = $basePrice;
            if ($passengerType === 'anak-anak') {
                $ticketPrice = $basePrice * 0.75; // Contoh: Anak-anak diskon 25% (Sesuaikan aturan Anda)
            } elseif ($passengerType === 'balita') {
                $ticketPrice = 0; // Balita gratis
            }

            $booking->passengers()->create([
                // --- 3 KOLOM BARU YANG WAJIB DIISI ---
                'ticket_code' => 'TKT-' . date('ym') . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'status' => 'booked',
                'ticket_price' => $p['ticket_price'] ?? 0,
                // -------------------------------------

                'name' => $p['name'],
                'gender' => $p['gender'] ?? 'male',
                'passenger_type' => $passengerType,
                'phone' => $p['phone'] ?? null,
                'id_card_number' => $p['id_card_number'] ?? null,
                'is_booker' => (bool) ($p['is_booker'] ?? false),
                'seat_number' => $p['seat_number'] ?? null,
                'pickup_address' => $p['pickup_address'] ?? null,
                'dropoff_address' => $p['dropoff_address'] ?? null,
                'need_pickup' => (bool) ($p['need_pickup'] ?? false),
                'need_dropoff' => (bool) ($p['need_dropoff'] ?? false),
            ]);
        }
    });

    session()->flash('success', 'Booking berhasil disimpan.');
    return $this->redirect(route('schedules.index'), navigate: true);
};

$toggleSeat = function ($seatNumber) {
    if (!$seatNumber) {
        return;
    }

    $seat = $this->busSeats->where('seat_number', $seatNumber)->first();

    // VALIDASI: Hanya tipe passenger yang bisa diproses
    if (!$seat || $seat->type !== 'passenger') {
        return;
    }

    // Jika kursi sudah terisi orang lain (di DB), abaikan
    if (in_array($seatNumber, $this->bookedSeats)) {
        return;
    }

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
            @if ($this->step > 1)
                <button type="button" wire:click="goStep({{ $this->step - 1 }})"
                    class="w-10 h-10 rounded-xl flex items-center justify-center border border-gray-200 bg-white shadow-sm active:scale-90 transition-transform">
                    <x-heroicon-o-arrow-left class="w-5 h-5 text-gray-600" />
                </button>
            @else
                <a href="{{ route('schedules.index') }}" wire:navigate
                    class="w-10 h-10 rounded-xl flex items-center justify-center border border-gray-200 bg-white shadow-sm">
                    <x-heroicon-o-arrow-left class="w-5 h-5 text-gray-600" />
                </a>
            @endif
            <div>
                <h1 class="text-xl font-bold text-gray-900">Buat Booking</h1>
                <p class="text-xs text-gray-500">Langkah {{ $this->step }} dari 4</p>
            </div>
        </div>

        <div class="flex gap-2">
            @foreach ([1, 2, 3, 4] as $s)
                <div class="flex-1 h-1.5 rounded-full {{ $this->step >= $s ? 'bg-blue-600' : 'bg-gray-200' }}"></div>
            @endforeach
        </div>

        {{-- STEP 1: JADWAL --}}
        @if ($this->step === 1)
            @include('livewire.bookings.partials.passenger.step1-jadwal')
        @endif

        {{-- STEP 2: PEMESAN --}}
        @if ($this->step === 2)
            @include('livewire.bookings.partials.passenger.step2-pemesan')
        @endif

        {{-- STEP 3: PENUMPANG --}}
        @if ($this->step === 3)
            @include('livewire.bookings.partials.passenger.step3-penumpang')
        @endif

        {{-- STEP 4: KONFIRMASI & BAYAR --}}
        @if ($this->step === 4)
            @include('livewire.bookings.partials.passenger.step4-pembayaran')
        @endif

    </div>
</div>
