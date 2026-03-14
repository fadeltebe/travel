@props(['title' => 'Dashboard'])

<x-app-layout :title="$title">

    <x-nav.top-bar :title="$title" />

    {{-- safe-bottom class = padding bawah otomatis sesuai tinggi bottom bar --}}
    <main class="min-h-screen pt-11 safe-bottom bg-gray-50">
        {{ $slot }}
    </main>

    <x-nav.bottom-bar />
    <x-nav.fab-menu />

</x-app-layout>