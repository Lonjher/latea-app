<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{
    darkMode: localStorage.getItem('darkMode') === null ?
        window.matchMedia('(prefers-color-scheme: dark)').matches : localStorage.getItem('darkMode') === 'true'
}" x-init="$watch('darkMode', value => {
    localStorage.setItem('darkMode', value);
    document.documentElement.classList.toggle('dark', value);
})"
    :class="{ 'dark': darkMode }">

<head>
    @include('partials.head')
</head>

<body class="bg-stone-100 text-stone-800 dark:bg-stone-950 dark:text-stone-100">

    <div class="app-shell">

        {{-- ═══════════════ SIDEBAR ═══════════════ --}}
        <aside class="app-sidebar" id="appSidebar">

            {{-- Logo --}}
            <a href="{{ route('dashboard') }}" wire:navigate
                class="h-13.25 flex shrink-0 items-center gap-2.5 border-b border-stone-200 px-5 no-underline dark:border-stone-800">
                <div class="sidebar-logo-icon">
                    <img src="{{ asset('assets/logo.webp') }}" alt="Logo" class="w-8" />
                </div>
                <div class="min-w-0">
                    <div class="sidebar-logo-name">Latea App</div>
                    <div class="sidebar-logo-sub">Latea Point of Sale Information System</div>
                </div>
            </a>

            {{-- Navigation --}}
            <nav class="sidebar-nav">

                <div class="sidebar-group">
                    <div class="sidebar-group-label">{{ __('Platform') }}</div>
                    <a href="{{ route('dashboard') }}" wire:navigate
                        class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            <polyline points="9 22 9 12 15 12 15 22" />
                        </svg>
                        {{ __('Dashboard') }}
                    </a>
                </div>

                <div class="sidebar-group" x-data="{ open: {{ request()->routeIs(['admin.*', 'koordinator.*', 'user.*']) ? 'true' : 'false' }} }">

                    @can('isAdmin')
                        {{-- Label Grup Menu --}}
                        <div class="sidebar-group-label">{{ __('Administrator') }}</div>
                        {{-- Products --}}
                        <a href="{{ route('admin.products') }}" wire:navigate
                            class="sidebar-item {{ request()->routeIs('admin.products') ? 'active' : '' }} flex items-center gap-2 text-[11px]">
                            <svg class="w-4 h-4 text-gray-800 dark:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                                <path
                                    d="M20 7h-.7c.229-.467.349-.98.351-1.5a3.5 3.5 0 0 0-3.5-3.5c-1.717 0-3.215 1.2-4.331 2.481C10.4 2.842 8.949 2 7.5 2A3.5 3.5 0 0 0 4 5.5c.003.52.123 1.033.351 1.5H4a2 2 0 0 0-2 2v2a1 1 0 0 0 1 1h18a1 1 0 0 0 1-1V9a2 2 0 0 0-2-2Zm-9.942 0H7.5a1.5 1.5 0 0 1 0-3c.9 0 2 .754 3.092 2.122-.219.337-.392.635-.534.878Zm6.1 0h-3.742c.933-1.368 2.371-3 3.739-3a1.5 1.5 0 0 1 0 3h.003ZM13 14h-2v8h2v-8Zm-4 0H4v6a2 2 0 0 0 2 2h3v-8Zm6 0v8h3a2 2 0 0 0 2-2v-6h-5Z" />
                            </svg>
                            <span>{{ __('Produk') }}</span>
                        </a>
                        {{-- Stores --}}
                        <a href="{{ route('admin.stores') }}" wire:navigate
                            class="sidebar-item {{ request()->routeIs('admin.stores') ? 'active' : '' }} flex w-full cursor-pointer items-center justify-between py-1.5 text-left text-xs transition-colors">
                            <div class="flex items-center gap-2.5">
                                {{-- Ikon Stores --}}
                                <svg data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z">
                                    </path>
                                </svg>

                                <span class="font-medium">{{ __('Toko') }}</span>
                            </div>
                        </a>

                        {{-- User Navigasi --}}
                        <div>
                            <button @click="open = !open"
                                class="sidebar-item flex w-full cursor-pointer items-center justify-between py-1.5 text-left text-xs transition-colors">
                                <div class="flex items-center gap-2.5">
                                    {{-- Ikon Users --}}
                                    <svg width="800px" height="800px" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="12" cy="12" r="3" stroke="currentColor"
                                            stroke-width="2" />
                                        <path
                                            d="M3.66122 10.6392C4.13377 10.9361 4.43782 11.4419 4.43782 11.9999C4.43781 12.558 4.13376 13.0638 3.66122 13.3607C3.33966 13.5627 3.13248 13.7242 2.98508 13.9163C2.66217 14.3372 2.51966 14.869 2.5889 15.3949C2.64082 15.7893 2.87379 16.1928 3.33973 16.9999C3.80568 17.8069 4.03865 18.2104 4.35426 18.4526C4.77508 18.7755 5.30694 18.918 5.83284 18.8488C6.07287 18.8172 6.31628 18.7185 6.65196 18.5411C7.14544 18.2803 7.73558 18.2699 8.21895 18.549C8.70227 18.8281 8.98827 19.3443 9.00912 19.902C9.02332 20.2815 9.05958 20.5417 9.15224 20.7654C9.35523 21.2554 9.74458 21.6448 10.2346 21.8478C10.6022 22 11.0681 22 12 22C12.9319 22 13.3978 22 13.7654 21.8478C14.2554 21.6448 14.6448 21.2554 14.8478 20.7654C14.9404 20.5417 14.9767 20.2815 14.9909 19.9021C15.0117 19.3443 15.2977 18.8281 15.7811 18.549C16.2644 18.27 16.8545 18.2804 17.3479 18.5412C17.6837 18.7186 17.9271 18.8173 18.1671 18.8489C18.693 18.9182 19.2249 18.7756 19.6457 18.4527C19.9613 18.2106 20.1943 17.807 20.6603 17C20.8677 16.6407 21.029 16.3614 21.1486 16.1272M20.3387 13.3608C19.8662 13.0639 19.5622 12.5581 19.5621 12.0001C19.5621 11.442 19.8662 10.9361 20.3387 10.6392C20.6603 10.4372 20.8674 10.2757 21.0148 10.0836C21.3377 9.66278 21.4802 9.13092 21.411 8.60502C21.3591 8.2106 21.1261 7.80708 20.6601 7.00005C20.1942 6.19301 19.9612 5.7895 19.6456 5.54732C19.2248 5.22441 18.6929 5.0819 18.167 5.15113C17.927 5.18274 17.6836 5.2814 17.3479 5.45883C16.8544 5.71964 16.2643 5.73004 15.781 5.45096C15.2977 5.1719 15.0117 4.6557 14.9909 4.09803C14.9767 3.71852 14.9404 3.45835 14.8478 3.23463C14.6448 2.74458 14.2554 2.35523 13.7654 2.15224C13.3978 2 12.9319 2 12 2C11.0681 2 10.6022 2 10.2346 2.15224C9.74458 2.35523 9.35523 2.74458 9.15224 3.23463C9.05958 3.45833 9.02332 3.71848 9.00912 4.09794C8.98826 4.65566 8.70225 5.17191 8.21891 5.45096C7.73557 5.73002 7.14548 5.71959 6.65205 5.4588C6.31633 5.28136 6.0729 5.18269 5.83285 5.15108C5.30695 5.08185 4.77509 5.22436 4.35427 5.54727C4.03866 5.78945 3.80569 6.19297 3.33974 7C3.13231 7.35929 2.97105 7.63859 2.85138 7.87273"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                    </svg>

                                    <span class="font-medium">{{ __('Akun') }}</span>
                                </div>

                                {{-- Ikon Chevron (Berputar otomatis menggunakan Alpine) --}}
                                <svg :class="open ? 'rotate-180' : ''"
                                    class="h-3 w-3 text-stone-400 transition-transform duration-200 dark:text-stone-500"
                                    fill="currentColor" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-collapse class="mt-0.5 flex flex-col space-y-0.5 pl-4 pr-1"
                                style="display: none;">

                                {{-- Cashier --}}
                                <a href="{{ route('admin.cashiers') }}" wire:navigate
                                    class="sidebar-item {{ request()->routeIs('admin.cashiers') ? 'active' : '' }} flex items-center gap-2 py-1 pl-3 text-[11px]">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.8" stroke="currentColor"
                                        class="h-3.5 w-3.5 shrink-0 text-stone-400 group-[.active]:text-current dark:text-stone-500">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                    <span>{{ __('Kasir') }}</span>
                                </a>

                            </div>
                        </div>

                        {{-- Sales --}}
                        <a href="{{ route('admin.sales') }}" wire:navigate
                            class="sidebar-item {{ request()->routeIs('admin.sales') ? 'active' : '' }} flex w-full cursor-pointer items-center justify-between py-1.5 text-left text-xs transition-colors">
                            <div class="flex items-center gap-2.5">
                                {{-- Ikon Stores --}}
                                <svg fill="currentColor" width="800px" height="800px" viewBox="0 0 52 52"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M23,2.42a1.37,1.37,0,0,1,2,0l10.77,10.4a1.3,1.3,0,0,1,0,1.94L25,25.16a1.37,1.37,0,0,1-2,0l-2-1.94a1.28,1.28,0,0,1,0-1.94L24.37,18a.9.9,0,0,0-.66-1.53H5.46A1.47,1.47,0,0,1,4,15.11V12.33A1.53,1.53,0,0,1,5.46,11H23.71a.89.89,0,0,0,.66-1.53L21,6.16a1.28,1.28,0,0,1,0-1.94Zm-5.8,24.42a1.38,1.38,0,0,0-2,0L4.44,37.24a1.28,1.28,0,0,0,0,1.94L15.2,49.58a1.38,1.38,0,0,0,2,0l2-1.94a1.3,1.3,0,0,0,0-1.94l-3.37-3.26a.89.89,0,0,1,.66-1.52h8.68A13.4,13.4,0,0,1,24.8,38a12.68,12.68,0,0,1,.27-2.63H16.45a.88.88,0,0,1-.66-1.53l3.37-3.26a1.3,1.3,0,0,0,0-1.94ZM28,38a9.6,9.6,0,1,1,9.6,9.6A9.6,9.6,0,0,1,28,38Zm15.62-2.24-6.46,6.45a1.15,1.15,0,0,1-.86.38,1.14,1.14,0,0,1-.86-.38l-3.12-3.12a.56.56,0,0,1,0-.86l.86-.86a.56.56,0,0,1,.86,0l2.26,2.26,5.54-5.54a.56.56,0,0,1,.86,0l.86.86A.55.55,0,0,1,43.62,35.76Z"
                                        fill-rule="evenodd" />
                                </svg>

                                <span class="font-medium">{{ __('Penjualan') }}</span>
                            </div>
                        </a>
                        {{-- Operationals --}}
                        <a href="{{ route('admin.operationals') }}" wire:navigate
                            class="sidebar-item {{ request()->routeIs('admin.operationals') ? 'active' : '' }} flex w-full cursor-pointer items-center justify-between py-1.5 text-left text-xs transition-colors">
                            <div class="flex items-center gap-2.5">
                                {{-- Ikon Operational --}}
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                    aria-label="An expense claim" viewBox="-34.0 -0.0 285.0 285.0">
                                    <g transform="translate(0.000000,285.000000) scale(0.100000,-0.100000)">
                                        <path d="M932 2641 c-26 -27 -38 -49 -56 -103 -5 -16 -19 -18 -104 -18 -83 0
                                                    -102 -3 -115 -18 -10 -10 -17 -26 -17 -34 0 -14 -24 -17 -167 -20 -198 -4
                                                    -228 -14 -270 -85 l-26 -45 7 -566 c3 -312 7 -760 9 -997 3 -472 3 -470 65
                                                    -528 16 -16 47 -32 67 -37 21 -5 378 -6 793 -3 744 6 756 7 795 27 22 12 48
                                                    37 59 56 18 34 18 60 13 615 -13 1378 -14 1433 -29 1468 -35 85 -118 112 -320
                                                    105 l-128 -5 -10 29 c-14 39 -42 48 -149 48 -62 0 -89 4 -89 12 0 28 -44 100
                                                    -71 113 -19 10 -59 15 -128 15 -97 0 -100 -1 -129 -29z m183 -76 c31 -30 31
                                                    -52 3 -82 -27 -29 -71 -30 -98 -3 -32 32 -26 75 15 98 31 18 54 14 80 -13z
                                                    m708 -213 c14 -15 18 -73 26 -412 6 -217 13 -571 16 -787 2 -215 8 -474 11
                                                    -575 5 -155 4 -186 -9 -203 -14 -19 -31 -20 -264 -27 -390 -10 -1209 -9 -1236
                                                    1 -49 19 -45 -78 -61 1654 -3 321 -2 329 18 343 16 11 56 14 168 14 l148 0 0
                                                    -55 c0 -46 3 -56 22 -66 31 -16 819 -3 830 14 4 7 8 33 8 58 0 25 3 49 7 52 3
                                                    4 72 7 153 7 128 0 149 -2 163 -18z M1032 2138 c-7 -7 -12 -22 -12 -34 0 -16
                                                    -8 -24 -28 -29 -80 -17 -132 -80 -132 -160 0 -46 4 -55 38 -87 20 -20 58 -46
                                                    85 -57 l47 -20 0 -66 c0 -77 -10 -82 -80 -35 l-44 29 -23 -21 c-50 -47 -8
                                                    -104 95 -128 47 -11 52 -14 52 -40 0 -49 65 -47 72 2 2 16 15 27 45 38 106 39
                                                    150 138 103 230 -20 40 -82 80 -121 80 -24 0 -26 6 -35 78 -6 61 -6 62 17 62
                                                    13 0 33 -7 43 -15 11 -8 27 -15 36 -15 21 0 50 31 50 52 0 25 -55 65 -100 73
                                                    -33 6 -40 11 -40 29 0 26 -17 46 -40 46 -9 0 -21 -5 -28 -12z m-12 -213 c0
                                                    -59 -4 -64 -32 -38 -23 21 -24 69 0 82 29 17 32 13 32 -44z m123 -213 c22 -19
                                                    22 -50 0 -74 -31 -34 -43 -23 -43 43 0 63 3 65 43 31z M571 1360 c-39 -9 -51
                                                    -24 -51 -62 0 -60 -18 -58 555 -58 585 0 559 -3 553 69 -5 62 2 61 -538 60
                                                    -267 -1 -500 -5 -519 -9z M806 1072 c-171 -1 -264 -6 -273 -13 -20 -17 -16
                                                    -66 7 -89 19 -19 33 -20 388 -20 411 0 412 0 412 64 0 19 -6 38 -12 44 -17 13
                                                    -199 18 -522 14z M1557 948 c-56 -57 -178 -199 -224 -260 -15 -21 -33 -37 -38
                                                    -38 -6 0 -35 25 -65 55 -32 32 -63 55 -75 55 -20 0 -52 -27 -69 -58 -11 -19
                                                    191 -222 220 -222 17 0 50 33 127 128 57 70 127 154 157 187 83 95 104 130 90
                                                    154 -6 11 -24 28 -41 37 l-31 15 -51 -53z"></path>
                                    </g>
                                </svg>
                                <span class="font-medium">{{ __('Operasional') }}</span>
                            </div>
                        </a>
                        {{-- Margin --}}
                        <a href="{{ route('admin.margin') }}" wire:navigate
                            class="sidebar-item {{ request()->routeIs('admin.margin') ? 'active' : '' }} flex w-full cursor-pointer items-center justify-between py-1.5 text-left text-xs transition-colors">
                            <div class="flex items-center gap-2.5">
                                {{-- Ikon Margin --}}
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                    aria-label="A profit margin bar" viewBox="-0.0 -17.0 285.0 285.0">
                                    <g transform="translate(0.000000,251.000000) scale(0.100000,-0.100000)">
                                        <path d="M1955 2325 c-5 -2 -354 -6 -775 -9 -789 -5 -856 -8 -918 -47 -31 -19
                                                    -63 -72 -71 -117 -7 -45 -8 -1786 -1 -1822 15 -68 77 -126 150 -139 38 -7 658
                                                    -8 1580 -3 541 3 588 5 625 21 60 28 104 79 113 131 7 45 10 1574 3 1745 -5
                                                    136 -51 205 -154 232 -26 7 -537 14 -552 8z m565 -90 c19 -12 44 -38 55 -60
                                                    20 -39 20 -60 20 -930 0 -834 -1 -893 -18 -918 -36 -55 -53 -59 -270 -69 -111
                                                    -5 -596 -7 -1077 -6 -859 3 -876 3 -910 23 -74 43 -70 -12 -70 959 0 637 3
                                                    879 12 908 6 22 24 50 39 62 26 22 39 24 211 30 291 11 1251 23 1628 22 327
                                                    -1 347 -2 380 -21z M2095 2094 c-38 -8 -96 -19 -127 -25 -32 -7 -65 -17 -74
                                                    -24 -26 -19 -8 -51 41 -74 25 -12 45 -23 45 -25 0 -4 -157 -129 -209 -167 -90
                                                    -65 -182 -126 -285 -188 -120 -74 -462 -247 -591 -300 -90 -37 -261 -105 -287
                                                    -114 -28 -10 -40 -43 -22 -61 21 -21 29 -20 126 10 437 137 991 433 1298 692
                                                    l65 55 19 -46 c22 -52 49 -68 74 -43 30 30 64 285 42 311 -15 18 -23 17 -115
                                                    -1z M1922 1604 l-22 -15 0 -545 0 -544 -90 0 -90 0 0 383 0 384 -23 21 c-22
                                                    21 -33 22 -173 22 -100 0 -157 -4 -174 -13 l-25 -13 -5 -394 -5 -395 -90 0
                                                    -90 0 -3 255 c-2 225 -4 256 -19 268 -18 13 -287 16 -333 3 l-25 -7 -5 -259
                                                    -5 -260 -141 -3 c-110 -2 -145 -6 -154 -17 -10 -12 -10 -18 0 -30 11 -13 135
                                                    -15 1002 -15 906 0 989 1 995 16 12 33 -11 49 -78 54 l-64 5 -2 544 c-2 390
                                                    -6 547 -14 557 -18 22 -336 20 -367 -2z"></path>
                                    </g>
                                </svg>


                                <span class="font-medium">{{ __('Pendapatan') }}</span>
                            </div>
                        </a>
                    @endcan
                    @can('isCashier')
                        {{-- Operationals --}}
                        <a href="{{ route('user.operationals') }}" wire:navigate
                            class="sidebar-item {{ request()->routeIs('user.operationals') ? 'active' : '' }} flex w-full cursor-pointer items-center justify-between py-1.5 text-left text-xs transition-colors">
                            <div class="flex items-center gap-2.5">
                                {{-- Ikon Operational --}}
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                    aria-label="An expense claim" viewBox="-34.0 -0.0 285.0 285.0">
                                    <g transform="translate(0.000000,285.000000) scale(0.100000,-0.100000)">
                                        <path d="M932 2641 c-26 -27 -38 -49 -56 -103 -5 -16 -19 -18 -104 -18 -83 0
                                                    -102 -3 -115 -18 -10 -10 -17 -26 -17 -34 0 -14 -24 -17 -167 -20 -198 -4
                                                    -228 -14 -270 -85 l-26 -45 7 -566 c3 -312 7 -760 9 -997 3 -472 3 -470 65
                                                    -528 16 -16 47 -32 67 -37 21 -5 378 -6 793 -3 744 6 756 7 795 27 22 12 48
                                                    37 59 56 18 34 18 60 13 615 -13 1378 -14 1433 -29 1468 -35 85 -118 112 -320
                                                    105 l-128 -5 -10 29 c-14 39 -42 48 -149 48 -62 0 -89 4 -89 12 0 28 -44 100
                                                    -71 113 -19 10 -59 15 -128 15 -97 0 -100 -1 -129 -29z m183 -76 c31 -30 31
                                                    -52 3 -82 -27 -29 -71 -30 -98 -3 -32 32 -26 75 15 98 31 18 54 14 80 -13z
                                                    m708 -213 c14 -15 18 -73 26 -412 6 -217 13 -571 16 -787 2 -215 8 -474 11
                                                    -575 5 -155 4 -186 -9 -203 -14 -19 -31 -20 -264 -27 -390 -10 -1209 -9 -1236
                                                    1 -49 19 -45 -78 -61 1654 -3 321 -2 329 18 343 16 11 56 14 168 14 l148 0 0
                                                    -55 c0 -46 3 -56 22 -66 31 -16 819 -3 830 14 4 7 8 33 8 58 0 25 3 49 7 52 3
                                                    4 72 7 153 7 128 0 149 -2 163 -18z M1032 2138 c-7 -7 -12 -22 -12 -34 0 -16
                                                    -8 -24 -28 -29 -80 -17 -132 -80 -132 -160 0 -46 4 -55 38 -87 20 -20 58 -46
                                                    85 -57 l47 -20 0 -66 c0 -77 -10 -82 -80 -35 l-44 29 -23 -21 c-50 -47 -8
                                                    -104 95 -128 47 -11 52 -14 52 -40 0 -49 65 -47 72 2 2 16 15 27 45 38 106 39
                                                    150 138 103 230 -20 40 -82 80 -121 80 -24 0 -26 6 -35 78 -6 61 -6 62 17 62
                                                    13 0 33 -7 43 -15 11 -8 27 -15 36 -15 21 0 50 31 50 52 0 25 -55 65 -100 73
                                                    -33 6 -40 11 -40 29 0 26 -17 46 -40 46 -9 0 -21 -5 -28 -12z m-12 -213 c0
                                                    -59 -4 -64 -32 -38 -23 21 -24 69 0 82 29 17 32 13 32 -44z m123 -213 c22 -19
                                                    22 -50 0 -74 -31 -34 -43 -23 -43 43 0 63 3 65 43 31z M571 1360 c-39 -9 -51
                                                    -24 -51 -62 0 -60 -18 -58 555 -58 585 0 559 -3 553 69 -5 62 2 61 -538 60
                                                    -267 -1 -500 -5 -519 -9z M806 1072 c-171 -1 -264 -6 -273 -13 -20 -17 -16
                                                    -66 7 -89 19 -19 33 -20 388 -20 411 0 412 0 412 64 0 19 -6 38 -12 44 -17 13
                                                    -199 18 -522 14z M1557 948 c-56 -57 -178 -199 -224 -260 -15 -21 -33 -37 -38
                                                    -38 -6 0 -35 25 -65 55 -32 32 -63 55 -75 55 -20 0 -52 -27 -69 -58 -11 -19
                                                    191 -222 220 -222 17 0 50 33 127 128 57 70 127 154 157 187 83 95 104 130 90
                                                    154 -6 11 -24 28 -41 37 l-31 15 -51 -53z"></path>
                                    </g>
                                </svg>
                                <span class="font-medium">{{ __('Operasional') }}</span>
                            </div>
                        </a>
                        {{-- Sales --}}
                        <a href="{{ route('user.sales') }}" wire:navigate
                            class="sidebar-item {{ request()->routeIs('user.sales') ? 'active' : '' }} flex w-full cursor-pointer items-center justify-between py-1.5 text-left text-xs transition-colors">
                            <div class="flex items-center gap-2.5">
                                {{-- Ikon Stores --}}
                                <svg fill="currentColor" width="800px" height="800px" viewBox="0 0 52 52"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M23,2.42a1.37,1.37,0,0,1,2,0l10.77,10.4a1.3,1.3,0,0,1,0,1.94L25,25.16a1.37,1.37,0,0,1-2,0l-2-1.94a1.28,1.28,0,0,1,0-1.94L24.37,18a.9.9,0,0,0-.66-1.53H5.46A1.47,1.47,0,0,1,4,15.11V12.33A1.53,1.53,0,0,1,5.46,11H23.71a.89.89,0,0,0,.66-1.53L21,6.16a1.28,1.28,0,0,1,0-1.94Zm-5.8,24.42a1.38,1.38,0,0,0-2,0L4.44,37.24a1.28,1.28,0,0,0,0,1.94L15.2,49.58a1.38,1.38,0,0,0,2,0l2-1.94a1.3,1.3,0,0,0,0-1.94l-3.37-3.26a.89.89,0,0,1,.66-1.52h8.68A13.4,13.4,0,0,1,24.8,38a12.68,12.68,0,0,1,.27-2.63H16.45a.88.88,0,0,1-.66-1.53l3.37-3.26a1.3,1.3,0,0,0,0-1.94ZM28,38a9.6,9.6,0,1,1,9.6,9.6A9.6,9.6,0,0,1,28,38Zm15.62-2.24-6.46,6.45a1.15,1.15,0,0,1-.86.38,1.14,1.14,0,0,1-.86-.38l-3.12-3.12a.56.56,0,0,1,0-.86l.86-.86a.56.56,0,0,1,.86,0l2.26,2.26,5.54-5.54a.56.56,0,0,1,.86,0l.86.86A.55.55,0,0,1,43.62,35.76Z"
                                        fill-rule="evenodd" />
                                </svg>

                                <span class="font-medium">{{ __('Penjualan') }}</span>
                            </div>
                        </a>
                        {{-- Margin --}}
                        <a href="{{ route('user.margin') }}" wire:navigate
                            class="sidebar-item {{ request()->routeIs('user.margin') ? 'active' : '' }} flex w-full cursor-pointer items-center justify-between py-1.5 text-left text-xs transition-colors">
                            <div class="flex items-center gap-2.5">
                                {{-- Ikon Margin --}}
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                    aria-label="A profit margin bar" viewBox="-0.0 -17.0 285.0 285.0">
                                    <g transform="translate(0.000000,251.000000) scale(0.100000,-0.100000)">
                                        <path d="M1955 2325 c-5 -2 -354 -6 -775 -9 -789 -5 -856 -8 -918 -47 -31 -19
                                                    -63 -72 -71 -117 -7 -45 -8 -1786 -1 -1822 15 -68 77 -126 150 -139 38 -7 658
                                                    -8 1580 -3 541 3 588 5 625 21 60 28 104 79 113 131 7 45 10 1574 3 1745 -5
                                                    136 -51 205 -154 232 -26 7 -537 14 -552 8z m565 -90 c19 -12 44 -38 55 -60
                                                    20 -39 20 -60 20 -930 0 -834 -1 -893 -18 -918 -36 -55 -53 -59 -270 -69 -111
                                                    -5 -596 -7 -1077 -6 -859 3 -876 3 -910 23 -74 43 -70 -12 -70 959 0 637 3
                                                    879 12 908 6 22 24 50 39 62 26 22 39 24 211 30 291 11 1251 23 1628 22 327
                                                    -1 347 -2 380 -21z M2095 2094 c-38 -8 -96 -19 -127 -25 -32 -7 -65 -17 -74
                                                    -24 -26 -19 -8 -51 41 -74 25 -12 45 -23 45 -25 0 -4 -157 -129 -209 -167 -90
                                                    -65 -182 -126 -285 -188 -120 -74 -462 -247 -591 -300 -90 -37 -261 -105 -287
                                                    -114 -28 -10 -40 -43 -22 -61 21 -21 29 -20 126 10 437 137 991 433 1298 692
                                                    l65 55 19 -46 c22 -52 49 -68 74 -43 30 30 64 285 42 311 -15 18 -23 17 -115
                                                    -1z M1922 1604 l-22 -15 0 -545 0 -544 -90 0 -90 0 0 383 0 384 -23 21 c-22
                                                    21 -33 22 -173 22 -100 0 -157 -4 -174 -13 l-25 -13 -5 -394 -5 -395 -90 0
                                                    -90 0 -3 255 c-2 225 -4 256 -19 268 -18 13 -287 16 -333 3 l-25 -7 -5 -259
                                                    -5 -260 -141 -3 c-110 -2 -145 -6 -154 -17 -10 -12 -10 -18 0 -30 11 -13 135
                                                    -15 1002 -15 906 0 989 1 995 16 12 33 -11 49 -78 54 l-64 5 -2 544 c-2 390
                                                    -6 547 -14 557 -18 22 -336 20 -367 -2z"></path>
                                    </g>
                                </svg>


                                <span class="font-medium">{{ __('Pendapatan') }}</span>
                            </div>
                        </a>
                    @endcan
                </div>

                {{-- Slot untuk navigasi tambahan --}}
                @isset($navigation)
                    {{ $navigation }}
                @endisset

            </nav>

        </aside>

        {{-- ═══════════════ MAIN ═══════════════ --}}
        <div class="app-main">

            {{-- Mobile header --}}
            <header class="mobile-header">
                <button class="header-icon-btn rounded-lg shadow-sm" onclick="toggleSidebar()" aria-label="Menu">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                </button>

                <span class="mobile-title">Latea App</span>

                <flux:dropdown position="top" align="end">
                    <button class="header-user">
                        <flux:avatar
                            circle
                            :name="auth()->user()->name"
                            :src="auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : null"
                            size="sm"
                            class="shrink-0"
                        />
                        <div class="header-chevron">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="w-3.5 h-3.5 text-stone-400 dark:text-stone-600">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </div>
                    </button>

                    <flux:menu>
                        {{-- Header user --}}
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="auth()->user()->name"
                                :src="auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : null"
                                color="cyan" />
                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>

                        <flux:menu.separator />

                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                                class="w-full cursor-pointer" data-test="logout-button">
                                {{ __('Log out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </header>

            {{-- Desktop header --}}
            <header class="app-header">

                <div class="header-page-title">
                    @isset($title)
                        {{ $title }}
                    @else
                        {{ __('Dashboard') }}
                    @endisset
                </div>

                <div class="header-actions">

                    {{-- Dark mode toggle --}}
                    <button
                        class="cursor-pointer rounded-lg p-2 text-stone-500 transition-colors hover:bg-stone-100 dark:text-stone-400 dark:hover:bg-stone-800"
                        :aria-label="darkMode ? 'Light Mode' : 'Dark Mode'" @click="darkMode = !darkMode">
                        <svg x-show="!darkMode" class="h-5 w-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg x-show="darkMode" class="h-5 w-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>

                    {{-- Notifications --}}
                    {{-- <button class="header-icon-btn" title="{{ __('Notifikasi') }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" />
                            <path d="M13.73 21a2 2 0 01-3.46 0" />
                        </svg>
                        <span class="notif-badge"></span>
                    </button> --}}

                    <div class="header-divider"></div>

                    {{-- User dropdown --}}
                    <x-desktop-user-menu class="hidden lg:block" avatar="{{ auth()->user()->avatar }}" />
                </div>
            </header>

            {{-- Mobile overlay --}}
            <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

            {{-- Page content --}}
            <main>
                {{ $slot }}
            </main>

        </div>
    </div>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts

    <script>
        function toggleSidebar() {
            document.getElementById('appSidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('open');
        }

        // function toggleDark() {
        //     const isDark = document.documentElement.classList.toggle('dark');
        //     localStorage.setItem('theme', isDark ? 'dark' : 'light');
        //     document.getElementById('iconSun').classList.toggle('hidden', isDark);
        // }

        // Inisialisasi tema
        // (function () {
        //     const saved = localStorage.getItem('theme');
        //     const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        //     const isDark = saved === 'dark' || (!saved && prefersDark);
        //     document.documentElement.classList.toggle('dark', isDark);
        //     document.getElementById('iconSun').classList.toggle('hidden', isDark);
        // })();
    </script>

</body>

</html>
