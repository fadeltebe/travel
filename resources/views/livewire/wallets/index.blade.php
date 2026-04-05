<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Company;

new class extends Component {
    use WithPagination;

    public $wallet;
    public $startDate;
    public $endDate;

    public function mount()
    {
        $user = auth()->user();

        // 1. Karena Single-Tenant, langsung ambil data perusahaan pertama
        $company = Company::first();

        if (!$company) {
            session()->flash('error', 'Data Perusahaan belum diatur di sistem. Hubungi administrator.');
            return;
        }

        $query = Wallet::query();

        // 2. Cek siapa yang bayar berdasarkan mode tagihan
        if ($company->billing_mode === 'per_agent') {
            // Jika agen mandiri, cari dompet milik agen tersebut
            $query->where('agent_id', $user->agent_id);
        } else {
            // Jika sentralisasi (Bos), cari Dompet Utama (agent_id kosong)
            $query->whereNull('agent_id');
        }

        $this->wallet = $query->first();

        // 3. Auto-create jika dompet belum ada
        if (!$this->wallet) {
            $this->wallet = Wallet::create([
                'company_id' => $company->id,
                'agent_id' => $company->billing_mode === 'centralized' ? null : $user->agent_id,
                'balance' => 0,
            ]);
        }
    }

    public function with()
    {
        $query = WalletTransaction::where('wallet_id', $this->wallet->id ?? 0);

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        return [
            'transactions' => $query->orderBy('created_at', 'desc')->paginate(10)
        ];
    }
    
    public function resetFilter()
    {
        $this->reset(['startDate', 'endDate']);
        $this->resetPage();
    }
    
    public function updatedStartDate() { $this->resetPage(); }
    public function updatedEndDate() { $this->resetPage(); }
};
?>

<div>
    <x-layouts.app title="Dompet & Billing">
    <div class="min-h-screen bg-gray-50 pt-6 pb-24">
        <div class="max-w-4xl mx-auto px-4">

            {{-- Header --}}
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Dompet & Billing</h1>
                <p class="text-gray-600 mt-2">Kelola saldo token dan riwayat transaksi Anda</p>
            </div>

            {{-- Balance Card --}}
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-3xl p-8 text-white mb-8 shadow-lg">
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <p class="text-blue-100 text-sm font-medium mb-2">Sisa Saldo Token</p>
                        <h2 class="text-4xl font-bold">
                            Rp{{ number_format($this->wallet->balance ?? 0, 0, ',', '.') }}
                        </h2>
                    </div>
                    <div class="bg-blue-500 bg-opacity-30 px-4 py-2 rounded-full">
                        <p class="text-blue-100 text-xs">Aktif</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('wallets.topup') }}"
                        class="bg-white text-blue-600 py-3 rounded-xl font-bold border border-transparent hover:bg-blue-50 transition-colors text-center inline-block">
                        + Top-Up Saldo
                    </a>
                    <a href="{{ route('billings.index') }}"
                        class="bg-blue-500 text-white py-3 rounded-xl font-bold text-center hover:bg-blue-700 transition-colors">
                        Lihat Invoices
                    </a>
                </div>
            </div>

            {{-- Usage Info --}}
            <div class="grid grid-cols-2 gap-4 mb-8">
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <p class="text-gray-600 text-sm mb-2">Tarif per Penumpang</p>
                    <p class="text-2xl font-bold text-gray-900">Rp{{ number_format(1000, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-3">Untuk setiap penumpang yang dipesan</p>
                </div>
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <p class="text-gray-600 text-sm mb-2">Tarif per Kargo</p>
                    <p class="text-2xl font-bold text-gray-900">Rp{{ number_format(500, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-3">Untuk setiap resi kargo yang diterbitkan</p>
                </div>
            </div>

            {{-- Riwayat Transaksi --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <h3 class="text-lg font-bold text-gray-900">Riwayat Transaksi</h3>
                    <div class="flex items-center gap-2">
                        <input type="date" wire:model.live="startDate" class="text-sm border-gray-200 rounded-lg px-3 py-2 text-gray-600 focus:border-blue-500 focus:ring-blue-500">
                        <span class="text-gray-400">-</span>
                        <input type="date" wire:model.live="endDate" class="text-sm border-gray-200 rounded-lg px-3 py-2 text-gray-600 focus:border-blue-500 focus:ring-blue-500">
                        @if($startDate || $endDate)
                            <button wire:click="resetFilter" title="Reset Filter" class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                                <x-heroicon-o-x-circle class="w-5 h-5" />
                            </button>
                        @endif
                    </div>
                </div>

                @if (count($transactions) > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach ($transactions as $trx)
                            <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm border {{ $trx->type === 'credit' ? 'bg-green-100 border-green-200' : 'bg-red-100 border-red-200' }}">
                                        @if($trx->type === 'credit')
                                            <x-heroicon-s-arrow-down-circle class="w-7 h-7 text-green-600" />
                                        @else
                                            <x-heroicon-s-arrow-up-circle class="w-7 h-7 text-red-600" />
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900">{{ $trx->description }}</p>
                                        <p class="text-sm text-gray-500">{{ $trx->created_at->toIndoDateTime() }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p
                                        class="font-bold {{ $trx->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $trx->type === 'credit' ? '+' : '-' }}Rp{{ number_format($trx->amount, 0, ',', '.') }}
                                    </p>
                                    <p class="text-sm text-gray-500">Saldo:
                                        Rp{{ number_format($trx->balance_after, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($transactions->hasPages())
                        <div class="px-6 py-4 border-t border-gray-100">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                @else
                    <div class="px-6 py-12 text-center">
                        <x-heroicon-o-inbox class="w-12 h-12 text-gray-300 mx-auto mb-4" />
                        <p class="text-gray-500">Belum ada transaksi</p>
                    </div>
                @endif
            </div>

        </div>
    </div>
    </x-layouts.app>
</div>

</div>
