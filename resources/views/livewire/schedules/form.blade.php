<?php
use function Livewire\Volt\{state, computed, mount, updated};
use App\Models\Route as RouteModel;
use App\Models\Bus;
use App\Models\User;
use App\Models\Schedule;

state([
    'scheduleId' => null,
    'route_id' => '',
    'bus_id' => '',
    'driver_id' => '',
    'departure_date' => '',
    'departure_time' => '',
    'arrival_date' => '',
    'arrival_time' => '',
    'price' => '',
    'available_seats' => '',
    'status' => 'scheduled',
    'isEdit' => false,
    'saving' => false,
]);

mount(function (int $scheduleId = null) {
    if ($scheduleId) {
        $schedule = Schedule::findOrFail($scheduleId);
        $this->scheduleId = $schedule->id;
        $this->route_id = $schedule->route_id;
        $this->bus_id = $schedule->bus_id;
        $this->driver_id = $schedule->driver_id;
        $this->departure_date = $schedule->departure_date->format('Y-m-d');
        $this->departure_time = $schedule->departure_time ? \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') : '';
        $this->arrival_date = $schedule->arrival_date ? $schedule->arrival_date->format('Y-m-d') : '';
        $this->arrival_time = $schedule->arrival_time ? \Carbon\Carbon::parse($schedule->arrival_time)->format('H:i') : '';
        $this->price = $schedule->price;
        $this->available_seats = $schedule->available_seats;
        $this->status = $schedule->status;
        $this->isEdit = true;
    } else {
        $this->departure_date = now()->format('Y-m-d');
        $this->arrival_date = now()->format('Y-m-d');
        $this->departure_time = '08:00';
        $this->arrival_time = '17:00';
    }
});

// ── BUG FIX #2: Auto-fill harga & kursi saat dropdown berubah ──
updated([
    'route_id' => function ($value) {
        if ($value) {
            $route = RouteModel::find($value);
            if ($route) {
                $this->price = $route->base_price;
            }
        } else {
            $this->price = '';
        }
    },
    'bus_id' => function ($value) {
        if ($value) {
            $bus = Bus::find($value);
            if ($bus) {
                $this->available_seats = $bus->total_seats;
            }
        } else {
            $this->available_seats = '';
        }
    },
]);

// ── BUG FIX #1: Rute hanya untuk agen yang terikat, dengan authorization check lebih ketat ──
$routes = computed(function () {
    $user = auth()->user();
    
    // Jika user bukan agent-bound (Super Admin, Owner), tampilkan semua rute aktif
    if (!$user || !$user->agent_id || !$user->role->isAgentBound()) {
        return RouteModel::query()
            ->with('originAgent', 'destinationAgent')
            ->where('is_active', true)
            ->get();
    }
    
    // Jika user adalah agent-bound, HANYA tampilkan rute yang berasal dari agen mereka
    return RouteModel::query()
        ->with('originAgent', 'destinationAgent')
        ->where('is_active', true)
        ->where('origin_agent_id', $user->agent_id)
        ->get();
});

$buses = computed(fn() => Bus::where('is_active', true)->get());
$drivers = computed(fn() => User::where('role', 'driver')->get());

$submit = function () {
    // GUARD: Cegah double-submit
    if ($this->saving) {
        return;
    }
    $this->saving = true;

    // ── BUG FIX #1: Tambahkan authorization check saat submit ──
    $user = auth()->user();
    if ($user && $user->agent_id && $user->role->isAgentBound()) {
        // Validasi route yang dipilih harus milik agen user
        $selectedRoute = RouteModel::find($this->route_id);
        if (!$selectedRoute || $selectedRoute->origin_agent_id !== $user->agent_id) {
            session()->flash('error', 'Anda tidak memiliki izin untuk menggunakan rute ini.');
            $this->saving = false;
            return;
        }
    }

    $validated = $this->validate([
        'route_id' => 'required|exists:routes,id',
        'bus_id' => 'required|exists:buses,id',
        'driver_id' => 'required|exists:users,id',
        'departure_date' => 'required|date',
        'departure_time' => 'required|date_format:H:i',
        'arrival_date' => 'required|date|after_or_equal:departure_date',
        'arrival_time' => [
            'required',
            'date_format:H:i',
            function ($attribute, $value, $fail) {
                if ($this->departure_date && $this->departure_time && $this->arrival_date) {
                    $departure = \Carbon\Carbon::parse($this->departure_date . ' ' . $this->departure_time);
                    $arrival = \Carbon\Carbon::parse($this->arrival_date . ' ' . $value);
                    if ($arrival->lessThanOrEqualTo($departure)) {
                        $fail('Waktu kedatangan harus lebih lambat dari waktu keberangkatan.');
                    }
                }
            },
        ],
        'price' => 'required|numeric|min:1', // ── BUG FIX #2: min:1 untuk cegah 0 ──
        'available_seats' => 'required|numeric|min:1|max:60', // ── BUG FIX #2: min:1 untuk cegah 0 ──
        'status' => 'required|in:scheduled,ongoing,completed,cancelled',
    ]);

    if ($this->isEdit && $this->scheduleId) {
        Schedule::findOrFail($this->scheduleId)->update($validated);
        session()->flash('success', 'Jadwal berhasil diperbarui!');
    } else {
        Schedule::create($validated);
        session()->flash('success', 'Jadwal berhasil ditambahkan!');
    }

    $this->redirect(route('schedules.index'), navigate: true);
};
?>

<form wire:submit="submit" class="space-y-5">
    @csrf

    {{-- Pilihan Rute --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Rute *</label>
        <select wire:model.live="route_id"
            class="w-full px-4 py-3 text-sm rounded-xl border border-gray-200
                   focus:outline-none focus:ring-2 focus:ring-primary-500">
            <option value="">-- Pilih Rute --</option>
            @foreach ($this->routes as $route)
                <option value="{{ $route->id }}">
                    {{ $route->originAgent->city }} → {{ $route->destinationAgent->city }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Pilihan Bus --}}
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Armada/Bus *</label>
        <select wire:model.live="bus_id"
            class="w-full px-4 py-3 text-sm rounded-xl border border-gray-200
                   focus:outline-none focus:ring-2 focus:ring-primary-500">
            <option value="">-- Pilih Bus --</option>
            @foreach ($this->buses as $bus)
                <option value="{{ $bus->id }}">
                    {{ $bus->name }} ({{ $bus->plate_number }})
                </option>
            @endforeach
        </select>
    </div>

    {{-- Driver --}}
    <div>
        <label for="driver_id" class="block text-sm font-semibold text-gray-700 mb-2">Driver *</label>
        <select id="driver_id" wire:model="driver_id"
            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            <option value="">-- Pilih Driver --</option>
            @foreach ($this->drivers as $driver)
                <option value="{{ $driver->id }}">{{ $driver->name }}</option>
            @endforeach
        </select>
        @error('driver_id')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Tanggal & Waktu Keberangkatan --}}
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label for="departure_date" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Berangkat
                *</label>
            <input type="date" id="departure_date" wire:model="departure_date" value="{{ old('departure_date') }}"
                class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            @error('departure_date')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="departure_time" class="block text-sm font-semibold text-gray-700 mb-2">Jam Berangkat *</label>
            <input type="time" id="departure_time" wire:model="departure_time"
                class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            @error('departure_time')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Tanggal & Waktu Tiba --}}
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label for="arrival_date" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Tiba *</label>
            <input type="date" id="arrival_date" wire:model="arrival_date"
                class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            @error('arrival_date')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="arrival_time" class="block text-sm font-semibold text-gray-700 mb-2">Jam Tiba *</label>
            <input type="time" id="arrival_time" wire:model="arrival_time"
                class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            @error('arrival_time')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Harga & Kursi --}}
    <div class="grid grid-cols-2 gap-3 items-start">

        {{-- Harga --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Harga per Kursi *
            </label>
            <div class="flex">
                <span
                    class="flex items-center px-3 bg-gray-100 border border-r-0
                         border-gray-200 rounded-l-xl text-gray-600 font-semibold
                         text-sm shrink-0">
                    Rp
                </span>
                <input type="number" wire:model.live.debounce-500ms="price" placeholder="0" value="{{ $price ?: '' }}"
                    class="w-0 flex-1 px-3 py-3 rounded-r-xl border border-gray-200
                          text-sm focus:outline-none focus:ring-2
                          focus:ring-primary-500 focus:border-transparent">
            </div>
            @error('price')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Kursi --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Kursi Tersedia *
            </label>
            <input type="number" wire:model.live.debounce-500ms="available_seats" placeholder="0" min="1" max="60" value="{{ $available_seats ?: '' }}"
                class="w-full px-3 py-3 rounded-xl border border-gray-200
                      text-sm focus:outline-none focus:ring-2
                      focus:ring-primary-500 focus:border-transparent">
            @error('available_seats')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

    </div>

    {{-- Status --}}
    <div>
        <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Status *</label>
        <select id="status" wire:model="status"
            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            <option value="scheduled">Terjadwal</option>
            <option value="ongoing">Berjalan</option>
            <option value="completed">Selesai</option>
            <option value="cancelled">Dibatalkan</option>
        </select>
        @error('status')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Buttons --}}
    <div class="flex gap-3 pt-5">
        <a href="{{ route('schedules.index') }}"
            class="flex-1 px-6 py-3 rounded-xl border border-gray-200 text-gray-700 font-semibold hover:bg-gray-50 transition-colors text-center">
            Batal
        </a>
        <button type="submit"
            wire:loading.attr="disabled"
            wire:loading.class="opacity-50 cursor-not-allowed"
            class="flex-1 px-6 py-3 rounded-xl bg-primary-600 text-white
               font-semibold hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="submit">{{ $isEdit ? 'Perbarui Jadwal' : 'Buat Jadwal' }}</span>
            <span wire:loading.flex wire:target="submit" class="items-center justify-center gap-2">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Menyimpan
            </span>
        </button>
    </div>
</form>