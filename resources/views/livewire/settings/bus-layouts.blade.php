<?php

use function Livewire\Volt\{state, computed, mount};
use App\Models\BusLayout;
use App\Models\BusLayoutSeat;

state([
    'showFormModal' => false,
    'showDeleteModal' => false,
    'isEdit' => false,
    'editingLayoutId' => null,
    'deletingLayoutId' => null,

    // Layout form
    'name' => '',
    'type' => '',
    'total_rows' => 4,
    'total_columns' => 4,
    'is_active' => true,

    'search' => '',
]);

mount(function () {
    $user = auth()->user();
    if (!in_array($user->role->value ?? $user->role, ['superadmin', 'owner', 'super_admin'])) {
        abort(403, 'Akses Ditolak');
    }
});

$layouts = computed(function () {
    return BusLayout::with(['seats' => fn($q) => $q->orderBy('row')->orderBy('column')])
        ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
        ->orderBy('created_at', 'desc')
        ->get();
});

$editLayoutSeats = computed(function () {
    if (!$this->editingLayoutId) return [];
    return BusLayoutSeat::where('bus_layout_id', $this->editingLayoutId)
        ->orderBy('row')->orderBy('column')
        ->get(['row', 'column', 'type', 'seat_number', 'label', 'capacity', 'is_available'])
        ->toArray();
});

$openCreate = function () {
    $this->resetFormState();
    $this->showFormModal = true;
};

$openEdit = function ($layoutId) {
    $layout = BusLayout::findOrFail($layoutId);
    $this->editingLayoutId = $layout->id;
    $this->isEdit = true;
    $this->name = $layout->name;
    $this->type = $layout->type ?? '';
    $this->total_rows = $layout->total_rows;
    $this->total_columns = $layout->total_columns;
    $this->is_active = $layout->is_active;
    
    // Refresh computed property for seats explicitly if needed, but it works reactively via editingLayoutId.
    $this->showFormModal = true;
};

$resetFormState = function () {
    $this->isEdit = false;
    $this->editingLayoutId = null;
    $this->name = '';
    $this->type = '';
    $this->total_rows = 4;
    $this->total_columns = 4;
    $this->is_active = true;
};

$saveLayout = function ($newLayoutData) {
    $this->validate([
        'name' => 'required|string|max:255',
        'type' => 'nullable|string|max:100',
    ]);

    $seats = $newLayoutData['seats'] ?? [];
    $totalSeats = collect($seats)->where('type', 'passenger')->count();

    if ($this->isEdit && $this->editingLayoutId) {
        $layout = BusLayout::findOrFail($this->editingLayoutId);
        $layout->update([
            'name' => $this->name,
            'type' => $this->type ?: null,
            'total_rows' => (int) ($newLayoutData['rows']),
            'total_columns' => (int) ($newLayoutData['cols']),
            'total_seats' => $totalSeats,
            'is_active' => $this->is_active,
        ]);

        // Recreate seats (simplest approach for full grid change)
        BusLayoutSeat::where('bus_layout_id', $layout->id)->delete();
        
        foreach ($seats as $s) {
            BusLayoutSeat::create([
                'bus_layout_id' => $layout->id,
                'row' => $s['row'],
                'column' => $s['column'],
                'seat_number' => $s['seat_number'] ?? null,
                'type' => $s['type'],
                'label' => $s['label'] ?? null,
                'capacity' => $s['capacity'] ?? 1,
                'is_available' => $s['is_available'] ?? true,
            ]);
        }
        
        $msg = 'Layout berhasil diperbarui!';
    } else {
        $layout = BusLayout::create([
            'name' => $this->name,
            'type' => $this->type ?: null,
            'total_rows' => (int) ($newLayoutData['rows']),
            'total_columns' => (int) ($newLayoutData['cols']),
            'total_seats' => $totalSeats,
            'is_active' => $this->is_active,
        ]);

        foreach ($seats as $s) {
            BusLayoutSeat::create([
                'bus_layout_id' => $layout->id,
                'row' => $s['row'],
                'column' => $s['column'],
                'seat_number' => $s['seat_number'] ?? null,
                'type' => $s['type'],
                'label' => $s['label'] ?? null,
                'capacity' => $s['capacity'] ?? 1,
                'is_available' => $s['is_available'] ?? true,
            ]);
        }
        
        $msg = 'Layout berhasil ditambahkan!';
    }

    $this->showFormModal = false;
    $this->resetFormState();
    $this->dispatch('notify', message: $msg, type: 'success');
};

$confirmDelete = function ($layoutId) {
    if (\App\Models\Bus::where('bus_layout_id', $layoutId)->exists()) {
        $this->dispatch('notify', message: 'Layout ini sedang dipakai oleh armada bus dan tidak bisa dihapus!', type: 'error');
        return;
    }
    
    $this->deletingLayoutId = $layoutId;
    $this->showDeleteModal = true;
};

$deleteLayout = function () {
    if ($this->deletingLayoutId) {
        BusLayout::findOrFail($this->deletingLayoutId)->delete();
        $this->showDeleteModal = false;
        $this->deletingLayoutId = null;
        $this->dispatch('notify', message: 'Layout berhasil dihapus!', type: 'success');
    }
};
?>

<div>
    <x-layouts.app title="Master Layout Kursi">
        <div class="max-w-4xl mx-auto px-4 py-6 pb-28">

            {{-- Header --}}
            <div class="mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="{{ route('settings.index') }}" wire:navigate
                        class="p-2.5 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors bg-white shadow-sm">
                        <x-heroicon-o-arrow-left class="w-5 h-5 text-gray-600" />
                    </a>
                    <div>
                        <h1 class="text-xl font-black text-gray-900">Master Layout Kursi</h1>
                        <p class="text-xs text-gray-500 mt-0.5">Kelola tipe dan denah kursi</p>
                    </div>
                </div>
                <button wire:click="openCreate"
                    class="w-full sm:w-auto flex items-center justify-center gap-2 px-5 py-2.5 bg-purple-600 text-white text-sm font-bold rounded-xl shadow-lg shadow-purple-200 hover:bg-purple-700 active:scale-95 transition-all">
                    <x-heroicon-s-plus class="w-4 h-4" />
                    <span>Tambah Layout</span>
                </button>
            </div>

            {{-- Search --}}
            <div class="mb-6 w-full sm:w-72">
                <div class="relative">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari layout..."
                        class="w-full pl-12 pr-4 py-3 bg-white border border-gray-200 rounded-2xl text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent shadow-sm">
                </div>
            </div>

            {{-- Layout List --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @forelse ($this->layouts as $layout)
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-lg hover:border-purple-200 transition-all group flex flex-col">
                        <div class="p-5 border-b border-gray-50 flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                <x-heroicon-s-squares-2x2 class="w-6 h-6" />
                            </div>
                            <div class="flex-1 min-w-0 pt-0.5">
                                <h3 class="font-bold text-gray-900 text-lg truncate">{{ $layout->name }}</h3>
                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                    @if ($layout->type)
                                        <span class="text-[10px] font-bold bg-gray-100 text-gray-600 px-2 py-0.5 rounded-lg border border-gray-200">{{ $layout->type }}</span>
                                    @endif
                                    <span class="text-[10px] font-bold bg-purple-50 text-purple-700 px-2 py-0.5 rounded-lg border border-purple-100">
                                        {{ $layout->total_seats }} Kursi Penumpang
                                    </span>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black tracking-wide {{ $layout->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                {{ $layout->is_active ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                        </div>

                        {{-- Layout Mini Preview --}}
                        <div class="bg-gray-50/50 flex-1 p-5 flex items-center justify-center min-h-[140px]">
                            <div class="inline-grid gap-1 bg-white p-3 rounded-xl shadow-sm border border-gray-100"
                                style="grid-template-columns: repeat({{ $layout->total_columns }}, 1fr);">
                                @foreach ($layout->seats as $seat)
                                    <div class="w-5 h-5 rounded flex items-center justify-center text-[8px] font-bold shadow-sm
                                        {{ $seat->type === 'passenger' ? 'bg-emerald-400 text-white shadow-emerald-200' : '' }}
                                        {{ $seat->type === 'driver' ? 'bg-indigo-400 text-white shadow-indigo-200' : '' }}
                                        {{ $seat->type === 'door' ? 'bg-amber-400 text-white shadow-amber-200' : '' }}
                                        {{ $seat->type === 'aisle' ? 'bg-gray-100 text-gray-400 shadow-none' : '' }}
                                        {{ $seat->type === 'empty' ? 'bg-white border border-gray-100 shadow-none' : '' }}">
                                        @if ($seat->type === 'passenger')
                                            {{ $seat->seat_number }}
                                        @elseif($seat->type === 'driver')
                                            S
                                        @elseif($seat->type === 'door')
                                            P
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="px-5 py-4 border-t border-gray-50 flex justify-end gap-2 bg-white">
                                <button wire:click="openEdit({{ $layout->id }})"
                                    class="px-4 py-2 text-xs font-bold text-blue-600 bg-blue-50 rounded-xl hover:bg-blue-100 active:scale-95 transition-all">
                                    Edit Denah
                                </button>
                            <button wire:click="confirmDelete({{ $layout->id }})"
                                class="px-4 py-2 text-xs font-bold text-red-600 bg-red-50 rounded-xl hover:bg-red-100 active:scale-95 transition-all">
                                Hapus
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-20 bg-white rounded-3xl border border-gray-100 border-dashed">
                        <div class="w-20 h-20 mx-auto mb-4 bg-purple-50 rounded-full flex items-center justify-center">
                            <x-heroicon-o-squares-2x2 class="w-10 h-10 text-purple-300" />
                        </div>
                        <h3 class="font-bold text-gray-500 text-lg">Belum ada layout</h3>
                        <p class="text-sm text-gray-400 mt-1">Buat template denah kursi untuk armada Anda.</p>
                        <button wire:click="openCreate" class="mt-4 px-6 py-2.5 bg-purple-50 text-purple-700 font-bold text-sm rounded-xl hover:bg-purple-100 transition-colors">
                            Buat Layout Sekarang
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ═══════════════════════════════════════════ --}}
        {{-- LAYOUT BUILDER MODAL                       --}}
        {{-- ═══════════════════════════════════════════ --}}
        @if ($showFormModal)
            <script id="edit-layout-data" type="application/json">
                {
                    "rows": {{ (int) $this->total_rows ?: 4 }},
                    "cols": {{ (int) $this->total_columns ?: 4 }},
                    "seats": {!! json_encode($this->editLayoutSeats ?? []) !!}
                }
            </script>
            <div x-data="busLayoutBuilder()" x-cloak
                 wire:key="layout-modal-{{ $editingLayoutId ?? 'new' }}"
                 class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center overflow-hidden">

                {{-- Backdrop --}}
                <div x-transition.opacity
                    @click="$wire.set('showFormModal', false)"
                    class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm"></div>

                {{-- Content --}}
                <div x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="translate-y-full sm:translate-y-10 sm:scale-95 sm:opacity-0"
                    x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100"
                    x-transition:leave="transition ease-in duration-200 transform"
                    x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100"
                    x-transition:leave-end="translate-y-full sm:translate-y-10 sm:scale-95 sm:opacity-0"
                    class="relative bg-white w-full max-w-3xl rounded-t-[2.5rem] sm:rounded-3xl shadow-2xl flex flex-col max-h-[95vh] overflow-hidden z-[10000]">

                    <div class="sm:hidden w-12 h-1.5 bg-gray-300 rounded-full mx-auto mt-4 mb-1 shrink-0"></div>

                    {{-- Header --}}
                    <div class="px-6 py-5 border-b flex justify-between items-center bg-white sticky top-0 shrink-0">
                        <div>
                            <h3 class="font-black text-gray-900 text-lg">
                                {{ $isEdit ? 'Edit Layout Kursi' : 'Buat Layout Baru' }}
                            </h3>
                            <p class="text-[10px] text-purple-600 font-bold uppercase tracking-wider">
                                Atur denah interaktif
                            </p>
                        </div>
                        <button @click="$wire.set('showFormModal', false)"
                            class="p-2 bg-gray-100 text-gray-500 rounded-full hover:bg-gray-200 transition-colors">
                            <x-heroicon-s-x-mark class="w-6 h-6" />
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="flex-1 overflow-y-auto p-6 space-y-6">
                        
                        {{-- Info Dasar --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1.5">Nama Layout *</label>
                                <input type="text" wire:model="name" placeholder="cth: ELF 15 Seat"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-200 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                @error('name')<span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1.5">Tipe Layout</label>
                                <input type="text" wire:model="type" placeholder="cth: Medium, Big Bus"
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-200 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                        </div>

                        {{-- Toggle --}}
                        <label class="flex items-center gap-3 cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-checked:bg-emerald-500 rounded-full transition-colors"></div>
                                <div class="absolute left-[2px] top-[2px] w-5 h-5 bg-white rounded-full shadow-md transition-transform peer-checked:translate-x-5"></div>
                            </div>
                            <span class="text-sm font-bold text-gray-700">Status Aktif</span>
                        </label>

                        <hr class="border-gray-100">

                        {{-- Grid Controls --}}
                        <div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                                <div class="flex items-center gap-4 bg-purple-50 px-4 py-3 rounded-xl border border-purple-100">
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs font-bold text-purple-700">Baris:</label>
                                        <button type="button" @click="gridRows = Math.max(1, gridRows - 1); rebuildGrid()"
                                            class="w-8 h-8 rounded-lg bg-white border border-purple-200 text-purple-600 font-bold shadow-sm active:scale-90">−</button>
                                        <span x-text="gridRows" class="w-8 text-center text-sm font-black text-purple-900"></span>
                                        <button type="button" @click="gridRows = Math.min(20, gridRows + 1); rebuildGrid()"
                                            class="w-8 h-8 rounded-lg bg-white border border-purple-200 text-purple-600 font-bold shadow-sm active:scale-90">+</button>
                                    </div>
                                    <div class="w-px h-6 bg-purple-200"></div>
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs font-bold text-purple-700">Kolom:</label>
                                        <button type="button" @click="gridCols = Math.max(1, gridCols - 1); rebuildGrid()"
                                            class="w-8 h-8 rounded-lg bg-white border border-purple-200 text-purple-600 font-bold shadow-sm active:scale-90">−</button>
                                        <span x-text="gridCols" class="w-8 text-center text-sm font-black text-purple-900"></span>
                                        <button type="button" @click="gridCols = Math.min(8, gridCols + 1); rebuildGrid()"
                                            class="w-8 h-8 rounded-lg bg-white border border-purple-200 text-purple-600 font-bold shadow-sm active:scale-90">+</button>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" @click="fillAllPassenger()" class="px-4 py-2 text-xs font-bold text-emerald-700 bg-emerald-50 rounded-xl hover:bg-emerald-100 transition-colors">Isi Semua</button>
                                    <button type="button" @click="clearAll()" class="px-4 py-2 text-xs font-bold text-red-600 bg-red-50 rounded-xl hover:bg-red-100 transition-colors">Reset</button>
                                </div>
                            </div>

                            <p class="text-[11px] text-gray-500 mb-3 italic">
                                Klik kotak untuk mengubah fungsinya. 
                            </p>

                            {{-- Grid Area --}}
                            <div class="bg-gray-50 p-6 rounded-3xl border border-gray-200 shadow-inner overflow-x-auto">
                                <div class="mx-auto inline-grid gap-3"
                                    :style="`grid-template-columns: repeat(${gridCols}, minmax(40px, 60px))`">
                                    <template x-for="cell in flatCells" :key="cell.key">
                                        <button type="button" @click="cycleType(cell.r, cell.c)"
                                            :class="getColor(grid[cell.key] || 'empty')"
                                            class="aspect-square rounded-2xl flex items-center justify-center font-black transition-all duration-200 active:scale-90 cursor-pointer select-none">
                                            <span x-show="grid[cell.key] === 'passenger'" x-text="seatLabels[cell.key] || ''" class="text-sm"></span>
                                            <span x-show="grid[cell.key] === 'driver'" class="text-2xl">ꔮ</span>
                                            <span x-show="grid[cell.key] === 'door'" class="text-2xl">🚪</span>
                                            <span x-show="grid[cell.key] === 'aisle'" class="text-2xl opacity-40">·</span>
                                            <span x-show="!grid[cell.key] || grid[cell.key] === 'empty'" class="text-gray-300 text-xl font-normal">+</span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Footer --}}
                    <div class="p-5 bg-white border-t shrink-0 flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Kapasitas</span>
                            <span class="text-lg font-black text-emerald-600"><span x-text="totalSeats"></span> Penumpang</span>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="$wire.set('showFormModal', false)"
                                class="px-5 py-3 rounded-xl hover:bg-gray-50 text-gray-700 font-bold text-sm transition-colors hidden sm:block">
                                Batal
                            </button>
                            <button type="button" @click="$wire.saveLayout({ rows: gridRows, cols: gridCols, seats: getSeatsData() })"
                                class="px-8 py-3 rounded-xl bg-purple-600 text-white font-bold text-sm shadow-lg shadow-purple-200 hover:bg-purple-700 active:scale-95 transition-all">
                                Simpan Layout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Delete Confirm Modal --}}
        @if ($showDeleteModal)
            <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4" x-cloak>
                <div @click="$wire.set('showDeleteModal', false)" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>
                <div class="relative bg-white rounded-3xl shadow-2xl p-6 max-w-sm w-full z-10 text-center transform transition-all">
                    <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                        <x-heroicon-s-trash class="w-8 h-8 text-red-500" />
                    </div>
                    <h3 class="text-lg font-black text-gray-900 mb-1">Hapus Layout?</h3>
                    <p class="text-sm text-gray-500 mb-6">Data tidak bisa dikembalikan. Pastikan layout tidak sedang dipakai armada lain.</p>
                    <div class="flex gap-3">
                        <button wire:click="$set('showDeleteModal', false)" class="flex-1 py-3 rounded-xl border border-gray-200 text-gray-700 font-bold text-sm hover:bg-gray-50">Batal</button>
                        <button wire:click="deleteLayout" class="flex-1 py-3 rounded-xl bg-red-600 text-white font-bold text-sm shadow-lg shadow-red-200 hover:bg-red-700 active:scale-95">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        @endif
    </x-layouts.app>

    <script>
        function busLayoutBuilder() {
            return {
                gridRows: 4,
                gridCols: 4,
                grid: {},
                seatLabels: {},

                init() {
                    let dataEl = document.getElementById('edit-layout-data');
                    let data = dataEl ? JSON.parse(dataEl.textContent) : { rows: 4, cols: 4, seats: [] };
                    
                    this.gridRows = data.rows || 4;
                    this.gridCols = data.cols || 4;
                    let existingSeats = data.seats || [];
                    
                    if (existingSeats && existingSeats.length > 0) {
                        this.gridRows = Math.max(this.gridRows, ...existingSeats.map(s => s.row));
                        this.gridCols = Math.max(this.gridCols, ...existingSeats.map(s => s.column));
                        this.rebuildGrid();
                        
                        existingSeats.forEach(seat => {
                            let key = (seat.row - 1) + '-' + (seat.column - 1);
                            this.grid[key] = seat.type;
                            if (seat.seat_number) this.seatLabels[key] = seat.seat_number;
                        });
                        this.grid = { ...this.grid };
                    } else {
                        this.rebuildGrid();
                    }
                },

                rebuildGrid() {
                    let newGrid = {};
                    for (let r = 0; r < this.gridRows; r++) {
                        for (let c = 0; c < this.gridCols; c++) {
                            let key = r + '-' + c;
                            newGrid[key] = this.grid[key] || 'empty';
                        }
                    }
                    this.grid = newGrid;
                    this.autoNumber();
                },

                cycleType(r, c) {
                    let key = r + '-' + c;
                    const types = ['empty', 'passenger', 'aisle', 'driver', 'door'];
                    let current = this.grid[key] || 'empty';
                    let nextIdx = (types.indexOf(current) + 1) % types.length;
                    this.grid = { ...this.grid, [key]: types[nextIdx] };
                    this.autoNumber();
                },

                autoNumber() {
                    let labels = {};
                    for (let r = 0; r < this.gridRows; r++) {
                        let letter = String.fromCharCode(65 + r);
                        let num = 1;
                        for (let c = 0; c < this.gridCols; c++) {
                            let key = r + '-' + c;
                            if (this.grid[key] === 'passenger') {
                                labels[key] = letter + num;
                                num++;
                            }
                        }
                    }
                    this.seatLabels = labels;
                },

                get flatCells() {
                    let cells = [];
                    for (let r = 0; r < this.gridRows; r++) {
                        for (let c = 0; c < this.gridCols; c++) {
                            cells.push({ r, c, key: r + '-' + c });
                        }
                    }
                    return cells;
                },

                get totalSeats() {
                    return Object.values(this.grid).filter(t => t === 'passenger').length;
                },

                getSeatsData() {
                    let seats = [];
                    for (let r = 0; r < this.gridRows; r++) {
                        for (let c = 0; c < this.gridCols; c++) {
                            let key = r + '-' + c;
                            let type = this.grid[key];
                            if (!type || type === 'empty') continue;
                            seats.push({
                                row: r + 1,
                                column: c + 1,
                                type: type,
                                seat_number: this.seatLabels[key] || null,
                                label: type === 'driver' ? 'Sopir' : (type === 'door' ? 'Pintu' : null),
                                capacity: 1,
                                is_available: type === 'passenger',
                            });
                        }
                    }
                    return seats;
                },

                getColor(type) {
                    const colors = {
                        'passenger': 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30 ring-2 ring-white',
                        'aisle': 'bg-gray-100 text-gray-500 shadow-inner',
                        'driver': 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/30',
                        'door': 'bg-amber-400 text-white shadow-lg shadow-amber-400/30',
                        'empty': 'bg-white border-2 border-dashed border-gray-200 hover:border-purple-300 hover:bg-purple-50/50',
                    };
                    return colors[type] || colors['empty'];
                },

                fillAllPassenger() {
                    for (let key in this.grid) this.grid[key] = 'passenger';
                    this.autoNumber();
                },

                clearAll() {
                    for (let key in this.grid) this.grid[key] = 'empty';
                    this.seatLabels = {};
                },
            }
        }
    </script>
</div>
