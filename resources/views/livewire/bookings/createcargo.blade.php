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
    public $saving = false;

    public function mount()
    {
        // Inisialisasi barang pertama
        $this->addItem();
    }

    // Computed Property untuk Jadwal (Solusi Error PropertyNotFound)
    public function with(): array
    {
        $user = auth()->user();

        return [
            'schedules' => Schedule::with(['route.originAgent', 'route.destinationAgent', 'bus'])
                ->where('departure_date', '>=', now()->toDateString())

                // PERBAIKAN DI SINI:
                // Gunakan logika terbalik. Selama dia bukan super_admin / owner,
                // dan dia memiliki agent_id, maka kunci hanya di markasnya (Origin).
                ->when(!in_array($user->role, ['super_admin', 'owner']) && $user->agent_id, function ($query) use ($user) {
                    $query->whereHas('route', function ($q) use ($user) {
                        $q->where('origin_agent_id', $user->agent_id);
                    });
                })

                ->orderBy('departure_date', 'desc')
                ->orderBy('departure_time', 'desc')
                ->get(),
        ];
    }

    public function addItem()
    {
        // Format: CRG + TahunBulan + 8 Karakter Acak (Contoh: CRG2603A8X9K2P4)
        $this->items[] = [
            'code' => 'CRG' . date('ym') . strtoupper(\Illuminate\Support\Str::random(8)),
            'item_name' => '',
            'description' => '',
            'qty' => 1,
            'weight' => 1,
            'price' => 0,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function goStep($step)
    {
        try {
            if ($step == 2) {
                if (!$this->schedule_id) {
                    $this->dispatch('notify', message: 'Pilih jadwal terlebih dahulu', type: 'error');
                    return;
                }
            } elseif ($step == 3) {
                $this->validate([
                    'sender_name' => 'required|min:3',
                    'sender_phone' => 'required',
                    'receiver_name' => 'required',
                ]);
            } elseif ($step == 4) {
                $this->validate(
                    [
                        'items.*.item_name' => 'required',
                        'items.*.description' => 'required',
                        'items.*.price' => 'required|numeric|min:0',
                    ],
                    [
                        'items.*.item_name.required' => 'Semua Nama Barang harus diisi',
                        'items.*.description.required' => 'Semua Keterangan Isi harus diisi',
                        'items.*.price.required' => 'Semua Biaya Kirim harus diisi (minimal 0)',
                    ],
                );

                if (collect($this->items)->sum('price') <= 0) {
                    $this->dispatch('notify', message: 'Total tagihan tidak boleh Rp0', type: 'error');
                }
            }

            $this->step = $step;
            $this->dispatch('scroll-to-top');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('notify', message: 'Mohon lengkapi semua data wajib pada form ini dengan benar.', type: 'error');
            throw $e;
        }
    }

    // Hitung Total Otomatis
    public function getTotalBillProperty()
    {
        return collect($this->items)->sum('price');
    }

    public function getHasEnoughTokenProperty()
    {
        $tokenService = app(\App\Services\TokenService::class);
        $user = auth()->user();

        $company = \App\Models\Company::first();
        $companyId = $company->id ?? 1;

        // Validasi: ambil agent_id dari asal pengiriman tiket, bukan data akun (jika kosong/super admin)
        $schedule = Schedule::with('route')->find($this->schedule_id);
        $agentId = $schedule ? $schedule->route->origin_agent_id : ($user->agent_id ?? 1);

        $tarifPerKargo = 500;
        $jumlahKargo = max(1, count($this->items));
        $totalPotonganToken = $tarifPerKargo * $jumlahKargo;

        return $tokenService->hasEnoughBalance($companyId, $agentId, $totalPotonganToken);
    }

    public function save(\App\Services\TokenService $tokenService)
    {
        // GUARD: Cegah double-submit
        if ($this->saving) {
            return;
        }
        $this->saving = true;

        // Panggil data user di luar try-catch agar bisa dipakai di dalam DB::transaction
        $user = auth()->user();

        try {
            $this->validate([
                'schedule_id' => 'required',
                'sender_name' => 'required|min:3',
                'sender_phone' => 'required',
                'receiver_name' => 'required',
                'items.*.item_name' => 'required',
                'items.*.description' => 'required',
                'items.*.price' => 'required|numeric|min:0',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->saving = false;
            $this->dispatch('notify', message: 'Validasi Gagal! Pastikan semua formulir sudah diisi di langkah sebelumnya.', type: 'error');
            return;
        }

        // --- 1. KALKULASI & CEK SALDO TOKEN ---
        $tarifPerKargo = 500;
        $jumlahKargo = count($this->items);
        $totalPotonganToken = $tarifPerKargo * $jumlahKargo; // Rp 500 per resi/item kargo

        $company = \App\Models\Company::first();
        $companyId = $company->id ?? 1;

        // Ambil jadwal untuk relasi agent SECARA AKURAT
        $schedule = Schedule::with('route')
            ->when(!in_array($user->role, ['super_admin', 'owner']) && $user->agent_id, function ($query) use ($user) {
                $query->whereHas('route', fn($q) => $q->where('origin_agent_id', $user->agent_id));
            })
            ->findOrFail($this->schedule_id);

        $agentId = $schedule->route->origin_agent_id; // SELALU POTONG DARI AGEN YANG MENGIRIM!

        if (!$companyId) {
            $this->saving = false;
            $this->dispatch('notify', message: 'Gagal! Akun atau Agen Anda belum terhubung dengan Perusahaan (Company ID kosong).', type: 'error');
            return;
        }

        if (!$tokenService->hasEnoughBalance($companyId, $agentId, $totalPotonganToken)) {
            $this->saving = false;
            $this->dispatch('notify', message: 'Gagal! Saldo Token tidak cukup. Butuh Rp ' . number_format($totalPotonganToken, 0, ',', '.'), type: 'error');
            return;
        }
        // --------------------------------------

        try {
            DB::transaction(function () use ($user, $tokenService, $companyId, $agentId, $totalPotonganToken, $jumlahKargo, $schedule) {

                // Otomatis set lunas jika bayar di agen asal, pending jika COD (bayar di tujuan)
                $paymentStatus = $this->payment_type === 'origin' ? 'paid' : 'pending';
                $isPaid = $paymentStatus === 'paid';

                // Buat Booking Induk
                $booking = \App\Models\Booking::create([
                    'schedule_id' => $schedule->id,
                    'agent_id' => $agentId,
                    'user_id' => $user->id,
                    'booker_name' => $this->sender_name,
                    'booker_phone' => $this->sender_phone,
                    'total_passengers' => 0,
                    'total_cargo' => collect($this->items)->sum('qty'),
                    'subtotal_price' => 0,
                    'cargo_fee' => $this->totalBill,
                    'total_price' => $this->totalBill,
                    'payment_status' => $paymentStatus,
                    'payment_method' => $this->payment_method,
                    'paid_at' => $isPaid ? now() : null,
                    'status' => 'confirmed',
                ]);

                // Loop & simpan setiap barang ke tabel cargos
                foreach ($this->items as $item) {
                    Cargo::create([
                        'tracking_code' => $item['code'],
                        'booking_id' => $booking->id,
                        'origin_agent_id' => $schedule->route->origin_agent_id,
                        'destination_agent_id' => $schedule->route->destination_agent_id,
                        'item_name' => $item['item_name'],
                        'description' => $item['description'],
                        'weight_kg' => (float) ($item['weight'] ?? 1),
                        'quantity' => (int) ($item['qty'] ?? 1),
                        'fee' => (float) ($item['price'] ?? 0),
                        'recipient_name' => $this->receiver_name,
                        'recipient_phone' => $this->receiver_phone,
                        'dropoff_address' => $this->pickup_address,
                        'payment_type' => $this->payment_type,
                        'payment_method' => $this->payment_method,
                        'is_paid' => $isPaid,
                        'paid_at' => $isPaid ? now() : null,
                        'status' => 'pending',
                    ]);
                }

                // --- 2. EKSEKUSI PEMOTONGAN SALDO (DEBIT) ---
                if ($totalPotonganToken > 0) {
                    $tokenService->deduct($companyId, $agentId, $totalPotonganToken, "Penerbitan $jumlahKargo resi kargo untuk pengirim {$this->sender_name}", $booking);
                }
                // --------------------------------------------
            });

            session()->flash('success', 'Data Cargo berhasil disimpan & Saldo Token terpotong!');
            return $this->redirect(route('cargo.index'), navigate: true); // Sesuaikan dengan nama route index cargo Anda
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->saving = false;
            $this->dispatch('notify', message: 'Akses Ditolak: Jadwal tidak valid atau bukan milik agen Anda.', type: 'error');
        } catch (\Exception $e) {
            $this->saving = false;
            $this->dispatch('notify', message: 'Gagal menyimpan: ' . $e->getMessage(), type: 'error');
        }
    }
}; ?>

<div x-on:scroll-to-top.window="window.scrollTo(0,0)">
    <div class="px-4 pt-6 pb-24 space-y-5 max-w-lg mx-auto">

        {{-- Header & Progress --}}
        <div class="flex items-center gap-3">
            @if ($step > 1)
                <button type="button" wire:click="goStep({{ $step - 1 }})"
                    class="w-10 h-10 rounded-xl flex items-center justify-center border border-gray-200 bg-white shadow-sm active:scale-90 transition-transform">
                    <x-heroicon-o-arrow-left class="w-5 h-5 text-gray-600" />
                </button>
            @else
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="w-10 h-10 rounded-xl flex items-center justify-center border border-gray-200 bg-white shadow-sm">
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
            @foreach ([1, 2, 3, 4] as $s)
                <div class="flex-1 h-1.5 rounded-full {{ $step >= $s ? 'bg-orange-500' : 'bg-gray-200' }}"></div>
            @endforeach
        </div>

        {{-- STEP 1: JADWAL --}}
        @if ($step === 1)
            @include('livewire.bookings.partials.cargo.step1-jadwal')
        @endif

        {{-- STEP 2: KONTAK --}}
        @if ($step === 2)
            @include('livewire.bookings.partials.cargo.step2-kontak')
        @endif

        {{-- STEP 3: BARANG --}}
        @if ($step === 3)
            @include('livewire.bookings.partials.cargo.step3-barang')
        @endif

        {{-- STEP 4: FINAL --}}
        @if ($step === 4)
            @include('livewire.bookings.partials.cargo.step4-pembayaran')
        @endif
    </div>
</div>
