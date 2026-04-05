<?php

use Livewire\Volt\Component;
use App\Models\Topup;
use App\Models\WalletTransaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public Topup $billing;

    public function mount(Topup $billing)
    {
        $user = auth()->user();
        
        // Cek izin (opsional: pastikan agent_id cocok kalau per_agent)
        if ($billing->agent_id && $billing->agent_id !== $user->agent_id && !$user->canViewAll()) {
            abort(403, 'Akses ditolak.');
        }

        $this->billing = $billing;
    }

    public function markAsPaid()
    {
        if ($this->billing->status === 'paid') return;

        DB::transaction(function () {
            // Ubah status
            $this->billing->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Tambah ke dompet
            $walletQuery = Wallet::where('company_id', $this->billing->company_id);
            if ($this->billing->agent_id) {
                $walletQuery->where('agent_id', $this->billing->agent_id);
            } else {
                $walletQuery->whereNull('agent_id');
            }

            $wallet = $walletQuery->first();
            
            if ($wallet) {
                $wallet->balance += $this->billing->amount;
                $wallet->save();

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'credit',
                    'amount' => $this->billing->amount,
                    'balance_after' => $wallet->balance,
                    'description' => "Top-Up via Invoice {$this->billing->invoice_number}",
                    'reference_id' => $this->billing->id,
                    'reference_type' => Topup::class,
                ]);
            }
        });

        $this->dispatch('notify', message: 'Pembayaran berhasil disimulasikan! Saldo dompet bertambah.', type: 'success');
        $this->billing->refresh();
    }

    public function cancelBilling()
    {
        if ($this->billing->status !== 'pending') return;

        $this->billing->update(['status' => 'failed']);
        
        $this->dispatch('notify', message: 'Tagihan dibatalkan. Anda kini bisa membuat tagihan baru.', type: 'info');
    }

    public function checkStatus()
    {
        $this->billing->refresh();
        if ($this->billing->status === 'paid') {
            $this->dispatch('notify', message: 'Pembayaran telah terverifikasi!', type: 'success');
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

    <x-layouts.app title="Detail Tagihan">
        <div class="px-4 pt-4 pb-24 max-w-2xl mx-auto space-y-6">

            {{-- Breadcrumb navigasi --}}
            <a href="{{ route('billings.index') }}" class="flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors">
                <x-heroicon-s-arrow-left class="w-4 h-4" />
                Kembali ke Tagihan
            </a>

            @if($billing->status === 'pending')
                <div class="bg-orange-50 border border-orange-200 text-orange-800 rounded-2xl p-4 flex gap-3 text-sm font-medium">
                    <x-heroicon-s-clock class="w-5 h-5 shrink-0 text-orange-500" />
                    <p>Harap segera selesaikan pembayaran agar saldo Top-Up otomatis masuk ke Dompet Anda.</p>
                </div>
            @elseif($billing->status === 'paid')
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 flex gap-3 text-sm font-medium shadow-sm">
                    <div class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center shrink-0">
                        <x-heroicon-s-check class="w-4 h-4 text-white" />
                    </div>
                    <div>
                        <p class="font-bold text-emerald-900">Pembayaran Berhasil Diterima</p>
                        <p class="text-xs text-emerald-700 mt-1">Saldo sebesar Rp{{ number_format($billing->amount, 0, ',', '.') }} telah masuk ke dompet Anda pada {{ $billing->paid_at->format('d M Y, H:i') }}.</p>
                    </div>
                </div>
            @endif

            {{-- Card Invoice Detail --}}
            <div class="bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden relative">
                
                {{-- Header --}}
                <div class="px-6 py-6 border-b border-dashed border-gray-300 {{ $billing->status === 'paid' ? 'bg-gray-50' : '' }}">
                    <p class="text-[11px] font-bold text-gray-500 uppercase tracking-widest text-center mb-1">INVOICE TOP-UP</p>
                    <h2 class="text-xl font-black text-gray-900 text-center">{{ $billing->invoice_number }}</h2>
                    <p class="text-xs text-gray-400 text-center mt-1">{{ $billing->created_at->format('d/m/Y H:i') }}</p>
                </div>

                {{-- Body Detail --}}
                <div class="p-6 space-y-5">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-gray-500">Nominal Top-Up</span>
                        <span class="text-lg font-black text-gray-900">Rp{{ number_format($billing->amount, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-gray-500">Metode Tagihan</span>
                        <span class="text-sm font-bold text-gray-800 uppercase">{{ str_replace('_', ' ', $billing->payment_method) }}</span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-gray-500">Status</span>
                        @if($billing->status === 'paid')
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">LUNAS</span>
                        @elseif($billing->status === 'failed')
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">GAGAL</span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-700 border border-orange-200">MENUNGGU PEMBAYARAN</span>
                        @endif
                    </div>
                </div>

                {{-- Total Footer --}}
                <div class="bg-blue-600 px-6 py-5 text-white flex justify-between items-center">
                    <span class="font-semibold text-blue-100">Total Bayar</span>
                    <span class="text-2xl font-black">Rp{{ number_format($billing->amount, 0, ',', '.') }}</span>
                </div>

            </div>

            @if($billing->status === 'pending')
                {{-- Payment Instructions Midtrans --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-gray-900 mb-3 block text-center">Selesaikan Pembayaran Anda</h3>
                    
                    @if($billing->snap_token)
                        <button onclick="snap.pay('{{ $billing->snap_token }}', {
                            onSuccess: function(result){ window.Livewire.find('{{ $this->getId() }}').checkStatus(); location.reload(); },
                            onPending: function(result){ alert('Menunggu pembayaran Anda!'); },
                            onError: function(result){ alert('Pembayaran gagal!'); },
                            onClose: function(){ alert('Anda menutup popup tanpa menyelesaikan pembayaran'); }
                        })" class="w-full py-4 rounded-xl font-black text-white bg-blue-600 hover:bg-blue-700 shadow-lg active:scale-95 transition-all flex items-center justify-center gap-2">
                            <x-heroicon-s-credit-card class="w-5 h-5" />
                            Bayar Sekarang (Midtrans)
                        </button>
                    @else
                        <div class="p-3 bg-red-50 text-red-600 rounded-lg text-sm text-center font-bold">
                            Token Pembayaran Gagal di-generate. Cek konfigurasi Key Midtrans Anda!
                        </div>
                    @endif

                    <button wire:click="cancelBilling" onclick="confirm('Yakin ingin membatalkan Tagihan ini?') || event.stopImmediatePropagation()" class="w-full mt-3 py-3 rounded-xl font-bold text-red-500 bg-red-50 hover:bg-red-100 active:scale-95 transition-all text-sm flex items-center justify-center gap-2">
                        Batalkan Tagihan Ini
                    </button>
                </div>
            @endif

        </div>
    </x-layouts.app>
</div>
