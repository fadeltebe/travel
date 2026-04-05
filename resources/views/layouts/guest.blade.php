<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Travel App') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap"
            rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                font-family: 'Outfit', sans-serif;
            }

            .glass-panel {
                background: rgba(255, 255, 255, 0.85);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border: 1px solid rgba(255, 255, 255, 0.5);
            }

            .dark .glass-panel {
                background: rgba(17, 24, 39, 0.75);
                border: 1px solid rgba(255, 255, 255, 0.1);
            }

            @keyframes blob {
                0% {
                    transform: translate(0px, 0px) scale(1);
                }

                33% {
                    transform: translate(30px, -50px) scale(1.1);
                }

                66% {
                    transform: translate(-20px, 20px) scale(0.9);
                }

                100% {
                    transform: translate(0px, 0px) scale(1);
                }
            }

            .animate-blob {
                animation: blob 7s infinite;
            }

            .animation-delay-2000 {
                animation-delay: 2s;
            }

            .animation-delay-4000 {
                animation-delay: 4s;
            }
        </style>
    </head>

    <body
        class="font-sans text-gray-900 antialiased h-full overflow-hidden bg-gray-50 dark:bg-gray-900 dark:text-gray-100 selection:bg-indigo-500 selection:text-white">

        <!-- Animated Background Elements -->
        <div class="fixed inset-0 z-[-1] overflow-hidden pointer-events-none">
            <!-- Top Right Blob -->
            <div
                class="absolute -top-[10%] -right-[10%] w-[70vw] max-w-[500px] h-[70vw] max-h-[500px] rounded-full bg-purple-400/30 dark:bg-purple-600/20 blur-3xl mix-blend-multiply dark:mix-blend-lighten animate-blob">
            </div>
            <!-- Bottom Left Blob -->
            <div
                class="absolute -bottom-[10%] -left-[10%] w-[60vw] max-w-[400px] h-[60vw] max-h-[400px] rounded-full bg-indigo-400/30 dark:bg-indigo-600/20 blur-3xl mix-blend-multiply dark:mix-blend-lighten animate-blob animation-delay-2000">
            </div>
            <!-- Center Blob -->
            <div
                class="absolute top-[30%] left-[20%] w-[50vw] max-w-[300px] h-[50vw] max-h-[300px] rounded-full bg-pink-400/30 dark:bg-pink-600/20 blur-3xl mix-blend-multiply dark:mix-blend-lighten animate-blob animation-delay-4000">
            </div>
        </div>

        <div class="min-h-full h-full flex flex-col justify-center items-center p-4 sm:p-6 w-full max-w-md mx-auto overflow-y-auto"
            style="-webkit-overflow-scrolling: touch;">

            <!-- Logo Section -->
            <div
                class="w-full flex justify-center mb-8 transform transition hover:scale-105 duration-300 mt-auto pt-8 sm:mt-0 sm:pt-0">
                <a href="/" wire:navigate class="flex flex-col items-center gap-3 group">
                    <div
                        class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 group-hover:shadow-indigo-500/50 transition-all duration-300 group-hover:-translate-y-1">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </div>
                </a>
            </div>

            <!-- Card Section -->
            <div
                class="w-full glass-panel rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.08)] dark:shadow-[0_8px_30px_rgba(0,0,0,0.3)] overflow-hidden relative">
                <!-- Top highlight gradient -->
                <div
                    class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500">
                </div>

                <div class="px-6 py-8 sm:px-10 sm:py-10">
                    {{ $slot }}
                </div>
            </div>

            {{-- <div class="mt-auto pb-4 pt-8 text-center">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 tracking-wide">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Travel') }}. All rights reserved.
                </p>
            </div> --}}
        </div>

    </body>

</html>
