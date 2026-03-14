@props(['route' => '#', 'label' => '', 'icon' => 'home'])

@php
$isActive = request()->routeIs($route);
@endphp

<a href="{{ route($route) }}" class="flex flex-col items-center justify-center gap-0.5
          {{ $isActive ? 'text-primary-800' : 'text-gray-400' }}
          text-[10px] font-medium transition-all active:scale-90">

    @if($isActive)
    <div class="relative flex items-center justify-center">
        <div class="absolute w-8 h-8 rounded-full bg-primary-50"></div>
        <x-dynamic-component :component="'heroicon-s-' . $icon" class="relative w-5 h-5 text-primary-800" />
    </div>
    @else
    <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-5 h-5" />
    @endif

    <span>{{ $label }}</span>
</a>