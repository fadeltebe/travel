<?php

use Livewire\Volt\Component;
use App\Models\Topup;
use App\Models\Company;
use Illuminate\Support\Str;

new class extends Component {
    public $topupAmount = 100000;

    public function mount()
    {
        $company = Company::first();
        $user = auth()->user();

        // Cek apakah ada invoice Topup yang masih pending
        $query = Topup::where('company_id', $company->id)
                      ->where('status', 'pending');

        if ($company->billing_mode === 'centralized') {
            $query->whereNull('agent_id');
        } else {
            $query->where('agent_id', $user->agent_id);
        }

        $pendingTopup = $query->first();

        if ($pendingTopup) {
            // Jika invoice sudah berusia lebih dari 24 jam (default expiry Midtrans), otomatis digagalkan
            if (now()->diffInHours($pendingTopup->created_at) >= 24) {
                $pendingTopup->update(['status' => 'failed']);
            } else {
                $this->dispatch('notify', message: 'Harap selesaikan atau batalkan tagihan sebelumnya.', type: 'warning');
                return $this->redirect(route('billings.show', $pendingTopup->id), navigate: true);
            }
        }
    }

    public function submit()
    {
        $this->validate([
            'topupAmount' => 'required|numeric|min:50000',
        ]);

        $company = Company::first();
        $user = auth()->user();

        $invoiceNumber = 'INV-TKN-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        $topup = Topup::create([
            'company_id' => $company->id,
            'agent_id' => $company->billing_mode === 'centralized' ? null : $user->agent_id,
            'invoice_number' => $invoiceNumber,
            'amount' => $this->topupAmount,
            'status' => 'pending',
            'payment_method' => 'midtrans_snap',
        ]);

        $paymentService = app(\App\Services\PaymentService::class);
        $snapToken = $paymentService->createSnapToken($topup);
        
        if ($snapToken) {
            $topup->update(['snap_token' => $snapToken]);
            // Langsung buka pop-up Snap di halaman ini
            $this->dispatch('open-midtrans-snap', token: $snapToken);
        } else {
            // Batalkan invoice otomatis jk API Midtrans error
            $topup->update(['status' => 'failed']);
            $this->dispatch('notify', message: 'Gagal komunikasi server Midtrans.', type: 'error');
        }
    }
};
?>

<div>
    {{-- Memanggil Midtrans Snap.js --}}
    @if(config('services.midtrans.is_production'))
        <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    @else
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    @endif
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('open-midtrans-snap', (event) => {
                snap.pay(event.token, {
                    onSuccess: function(result){ window.location.href = "{{ route('wallets.index') }}"; },
                    onPending: function(result){ window.location.href = "{{ route('billings.index') }}"; },
                    onError: function(result){ alert('Pembayaran gagal!'); window.location.href = "{{ route('billings.index') }}"; },
                    onClose: function(){ window.location.href = "{{ route('billings.index') }}"; }
                });
            });
        });
    </script>

    <x-layouts.app title="Top-Up Saldo">
        <div class="min-h-screen bg-gray-50 pt-6 pb-24">
            <div class="max-w-2xl mx-auto px-4">

        <div class="mb-8">
            <a href="{{ route('wallets.index') }}" class="text-blue-600 font-semibold flex items-center gap-2 mb-6">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Kembali ke Dompet
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Top-Up Saldo Token</h1>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">

            {{-- Amount Selection --}}
            <div class="mb-8">
                <label class="block text-sm font-semibold text-gray-700 mb-4">Pilih Jumlah Top-Up</label>
                <div class="grid grid-cols-2 gap-3 mb-4">
                    @foreach ([50000, 100000, 500000, 1000000] as $amount)
                        <button wire:click="$set('topupAmount', {{ $amount }})"
                            class="py-3 px-4 text-sm font-bold rounded-lg border-2 transition-all
                        {{ $this->topupAmount === $amount
                            ? 'border-blue-600 bg-blue-50 text-blue-600'
                            : 'border-gray-200 text-gray-700 hover:border-gray-300' }}">
                            Rp{{ number_format($amount, 0, ',', '.') }}
                        </button>
                    @endforeach
                </div>

                <label class="block text-sm font-semibold text-gray-700 mb-2">Atau Input Nominal Custom</label>
                <input type="number" wire:model.live="topupAmount"
                    class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-blue-600 focus:outline-none"
                    placeholder="Minimal Rp 50.000" min="50000">
            </div>



            {{-- Summary --}}
            <div class="bg-gray-50 border border-gray-200 rounded-lg px-6 py-4 mb-8">
                <div class="flex justify-between pt-2 border-t border-gray-200">
                    <span class="font-semibold text-gray-900">Total Bayar</span>
                    <span
                        class="text-xl font-bold text-blue-600">Rp{{ number_format($this->topupAmount, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Action Button --}}
            <button wire:click="submit"
                class="w-full bg-blue-600 text-white py-4 rounded-lg font-bold hover:bg-blue-700 active:scale-95 transition-all">
                Bayar Sekarang
            </button>

            <p class="text-xs text-gray-400 text-center mt-4">Jendela pop-up pembayaran akan langsung terbuka saat ditekan. Pastikan popup pada browser tidak diblokir.</p>

        </div>

            </div>

        </div>
    </x-layouts.app>
</div>
