<nav class="fixed bottom-0 inset-x-0 z-40 bg-white border-t border-gray-100" style="height: 64px; padding-bottom: env(safe-area-inset-bottom);
            box-shadow: 0 -4px 20px rgba(0,0,0,0.08);">
    <div class="grid grid-cols-5 h-full max-w-lg mx-auto px-2">

        <x-nav.bottom-item route="dashboard" label="Home" icon="home" />
        <x-nav.bottom-item route="schedules.index" label="Jadwal" icon="calendar-days" />

        {{-- FAB Space --}}
        <div></div>

        <x-nav.bottom-item route="cargo.index" label="Cargo" icon="cube" />
        <x-nav.bottom-item route="profile.edit" label="Profil" icon="user" />

    </div>
</nav>