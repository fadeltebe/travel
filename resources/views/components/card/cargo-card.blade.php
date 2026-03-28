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

<a href="{{ route('cargo.show', $cargo) }}"
    class="block bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all overflow-hidden relative">

    {{-- Status di Kanan Atas --}}
    <div class="absolute top-0 right-0 flex flex-col items-end">
        <span
            class="text-[9px] font-bold px-3 py-1 rounded-bl-lg text-white shadow-sm {{ $cargo->status === 'received' ? 'bg-emerald-600' : 'bg-blue-600' }}">
            {{ $cargo->status === 'received' ? 'SUDAH DIAMBIL' : 'BELUM DIAMBIL' }}
        </span>
        <span
            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-bl-md text-[8px] font-bold text-white shadow-sm {{ $cargo->is_paid ? 'bg-emerald-500' : 'bg-red-500' }}">
            {{ $cargo->is_paid ? 'LUNAS' : 'BELUM LUNAS' }}
        </span>
    </div>

    {{-- Baris 1: Ikon, Resi & Rute --}}
    <div class="flex items-start gap-3 p-3 pb-2 pr-24">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 bg-orange-50">
            <x-heroicon-o-cube class="w-4 h-4 text-orange-600" />
        </div>
        <div class="min-w-0">
            <p class="text-xs text-gray-900 leading-tight font-bold">
                {{ $cargo->tracking_code ?? 'N/A' }}
            </p>
            <p class="text-[10px] text-gray-500 font-semibold mt-0.5 flex items-center gap-1">
                <x-heroicon-o-map-pin class="w-3 h-3" />

                {{ $cargo->originAgent->city ?? '-' }} →
                {{ $cargo->destinationAgent->city ?? '-' }}

                <span class="text-gray-300">|</span>
                <x-heroicon-o-calendar class="w-3 h-3" />
                {{ $cargo->created_at->toIndoDate() }}

            </p>
        </div>
    </div>

    {{-- Baris 2: Detail Barang (Nama, Deskripsi, Berat) --}}
    <div class="px-3 pb-2 flex items-center justify-between text-[10px]">
        <div class="text-gray-600 truncate mr-2">
            <span class="font-black text-gray-800">
                {{ $cargo->item_name ?? 'Barang' }}
            </span>
            ({{ $cargo->description }} - {{ $cargo->weight_kg }}Kg)
        </div>
    </div>

    {{-- Baris 3: Status, Penerima & Harga --}}
    <div class="px-3 pb-2 pt-2 flex items-center justify-between gap-2 border-t border-gray-50 mt-1 bg-gray-50/50">
        <div
            class="flex items-center gap-1 text-[10px] font-bold px-2 py-1 rounded {{ $st['bg'] }} {{ $st['text'] }}">
            {{ $st['label'] }}
        </div>
        <div class="text-[10px] text-gray-500 truncate">
            <x-heroicon-o-user class="w-3 h-3 inline-block" /> {{ $cargo->recipient_name }}
        </div>
        <span class="font-black text-orange-500 shrink-0">
            Rp{{ number_format($cargo->fee, 0, ',', '.') }}
        </span>
    </div>
</a>
