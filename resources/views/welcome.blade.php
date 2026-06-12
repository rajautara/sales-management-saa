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

        <title>{{ $companyName }} - SaaS Sales Management Platform</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-100 bg-slate-950 overflow-x-hidden min-h-screen flex flex-col justify-between selection:bg-indigo-500 selection:text-white relative">
        <!-- Radial Gradient Background Blobs -->
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-indigo-500/10 rounded-full blur-[120px] pointer-events-none -mr-40 -mt-20"></div>
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-violet-500/10 rounded-full blur-[120px] pointer-events-none -ml-40 -mb-20"></div>

        <!-- Header -->
        <header class="w-full max-w-7xl mx-auto px-6 lg:px-8 py-6 flex justify-between items-center z-10">
            <!-- Brand Logo -->
            <div class="flex items-center space-x-3">
                @if ($logoPath)
                    <div class="w-8 h-8 rounded-lg overflow-hidden bg-white shadow-sm flex items-center justify-center flex-shrink-0">
                        <img src="{{ asset('storage/' . $logoPath) }}" class="max-w-full max-h-full object-contain" alt="Company Logo">
                    </div>
                @else
                    <div class="bg-gradient-to-tr from-indigo-600 to-violet-600 p-2 rounded-xl shadow-lg shadow-indigo-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                @endif
                <span class="font-bold text-xl tracking-tight text-white bg-clip-text bg-gradient-to-r from-white to-slate-200">
                    {{ $companyName }}
                </span>
            </div>

            <!-- Navigation Links -->
            <div class="flex items-center space-x-4">
                @auth
                    <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-4 py-2 border border-slate-700/60 rounded-xl text-sm font-semibold text-white bg-slate-900/60 backdrop-blur-md hover:bg-slate-800/80 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200 shadow-sm">
                        Go to Dashboard
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-300 hover:text-white transition-colors py-2 px-3">
                        Log in
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-tr from-indigo-600 to-violet-600 border border-transparent rounded-xl text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200">
                            Get Started
                        </a>
                    @endif
                @endauth
            </div>
        </header>

        <!-- Main Content (Hero) -->
        <main class="w-full max-w-7xl mx-auto px-6 lg:px-8 py-12 lg:py-24 flex-1 flex flex-col lg:flex-row items-center justify-between gap-12 z-10">
            <div class="flex-1 text-center lg:text-left space-y-8 max-w-2xl">
                <!-- Premium Badge -->
                <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-semibold tracking-wide">
                    <span>✨</span>
                    <span>All-in-one SaaS Sales Portal</span>
                </div>

                <!-- Main Headline -->
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight tracking-tight">
                    Streamline Your <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400">SaaS Sales</span> Operations
                </h1>

                <!-- Subtitle -->
                <p class="text-base sm:text-lg text-slate-400 leading-relaxed max-w-xl mx-auto lg:mx-0">
                    Create quotes, dispatch delivery orders, manage invoices, collect payments, and track inventory. Share signed billing links with your customers instantly on WhatsApp.
                </p>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3.5 bg-gradient-to-r from-indigo-600 to-violet-600 border border-transparent rounded-xl font-bold text-white shadow-xl shadow-indigo-600/25 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200">
                            Go to Dashboard
                            <svg class="w-5 h-5 ml-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3.5 bg-gradient-to-r from-indigo-600 to-violet-600 border border-transparent rounded-xl font-bold text-white shadow-xl shadow-indigo-600/25 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200">
                            Start Free Trial
                        </a>
                        <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3.5 border border-slate-800 rounded-xl font-bold text-slate-300 bg-slate-900/40 hover:bg-slate-800/60 hover:text-white transition-all duration-200">
                            Sign In to Workspace
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Features Card Grid / Preview Section -->
            <div class="flex-1 w-full max-w-xl grid grid-cols-1 gap-6">
                <!-- Feature 1 -->
                <div class="p-6 bg-slate-900/50 border border-slate-800/80 rounded-2xl flex items-start space-x-4 backdrop-blur-md shadow-lg group hover:border-slate-700/80 transition-all duration-300 hover:translate-y-[-2px]">
                    <div class="bg-gradient-to-tr from-indigo-500/10 to-indigo-500/20 p-3 rounded-xl border border-indigo-500/30 text-indigo-400 flex-shrink-0 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white mb-2">Quotation & Invoice Pipeline</h3>
                        <p class="text-sm text-slate-400 leading-relaxed">
                            Generate beautiful quotation proposals and seamlessly convert them to sales orders, delivery receipts, and invoices with a single tap.
                        </p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="p-6 bg-slate-900/50 border border-slate-800/80 rounded-2xl flex items-start space-x-4 backdrop-blur-md shadow-lg group hover:border-slate-700/80 transition-all duration-300 hover:translate-y-[-2px]">
                    <div class="bg-gradient-to-tr from-violet-500/10 to-violet-500/20 p-3 rounded-xl border border-violet-500/30 text-violet-400 flex-shrink-0 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white mb-2">WhatsApp Smart Share</h3>
                        <p class="text-sm text-slate-400 leading-relaxed">
                            Instantly dispatch custom billing links with unique signed URLs. Customers can review invoices or receipts without requiring accounts.
                        </p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="p-6 bg-slate-900/50 border border-slate-800/80 rounded-2xl flex items-start space-x-4 backdrop-blur-md shadow-lg group hover:border-slate-700/80 transition-all duration-300 hover:translate-y-[-2px]">
                    <div class="bg-gradient-to-tr from-pink-500/10 to-pink-500/20 p-3 rounded-xl border border-pink-500/30 text-pink-400 flex-shrink-0 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white mb-2">Real-Time Dashboards</h3>
                        <p class="text-sm text-slate-400 leading-relaxed">
                            Monitor revenue, expense metrics, inventory status thresholds, and customer balances instantly through rich charts and reports.
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full max-w-7xl mx-auto px-6 lg:px-8 py-8 border-t border-slate-900/80 text-center text-xs text-slate-500 z-10 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                &copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.
            </div>
            <div class="flex items-center space-x-4">
                <span>Laravel v{{ Illuminate\Foundation\Application::VERSION }}</span>
                <span class="text-slate-800">&bull;</span>
                <span>PHP v{{ PHP_VERSION }}</span>
            </div>
        </footer>
    </body>
</html>
