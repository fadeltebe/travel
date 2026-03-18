<?php

use Livewire\Volt\Component;
use App\Models\Schedule;
use App\Models\Agent;
use App\Models\Cargo; // Pastikan Model Cargo sudah ada
use Illuminate\Support\Facades\DB;

new class extends Component {
    // State Form
    public $step = 1;
    public $schedule_id;
    
    // Data Pengirim & Penerima
    public $sender_name, $sender_phone;
    public $receiver_name, $receiver_phone, $pickup_address;
    
    // Data Barang (Multiple Items)
    public $items = [];
    
    // Pembayaran
    public $payment_method = 'cash';
    public $payment_status = 'pending';
    public $payment_type = 'origin'; // origin atau destination (COD)
    public $shipping_status = 'scheduled';

    public function mount()
    {
        // Inisialisasi barang pertama
        $this->addItem();
    }

    // Computed Property untuk Jadwal (Solusi Error PropertyNotFound)
    public function with(): array
    {
        return [
            'schedules' => Schedule::with(['route.originAgent', 'route.destinationAgent', 'bus'])
                ->where('departure_date', '>=', now()->toDateString())
                ->orderBy('departure_date')
                ->orderBy('departure_time')
                ->get(),
        ];
    }

    public function addItem()
    {
        $this->items[] = [
            'code' => 'CRG-' . strtoupper(Str::random(5)),
            'description' => '',
            'qty' => 1,
            'weight' => 1,
            'price' => 0
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function goStep($step)
    {
        // Validasi sederhana sebelum pindah step
        if ($step == 2 && !$this->schedule_id) {
            $this->dispatch('notify', message: 'Pilih jadwal terlebih dahulu', type: 'error');
            return;
        }
        
        $this->step = $step;
        $this->dispatch('scroll-to-top');
    }

    // Hitung Total Otomatis
    public function getTotalBillProperty()
    {
        return collect($this->items)->sum('price');
    }

    public function save()
    {
        $this->validate([
            'schedule_id' => 'required',
            'sender_name' => 'required|min:3',
            'sender_phone' => 'required',
            'receiver_name' => 'required',
            'items.*.description' => 'required',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () {
                // Logika simpan ke table cargos dan cargo_items Anda di sini
                // Contoh:
                // $cargo = Cargo::create([...]);
            });

            session()->flash('success', 'Data Cargo berhasil disimpan!');
            return $this->redirect(route('cargo.index'), navigate: true);

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Gagal menyimpan: ' . $e->getMessage(), type: 'error');
        }
    }
}; ?>

<div x-on:scroll-to-top.window="window.scrollTo(0,0)">
    <div class="px-4 pt-6 pb-24 space-y-5 max-w-lg mx-auto">

        {{-- Header & Progress --}}
        <div class="flex items-center gap-3">
            @if($step > 1)
            <button type="button" wire:click="goStep({{ $step - 1 }})" class="w-10 h-10 rounded-xl flex items-center justify-center border border-gray-200 bg-white shadow-sm active:scale-90 transition-transform">
                <x-heroicon-o-arrow-left class="w-5 h-5 text-gray-600" />
            </button>
            @else
            <a href="{{ route('dashboard') }}" wire:navigate class="w-10 h-10 rounded-xl flex items-center justify-center border border-gray-200 bg-white shadow-sm">
                <x-heroicon-o-arrow-left class="w-5 h-5 text-gray-600" />
            </a>
            @endif
            <div>
                <h1 class="text-xl font-bold text-gray-900">Kirim Paket (Cargo)</h1>
                <p class="text-xs text-gray-500">Langkah {{ $step }} dari 4</p>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="flex gap-2">
            @foreach([1,2,3,4] as $s)
            <div class="flex-1 h-1.5 rounded-full {{ $step >= $s ? 'bg-blue-600' : 'bg-gray-200' }}"></div>
            @endforeach
        </div>

        {{-- STEP 1: JADWAL --}}
        @if($step === 1)
        <div class="space-y-4">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <label class="block text-sm font-bold text-gray-800 mb-3">Pilih Jadwal Keberangkatan Bus</label>
                <div class="space-y-3 overflow-y-auto pr-2 custom-scrollbar" style="max-height: 400px;">
                    @forelse($schedules as $schedule)
                    <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all {{ $schedule_id == $schedule->id ? 'border-blue-500 bg-blue-50' : 'border-gray-100 bg-gray-50' }}">
                        <input type="radio" wire:model.live="schedule_id" value="{{ $schedule->id }}" class="sr-only">
                        <div class="flex justify-between items-start">
                            <span class="text-sm font-bold text-gray-900">{{ $schedule->route->originAgent->city }} → {{ $schedule->route->destinationAgent->city }}</span>
                            <span class="text-[10px] px-2 py-0.5 bg-blue-600 text-white rounded font-bold uppercase">{{ $schedule->bus->name }}</span>
                        </div>
                        <div class="mt-2 flex items-center gap-3 text-[10px] text-gray-500 font-medium">
                            <span class="flex items-center gap-1"><x-heroicon-o-calendar class="w-3.5 h-3.5" /> {{ $schedule->departure_date->format('d M Y') }}</span>
                            <span class="flex items-center gap-1"><x-heroicon-o-clock class="w-3.5 h-3.5" /> {{ substr($schedule->departure_time, 0, 5) }}</span>
                        </div>
                    </label>
                    @empty
                    <p class="text-center text-sm text-gray-500 py-10">Jadwal tidak tersedia.</p>
                    @endforelse
                </div>
            </div>
            <button wire:click="goStep(2)" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-bold shadow-lg">Lanjut: Data Pengirim</button>
        </div>
        @endif

        {{-- STEP 2: KONTAK --}}
        @if($step === 2)
        <div class="space-y-4">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
                <h2 class="font-bold text-gray-900 text-sm uppercase tracking-wider">Data Pengirim</h2>
                <input type="text" wire:model="sender_name" class="w-full px-4 py-3 rounded-xl border-gray-200 text-sm" placeholder="Nama Pengirim">
                <input type="tel" wire:model="sender_phone" class="w-full px-4 py-3 rounded-xl border-gray-200 text-sm" placeholder="Nomor WA Pengirim">
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
                <h2 class="font-bold text-gray-900 text-sm uppercase tracking-wider text-orange-600">Data Penerima</h2>
                <input type="text" wire:model="receiver_name" class="w-full px-4 py-3 rounded-xl border-gray-200 text-sm" placeholder="Nama Penerima">
                <input type="tel" wire:model="receiver_phone" class="w-full px-4 py-3 rounded-xl border-gray-200 text-sm" placeholder="Nomor WA Penerima">
                <textarea wire:model="pickup_address" class="w-full px-4 py-3 rounded-xl border-gray-200 text-sm" placeholder="Alamat Detail (Opsional)"></textarea>
            </div>
            <button wire:click="goStep(3)" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-bold shadow-lg">Lanjut: Detail Barang</button>
        </div>
        @endif

        {{-- STEP 3: BARANG --}}
        @if($step === 3)
        <div class="space-y-4">
            <div class="flex justify-between items-center px-1">
                <h2 class="font-bold text-gray-900">Daftar Barang</h2>
                <button wire:click="addItem" class="text-blue-600 text-sm font-bold flex items-center gap-1">
                    <x-heroicon-o-plus-circle class="w-5 h-5" /> Tambah
                </button>
            </div>

            @foreach($items as $index => $item)
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-3 relative">
                @if(count($items) > 1)
                <button wire:click="removeItem({{ $index }})" class="absolute top-4 right-4 text-red-400"><x-heroicon-o-trash class="w-5 h-5" /></button>
                @endif
                <input type="text" wire:model="items.{{ $index }}.description" class="w-full px-4 py-2 rounded-lg border-gray-200 text-sm font-bold" placeholder="Deskripsi Barang">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Berat (Kg)</label>
                        <input type="number" wire:model="items.{{ $index }}.weight" class="w-full px-4 py-2 rounded-lg border-gray-200 text-sm">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Biaya Kirim (Rp)</label>
                        <input type="number" wire:model="items.{{ $index }}.price" class="w-full px-4 py-2 rounded-lg border-gray-200 text-sm font-black text-blue-600">
                    </div>
                </div>
            </div>
            @endforeach
            <button wire:click="goStep(4)" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-bold shadow-lg">Lanjut: Pembayaran</button>
        </div>
        @endif

        {{-- STEP 4: FINAL --}}
        @if($step === 4)
        <div class="space-y-4">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-4 text-center">
                <span class="text-xs font-bold text-gray-400 uppercase">Total Tagihan</span>
                <h2 class="text-4xl font-black text-blue-600">Rp{{ number_format($this->totalBill, 0, ',', '.') }}</h2>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
                <div class="grid grid-cols-2 gap-2">
                    <button wire:click="$set('payment_type', 'origin')" class="py-3 text-xs font-bold rounded-xl border-2 {{ $payment_type == 'origin' ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-gray-50 text-gray-400' }}">Bayar di Sini</button>
                    <button wire:click="$set('payment_type', 'destination')" class="py-3 text-xs font-bold rounded-xl border-2 {{ $payment_type == 'destination' ? 'border-orange-600 bg-orange-50 text-orange-600' : 'border-gray-50 text-gray-400' }}">Bayar di Tujuan (COD)</button>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    @foreach(['cash' => 'Tunai', 'transfer' => 'TF', 'qris' => 'QRIS'] as $v => $l)
                    <button wire:click="$set('payment_method', '{{ $v }}')" class="py-2 text-[10px] font-bold rounded-lg border {{ $payment_method == $v ? 'bg-gray-900 text-white' : 'bg-gray-50 text-gray-400' }}">{{ $l }}</button>
                    @endforeach
                </div>

                <button wire:click="save" class="w-full py-4 bg-green-600 text-white rounded-2xl font-bold shadow-lg flex items-center justify-center gap-2">
                    <x-heroicon-o-check-badge class="w-6 h-6" /> Simpan & Selesai
                </button>
            </div>
        </div>
        @endif
    </div>
</div>