@props(['title' => 'Dashboard'])

<x-app-layout :title="$title">

    {{-- Top Bar --}}
    <x-nav.top-bar :title="$title" />

    {{-- Main Content Area --}}
    <main class="min-h-screen pt-8 pb-15">
        {{-- pt-8 = tinggi top bar --}}
        {{-- pb-15 = tinggi bottom bar --}}
        {{ $slot }}
    </main>

    {{-- Bottom Navigation --}}
    <x-nav.bottom-bar />

    {{-- FAB Button + Menu --}}
    <x-nav.fab-menu />

</x-app-layout>