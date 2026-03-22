<?php
use function Livewire\Volt\{state, computed};
use App\Models\Cargo;

state([
    'search' => '',
    'filterStatus' => '', // '', 'pending', 'in_transit', 'arrived', 'received'
]);

$cargos = computed(function () {
    $user = auth()->user();

    return Cargo::query()
        ->with(['booking', 'originAgent', 'destinationAgent'])
        ->when($this->search, function ($query) {
            $query->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('recipient_name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('booking', function($q) {
                      $q->where('booking_code', 'like', '%' . $this->search . '%');
                  });
        })
        ->when($this->filterStatus, function ($query) {
            $query->where('status', $this->filterStatus);
        })
        ->when(!$user->canViewAll(), function ($query) use ($user) {
            // Untuk agen biasa, hanya melihat cargo di mana mereka adalah origin atau destination
            $query->where(function($q) use ($user) {
                $q->where('origin_agent_id', $user->agent_id)
                  ->orWhere('destination_agent_id', $user->agent_id);
            });
        })
        ->latest()
        ->get();
});
?>

<div>
    <x-layouts.app title="Daftar Cargo">
        {{-- Header --}}
        <div class="relative overflow-hidden text-white mx-4 rounded-2xl px-4 pt-5 pb-10" style="background: linear-gradient(160deg, #F57C00 0%, #FF9800 50%, #FFB74D 100%);">
            <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full opacity-10" style="background: white;"></div>

            {{-- Judul & Tombol Tambah --}}
            <div class="flex items-center justify-between relative z-10">
                <h2 class="text-2xl font-bold">Daftar Cargo</h2>

                {{-- Tombol Tambah --}}
                <a href="{{ route('cargo.create') }}" wire:navigate class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/40 backdrop-blur-sm hover:bg-white/60 transition-colors">
                    <x-heroicon-o-plus class="w-6 h-6 text-white" />
                </a>
            </div>

            {{-- Search Bar --}}
            <div class="mt-4 relative z-10">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kode resi, penerima, atau barang..." class="w-full pl-10 pr-4 py-3 rounded-xl border-none shadow-inner text-sm text-gray-800 focus:ring-2 focus:ring-orange-300">
                <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" />
            </div>
        </div>

        {{-- Content --}}
        <div class="px-4 -mt-5 space-y-4 pb-24 relative z-20">
            {{-- Status Filter --}}
            <div class="bg-white rounded-2xl p-2 shadow-sm border border-gray-100 overflow-x-auto custom-scrollbar">
                <div class="flex gap-2 min-w-max">
                    @foreach(['' => 'Semua', 'pending' => 'Pending', 'in_transit' => 'Perjalanan', 'arrived' => 'Tiba', 'received' => 'Diterima'] as $val => $label)
                    <button wire:click="$set('filterStatus', '{{ $val }}')" class="px-3 py-2 text-xs font-bold rounded-xl transition-all duration-200 {{ $filterStatus === $val ? 'bg-orange-500 text-white shadow-md' : 'bg-gray-50 text-gray-500 hover:bg-gray-100' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Cargo List --}}
            <div class="space-y-3">
                @forelse($this->cargos as $cargo)
                @php
                    $statusConfig = [
                        'pending'    => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'Pending'],
                        'in_transit' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Perjalanan'],
                        'arrived'    => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Tiba'],
                        'received'   => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => 'Diterima'],
                    ];
                    $st = $statusConfig[$cargo->status] ?? $statusConfig['pending'];
                @endphp
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <span class="text-xs font-black text-gray-900">{{ $cargo->booking->booking_code ?? 'N/A' }}</span>
                            <p class="text-[10px] text-gray-500 mt-0.5">{{ $cargo->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <span class="px-2 py-1 rounded text-[9px] font-bold uppercase {{ $st['bg'] }} {{ $st['text'] }}">
                            {{ $st['label'] }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2 mb-3 bg-gray-50 p-2 rounded-lg">
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] text-gray-500 uppercase font-bold">Asal</p>
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $cargo->originAgent->city ?? '-' }}</p>
                        </div>
                        <x-heroicon-o-arrow-right class="w-4 h-4 text-gray-400 shrink-0" />
                        <div class="flex-1 min-w-0 text-right">
                            <p class="text-[10px] text-gray-500 uppercase font-bold">Tujuan</p>
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $cargo->destinationAgent->city ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="flex justify-between items-end border-t border-gray-100 pt-3">
                        <div class="space-y-1 w-2/3">
                            <p class="text-xs font-bold text-gray-800 truncate">{{ $cargo->description }}</p>
                            <p class="text-[10px] text-gray-500">{{ $cargo->weight_kg }} Kg &bull; {{ $cargo->quantity }} Koli</p>
                            <p class="text-[10px] text-gray-500 flex items-center gap-1 mt-1 truncate">
                                <x-heroicon-o-user class="w-3 h-3 shrink-0" /> {{ $cargo->recipient_name }}
                            </p>
                        </div>
                        <div class="text-right w-1/3">
                            <span class="text-[9px] font-bold uppercase {{ $cargo->is_paid ? 'text-emerald-600' : 'text-red-500' }}">
                                {{ $cargo->is_paid ? 'LUNAS' : 'BELUM BAYAR' }}
                            </span>
                            <p class="text-sm font-black text-orange-500 mt-0.5">Rp{{ number_format($cargo->fee, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-xl p-8 shadow-sm border border-dashed border-gray-300 text-center">
                    <x-heroicon-o-cube class="w-10 h-10 text-gray-300 mx-auto mb-3" />
                    <p class="text-gray-500 font-bold text-sm">Tidak Ada Data Cargo</p>
                    <p class="text-xs text-gray-400 mt-1">Belum ada transaksi pengiriman barang.</p>
                </div>
                @endforelse
            </div>
        </div>
    </x-layouts.app>
</div>
