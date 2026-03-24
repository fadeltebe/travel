<?php
use function Livewire\Volt\{state, mount, layout};
use App\Models\Cargo;

state([
    'trackingCode' => '',
    'cargo'        => null,
    'notFound'     => false,
]);

layout('layouts.blank');

mount(function () {
    $code = request()->query('trackingCode');
    if ($code) {
        $this->trackingCode = $code;
        $this->search();
    }
});

$search = function () {
    $this->trackingCode = trim($this->trackingCode);

    $this->validate([
        'trackingCode' => 'required|string|min:3',
    ]);

    $this->cargo = Cargo::with(['originAgent', 'destinationAgent', 'booking'])
        ->where('tracking_code', 'LIKE', '%' . $this->trackingCode . '%')
        ->first();

    $this->notFound = !$this->cargo;
};
?>

<div> {{-- ← 1 root element --}}
    {{-- konten --}}


    <div class="min-h-screen flex flex-col items-center pt-6 pb-20 px-4 relative">

        {{-- Tombol Kembali --}}
        {{-- <div class="w-full max-w-xl mb-6 no-print">
            <a href="/" class="inline-flex items-center text-[10px] font-black text-gray-400 
                           hover:text-orange-600 transition-colors uppercase tracking-[0.2em]">
                <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" /> Kembali ke App
            </a>
        </div> --}}

        <div class="w-full max-w-xl relative">

            {{-- Header --}}
            <div class="text-center mb-8 no-print">
                {{-- <div class="inline-flex items-center justify-center w-20 h-20 rounded-[2rem] 
                        text-white shadow-2xl mb-6 ring-8 ring-white" style="background: linear-gradient(160deg, #0D47A1 0%, #1565C0 50%, #1976D2 100%);">
                    <x-heroicon-o-truck class="w-10 h-10 text-orange-400" />
                </div> --}}
                <h1 class="text-4xl font-black text-blue-900 mb-2 tracking-tighter">CEK RESI</h1>
                {{-- <p class="text-gray-500 text-xs font-bold uppercase tracking-widest opacity-60">
                    Logistics Real-time Monitor
                </p> --}}
            </div>

            {{-- Input Section --}}
            <div class="bg-white p-2 rounded-[2.5rem] shadow-2xl shadow-gray-200/80 
                    border border-gray-100 mb-8 no-print">
                <form wire:submit="search" class="flex flex-col gap-2">
                    <input type="text" wire:model="trackingCode" placeholder="MASUKKAN NOMOR RESI..." class="w-full px-8 py-6 bg-gray-50 border-none rounded-[2rem] 
                              focus:ring-4 focus:ring-orange-500/10 text-xl font-black 
                              tracking-widest text-gray-800 placeholder:text-gray-300 uppercase">
                    <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-black 
                               py-5 rounded-[2rem] transition-all shadow-lg shadow-orange-500/30 
                               active:scale-[0.97] flex items-center justify-center gap-3">
                        <span class="tracking-widest">LACAK SEKARANG</span>
                        <x-heroicon-o-magnifying-glass class="w-6 h-6" />
                    </button>
                </form>
            </div>

            {{-- Loading --}}
            <div wire:loading wire:target="search" class="py-12 text-center no-print">
                <div class="animate-spin inline-block w-12 h-12 border-[6px] 
                        border-orange-500 border-t-transparent rounded-full mb-4"></div>
                <p class="text-gray-900 font-black tracking-tighter animate-pulse">
                    MENGAMBIL DATA...
                </p>
            </div>

            {{-- Tidak Ditemukan --}}
            @if($notFound)
            <div wire:loading.remove wire:target="search" class="bg-white rounded-[2.5rem] p-10 text-center shadow-xl 
                    border-2 border-dashed border-gray-200 no-print">
                <x-heroicon-o-magnifying-glass-circle class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                <h3 class="text-xl font-black text-gray-900 uppercase tracking-tighter">
                    Resi Tidak Valid
                </h3>
                <p class="text-gray-400 text-sm font-medium mt-2">
                    Pastikan kode yang dimasukkan sesuai dengan yang tertera di struk kargo Anda.
                </p>
            </div>
            @endif

            {{-- Ditemukan --}}
            @if($cargo)
            <div wire:loading.remove wire:target="search" class="space-y-6 animate-in fade-in slide-in-from-bottom-8 duration-700 print-area w-full mt-4">

                {{-- Detail Card --}}
                <div class="rounded-[2.5rem] p-8 text-white relative overflow-hidden shadow-2xl" style="background: linear-gradient(160deg, #0D47A1 0%, #1565C0 50%, #1976D2 100%);">
                    {{-- Decor --}}
                    <div class="absolute -right-10 -top-10 opacity-10">
                        <x-heroicon-s-cube class="w-40 h-40 text-white" />
                    </div>

                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-10">
                            <div>
                                <span class="text-[10px] font-black bg-orange-500 px-3 py-1 rounded-full uppercase tracking-widest">Kargo Resmi</span>
                                <h2 class="text-3xl font-black mt-3 tracking-tighter italic">{{ $cargo->tracking_code }}</h2>
                                <p class="text-xs text-gray-400 mt-1">{{ $cargo->created_at->format('d F Y • H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <x-heroicon-s-qr-code class="w-16 h-16 text-white opacity-20" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6 bg-white/5 p-6 rounded-3xl border border-white/10">
                            <div>
                                <p class="text-[9px] text-gray-500 font-black uppercase tracking-widest mb-1">Pengirim</p>
                                <p class="font-bold text-sm truncate uppercase">{{ $cargo->booking->booker_name ?? 'N/A' }}</p>
                                <p class="text-[10px] text-orange-400 font-bold uppercase">{{ $cargo->originAgent->city ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] text-gray-500 font-black uppercase tracking-widest mb-1">Penerima</p>
                                <p class="font-bold text-sm truncate uppercase">{{ $cargo->recipient_name }}</p>
                                <p class="text-[10px] text-orange-400 font-bold uppercase">{{ $cargo->destinationAgent->city ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-between px-2">
                            <div class="flex gap-4">
                                <div class="text-center">
                                    <p class="text-[9px] text-gray-500 font-black uppercase">Berat</p>
                                    <p class="text-sm font-black">{{ $cargo->weight_kg }}<span class="text-[10px] ml-0.5">KG</span></p>
                                </div>
                                <div class="text-center">
                                    <p class="text-[9px] text-gray-500 font-black uppercase">Koli</p>
                                    <p class="text-sm font-black">{{ $cargo->quantity }}<span class="text-[10px] ml-0.5">BOX</span></p>
                                </div>
                            </div>
                            <div class="text-right italic">
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Layanan</p>
                                <p class="text-sm font-black text-orange-400">REGULAR DELIVERY</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Timeline --}}
                <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-gray-100">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] mb-10 text-center">History Perjalanan</h3>

                    <div class="space-y-12 relative">
                        <div class="absolute left-6 top-2 bottom-2 w-1 bg-gray-50"></div>

                        @php
                        $statuses = [
                        'pending' => ['label' => 'Paket Masuk Agen', 'desc' => 'Diterima oleh agen asal', 'icon' => 'heroicon-o-home-modern'],
                        'in_transit' => ['label' => 'Dalam Perjalanan', 'desc' => 'Sedang menuju kota tujuan', 'icon' => 'heroicon-o-truck'],
                        'arrived' => ['label' => 'Sampai di Agen', 'desc' => 'Siap untuk diambil/diantar', 'icon' => 'heroicon-o-map-pin'],
                        'received' => ['label' => 'Sudah Diterima', 'desc' => 'Diterima oleh ybs', 'icon' => 'heroicon-o-check-badge'],
                        ];
                        $foundActive = false;
                        @endphp

                        @foreach($statuses as $key => $val)
                        @php
                        $isCurrent = $cargo->status === $key;
                        if($isCurrent) $foundActive = true;
                        $activeClass = $isCurrent || !$foundActive;
                        @endphp

                        <div class="flex items-start gap-6 relative">
                            <div class="relative z-10 flex-shrink-0">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-500 
                                            {{ $isCurrent ? 'bg-orange-500 text-white shadow-xl shadow-orange-500/40 scale-125' : ($activeClass ? 'bg-gray-900 text-white' : 'bg-gray-50 text-gray-200') }}">
                                    <x-dynamic-component :component="$val['icon']" class="w-6 h-6" />
                                </div>
                            </div>
                            <div class="flex flex-col pt-1">
                                <span class="text-sm font-black uppercase tracking-tight {{ $activeClass ? 'text-gray-900' : 'text-gray-300' }}">
                                    {{ $val['label'] }}
                                </span>
                                <span class="text-[10px] font-bold text-gray-400 mt-0.5 leading-relaxed">{{ $val['desc'] }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="grid grid-cols-2 gap-4 no-print">
                    @php
                    $waText = urlencode("Halo, cek status paket saya dengan Nomor Resi: " . $cargo->tracking_code . "\nStatus: " . strtoupper($cargo->status) . "\nLacak di sini: " . url()->current() . "?trackingCode=" . $cargo->tracking_code);
                    @endphp
                    <a href="https://wa.me/?text={{ $waText }}" target="_blank" class="flex flex-col items-center justify-center gap-2 bg-emerald-500 text-white p-5 rounded-[2rem] shadow-lg hover:bg-emerald-600 transition-all active:scale-95">
                        <x-heroicon-s-chat-bubble-left-right class="w-6 h-6" />
                        <span class="text-[9px] font-black tracking-widest uppercase">WhatsApp Share</span>
                    </a>

                    <button onclick="window.print()" class="flex flex-col items-center justify-center gap-2 bg-white text-gray-900 p-5 rounded-[2rem] shadow-lg border border-gray-100 hover:bg-gray-50 transition-all active:scale-95">
                        <x-heroicon-s-printer class="w-6 h-6" />
                        <span class="text-[9px] font-black tracking-widest uppercase">Cetak Resi</span>
                    </button>
                </div>
            </div>
            @endif

        </div>
    </div>