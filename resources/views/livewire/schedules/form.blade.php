<?php
use function Livewire\Volt\{state, computed, mount};
use App\Models\Route as RouteModel;
use App\Models\Bus;
use App\Models\User;
use App\Models\Schedule;

state([
    'scheduleId'      => null,
    'route_id'        => '',
    'bus_id'          => '',
    'driver_id'       => '',
    'departure_date'  => '',
    'departure_time'  => '',
    'arrival_date'    => '',
    'arrival_time'    => '',
    'price'           => '',
    'available_seats' => '',
    'status'          => 'scheduled',
    'isEdit'          => false,
]);

mount(function (int $scheduleId = null) {
    if ($scheduleId) {
        $schedule = Schedule::findOrFail($scheduleId);
        $this->scheduleId      = $schedule->id;
        $this->route_id        = $schedule->route_id;
        $this->bus_id          = $schedule->bus_id;
        $this->driver_id       = $schedule->driver_id;
        $this->departure_date  = $schedule->departure_date->format('Y-m-d');
        $this->departure_time  = $schedule->departure_time;
        $this->arrival_date    = $schedule->arrival_date?->format('Y-m-d');
        $this->arrival_time    = $schedule->arrival_time;
        $this->price           = $schedule->price;
        $this->available_seats = $schedule->available_seats;
        $this->status          = $schedule->status;
        $this->isEdit          = true;
    }
});

$routes = computed(function () {
    return RouteModel::query()
        ->with('originAgent', 'destinationAgent')
        ->where('is_active', true)
        ->get();
    // Global scope AgentRouteScope otomatis filter by role
});

$buses   = computed(fn() => Bus::where('is_active', true)->get());
$drivers = computed(fn() => User::where('role', 'driver')->get());

$submit = function () {
    $validated = $this->validate([
        'route_id'        => 'required|exists:routes,id',
        'bus_id'          => 'required|exists:buses,id',
        'driver_id'       => 'required|exists:users,id',
        'departure_date'  => 'required|date',
        'departure_time'  => 'required|date_format:H:i',
        'arrival_date'    => 'required|date|after_or_equal:departure_date',
        'arrival_time'    => 'required|date_format:H:i',
        'price'           => 'required|numeric|min:1',
        'available_seats' => 'required|numeric|min:1|max:60',
        'status'          => 'required|in:scheduled,ongoing,completed,cancelled',
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

    {{-- Rute --}}
    <div>
        <label for="route_id" class="block text-sm font-semibold text-gray-700 mb-2">Rute Perjalanan *</label>
        <select id="route_id" wire:model="route_id" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            <option value="">-- Pilih Rute --</option>
            @foreach($this->routes as $route)
            <option value="{{ $route->id }}">
                {{ $route->originAgent->city ?? 'N/A' }} → {{ $route->destinationAgent->city ?? 'N/A' }}
                ({{ number_format($route->distance_km, 0) }} km)
            </option>
            @endforeach
        </select>
        @error('route_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Bus --}}
    <div>
        <label for="bus_id" class="block text-sm font-semibold text-gray-700 mb-2">Bus *</label>
        <select id="bus_id" wire:model="bus_id" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            <option value="">-- Pilih Bus --</option>
            @foreach($this->buses as $bus)
            <option value="{{ $bus->id }}">{{ $bus->name }} ({{ $bus->plate_number }}) - {{ $bus->total_seats }} kursi</option>
            @endforeach
        </select>
        @error('bus_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Driver --}}
    <div>
        <label for="driver_id" class="block text-sm font-semibold text-gray-700 mb-2">Driver *</label>
        <select id="driver_id" wire:model="driver_id" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            <option value="">-- Pilih Driver --</option>
            @foreach($this->drivers as $driver)
            <option value="{{ $driver->id }}">{{ $driver->name }}</option>
            @endforeach
        </select>
        @error('driver_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Tanggal & Waktu Keberangkatan --}}
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label for="departure_date" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Berangkat *</label>
            <input type="date" id="departure_date" wire:model="departure_date" value="{{ old('departure_date') }}" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            @error('departure_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="departure_time" class="block text-sm font-semibold text-gray-700 mb-2">Jam Berangkat *</label>
            <input type="time" id="departure_time" wire:model="departure_time" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            @error('departure_time') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Tanggal & Waktu Tiba --}}
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label for="arrival_date" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Tiba *</label>
            <input type="date" id="arrival_date" wire:model="arrival_date" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            @error('arrival_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="arrival_time" class="block text-sm font-semibold text-gray-700 mb-2">Jam Tiba *</label>
            <input type="time" id="arrival_time" wire:model="arrival_time" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            @error('arrival_time') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
                <span class="flex items-center px-3 bg-gray-100 border border-r-0
                         border-gray-200 rounded-l-xl text-gray-600 font-semibold
                         text-sm shrink-0">
                    Rp
                </span>
                <input type="number" wire:model="price" placeholder="0" class="w-0 flex-1 px-3 py-3 rounded-r-xl border border-gray-200
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
            <input type="number" wire:model="available_seats" placeholder="0" min="1" max="60" class="w-full px-3 py-3 rounded-xl border border-gray-200
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
        <select id="status" wire:model="status" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            <option value="scheduled">Terjadwal</option>
            <option value="ongoing">Berjalan</option>
            <option value="completed">Selesai</option>
            <option value="cancelled">Dibatalkan</option>
        </select>
        @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Buttons --}}
    <div class="flex gap-3 pt-5">
        <a href="{{ route('schedules.index') }}" class="flex-1 px-6 py-3 rounded-xl border border-gray-200 text-gray-700 font-semibold hover:bg-gray-50 transition-colors text-center">
            Batal
        </a>
        <button type="submit" class="flex-1 px-6 py-3 rounded-xl bg-primary-600 text-white
               font-semibold hover:bg-primary-700 transition-colors">
            {{ $isEdit ? 'Perbarui Jadwal' : 'Buat Jadwal' }}
        </button>
    </div>
</form>