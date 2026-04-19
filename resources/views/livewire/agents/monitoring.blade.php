<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Agent;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Cargo;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\TokenService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

new class extends Component {
    use WithPagination;

    public $agents = [];
    public $selectedAgentId = '';
    public $selectedMonth;
    public $selectedYear;

    public $showTransferModal = false;
    public $showWithdrawModal = false;
    public $transferAmount = '';
    public $withdrawAmount = '';
    
    protected $rules = [
        'transferAmount' => 'required|numeric|min:1000',
        'withdrawAmount' => 'required|numeric|min:1000',
    ];

    public function mount()
    {
        $user = auth()->user();
        if (!in_array($user->role->value ?? $user->role, ['superadmin', 'owner', 'super_admin'])) {
            abort(403, 'Akses Ditolak');
        }

        $this->agents = Agent::all();
        $this->selectedMonth = now()->format('m');
        $this->selectedYear = now()->format('Y');
        
        if ($this->agents->isNotEmpty()) {
            $this->selectedAgentId = $this->agents->first()->id;
        }
    }

    public function with()
    {
        $metrics = [
            'wallet_balance' => 0,
            'passenger' => ['paid' => 0, 'unpaid' => 0, 'revenue' => 0, 'total' => 0],
            'cargo' => ['paid' => 0, 'unpaid' => 0, 'revenue' => 0, 'total' => 0]
        ];

        $transactions = \App\Models\WalletTransaction::whereRaw('1 = 0')->paginate(5, ['*'], 'trx_page'); // Kosong by default
        $passengersList = collect();
        $cargosList = collect();

        if ($this->selectedAgentId) {
            // 1. Ambil Dompet Agen
            $wallet = Wallet::where('agent_id', $this->selectedAgentId)->first();
            $metrics['wallet_balance'] = $wallet ? $wallet->balance : 0;
            
            if ($wallet) {
                // Paginated mutasi
                $transactions = WalletTransaction::where('wallet_id', $wallet->id)
                    ->orderBy('created_at', 'desc')
                    ->paginate(5, ['*'], 'trx_page');
            }

            // 2. Query Booking Penumpang (Yang dibuat oleh agen ini)
            $bookingsQuery = Booking::where('agent_id', $this->selectedAgentId)
                ->whereHas('schedule', function (Builder $q) {
                    $q->whereMonth('departure_date', $this->selectedMonth)
                      ->whereYear('departure_date', $this->selectedYear);
                });

            // Kita ambil Passenger dari booking tersebut
            $passengerQuery = Passenger::whereHas('booking', function(Builder $query) {
                $query->where('agent_id', $this->selectedAgentId)
                      ->whereHas('schedule', function (Builder $sq) {
                          $sq->whereMonth('departure_date', $this->selectedMonth)
                             ->whereYear('departure_date', $this->selectedYear);
                      });
            });
            
            $passengersList = (clone $passengerQuery)->with('booking')->limit(10)->get(); // sampel 10

            // Hitung metrik penumpang
            $metrics['passenger']['paid'] = (clone $passengerQuery)->whereHas('booking', fn($q) => $q->where('payment_status', 'paid'))->count();
            $metrics['passenger']['unpaid'] = (clone $passengerQuery)->whereHas('booking', fn($q) => $q->where('payment_status', '!=', 'paid'))->count();
            $metrics['passenger']['total'] = (clone $passengerQuery)->count();
            
            // Revenue: Hanya gunakan subtotal tiket dan jasa antar jemput agar tidak double hitung harga kargo
            $metrics['passenger']['revenue'] = (clone $bookingsQuery)->where('payment_status', 'paid')->sum('subtotal_price') + (clone $bookingsQuery)->where('payment_status', 'paid')->sum('pickup_dropoff_fee');

            // 3. Query Kargo Logistik (Yang masuk/keluar lewat agen ini) agar konsisten dengan agent-reports
            $cargoQuery = Cargo::where(function($q) {
                    $q->where('origin_agent_id', $this->selectedAgentId)
                      ->orWhere('destination_agent_id', $this->selectedAgentId);
                })
                ->whereMonth('created_at', $this->selectedMonth)
                ->whereYear('created_at', $this->selectedYear);
                
            $cargosList = (clone $cargoQuery)->limit(10)->get();

            $metrics['cargo']['paid'] = (clone $cargoQuery)->where('is_paid', true)->count();
            $metrics['cargo']['unpaid'] = (clone $cargoQuery)->where('is_paid', false)->count();
            $metrics['cargo']['total'] = (clone $cargoQuery)->count();
            // Cargo revenue: Sum fee dari Cargo yang lunas
            $metrics['cargo']['revenue'] = (clone $cargoQuery)->where('is_paid', true)->sum('fee');
        }

        return [
            'metrics' => $metrics,
            'transactions' => $transactions,
            'passengersList' => $passengersList,
            'cargosList' => $cargosList,
            'months' => [
                '01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', 
                '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', 
                '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'
            ],
            'years' => range(now()->year - 2, now()->year + 1),
        ];
    }

    public function updatedSelectedAgentId() { $this->resetPage('trx_page'); }
    public function updatedSelectedMonth() { $this->resetPage('trx_page'); }

    /**
     * TRANSFER SALDO DARI DOMPET UTAMA KE DOMPET AGEN
     */
    public function submitTransfer()
    {
        $this->validate(['transferAmount' => 'required|numeric|min:1000']);
        $tokenService = app(TokenService::class);
        $agent = Agent::find($this->selectedAgentId);

        try {
            DB::transaction(function () use ($tokenService, $agent) {
                // 1. Kurangi Saldo Bos (Kasir)
                $tokenService->deduct(null, null, $this->transferAmount, "Transfer Subsidi ke Agen: {$agent->name}");
                
                // 2. Tambah Saldo Agen
                $walletAgent = Wallet::firstOrCreate(
                    ['agent_id' => $agent->id, 'company_id' => 1],
                    ['balance' => 0]
                );
                $tokenService->credit($walletAgent->id, $this->transferAmount, "Subsidi Masuk dari Kasir Pusat");
            });

            $this->dispatch('notify', message: 'Saldo berhasil disalurkan ke agen!', type: 'success');
            $this->showTransferModal = false;
            $this->transferAmount = '';
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Gagal! Pastikan Saldo Utama Kasir Anda mencukupi.', type: 'error');
        }
    }

    /**
     * TARIK SALDO DARI DOMPET AGEN KE DOMPET UTAMA
     */
    public function submitWithdraw()
    {
        $this->validate(['withdrawAmount' => 'required|numeric|min:1000']);
        $tokenService = app(TokenService::class);
        $agent = Agent::find($this->selectedAgentId);

        try {
            DB::transaction(function () use ($tokenService, $agent) {
                // 1. Kurangi Saldo Agen (Tarik Paksa)
                $tokenService->deduct(null, $agent->id, $this->withdrawAmount, "Dana Ditarik oleh Kasir Pusat ($agent->name)");
                
                // 2. Tambah Saldo Bos (Kasir)
                // Cari boss wallet
                $bossWallet = Wallet::whereNull('agent_id')->first();
                if($bossWallet) {
                    $tokenService->credit($bossWallet->id, $this->withdrawAmount, "Penarikan Saldo dari Agen: {$agent->name}");
                }
            });

            $this->dispatch('notify', message: 'Saldo berhasil ditarik dari agen!', type: 'success');
            $this->showWithdrawModal = false;
            $this->withdrawAmount = '';
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Gagal ditarik! Pastikan target saldo agen agen tidak minus atau kosong.', type: 'error');
        }
    }
};
?>

<div>
    <x-layouts.app title="Pemantauan Per Agen">
        
        {{-- Header Sticky Filter --}}
        <div class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-gray-100 px-4 py-4 mb-6 shadow-sm">
            <div class="max-w-4xl mx-auto flex flex-col sm:flex-row gap-3 justify-between items-center">
                <div class="flex items-center gap-2 text-indigo-800 font-bold w-full sm:w-auto">
                    <x-heroicon-s-chart-bar class="w-6 h-6 animate-pulse" />
                    Monitoring Kasir Agen
                </div>
                
                <div class="flex gap-2 w-full sm:w-auto">
                    {{-- Dropdown Agen --}}
                    <select wire:model.live="selectedAgentId" class="flex-1 sm:w-48 border-gray-200 rounded-lg text-sm bg-gray-50 focus:ring-indigo-500 font-semibold shadow-inner">
                        <option value="">-- Pilih Agen --</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                        @endforeach
                    </select>
                    
                    {{-- Dropdown Kalender --}}
                    <select wire:model.live="selectedMonth" class="w-16 border-gray-200 rounded-lg text-sm bg-gray-50 focus:ring-indigo-500 font-semibold shadow-inner px-2">
                        @foreach($months as $m => $label)
                            <option value="{{ $m }}">{{ $m }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="selectedYear" class="w-20 border-gray-200 rounded-lg text-sm bg-gray-50 focus:ring-indigo-500 font-semibold shadow-inner px-2">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-4 pb-24 space-y-6">

            @if(!$selectedAgentId)
                <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-300">
                    <x-heroicon-o-user-group class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                    <p class="text-gray-500 font-medium">Silakan pilih Agen terlebih dahulu pada menu dropdown di atas.</p>
                </div>
            @else

            {{-- 1. Blok Kasir & Keuangan Utama --}}
            <div class="bg-gradient-to-r from-indigo-700 to-indigo-900 rounded-3xl shadow-lg text-white p-6 relative overflow-hidden">
                <div class="absolute -right-8 -top-8 w-40 h-40 bg-white opacity-5 rounded-full blur-2xl"></div>
                <div class="absolute -left-8 -bottom-8 w-32 h-32 bg-white opacity-5 rounded-full blur-2xl"></div>
                
                <h2 class="text-indigo-200 text-xs font-bold uppercase tracking-widest mb-1 relative z-10">Total Pendapatan Tiket & Kargo (Bulan Ini)</h2>
                <div class="text-3xl font-black mb-6 relative z-10">
                    Rp {{ number_format($metrics['passenger']['revenue'] + $metrics['cargo']['revenue'], 0, ',', '.') }}
                </div>
                
                <div class="flex flex-col sm:flex-row bg-white/10 border border-white/20 rounded-2xl p-4 gap-4 justify-between items-center relative z-10 backdrop-blur-sm">
                    <div>
                        <p class="text-[10px] text-indigo-200 font-bold uppercase mb-1">Sisa Saldo Kasir Agen</p>
                        <p class="text-2xl font-bold font-mono">Rp {{ number_format($metrics['wallet_balance'], 0, ',', '.') }}</p>
                    </div>
                    
                    <div class="flex gap-2 w-full sm:w-auto">
                        <button wire:click="$set('showTransferModal', true)" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-xl shadow transition active:scale-95 text-sm flex items-center justify-center gap-2">
                            <x-heroicon-s-arrow-down-tray class="w-4 h-4" /> Subsidi
                        </button>
                        <button wire:click="$set('showWithdrawModal', true)" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-xl shadow transition active:scale-95 text-sm flex items-center justify-center gap-2">
                            <x-heroicon-s-archive-box-x-mark class="w-4 h-4" /> Tarik
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- 2. Rekap Penumpang --}}
                <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm relative overflow-hidden">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <x-heroicon-s-users class="w-5 h-5"/>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm">Rapor Penumpang</h3>
                            <p class="text-xs text-emerald-600 font-bold">Rp {{ number_format($metrics['passenger']['revenue'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-end mb-4 border-b border-gray-50 pb-4">
                        <div>
                            <p class="text-[10px] uppercase text-gray-400 font-bold mb-1">Total Tiket</p>
                            <p class="text-3xl font-black text-gray-800">{{ $metrics['passenger']['total'] }} <span class="text-sm font-semibold text-gray-400">Pax</span></p>
                        </div>
                        <div class="text-right text-xs font-semibold text-gray-500 space-y-1">
                            <p class="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">Lunas: {{ $metrics['passenger']['paid'] }}</p>
                            <p class="text-red-500 bg-red-50 px-2 py-0.5 rounded-md">Pending: {{ $metrics['passenger']['unpaid'] }}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        @forelse($passengersList as $pax)
                            <div class="flex justify-between items-center text-xs p-2 hover:bg-gray-50 rounded-lg">
                                <span class="font-semibold text-gray-700 truncate min-w-0 pr-2">{{ Str::limit($pax->name, 15) }}</span>
                                <span class="whitespace-nowrap {{ $pax->booking->payment_status === 'paid' ? 'text-emerald-600' : 'text-orange-500' }} font-bold">
                                    Rp {{ number_format($pax->ticket_price, 0, ',', '.') }}
                                </span>
                            </div>
                        @empty
                            <p class="text-center text-xs text-gray-400 py-4">Belum ada penumpang bulan ini.</p>
                        @endforelse
                    </div>
                </div>

                {{-- 3. Rekap Kargo --}}
                <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm relative overflow-hidden">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center shrink-0">
                            <x-heroicon-s-cube class="w-5 h-5"/>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm">Rapor Kargo Barang</h3>
                            <p class="text-xs text-orange-600 font-bold">Rp {{ number_format($metrics['cargo']['revenue'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-end mb-4 border-b border-gray-50 pb-4">
                        <div>
                            <p class="text-[10px] uppercase text-gray-400 font-bold mb-1">Total Resi</p>
                            <p class="text-3xl font-black text-gray-800">{{ $metrics['cargo']['total'] }} <span class="text-sm font-semibold text-gray-400">Resi</span></p>
                        </div>
                        <div class="text-right text-xs font-semibold text-gray-500 space-y-1">
                            <p class="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">Terbayar: {{ $metrics['cargo']['paid'] }}</p>
                            <p class="text-red-500 bg-red-50 px-2 py-0.5 rounded-md">Tertunggak: {{ $metrics['cargo']['unpaid'] }}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        @forelse($cargosList as $cargo)
                            <div class="flex justify-between items-center text-xs p-2 hover:bg-gray-50 rounded-lg">
                                <span class="font-semibold text-gray-700 truncate min-w-0 pr-2">{{ Str::limit($cargo->recipient_name ?? 'Kargo', 15) }} ({!! $cargo->quantity !!} Kg)</span>
                                <span class="whitespace-nowrap {{ $cargo->is_paid ? 'text-emerald-600' : 'text-red-500' }} font-bold">
                                    Rp {{ number_format($cargo->fee, 0, ',', '.') }}
                                </span>
                            </div>
                        @empty
                            <p class="text-center text-xs text-gray-400 py-4">Belum ada barang masuk bulan ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- 4. Riwayat Transaksi Dompet --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900">Riwayat Mutasi Saldo Agen</h3>
                    <div class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-xs font-bold">{{ method_exists($transactions, 'total') ? $transactions->total() : $transactions->count() }} Data</div>
                </div>

                @if(count($transactions) > 0)
                    <div class="divide-y divide-gray-50">
                        @foreach ($transactions as $trx)
                            <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm border {{ $trx->type === 'credit' ? 'bg-green-50 border-green-100' : 'bg-red-50 border-red-100' }}">
                                        @if($trx->type === 'credit')
                                            <x-heroicon-s-arrow-down-circle class="w-6 h-6 text-green-500" />
                                        @else
                                            <x-heroicon-s-arrow-up-circle class="w-6 h-6 text-red-500" />
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-800 text-xs truncate">{{ $trx->description }}</p>
                                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $trx->created_at->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>
                                <div class="text-right text-xs shrink-0 pl-2">
                                    <p class="font-black {{ $trx->type === 'credit' ? 'text-green-600' : 'text-red-500' }}">
                                        {{ $trx->type === 'credit' ? '+' : '-' }}Rp{{ number_format($trx->amount, 0, ',', '.') }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 font-medium">Sisa: Rp{{ number_format($trx->balance_after, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if(method_exists($transactions, 'hasPages') && $transactions->hasPages())
                        <div class="p-4 border-t border-gray-50 bg-gray-50/50">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                @else
                    <div class="py-12 text-center text-sm text-gray-400">
                        Belum ada mutasi keluar-masuk di dompet agen ini.
                    </div>
                @endif
            </div>

            @endif
        </div>

        {{-- Modals Transfer & Withdraw --}}
        
        @if($showTransferModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
            <div class="bg-white rounded-3xl w-full max-w-sm p-6 shadow-2xl relative animate-up">
                <button wire:click="$set('showTransferModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"><x-heroicon-o-x-mark class="w-6 h-6" /></button>
                <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4">
                    <x-heroicon-s-paper-airplane class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Subsidi Saldo Kasir</h3>
                <p class="text-xs text-gray-500 mb-6">Uang akan dikurangkan dari Dompet Utama Owner dan ditambahkan ke Dompet Agen.</p>
                
                <form wire:submit="submitTransfer">
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">Nominal Transfer (Rp)</label>
                        <input type="number" wire:model="transferAmount" class="w-full text-lg font-bold border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-0 focus:border-green-500 transition" placeholder="Contoh: 500000" min="1000" required>
                        @error('transferAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-xl shadow-lg active:scale-95 transition-all">Hubungkan Saldo</button>
                </form>
            </div>
        </div>
        @endif

        @if($showWithdrawModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
            <div class="bg-white rounded-3xl w-full max-w-sm p-6 shadow-2xl relative animate-up">
                <button wire:click="$set('showWithdrawModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"><x-heroicon-o-x-mark class="w-6 h-6" /></button>
                <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4">
                    <x-heroicon-s-archive-box-arrow-down class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Tarik Paksa Saldo</h3>
                <p class="text-xs text-gray-500 mb-6">Menyedot sisa saldo dari dompet Agen untuk dikembalikan utuh ke dalam Dompet Utama Owner.</p>
                
                <form wire:submit="submitWithdraw">
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">Nominal Tarik (Rp)</label>
                        <input type="number" wire:model="withdrawAmount" class="w-full text-lg font-bold border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-0 focus:border-red-500 transition" placeholder="Contoh: 500000" min="1000" required>
                        @error('withdrawAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        <p class="text-[10px] text-gray-400 mt-2">Maksimal yang bisa ditarik adalah saldo terakhir agen: Rp{{ number_format($metrics['wallet_balance'],0,',','.') }}</p>
                    </div>
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl shadow-lg active:scale-95 transition-all">Cabut Token / Sedot Dana</button>
                </form>
            </div>
        </div>
        @endif
        
        <style>
            .animate-up { animation: modalUp 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
            @keyframes modalUp { from { opacity: 0; transform: translateY(20px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
        </style>

    </x-layouts.app>
</div>
