<?php
use function Livewire\Volt\{state, computed, uses};
use Livewire\WithPagination;
use App\Models\Passenger;
use Illuminate\Database\Eloquent\Builder;

uses(WithPagination::class);

state([
    'search' => '',
    'filterStatus' => '', // Tambahan state untuk filter status
]);

$passengers = computed(function () {
    $user = auth()->user();

    return Passenger::query()
        ->with(['booking.schedule.route.originAgent', 'booking.schedule.route.destinationAgent'])
        ->whereHas('booking.schedule.route', function (Builder $query) use ($user) {
            if (!$user->canViewAll()) {
                $query->where('origin_agent_id', $user->agent_id)->orWhere('destination_agent_id', $user->agent_id);
            }
        })
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%')
                    ->orWhereHas('booking', function ($b) {
                        $b->where('booking_code', 'like', '%' . $this->search . '%');
                    });
            });
        })
        // Logika Filter Status ditambahkan di sini
        ->when($this->filterStatus, function ($query) {
            $query->whereHas('booking', function ($q) {
                $q->where('payment_status', $this->filterStatus);
            });
        })
        ->latest()
        ->simplePaginate(30);
});
?>

<div>
    <x-layouts.app title="Daftar Penumpang">
        {{-- Header --}}
        <div class="relative overflow-hidden text-white mx-4 rounded-2xl px-4 pt-5 pb-10"
            style="background: linear-gradient(160deg, #10B981 0%, #059669 50%, #047857 100%);">
            <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full opacity-10" style="background: white;"></div>

            {{-- Judul --}}
            <div class="flex items-center justify-between relative z-10">
                <h2 class="text-2xl font-bold">Daftar Penumpang</h2>
            </div>

            {{-- Search Bar --}}
            <div class="mt-4 relative z-10 w-full">
                <input type="text" wire:model.live.debounce.500ms="search"
                    placeholder="Cari nama, no hp, atau kode booking..."
                    class="w-full pl-10 pr-4 py-3 rounded-xl border-none shadow-sm text-sm text-gray-800 focus:ring-2 focus:ring-emerald-300">
            </div>
        </div>

        {{-- Content --}}
        <div class="px-4 -mt-4 space-y-4 pb-24">

            {{-- Status Filter Pembayaran (Tema Emerald/Hijau) --}}
            <div class="bg-white rounded-2xl p-2 shadow-sm border border-gray-100">
                <div class="grid grid-cols-3 gap-2">
                    {{-- Sesuaikan 'paid' dan 'pending' dengan enum database Anda --}}
                    @foreach (['' => 'Semua', 'paid' => 'Lunas', 'pending' => 'Belum Lunas'] as $val => $label)
                        <button wire:click="$set('filterStatus', '{{ $val }}')"
                            class="px-5 py-2 text-xs mt-3 font-bold rounded-xl transition-all duration-200 {{ $this->filterStatus === $val ? 'bg-emerald-500 text-white shadow-md' : 'bg-gray-50 text-gray-500 hover:bg-gray-100' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Content --}}

            {{-- Passenger List --}}
            <div class="space-y-3">
                @forelse($this->passengers as $passenger)
                    {{-- Panggil komponen di sini --}}
                    <x-card.passenger-card :passenger="$passenger" />
                @empty
                    <div class="text-center py-10 bg-white rounded-2xl shadow-sm border border-gray-100">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <x-heroicon-o-users class="w-8 h-8 text-gray-300" />
                        </div>
                        <p class="text-gray-500 font-semibold text-sm">Tidak ada penumpang ditemukan.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $this->passengers->links() }}
            </div>
        </div>
    </x-layouts.app>
</div>
