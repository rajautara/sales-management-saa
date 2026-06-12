@php
$groups = [
    'Dashboard & Analytics' => [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['route' => 'reports.index', 'label' => 'Reports', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'roles' => ['admin', 'super-admin']],
    ],
    'Sales Operations' => [
        ['route' => 'quotations.index', 'label' => 'Quotations', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['route' => 'sales-orders.index', 'label' => 'Sales Orders', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
        ['route' => 'delivery-orders.index', 'label' => 'Delivery Orders', 'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'],
        ['route' => 'invoices.index', 'label' => 'Invoices', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
        ['route' => 'payments.index', 'label' => 'Payments', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
        ['route' => 'receipts.index', 'label' => 'Receipts', 'icon' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z'],
    ],
    'Inventory & Supply' => [
        ['route' => 'inventory.index', 'label' => 'Inventory', 'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4'],
        ['route' => 'purchase-orders.index', 'label' => 'Purchase Orders', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
        ['route' => 'suppliers.index', 'label' => 'Suppliers', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
        ['route' => 'customers.index', 'label' => 'Customers', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['route' => 'products.index', 'label' => 'Products', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
        ['route' => 'categories.index', 'label' => 'Categories', 'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'],
        ['route' => 'price-levels.index', 'label' => 'Price Levels', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'roles' => ['admin', 'super-admin']],
        ['route' => 'discounts.index', 'label' => 'Discounts', 'icon' => 'M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', 'roles' => ['admin', 'super-admin']],
    ],
    'Financials & Admin' => [
        ['route' => 'expenses.index', 'label' => 'Expenses', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['route' => 'settings.index', 'label' => 'Settings', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 00-2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'roles' => ['admin', 'super-admin']],
    ]
];
@endphp

<aside class="w-64 bg-gradient-to-b from-slate-950 via-slate-900 to-indigo-950 text-white h-screen sticky top-0 flex flex-col border-r border-slate-800 shadow-xl">
    <div class="h-16 flex items-center px-6 border-b border-slate-800/80 bg-slate-950/20 backdrop-blur-md">
        <div class="flex items-center space-x-3 min-w-0">
            @if (auth()->user()?->company?->logo_path)
                <div class="w-8 h-8 rounded-lg overflow-hidden bg-white shadow-sm flex items-center justify-center flex-shrink-0">
                    <img src="{{ asset('storage/' . auth()->user()->company->logo_path) }}" class="max-w-full max-h-full object-contain" alt="Company Logo">
                </div>
            @else
                <div class="bg-gradient-to-tr from-indigo-600 to-violet-600 p-1.5 rounded-lg shadow-md shadow-indigo-500/10 flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            @endif
            <span class="font-bold text-sm tracking-tight text-white truncate max-w-[140px]" title="{{ auth()->user()?->company?->name ?? config('app.name') }}">
                {{ auth()->user()?->company?->name ?? config('app.name') }}
            </span>
        </div>
    </div>
    
    <nav class="flex-1 px-3 py-4 space-y-5 overflow-y-auto">
        @foreach ($groups as $groupTitle => $links)
            @php
                $visibleLinks = array_filter($links, function($link) {
                    return !isset($link['roles']) || auth()->user()?->hasAnyRole($link['roles']);
                });
            @endphp
            @if (count($visibleLinks) > 0)
                <div class="space-y-1">
                    <h4 class="text-[9px] uppercase font-bold tracking-widest text-slate-500 px-3 mb-2">{{ $groupTitle }}</h4>
                    @foreach ($visibleLinks as $link)
                        @php $active = request()->routeIs($link['route'] . '*'); @endphp
                        <a href="{{ route($link['route']) }}" wire:navigate
                           class="group flex items-center px-3 py-2 text-xs font-semibold rounded-lg transition-all duration-200 hover-translate-up {{ $active ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-600/15' : 'text-slate-400 hover:bg-slate-800/40 hover:text-white' }}">
                            <svg class="w-4 h-4 mr-2.5 flex-shrink-0 transition-transform group-hover:scale-105 {{ $active ? 'text-white' : 'text-slate-500 group-hover:text-slate-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}" />
                            </svg>
                            <span class="group-hover:translate-x-0.5 transition-transform">{{ $link['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        @endforeach
    </nav>
    
    <div class="p-4 border-t border-slate-800/80 bg-slate-950/20 backdrop-blur-md flex flex-col space-y-3">
        <a href="{{ route('profile') }}" wire:navigate class="group flex items-center space-x-3 px-2 py-1 rounded-lg hover:bg-slate-850 transition-colors">
            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-50 to-violet-50 text-indigo-950 flex items-center justify-center font-bold text-xs shadow-md shadow-indigo-500/25 group-hover:scale-105 transition-transform">
                {{ substr(auth()->user()?->name ?? 'U', 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-xs font-semibold text-white truncate group-hover:text-indigo-300 transition-colors">{{ auth()->user()?->name }}</div>
                <div class="text-[10px] text-slate-400 truncate">{{ auth()->user()?->company?->name }}</div>
            </div>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="flex items-center w-full px-3 py-1.5 text-[11px] font-medium text-slate-400 hover:text-rose-400 rounded-lg hover:bg-rose-500/10 transition-colors">
                <svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Log out
            </button>
        </form>
    </div>
</aside>
