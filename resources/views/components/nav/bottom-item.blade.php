@props([
'route' => '#',
'label' => '',
'icon' => 'home',
])

@php
$isActive = request()->routeIs($route);
$color = $isActive ? 'text-primary-700' : 'text-gray-400';
@endphp

<a href="{{ route($route) }}" class="flex flex-col items-center justify-center gap-0.5 {{ $color }}
          text-[10px] font-medium transition-colors active:scale-95">
    <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-6 h-6" />
    <span>{{ $label }}</span>
</a>