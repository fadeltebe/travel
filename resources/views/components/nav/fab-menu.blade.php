<div x-data="{ open: false }">

    {{-- Overlay --}}
    <div x-show="open" x-transition.opacity @click="open = false" class="fixed inset-0 z-40 bg-black/40" style="display: none;">
    </div>

    {{-- FAB Options (muncul saat open) --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" class="fixed bottom-20 inset-x-0 z-50 flex flex-col items-center gap-3" style="display: none;">

        {{-- Option: Tambah Penumpang --}}
        <a href="#" class="flex items-center gap-3 bg-white rounded-2xl px-6 py-3.5
                  shadow-xl text-primary-700 font-semibold text-sm
                  border border-gray-100 active:scale-95 transition-transform">
            <x-heroicon-o-user-plus class="w-5 h-5" />
            Tambah Penumpang
        </a>

        {{-- Option: Kirim Barang --}}
        <a href="#" class="flex items-center gap-3 bg-white rounded-2xl px-6 py-3.5
                  shadow-xl text-primary-700 font-semibold text-sm
                  border border-gray-100 active:scale-95 transition-transform">
            <x-heroicon-o-cube class="w-5 h-5" />
            Kirim Barang
        </a>

    </div>

    {{-- FAB Button --}}
    <button @click="open = !open" :class="open ? 'rotate-45 bg-red-500' : 'bg-primary-700'" class="fixed bottom-5 left-1/2 -translate-x-1/2 z-50
                   w-14 h-14 rounded-full text-white shadow-xl
                   flex items-center justify-center
                   transition-all duration-200 active:scale-95">
        <x-heroicon-o-plus class="w-7 h-7" />
    </button>

</div>