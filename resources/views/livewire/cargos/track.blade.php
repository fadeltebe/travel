<?php
use function Livewire\Volt\{state};
use App\Models\Cargo;

state([
    'trackingCode' => '',
    'cargo' => null,
    'notFound' => false,
]);

$searchCargo = function () {
    $this->validate([
        'trackingCode' => 'required|string|min:3',
    ]);

    $this->cargo = Cargo::with(['originAgent', 'destinationAgent', 'booking'])
        ->where('tracking_code', $this->trackingCode)
        ->first();

    $this->notFound = !$this->cargo;
};
?>

<div>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Lacak Resi Kargo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <div class="min-h-screen bg-gray-50/50 flex flex-col items-center pt-10 pb-20 px-4 relative overflow-x-hidden">
            {{-- Hiasan Latar --}}
            <div class="absolute top-0 inset-x-0 h-64 bg-gradient-to-b from-orange-500/10 to-transparent pointer-events-none"></div>

            <div class="w-full max-w-xl z-10 relative">
                
                {{-- Header --}}
                <div class="text-center mb-10">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-orange-400 to-red-500 text-white shadow-xl shadow-orange-500/30 mb-5">
                        <x-heroicon-o-truck class="w-8 h-8" />
                    </div>
                    <h1 class="text-3xl font-black text-gray-900 mb-2 tracking-tight">Lacak Kargo</h1>
                    <p class="text-gray-500 font-medium">Masukkan nomor resi (tracking code) untuk melihat status pengiriman barang Anda.</p>
                </div>

                {{-- Kotak Pencarian --}}
                <div class="mb-8">
                    <form wire:submit="searchCargo" class="flex flex-col gap-3">
                        <div class="bg-white p-2 rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 flex items-center transition-all focus-within:ring-4 focus-within:ring-orange-500/20">
                            <div class="pl-4 pr-2 text-gray-400">
                                <x-heroicon-o-magnifying-glass class="w-6 h-6" />
                            </div>
                            <input type="text" wire:model="trackingCode" placeholder="Misal: CRG-XYZ12" 
                                class="flex-1 w-full bg-transparent border-none focus:ring-0 text-gray-800 font-bold tracking-wider placeholder:font-normal placeholder:text-gray-400 py-4 uppercase"
                                autocomplete="off">
                        </div>
                        <button type="submit" class="w-full bg-gray-900 text-white font-bold py-4 px-6 rounded-2xl hover:bg-orange-500 transition-colors shadow-lg transform active:scale-[0.98]">
                            Lacak Sekarang
                        </button>
                    </form>
                </div>

                {{-- Loading State --}}
                <div wire:loading wire:target="searchCargo" class="w-full py-12 text-center text-gray-500">
                    <div class="animate-spin inline-block w-8 h-8 border-[3px] border-current border-t-transparent text-orange-500 rounded-full mb-3" role="status" aria-label="loading">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="font-bold animate-pulse">Mencari data resi...</p>
                </div>

                {{-- Hasil Pencarian (Not Found) --}}
                @if($notFound)
                <div wire:loading.remove wire:target="searchCargo" class="bg-white rounded-3xl p-8 text-center shadow-lg border border-red-100">
                    <div class="inline-flex w-16 h-16 bg-red-50 text-red-500 rounded-full items-center justify-center mb-4">
                        <x-heroicon-o-x-mark class="w-8 h-8" />
                    </div>
                    <h3 class="text-lg font-black text-gray-900 mb-1">Resi Tidak Ditemukan</h3>
                    <p class="text-gray-500 text-sm">Pastikan nomor resi (tracking code) yang Anda masukkan sudah benar.</p>
                </div>
                @endif

                {{-- Hasil Pencarian (Found) --}}
                @if($cargo)
                <div wire:loading.remove wire:target="searchCargo" class="space-y-6 animate-fade-in-up">
                    
                    {{-- Info Singkat Kargo --}}
                    <div class="bg-white rounded-3xl p-6 shadow-xl shadow-gray-200/50 border border-gray-100 relative overflow-hidden">
                        {{-- Watermark --}}
                        <div class="absolute -right-6 -bottom-6 text-gray-50 opacity-50 z-0">
                            <x-heroicon-s-cube class="w-40 h-40" />
                        </div>

                        <div class="relative z-10 flex flex-col gap-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mb-1">Nomor Resi</p>
                                    <h2 class="text-xl font-black text-gray-900">{{ $cargo->tracking_code }}</h2>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mb-1">Waktu Dibuat</p>
                                    <p class="text-sm font-bold text-gray-900">{{ $cargo->created_at->format('d M Y, H:i') }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-2xl">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wide mb-1">Pengirim / Asal</p>
                                    <p class="font-bold text-sm text-gray-900">{{ $cargo->booking->sender_name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">{{ $cargo->originAgent->city ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wide mb-1">Penerima / Tujuan</p>
                                    <p class="font-bold text-sm text-gray-900">{{ $cargo->recipient_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $cargo->destinationAgent->city ?? '-' }}</p>
                                </div>
                            </div>
                            
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wide mb-1">Isi Barang</p>
                                <p class="font-bold text-sm text-gray-900">{{ $cargo->description }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $cargo->weight_kg }} Kg &bull; {{ $cargo->quantity }} Koli</p>
                            </div>
                        </div>
                    </div>

                    {{-- Timeline Status --}}
                    @php
                        $statuses = [
                            'pending'    => ['label' => 'Paket Diterima Agen', 'desc' => 'Paket telah diserahkan pengirim ke loket agen asal.', 'icon' => 'heroicon-o-cube', 'color' => 'orange'],
                            'in_transit' => ['label' => 'Dalam Perjalanan', 'desc' => 'Paket sedang di jalan menuju kota agen tujuan.', 'icon' => 'heroicon-o-truck', 'color' => 'blue'],
                            'arrived'    => ['label' => 'Tiba di Tujuan', 'desc' => 'Paket telah sampai di agen tujuan dan menanti diambil.', 'icon' => 'heroicon-o-map-pin', 'color' => 'indigo'],
                            'received'   => ['label' => 'Sudah Diambil', 'desc' => 'Paket telah diambil dengan selamat oleh penerima.', 'icon' => 'heroicon-o-check-badge', 'color' => 'emerald'],
                        ];
                        
                        $statusKeys = array_keys($statuses);
                        $currentIndex = array_search($cargo->status, $statusKeys);
                        if ($currentIndex === false) $currentIndex = 0;
                    @endphp

                    <div class="bg-white rounded-3xl p-6 shadow-xl shadow-gray-200/50 border border-gray-100">
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-6">Status Pengiriman</h3>
                        
                        <div class="relative">
                            {{-- Garis vertikal timeline --}}
                            <div class="absolute left-6 top-6 bottom-6 w-0.5 bg-gray-100"></div>

                            <div class="space-y-8 relative">
                                @foreach($statuses as $key => $data)
                                    @php
                                        $isPastOrCurrent = array_search($key, $statusKeys) <= $currentIndex;
                                        $isCurrent = $key === $cargo->status;
                                        $iconColor = $isCurrent ? "bg-{$data['color']}-500 text-white ring-4 ring-{$data['color']}-100" : ($isPastOrCurrent ? "bg-{$data['color']}-500 text-white" : "bg-white text-gray-300 ring-2 ring-gray-100");
                                        $textColor = $isPastOrCurrent ? 'text-gray-900' : 'text-gray-400';
                                    @endphp
                                    
                                    <div class="flex items-start gap-4">
                                        <div class="relative z-10 flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center shadow-sm {{ $iconColor }} transition-all">
                                            <x-dynamic-component :component="$data['icon']" class="w-6 h-6" />
                                        </div>
                                        <div class="pt-2">
                                            <h4 class="font-bold {{ $textColor }}">{{ $data['label'] }}</h4>
                                            <p class="text-xs text-gray-500 mt-1 {{ !$isPastOrCurrent ? 'opacity-50' : '' }}">{{ $data['desc'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
            </div>
        </div>

        <style>
            .animate-fade-in-up {
                animation: fadeInUp 0.4s ease-out forwards;
            }
            @keyframes fadeInUp {
                from { opacity: 0; transform: translate3d(0, 20px, 0); }
                to { opacity: 1; transform: translate3d(0, 0, 0); }
            }
        </style>
    </body>
</html>
</div>
