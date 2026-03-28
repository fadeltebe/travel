@props(['passenger'])

<a href="{{ route('passengers.show', $passenger) }}"
    class="block bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all overflow-hidden relative">

    {{-- Status Lunas/Belum di Kanan Atas --}}
    <div class="absolute top-0 right-0 flex flex-col items-end">
        <span
            class="text-[9px] font-bold px-3 py-1 rounded-bl-lg text-white shadow-sm {{ $passenger->booking->payment_status === 'paid' ? 'bg-emerald-500' : 'bg-red-500' }}">
            {{ $passenger->booking->payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}
        </span>
    </div>

    {{-- Baris 1: Ikon Avatar, Nama, HP, & Kode Booking --}}
    <div class="flex items-start gap-3 p-3 pb-2 pr-24">
        {{-- Ikon User (Kiri) --}}
        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 bg-emerald-50">
            <x-heroicon-s-user class="w-4 h-4 text-emerald-600" />
        </div>

        <div class="min-w-0">
            {{-- Baris 1 Sebelah Ikon: Nama (Bold) --}}
            <p class="text-xs text-gray-900 leading-tight font-bold">
                {{ $passenger->name }}
            </p>

            {{-- Baris 2 Sebelah Ikon: Nomor HP | Kode Booking --}}
            <p class="text-[10px] text-gray-500 font-semibold mt-0.5 flex items-center gap-1">
                <x-heroicon-o-device-phone-mobile class="w-3 h-3 inline-block" />
                {{ $passenger->phone ?? '-' }}

                <span class="text-gray-300">|</span>

                <x-heroicon-o-ticket class="w-3 h-3 inline-block" />
                <span class="font-black text-gray-800">{{ $passenger->booking->booking_code ?? 'N/A' }}</span>
            </p>
        </div>
    </div>

    {{-- Baris 2: Data Rute & Tanggal (Di bawah baris pertama) --}}
    <div class="px-3 pb-2 flex items-center justify-between text-[10px]">
        <div class="text-gray-600 truncate mr-2 flex items-center gap-1">
            <x-heroicon-o-map-pin class="w-3 h-3 text-emerald-600 inline-block" />
            <span class="font-black text-gray-800">
                {{ $passenger->booking->schedule->route->originAgent->city ?? '-' }} →
                {{ $passenger->booking->schedule->route->destinationAgent->city ?? '-' }}
            </span>

            <span class="text-gray-300 mx-1">|</span>

            <x-heroicon-o-calendar class="w-3 h-3 text-emerald-600 inline-block" />
            {{ \Carbon\Carbon::parse($passenger->booking->schedule->departure_date)->toIndoDate() }}
        </div>
    </div>

    {{-- Baris 3 (Paling Bawah): Nomor Kursi & Harga --}}
    <div class="px-3 pb-2 pt-2 flex items-center justify-between gap-2 border-t border-gray-50 mt-1 bg-gray-50/50">
        {{-- Kiri: Badge Nomor Kursi --}}
        <div
            class="flex items-center gap-1 text-[10px] font-black px-2 py-1 rounded bg-emerald-100 text-emerald-700 shadow-sm">
            KURSI: {{ $passenger->seat_number ?? 'N/A' }}
        </div>

        {{-- Kanan: Harga Tiket (Warna Oranye) --}}
        <span class="font-black text-orange-500 shrink-0 text-xs">
            Rp{{ number_format($passenger->booking->total_price ?? 0, 0, ',', '.') }}
        </span>
    </div>
</a>
