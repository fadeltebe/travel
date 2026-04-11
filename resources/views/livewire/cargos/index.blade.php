<?php
use function Livewire\Volt\{state, computed, uses};
use Livewire\WithPagination;
use App\Models\Cargo;

uses(WithPagination::class);

state([
    'search' => '',
    'filterStatus' => '', // '', 'pending', 'in_transit', 'arrived', 'received'
]);

$cargos = computed(function () {
    $user = auth()->user();

    return Cargo::query()
        ->with(['booking', 'originAgent', 'destinationAgent'])
        ->when($this->search, function ($query) {
            $query
                ->where('description', 'like', '%' . $this->search . '%')
                ->orWhere('recipient_name', 'like', '%' . $this->search . '%')
                ->orWhere('tracking_code', 'like', '%' . $this->search . '%')
                ->orWhereHas('booking', function ($q) {
                    $q->where('booking_code', 'like', '%' . $this->search . '%');
                });
        })
        ->when($this->filterStatus, function ($query) {
            if ($this->filterStatus === 'received') {
                $query->where('status', 'received');
            } elseif ($this->filterStatus === 'not_received') {
                $query->where('status', '!=', 'received');
            }
        })
        ->when(!$user->canViewAll(), function ($query) use ($user) {
            if ($user->isDriver()) {
                // Driver: hanya cargo dari jadwal yang dia sopiri
                $query->whereHas('booking.schedule', function ($q) use ($user) {
                    $q->where('driver_id', $user->id);
                });
            } else {
                // Admin Agen: hanya melihat cargo di mana mereka adalah origin atau destination
                $query->where(function ($q) use ($user) {
                    $q->where('origin_agent_id', $user->agent_id)->orWhere('destination_agent_id', $user->agent_id);
                });
            }
        })
        ->latest()
        ->simplePaginate(30);
});
?>

<div>
    <x-layouts.app title="Daftar Cargo">
        {{-- Header --}}
        <div class="relative overflow-hidden text-white mx-4 rounded-2xl px-4 pt-5 pb-10"
            style="background: linear-gradient(160deg, #F57C00 0%, #FF9800 50%, #FFB74D 100%);">
            <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full opacity-10" style="background: white;"></div>

            {{-- Judul & Tombol Tambah --}}
            <div class="flex items-center justify-between relative z-10">
                <h2 class="text-2xl font-bold">Daftar Cargo</h2>

                {{-- Tombol Tambah --}}
                {{-- <a href="{{ route('cargo.create') }}" wire:navigate class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/40 backdrop-blur-sm hover:bg-white/60 transition-colors">
                    <x-heroicon-o-plus class="w-6 h-6 text-white" />
                </a> --}}
            </div>

            {{-- Search Bar --}}
            <div class="mt-4 relative z-10 w-full">
                {{-- <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400" />
                </div> --}}
                <input type="text" wire:model.live.debounce.500ms="search"
                    placeholder="Cari kode resi, penerima, atau barang..."
                    class="w-full pl-10 pr-4 py-3 rounded-xl border-none shadow-sm text-sm text-gray-800 focus:ring-2 focus:ring-orange-300">
            </div>
        </div>

        {{-- Content --}}
        <div class="px-4 -mt-4 space-y-4 pb-24">

            {{-- Status Filter --}}
            <div class="bg-white rounded-2xl p-2 shadow-sm border border-gray-100">
                <div class="grid grid-cols-3 gap-2">
                    @foreach (['' => 'Semua', 'not_received' => 'Pending', 'received' => 'Diterima'] as $val => $label)
                        <button wire:click="$set('filterStatus', '{{ $val }}')"
                            class="px-5 py-2 text-xs mt-3 font-bold rounded-xl transition-all duration-200 {{ $filterStatus === $val ? 'bg-orange-500 text-white shadow-md' : 'bg-gray-50 text-gray-500 hover:bg-gray-100' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Cargo List --}}
            <div class="space-y-3">
                @forelse($this->cargos as $cargo)
                    {{-- PANGGIL KOMPONEN DI SINI --}}
                    <x-card.cargo-card :cargo="$cargo" />
                @empty
                    <div class="bg-white rounded-xl p-8 shadow-sm border border-dashed border-gray-300 text-center">
                        <x-heroicon-o-cube class="w-10 h-10 text-gray-300 mx-auto mb-3" />
                        <p class="text-gray-500 font-bold text-sm">Tidak Ada Data Cargo</p>
                        <p class="text-xs text-gray-400 mt-1">Belum ada transaksi pengiriman barang.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $this->cargos->links() }}
            </div>
        </div>
    </x-layouts.app>
</div>
