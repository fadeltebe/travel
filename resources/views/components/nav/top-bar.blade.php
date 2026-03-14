@props(['title' => 'Dashboard'])

<header class="fixed top-0 inset-x-0 z-40 bg-primary-700 text-white" style="height: 56px;">
    <div class="flex items-center justify-between h-full px-4">

        {{-- Title --}}
        <h1 class="text-base font-semibold tracking-tight truncate">
            {{ $title }}
        </h1>

        {{-- Right: User Avatar --}}
        <div class="flex items-center gap-2 shrink-0">
            <span class="text-xs text-primary-200 hidden sm:block">
                {{ auth()->user()->name }}
            </span>
            <div class="w-8 h-8 rounded-full bg-primary-500 border-2 border-primary-400
                        flex items-center justify-center
                        text-sm font-bold cursor-pointer
                        hover:bg-primary-400 transition-colors">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
        </div>

    </div>
</header>