@props(['cargo'])

@php
    $statusConfig = [
        'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'Pending'],
        'in_transit' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Perjalanan'],
        'arrived' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Tiba'],
        'received' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => 'Diterima'],
    ];
    $st = $statusConfig[$cargo->status] ?? $statusConfig['pending'];
@endphp

<div x-data="{ showPhotoModal: false }" class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all overflow-hidden relative block">
    
    {{-- Status di Kanan Atas --}}
    <div class="absolute top-0 right-0 flex flex-col items-end z-10 pointer-events-none">
        <span class="text-[9px] font-bold px-3 py-1 rounded-bl-lg text-white shadow-sm {{ $cargo->status === 'received' ? 'bg-emerald-600' : 'bg-blue-600' }}">
            {{ $cargo->status === 'received' ? 'SUDAH DIAMBIL' : 'BELUM DIAMBIL' }}
        </span>
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-bl-md text-[8px] font-bold text-white shadow-sm {{ $cargo->is_paid ? 'bg-emerald-500' : 'bg-red-500' }}">
            {{ $cargo->is_paid ? 'LUNAS' : 'BELUM LUNAS' }}
        </span>
    </div>

    {{-- Main Content Link --}}
    <div class="flex items-start gap-3 p-3 pb-2 pr-24">
        {{-- Photo / Icon on the left --}}
        <div class="w-14 h-14 shrink-0 rounded-lg overflow-hidden border border-gray-200 bg-gray-50 flex items-center justify-center relative group {{ $cargo->photo ? 'cursor-pointer' : '' }}" 
             @if($cargo->photo) @click="showPhotoModal = true" @endif>
            @if($cargo->photo)
                <img src="{{ route('tenant.storage', ['path' => $cargo->photo]) }}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <x-heroicon-o-arrows-pointing-out class="w-5 h-5 text-white" />
                </div>
            @else
                <x-heroicon-o-cube class="w-6 h-6 text-orange-400" />
            @endif
        </div>

        {{-- Right side of photo: 3 lines --}}
        <a href="{{ route('cargo.show', $cargo) }}" class="flex-1 min-w-0 block">
            {{-- Line 1: Item name (description) --}}
            <p class="text-xs font-bold text-gray-900 leading-tight truncate">
                {{ $cargo->item_name ?? 'Barang' }} <span class="text-[10px] font-normal text-gray-500">({{ $cargo->description }})</span>
            </p>

            {{-- Line 2: Route | Date --}}
            <p class="text-[10px] text-gray-500 font-semibold mt-1 flex items-center gap-1 truncate">
                <x-heroicon-o-map-pin class="w-3 h-3 text-orange-500 shrink-0" />
                <span class="truncate">{{ $cargo->originAgent->city ?? '-' }} &rarr; {{ $cargo->destinationAgent->city ?? '-' }}</span>
                <span class="text-gray-300">|</span>
                <x-heroicon-o-calendar class="w-3 h-3 text-orange-500 shrink-0" />
                <span class="truncate">
                    {{ $cargo->booking?->schedule?->departure_date ? $cargo->booking->schedule->departure_date->toIndoDate() : $cargo->created_at->toIndoDate() }}
                </span>
            </p>

            {{-- Line 3: Sender Name - Receiver Name --}}
            <p class="text-[10px] text-gray-600 font-medium mt-0.5 flex items-center gap-1 truncate">
                <x-heroicon-o-user class="w-3 h-3 text-gray-400 shrink-0" />
                <span class="truncate">{{ $cargo->booking->booker_name ?? 'Pengirim' }} - {{ $cargo->recipient_name }}</span>
            </p>
        </a>
    </div>

    {{-- Bottom/footer: Status - Code - Shipping Cost --}}
    <a href="{{ route('cargo.show', $cargo) }}" class="px-3 pb-2 pt-2 flex items-center justify-between gap-2 border-t border-gray-50 mt-1 bg-gray-50/50 block">
        <div class="flex items-center gap-1 text-[10px] font-bold px-2 py-1 rounded {{ $st['bg'] }} {{ $st['text'] }}">
            {{ $st['label'] }}
        </div>
        <div class="text-[11px] font-bold text-gray-700 truncate text-center flex-1">
            {{ $cargo->tracking_code ?? 'N/A' }}
        </div>
        <span class="font-black text-orange-500 shrink-0">
            Rp{{ number_format($cargo->fee, 0, ',', '.') }}
        </span>
    </a>

    {{-- Modal Preview Foto --}}
    @if($cargo->photo)
    <div x-show="showPhotoModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        
        <div @click="showPhotoModal = false" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl p-2 max-w-sm w-full z-10 text-center">
            <button @click="showPhotoModal = false" class="absolute -top-12 right-0 w-10 h-10 flex items-center justify-center bg-white/20 hover:bg-white/40 rounded-full text-white backdrop-blur-md transition-colors">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>
            <img src="{{ route('tenant.storage', ['path' => $cargo->photo]) }}" class="w-full h-auto rounded-2xl object-contain max-h-[70vh]">
        </div>
    </div>
    @endif
</div>
