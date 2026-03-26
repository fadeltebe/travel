<div x-data="{ open: false }">

    {{-- Overlay --}}
    <div x-show="open" x-transition.opacity @click="open = false" class="fixed inset-0 z-40 bg-black/30 backdrop-blur-sm" style="display:none"></div>

    {{-- FAB Options --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" class="fixed bottom-24 inset-x-0 z-50 flex flex-col items-center gap-3 px-6" style="display:none">

        <a href="{{ route('schedules.create') }}" class="w-full max-w-xs flex items-center gap-4 bg-white rounded-2xl px-5 py-4
                  shadow-xl border border-gray-100 active:scale-95 transition-transform">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #1565C0, #1976D2)">
                <x-heroicon-o-calendar-days class="w-5 h-5 text-white" />
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-800">Tambah Jadwal</p>
                <p class="text-xs text-gray-400">Buat jadwal keberangkatan</p>
            </div>
            <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-300 ml-auto" />
        </a>
        
        <a href="{{ route('bookings.create') }}" class="w-full max-w-xs flex items-center gap-4 bg-white rounded-2xl px-5 py-4
                  shadow-xl border border-gray-100 active:scale-95 transition-transform">

                  
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(160deg, #10B981 0%, #059669 50%, #047857 100%);">
                <x-heroicon-o-user-plus class="w-5 h-5 text-white" />
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-800">Tambah Penumpang</p>
                <p class="text-xs text-gray-400">Buat booking baru</p>
            </div>
            <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-300 ml-auto" />
        </a>

        <a href="{{ route('cargo.create') }}" class="w-full max-w-xs flex items-center gap-4 bg-white rounded-2xl px-5 py-4
                  shadow-xl border border-gray-100 active:scale-95 transition-transform">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #F57C00, #FF9800)">
                <x-heroicon-o-cube class="w-5 h-5 text-white" />
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-800">Kirim Barang</p>
                <p class="text-xs text-gray-400">Buat pengiriman cargo</p>
            </div>
            <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-300 ml-auto" />
        </a>

    </div>

    {{-- FAB Button --}}
    <button @click="open = !open" :class="open ? 'rotate-45' : ''" class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50
                   w-14 h-14 rounded-full text-white
                   flex items-center justify-center
                   transition-all duration-300 active:scale-95" style="background: linear-gradient(135deg, #1565C0, #1976D2);
                   box-shadow: 0 4px 20px rgba(21,101,192,0.5);">
        <x-heroicon-o-plus class="w-7 h-7" />
    </button>

</div>