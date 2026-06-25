@php
    $companyName = auth()->user()?->company?->name ?? config('app.name', 'Sales SaaS');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $companyName }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-800 bg-[#f8fafc]">
        <div class="flex min-h-screen" x-data="{ collapsed: $persist(false).as('sidebar_collapsed'), mobileOpen: false }">
            <x-sidebar />

            {{-- Mobile backdrop overlay --}}
            <div x-show="mobileOpen" @click="mobileOpen = false" x-transition.opacity
                 class="fixed inset-0 bg-black/50 z-30 lg:hidden" style="display: none;"></div>

            <div class="flex-1 flex flex-col min-w-0">
                {{-- Mobile top bar with hamburger --}}
                <div class="lg:hidden sticky top-0 z-20 flex items-center h-14 px-4 bg-white border-b border-slate-200 shadow-sm">
                    <button type="button" @click="mobileOpen = true"
                            class="p-2 -ml-2 rounded-lg text-slate-600 hover:bg-slate-100 transition-colors" aria-label="Open menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <span class="ml-2 font-bold text-sm text-slate-800 truncate">{{ $companyName }}</span>
                </div>

                @if (isset($header))
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <main class="flex-1 p-4 sm:p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
