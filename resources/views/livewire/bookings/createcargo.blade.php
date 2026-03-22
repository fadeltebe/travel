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
            <div class="flex-1 h-1.5 rounded-full {{ $step >= $s ? 'bg-orange-500' : 'bg-gray-200' }}"></div>
            @endforeach
        </div>

        {{-- STEP 1: JADWAL --}}
        @if($step === 1)
        @include('livewire.bookings.partials.cargo-step1-jadwal')
        @endif

        {{-- STEP 2: KONTAK --}}
        @if($step === 2)
        @include('livewire.bookings.partials.cargo-step2-kontak')
        @endif

        {{-- STEP 3: BARANG --}}
        @if($step === 3)
        @include('livewire.bookings.partials.cargo-step3-barang')
        @endif

        {{-- STEP 4: FINAL --}}
        @if($step === 4)
        @include('livewire.bookings.partials.cargo-step4-pembayaran')
        @endif
    </div>
</div>