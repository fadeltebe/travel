<nav class="fixed bottom-0 inset-x-0 z-40 bg-white border-t border-gray-200" style="height: 60px; padding-bottom: env(safe-area-inset-bottom);">
    <div class="grid grid-cols-5 h-full max-w-lg mx-auto">

        {{-- Home --}}
        <x-nav.bottom-item route="dashboard" label="Home" icon="home" />

        {{-- Jadwal --}}
        <x-nav.bottom-item route="schedules.index" label="Jadwal" icon="calendar-days" />

        {{-- FAB Placeholder (ruang kosong) --}}
        <div></div>

        {{-- Cargo --}}
        <x-nav.bottom-item route="cargo.index" label="Cargo" icon="cube" />

        {{-- Profil --}}
        <x-nav.bottom-item route="profile.edit" label="Profil" icon="user" />

    </div>
</nav>