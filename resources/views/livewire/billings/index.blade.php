<?php

use Livewire\Volt\Component;
use App\Models\Topup;
use App\Models\Company;

new class extends Component {
    public function with()
    {
        $user = auth()->user();
        $company = Company::first();

        $query = Topup::query()->orderBy('created_at', 'desc');

        if ($company && $company->billing_mode === 'per_agent') {
            $query->where('agent_id', $user->agent_id);
        } else {
            $query->whereNull('agent_id');
        }

        return [
            'billings' => $query->get(),
        ];
    }
};
?>

<div>
    <x-layouts.app title="Daftar Tagihan">
        <div class="px-4 pt-6 pb-24 space-y-6">

            {{-- Header --}}
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Tagihan & Invoices</h1>
                    <p class="text-xs text-gray-500 mt-0.5">Riwayat permintaan Top-Up Dompet Token</p>
                </div>
                <a href="{{ route('wallets.index') }}"
                    class="w-10 h-10 rounded-full flex items-center justify-center bg-white border border-gray-200 text-gray-600 shadow-sm active:scale-95 transition-all">
                    <x-heroicon-o-wallet class="w-5 h-5" />
                </a>
            </div>

            {{-- Daftar Tagihan --}}
            <div class="space-y-4">
                @forelse($billings as $billing)
                    <a href="{{ route('billings.show', $billing) }}" class="block bg-white rounded-2xl p-5 shadow-sm border border-gray-100 relative active:scale-[0.98] transition-transform overflow-hidden">
                        
                        {{-- Status Badge (Absolute) --}}
                        <div class="absolute top-0 right-0">
                            @if($billing->status === 'paid')
                                <span class="inline-flex px-3 py-1 bg-emerald-500 text-white text-[9px] font-bold rounded-bl-xl shadow-sm">LUNAS</span>
                            @elseif($billing->status === 'failed' || $billing->status === 'cancelled')
                                <span class="inline-flex px-3 py-1 bg-red-500 text-white text-[9px] font-bold rounded-bl-xl shadow-sm">GAGAL</span>
                            @else
                                <span class="inline-flex px-3 py-1 bg-orange-500 text-white text-[9px] font-bold rounded-bl-xl shadow-sm animate-pulse">MENUNGGU PEMBAYARAN</span>
                            @endif
                        </div>

                        <div class="flex items-start gap-4 pr-16 mt-1">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 {{ $billing->status === 'paid' ? 'bg-emerald-50 text-emerald-600' : 'bg-orange-50 text-orange-600' }}">
                                <x-heroicon-o-document-text class="w-6 h-6" />
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-gray-900 truncate">{{ $billing->invoice_number }}</h3>
                                <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                    <x-heroicon-o-calendar class="w-3.5 h-3.5 text-gray-400" />
                                    {{ $billing->created_at->format('d M Y, H:i') }}
                                </p>
                                <p class="text-lg font-black text-gray-900 mt-2">
                                    Rp{{ number_format($billing->amount, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>

                        @if($billing->status === 'pending')
                        <div class="mt-4 pt-3 border-t border-dashed border-gray-200 flex justify-end">
                            <span class="text-xs font-bold text-blue-600 flex items-center gap-1">
                                Selesaikan Pembayaran <x-heroicon-s-arrow-right class="w-3 h-3" />
                            </span>
                        </div>
                        @else
                        <div class="mt-4 pt-3 border-t border-dashed border-gray-200 flex justify-between items-center">
                            <span class="text-[10px] text-gray-400 font-semibold uppercase">Metode Pembayaran:</span>
                            <span class="text-[11px] font-bold text-gray-700 capitalize">{{ str_replace('_', ' ', $billing->payment_method ?? 'N/A') }}</span>
                        </div>
                        @endif
                    </a>
                @empty
                    <div class="text-center py-16 bg-white rounded-3xl border border-dashed border-gray-300">
                        <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-4">
                            <x-heroicon-o-document-magnifying-glass class="w-8 h-8 text-gray-400" />
                        </div>
                        <h3 class="text-gray-900 font-bold mb-1">Belum Ada Tagihan</h3>
                        <p class="text-xs text-gray-500 max-w-[200px] mx-auto">Anda belum pernah meminta pengisian ulang saldo Token.</p>
                        
                        <a href="{{ route('wallets.topup') }}" class="inline-flex px-6 py-3 bg-blue-600 text-white text-sm font-bold rounded-2xl mt-5 shadow-lg active:scale-95 transition-transform">
                            Buat Tagihan Baru
                        </a>
                    </div>
                @endforelse
            </div>

            {{-- Floating Button untuk Topup --}}
            <a href="{{ route('wallets.topup') }}" class="fixed right-4 z-40 w-14 h-14 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-lg hover:shadow-xl active:scale-95 transition-transform border border-blue-500" style="bottom: calc(72px + env(safe-area-inset-bottom));">
                <x-heroicon-o-plus class="w-6 h-6" />
            </a>

        </div>
    </x-layouts.app>
</div>
