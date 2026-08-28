@php
    $company = auth()->user()?->company ?? \App\Models\Company::first();
    $companyName = $company?->name ?? config('app.name', 'Sales SaaS');
    $logoPath = $company?->logo_path;
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
    <body class="font-sans text-slate-800 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-slate-50 via-indigo-50/30 to-slate-100">
            <div class="mb-6">
                <a href="/" wire:navigate>
                    <div class="flex items-center space-x-3">
                        @if ($logoPath)
                            <div class="w-10 h-10 rounded-xl overflow-hidden bg-white shadow-md flex items-center justify-center flex-shrink-0">
                                <img src="{{ asset('storage/' . $logoPath) }}" class="max-w-full max-h-full object-contain" alt="Logo">
                            </div>
                        @else
                            <div class="bg-gradient-to-tr from-indigo-600 to-violet-600 p-2.5 rounded-xl shadow-md shadow-indigo-600/10">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        @endif
                        <span class="text-2xl font-bold tracking-tight text-slate-900 bg-clip-text bg-gradient-to-r from-slate-900 to-indigo-950">{{ $companyName }}</span>
                    </div>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-2 px-4 sm:px-8 py-8 bg-white border border-slate-100 shadow-[0_12px_40px_rgba(0,0,0,0.03)] overflow-hidden sm:rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
