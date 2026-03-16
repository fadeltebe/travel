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

// --- Computed Properties ---

$schedules = computed(function () {
    $user = auth()->user();
    return Schedule::query()
        ->with('route.originAgent', 'route.destinationAgent')
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

$selectedSchedule = computed(function () {
    if (!$this->schedule_id) return null;
    return Schedule::with('route.originAgent', 'route.destinationAgent')->find($this->schedule_id);
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
                <div class="mt-1 px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-between">
                    <span class="text-sm font-bold text-gray-700">{{ auth()->user()->agent->name ?? 'Internal Pusat' }}</span>
                    <x-heroicon-m-lock-closed class="w-4 h-4 text-gray-400" />
                </div>
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
        <div class="space-y-4">
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
                    <button wire:click="removePassenger({{ $index }})" class="absolute top-3 right-3 text-red-400 hover:text-red-600">
                        <x-heroicon-o-x-circle class="w-5 h-5" />
                    </button>
                    @endif

                    @if($passenger['is_booker'] ?? false)
                    <div class="flex items-center gap-1.5 text-[10px] bg-blue-600 text-white px-2 py-0.5 rounded-md font-bold uppercase w-fit">
                        <x-heroicon-s-user class="w-3 h-3" /> Data Pemesan
                    </div>
                    @endif

                    <div>
                        <input type="text" wire:model="passengers.{{ $index }}.name" class="w-full px-4 py-2 rounded-lg border-gray-200 text-sm" placeholder="Nama Penumpang" {{ ($passenger['is_booker'] ?? false) ? 'readonly' : '' }}>
                        @error("passengers.{$index}.name") <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" wire:model="passengers.{{ $index }}.phone" class="w-full px-4 py-2 rounded-lg border-gray-200 text-xs" placeholder="No. HP" {{ ($passenger['is_booker'] ?? false) ? 'readonly' : '' }}>
                        <input type="text" wire:model="passengers.{{ $index }}.id_card_number" class="w-full px-4 py-2 rounded-lg border-gray-200 text-xs" placeholder="NIK">
                    </div>
                </div>
                @endforeach
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