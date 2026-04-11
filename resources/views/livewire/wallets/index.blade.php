<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Company;
use Illuminate\Support\Str;

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
            'transactions' => $query->orderBy('created_at', 'desc')->paginate(10),
        ];
    }

    public function resetFilter()
    {
        $this->reset(['startDate', 'endDate']);
        $this->resetPage();
    }

    public function updatedStartDate()
    {
        $this->resetPage();
    }
    public function updatedEndDate()
    {
        $this->resetPage();
    }
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

                    @if (in_array(auth()->user()->role->value ?? auth()->user()->role, ['superadmin', 'owner', 'super_admin']))
                        <div class="mt-4 pt-4 border-t border-blue-500/30">
                            <a href="{{ route('agents.monitoring') }}" wire:navigate
                                class="w-full bg-indigo-900 border border-indigo-700 hover:bg-black text-white font-bold py-3 px-4 rounded-xl shadow-lg transition active:scale-[0.98] flex items-center justify-center gap-2">
                                <x-heroicon-s-chart-bar class="w-5 h-5 text-indigo-400 animate-pulse" />
                                Pusat Pantau Kinerja & Kasir Agen
                            </a>
                        </div>
                    @endif
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
                    <div class="px-4 py-3 border-b border-gray-100">
                        <div class="flex flex-col gap-3">
                            <h3 class="text-lg font-bold text-gray-900">Riwayat Transaksi</h3>

                            {{-- Filter Mobile-First --}}
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                <div class="flex items-center gap-2 flex-1">
                                    <input type="date" wire:model.live="startDate"
                                        class="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-600 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                        placeholder="Dari tanggal">
                                    <span class="text-gray-400 text-sm">-</span>
                                    <input type="date" wire:model.live="endDate"
                                        class="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-600 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                        placeholder="Sampai tanggal">
                                </div>
                                @if ($startDate || $endDate)
                                    <button wire:click="resetFilter"
                                        class="flex-shrink-0 px-3 py-2 text-sm text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors flex items-center gap-1">
                                        <x-heroicon-o-x-circle class="w-4 h-4" />
                                        <span class="hidden sm:inline">Reset</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if (count($transactions) > 0)
                        <div class="divide-y divide-gray-50">
                            @foreach ($transactions as $trx)
                                <div class="px-4 py-3 hover:bg-gray-50/50 transition-colors">
                                    <div class="flex items-center gap-3">
                                        {{-- Icon --}}
                                        <div
                                            class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $trx->type === 'credit' ? 'bg-green-100' : 'bg-red-100' }}">
                                            @if ($trx->type === 'credit')
                                                <x-heroicon-s-arrow-down-circle class="w-5 h-5 text-green-600" />
                                            @else
                                                <x-heroicon-s-arrow-up-circle class="w-5 h-5 text-red-600" />
                                            @endif
                                        </div>

                                        {{-- Content --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2">
                                                {{-- Description --}}
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-medium text-gray-900 text-sm leading-tight"
                                                        title="{{ $trx->description }}">
                                                        {!! nl2br(e(Str::limit(str_replace("\n", ' ', $trx->description), 35))) !!}
                                                    </p>
                                                    <p class="text-xs text-gray-500 flex items-center gap-1">
                                                        <x-heroicon-o-clock class="w-3 h-3" />
                                                        {{ $trx->created_at->format('d/m H:i') }}
                                                    </p>
                                                </div>

                                                {{-- Amount & Balance --}}
                                                <div class="text-right flex-shrink-0">
                                                    <p
                                                        class="font-bold text-sm {{ $trx->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ $trx->type === 'credit' ? '+' : '-' }}Rp{{ number_format($trx->amount, 0, ',', '.') }}
                                                    </p>
                                                    <p class="text-xs text-gray-500">
                                                        Saldo: Rp{{ number_format($trx->balance_after, 0, ',', '.') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if ($transactions->hasPages())
                            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs text-gray-500">
                                        Halaman {{ $transactions->currentPage() }} dari
                                        {{ $transactions->lastPage() }}
                                    </p>
                                    <div class="flex gap-1">
                                        @if ($transactions->onFirstPage())
                                            <span class="px-3 py-1 text-xs bg-gray-100 text-gray-400 rounded">«</span>
                                        @else
                                            <button wire:click="previousPage"
                                                class="px-3 py-1 text-xs bg-white border border-gray-200 text-gray-700 rounded hover:bg-gray-50">«</button>
                                        @endif

                                        @if ($transactions->hasMorePages())
                                            <button wire:click="nextPage"
                                                class="px-3 py-1 text-xs bg-white border border-gray-200 text-gray-700 rounded hover:bg-gray-50">»</button>
                                        @else
                                            <span class="px-3 py-1 text-xs bg-gray-100 text-gray-400 rounded">»</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="px-4 py-8 text-center">
                            <x-heroicon-o-inbox class="w-8 h-8 text-gray-300 mx-auto mb-2" />
                            <p class="text-sm text-gray-500">Belum ada transaksi</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </x-layouts.app>
</div>

</div>
