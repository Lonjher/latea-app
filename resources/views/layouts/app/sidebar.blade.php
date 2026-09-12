<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{
    darkMode: localStorage.getItem('darkMode') ?
        localStorage.getItem('darkMode') === 'true' :
        window.matchMedia('(prefers-color-scheme: dark)').matches
}" x-init="$watch('darkMode', val => {
    localStorage.setItem('darkMode', val);
    document.documentElement.classList.toggle('dark', val);
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
                    <div class="sidebar-logo-name">SMILE</div>
                    <div class="sidebar-logo-sub">Sistem Manajemen Inventaris</div>
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

                    @can('isSuperAdminAndAdmin')
                        {{-- Label Grup Menu --}}
                        <div class="sidebar-group-label">{{ __('Administrator') }}</div>
                        {{-- Departemen --}}
                        <a href="{{ route('admin.departemens') }}" wire:navigate
                            class="sidebar-item {{ request()->routeIs('admin.departemens') ? 'active' : '' }} flex w-full cursor-pointer items-center justify-between py-1.5 text-left text-xs transition-colors">
                            <div class="flex items-center gap-2.5">
                                {{-- Ikon Departemens --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor"
                                    class="h-3.5 w-3.5 shrink-0 text-stone-400 group-[.active]:text-current dark:text-stone-500">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636 10.5-3.819" />
                                </svg>

                                <span class="font-medium">{{ __('Departemens') }}</span>
                            </div>
                        </a>

                        {{-- User Navigasi --}}
                        <div>
                            {{-- Dropdown Trigger (User) --}}
                            <button @click="open = !open"
                                class="sidebar-item flex w-full cursor-pointer items-center justify-between py-1.5 text-left text-xs transition-colors">
                                <div class="flex items-center gap-2.5">
                                    {{-- Ikon Users --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4.5 12a7.5 7.5 0 0 0 15 0m-15 0a7.5 7.5 0 1 1 15 0m-15 0H3m16.5 0H21m-1.5 0H12m-8.457 3.077 1.41-.513m14.095-5.13 1.41-.513M5.106 17.785l1.15-.964m11.49-9.642 1.149-.964M7.501 19.795l.75-1.3m7.5-12.99.75-1.3m-6.063 16.658.26-1.477m2.605-14.772.26-1.477m0 17.726-.26-1.477M10.698 4.614l-.26-1.477M16.5 19.794l-.75-1.299M7.5 4.205 12 12m6.894 5.785-1.149-.964M6.256 7.178l-1.15-.964m15.352 8.864-1.41-.513M4.954 9.435l-1.41-.514M12.002 12l-3.75 6.495" />
                                    </svg>

                                    <span class="font-medium">{{ __('User') }}</span>
                                </div>

                                {{-- Ikon Chevron (Berputar otomatis menggunakan Alpine) --}}
                                <svg :class="open ? 'rotate-180' : ''"
                                    class="h-3 w-3 text-stone-400 transition-transform duration-200 dark:text-stone-500"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            {{-- Sub Navigasi Vertikal --}}
                            <div x-show="open" x-collapse class="mt-0.5 flex flex-col space-y-0.5 pl-4 pr-1"
                                style="display: none;">

                                @can('isSuperAdmin')
                                    {{-- Admin --}}
                                    <a href="{{ route('admin.admins') }}" wire:navigate
                                        class="sidebar-item {{ request()->routeIs('admin.admins') ? 'active' : '' }} flex items-center gap-2 py-1 pl-3 text-[11px]">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.8" stroke="currentColor"
                                            class="h-3.5 w-3.5 shrink-0 text-stone-400 group-[.active]:text-current dark:text-stone-500">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                        </svg>
                                        <span>{{ __('Admin') }}</span>
                                    </a>
                                @endcan

                                {{-- Koordinator --}}
                                <a href="{{ route('admin.koordinators') }}" wire:navigate
                                    class="sidebar-item {{ request()->routeIs('admin.koordinators') ? 'active' : '' }} flex items-center gap-2 py-1 pl-3 text-[11px]">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.8" stroke="currentColor"
                                        class="h-3.5 w-3.5 shrink-0 text-stone-400 group-[.active]:text-current dark:text-stone-500">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                                    </svg>
                                    <span>{{ __('Koordinator') }}</span>
                                </a>

                                {{-- User --}}
                                <a href="{{ route('admin.users') }}" wire:navigate
                                    class="sidebar-item {{ request()->routeIs('admin.users') ? 'active' : '' }} flex items-center gap-2 py-1 pl-3 text-[11px]">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.8" stroke="currentColor"
                                        class="h-3.5 w-3.5 shrink-0 text-stone-400 group-[.active]:text-current dark:text-stone-500">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                    </svg>
                                    <span>{{ __('User') }}</span>
                                </a>

                            </div>
                        </div>

                        {{-- Inventaris --}}
                        <a href="{{ route('admin.inventaris') }}" wire:navigate
                            class="sidebar-item {{ request()->routeIs('admin.inventaris') ? 'active' : '' }} flex w-full cursor-pointer items-center justify-between py-1.5 text-left text-xs transition-colors">
                            <div class="flex items-center gap-2.5">
                                {{-- Ikon Inventaris --}}
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M20 9c0 .55-.45 1-1 1h-2v2c0 .55-.45 1-1 1s-1-.45-1-1v-2h-2c-.55 0-1-.45-1-1s.45-1 1-1h2V6c0-.55.45-1 1-1s1 .45 1 1v2h2c.55 0 1 .45 1 1zM4 8h3V3H4v5zm-2 9h5v-7H2v7zm14-2c-.55 0-1 .45-1 1v1H9V6h3c.55 0 1-.45 1-1s-.45-1-1-1H9V2c0-.55-.45-1-1-1H3c-.55 0-1 .45-1 1v6H1c-.55 0-1 .45-1 1v9c0 .55.45 1 1 1h15c.55 0 1-.45 1-1v-2c0-.55-.45-1-1-1z"
                                        fill="currentColor" />
                                </svg>

                                <span class="font-medium">{{ __('Inventaris') }}</span>
                            </div>
                        </a>

                        {{-- Peminjaman --}}
                        <a href="{{ route('admin.peminjaman') }}" wire:navigate
                            class="sidebar-item {{ request()->routeIs('admin.peminjaman') ? 'active' : '' }} flex w-full cursor-pointer items-center justify-between py-1.5 text-left text-xs transition-colors">
                            <div class="flex items-center gap-2.5">
                                {{-- Ikon Peminjamans --}}
                                <svg fill="currentColor" version="1.1" id="Layer_1"
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="{{ request()->routeIs('admin.peminjaman') ? 'w-3.5 h-3.5' : '' }} h-3 w-3"
                                    xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 502.56 502.56"
                                    xml:space="preserve">
                                    <g>
                                        <g>
                                            <g>
                                                <path
                                                    d="M467.28,467.496h24v-128h-24V129.328l12.088,3.888l23.192-46.392L251.28,3.064L0,86.824l23.192,46.392l12.088-3.888  v354.168h-32v16h32h432h32v-16h-32V467.496z M475.28,355.496v96h-8v-96H475.28z M215.6,483.496H107.28v-216h80v184h-64v16h80v-16 v-184v-16v-16h16v180.32c-9.792,8.792-16,21.504-16,35.68C203.28,463.8,207.968,474.992,215.6,483.496z M251.28,483.496 c-17.648,0-32-14.352-32-32s14.352-32,32-32s32,14.352,32,32S268.928,483.496,251.28,483.496z M296.936,309.84l-11.312,11.312 l10.344,10.344l-31.04,31.04l32.464-119.04h92.256l29.6,82.896l-97.728,45.104h-42.928l28.688-28.688l10.344,10.344 l11.312-11.312L296.936,309.84z M236.528,405.84c-0.424,0.136-0.832,0.304-1.248,0.456v-138.8h38.984L236.528,405.84z M286.96,483.496c4.144-4.624,7.4-10.032,9.52-16h77.6c2.12,5.968,5.376,11.376,9.52,16H286.96z M419.28,483.496 c-17.648,0-32-14.352-32-32s14.352-32,32-32s32,14.352,32,32S436.928,483.496,419.28,483.496z M451.28,415.816  c-8.504-7.632-19.696-12.32-32-12.32c-26.472,0-48,21.528-48,48h-72c0-25.648-20.232-46.592-45.56-47.88l4.4-16.12h66.92 l99.608-45.976l4.992,13.976h21.64V415.816z M451.28,339.496h-10.36l-34.288-96h12.648v-16h-152v16h13.528l-2.184,8H235.28v-16 h16v-16h-80v16h16v16h-96v232h-40v-288h400V339.496z M451.28,179.496h-400v-55.312l200-64.28l200,64.288V179.496z M251.28,43.088L31.368,113.776L22.56,96.168l228.72-76.24L480,96.168l-8.808,17.608L251.28,43.088z" />
                                                <rect x="243.28" y="91.496" width="16" height="16" />
                                                <rect x="275.28" y="91.496" width="16" height="16" />
                                                <rect x="211.28" y="91.496" width="16" height="16" />
                                            </g>
                                        </g>
                                    </g>
                                </svg>

                                <span class="font-medium">{{ __('Peminjaman') }}</span>
                            </div>
                        </a>

                        {{-- Navigasi Laporan --}}
                        <div x-data="{ openL: {{ request()->routeIs(['admin.laporan-inventaris', 'admin.laporan-peminjaman']) ? 'true' : 'false' }} }">
                            {{-- Dropdown Trigger (User) --}}
                            <button @click="openL = !openL"
                                class="sidebar-item flex w-full cursor-pointer items-center justify-between py-1.5 text-left text-xs transition-colors">
                                <div class="flex items-center gap-2.5">
                                    {{-- Ikon Laporan --}}
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 40 40" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect width="40" height="40" rx="8" fill="currentColor"
                                            fill-opacity="0.4" />
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M16.7333 7.86667H23.2667C24.482 7.86667 25.5206 8.64758 25.9064 9.73335H28.4C28.8789 9.73335 29.3395 9.91742 29.6865 10.2475C30.0335 10.5776 30.2404 11.0284 30.2643 11.5067L30.2667 11.6V30.2667C30.2667 30.7456 30.0826 31.2062 29.7525 31.5532C29.4225 31.9002 28.9717 32.1071 28.4933 32.131L28.4 32.1333H11.6C11.1211 32.1333 10.6605 31.9493 10.3135 31.6192C9.96649 31.2891 9.75962 30.8383 9.73567 30.36L9.73334 30.2667V11.6C9.73334 11.1211 9.91741 10.6605 10.2475 10.3135C10.5775 9.9665 11.0284 9.75963 11.5067 9.73568L11.6 9.73335H14.0937C14.4794 8.64758 15.518 7.86667 16.7333 7.86667ZM15.8021 10.7289C15.8043 10.6966 15.8047 10.6642 15.8035 10.6317C15.8032 10.6226 15.8027 10.6135 15.8021 10.6045C15.8347 10.1219 16.2432 9.73334 16.7333 9.73334H23.2667C23.7777 9.73334 24.2 10.1557 24.2 10.6667C24.2 11.1777 23.7777 11.6 23.2667 11.6H16.7333C16.2432 11.6 15.8347 11.2115 15.8021 10.7289Z"
                                            fill="white" />
                                        <path
                                            d="M23.8136 18.1332C24.178 17.7688 24.7689 17.7689 25.1333 18.1333V18.1333C25.4978 18.4977 25.4978 19.0886 25.1333 19.4531L19.7265 24.8598C19.5515 25.0348 19.3142 25.1331 19.0667 25.1331C18.8192 25.1331 18.5818 25.0348 18.4068 24.8598L15.3334 21.7864C14.9689 21.422 14.9689 20.8311 15.3334 20.4666V20.4666C15.6978 20.1022 16.2886 20.1021 16.6531 20.4665L19.0667 22.8793L23.8136 18.1332Z"
                                            fill="#B5B5B5" />
                                    </svg>
                                    <span class="font-medium">{{ __('Laporan') }}</span>
                                </div>

                                {{-- Ikon Chevron --}}
                                <svg :class="openL ? 'rotate-180' : ''"
                                    class="h-3.5 w-3.5 text-stone-400 transition-transform duration-200 dark:text-stone-500"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            {{-- Sub Navigasi Vertikal --}}
                            <div x-show="openL" x-collapse class="mt-0.5 flex flex-col space-y-0.5 pl-4 pr-1"
                                style="display: none;">
                                {{-- Laporan Inventaris --}}
                                <a href="{{ route('admin.laporan-inventaris') }}" wire:navigate
                                    class="sidebar-item {{ request()->routeIs('admin.laporan-inventaris') ? 'active' : '' }} flex w-full cursor-pointer items-center justify-between py-1.5 text-left text-xs transition-colors">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 32 32" id="icon"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <defs>
                                                <style>
                                                    .cls-1 {
                                                        fill: currentColor;
                                                    }
                                                </style>
                                            </defs>
                                            <title>inventory-management</title>
                                            <rect x="19" y="24" width="4" height="4" />
                                            <rect x="26" y="24" width="4" height="4" />
                                            <rect x="19" y="17" width="4" height="4" />
                                            <rect x="26" y="17" width="4" height="4" />
                                            <path
                                                d="M17,24H4V10H28v5h2V10a2.0023,2.0023,0,0,0-2-2H22V4a2.0023,2.0023,0,0,0-2-2H12a2.002,2.002,0,0,0-2,2V8H4a2.002,2.002,0,0,0-2,2V24a2.0023,2.0023,0,0,0,2,2H17ZM12,4h8V8H12Z" />
                                            <rect id="_Transparent_Rectangle_" data-name="&lt;Transparent Rectangle&gt;"
                                                class="cls-1" />
                                        </svg>

                                        <span class="font-medium">{{ __('Inventaris') }}</span>
                                    </div>
                                </a>

                                {{-- Laporan Peminjaman --}}
                                <a href="{{ route('admin.laporan-peminjaman') }}" wire:navigate
                                    class="sidebar-item {{ request()->routeIs('admin.laporan-peminjaman') ? 'active' : '' }} flex w-full cursor-pointer items-center justify-between py-1.5 text-left text-xs transition-colors">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M18.18 8.03933L18.6435 7.57589C19.4113 6.80804 20.6563 6.80804 21.4241 7.57589C22.192 8.34374 22.192 9.58868 21.4241 10.3565L20.9607 10.82M18.18 8.03933C18.18 8.03933 18.238 9.02414 19.1069 9.89309C19.9759 10.762 20.9607 10.82 20.9607 10.82M18.18 8.03933L13.9194 12.2999C13.6308 12.5885 13.4865 12.7328 13.3624 12.8919C13.2161 13.0796 13.0906 13.2827 12.9882 13.4975C12.9014 13.6797 12.8368 13.8732 12.7078 14.2604L12.2946 15.5L12.1609 15.901M20.9607 10.82L16.7001 15.0806C16.4115 15.3692 16.2672 15.5135 16.1081 15.6376C15.9204 15.7839 15.7173 15.9094 15.5025 16.0118C15.3203 16.0986 15.1268 16.1632 14.7396 16.2922L13.5 16.7054L13.099 16.8391M13.099 16.8391L12.6979 16.9728C12.5074 17.0363 12.2973 16.9867 12.1553 16.8447C12.0133 16.7027 11.9637 16.4926 12.0272 16.3021L12.1609 15.901M13.099 16.8391L12.1609 15.901"
                                                stroke="#1C274C" stroke-width="1.5" />
                                            <path d="M8 13H10.5" stroke="#1C274C" stroke-width="1.5"
                                                stroke-linecap="round" />
                                            <path d="M8 9H14.5" stroke="#1C274C" stroke-width="1.5"
                                                stroke-linecap="round" />
                                            <path d="M8 17H9.5" stroke="#1C274C" stroke-width="1.5"
                                                stroke-linecap="round" />
                                            <path
                                                d="M3 14V10C3 6.22876 3 4.34315 4.17157 3.17157C5.34315 2 7.22876 2 11 2H13C16.7712 2 18.6569 2 19.8284 3.17157M21 14C21 17.7712 21 19.6569 19.8284 20.8284M4.17157 20.8284C5.34315 22 7.22876 22 11 22H13C16.7712 22 18.6569 22 19.8284 20.8284M19.8284 20.8284C20.7715 19.8853 20.9554 18.4796 20.9913 16"
                                                stroke="#1C274C" stroke-width="1.5" stroke-linecap="round" />
                                        </svg>
                                        <span class="font-medium">{{ __('Peminjaman') }}</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endcan
                    @can('isKoordinator')
                        {{-- Inventaris Koordinator --}}
                        <a href="{{ route('koordinator.inventaris') }}" wire:navigate
                            class="sidebar-item {{ request()->routeIs('koordinator.inventaris') ? 'active' : '' }} flex w-full cursor-pointer items-center justify-between py-1.5 text-left text-xs transition-colors">
                            <div class="flex items-center gap-2.5">
                                {{-- Ikon Inventaris --}}
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    class="{{ request()->routeIs('koordinator.inventaris') ? 'w-4 h-4' : '' }} h-3.5 w-3.5">
                                    <path fill="currentColor"
                                        d="M5.616 21q-.672 0-1.144-.472T4 19.385V8.263q-.43-.178-.715-.577Q3 7.286 3 6.769V4.615q0-.67.472-1.143Q3.944 3 4.616 3h14.769q.67 0 1.143.472q.472.472.472 1.144v2.153q0 .517-.285.916q-.284.4-.715.578v11.122q0 .67-.472 1.143q-.472.472-1.143.472zM5 8.385v10.904q0 .307.221.509T5.77 20h12.616q.269 0 .442-.173t.173-.442v-11zm-.385-1h14.77q.269 0 .442-.173T20 6.769V4.616q0-.27-.173-.443T19.384 4H4.616q-.27 0-.443.１７３T４ ４．６１６v２．１５３q０．２７．１７３．４４２q．１７３．１７３．４４３．１７３m４．７６９ ５．４８２h５．２３V１２h-５．２３zM１２ １４．１９２" />
                                </svg>

                                <span class="font-medium">{{ __('Inventaris') }}</span>
                            </div>
                        </a>
                        {{-- Peminjaman Koordinator --}}
                        <a href="{{ route('koordinator.peminjaman') }}" wire:navigate
                            class="sidebar-item {{ request()->routeIs('koordinator.peminjaman') ? 'active' : '' }} flex w-full cursor-pointer items-center justify-between py-1.5 text-left text-xs transition-colors">
                            <div class="flex items-center gap-2.5">
                                {{-- Ikon Peminjamans --}}
                                <svg fill="currentColor" version="1.1" id="Layer_1"
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="{{ request()->routeIs('koordinator.peminjaman') ? 'w-3.5 h-3.5' : '' }} h-3 w-3"
                                    xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 502.56 502.56"
                                    xml:space="preserve">
                                    <g>
                                        <g>
                                            <g>
                                                <path
                                                    d="M467.28,467.496h24v-128h-24V129.328l12.088,3.888l23.192-46.392L251.28,3.064L0,86.824l23.192,46.392l12.088-3.888  v354.168h-32v16h32h432h32v-16h-32V467.496z M475.28,355.496v96h-8v-96H475.28z M215.6,483.496H107.28v-216h80v184h-64v16h80v-16 v-184v-16v-16h16v180.32c-9.792,8.792-16,21.504-16,35.68C203.28,463.8,207.968,474.992,215.6,483.496z M251.28,483.496 c-17.648,0-32-14.352-32-32s14.352-32,32-32s32,14.352,32,32S268.928,483.496,251.28,483.496z M296.936,309.84l-11.312,11.312 l10.344,10.344l-31.04,31.04l32.464-119.04h92.256l29.6,82.896l-97.728,45.104h-42.928l28.688-28.688l10.344,10.344 l11.312-11.312L296.936,309.84z M236.528,405.84c-0.424,0.136-0.832,0.304-1.248,0.456v-138.8h38.984L236.528,405.84z M286.96,483.496c4.144-4.624,7.4-10.032,9.52-16h77.6c2.12,5.968,5.376,11.376,9.52,16H286.96z M419.28,483.496 c-17.648,0-32-14.352-32-32s14.352-32,32-32s32,14.352,32,32S436.928,483.496,419.28,483.496z M451.28,415.816  c-8.504-7.632-19.696-12.32-32-12.32c-26.472,0-48,21.528-48,48h-72c0-25.648-20.232-46.592-45.56-47.88l4.4-16.12h66.92 l99.608-45.976l4.992,13.976h21.64V415.816z M451.28,339.496h-10.36l-34.288-96h12.648v-16h-152v16h13.528l-2.184,8H235.28v-16 h16v-16h-80v16h16v16h-96v232h-40v-288h400V339.496z M451.28,179.496h-400v-55.312l200-64.28l200,64.288V179.496z M251.28,43.088L31.368,113.776L22.56,96.168l228.72-76.24L480,96.168l-8.808,17.608L251.28,43.088z" />
                                                <rect x="243.28" y="91.496" width="16" height="16" />
                                                <rect x="275.28" y="91.496" width="16" height="16" />
                                                <rect x="211.28" y="91.496" width="16" height="16" />
                                            </g>
                                        </g>
                                    </g>
                                </svg>

                                <span class="font-medium">{{ __('Peminjaman') }}</span>
                            </div>
                        </a>
                        {{-- Laporan Koordinator --}}
                        <a href="{{ route('koordinator.laporan-inventaris') }}" wire:navigate
                            class="sidebar-item {{ request()->routeIs('koordinator.laporan-inventaris') ? 'active' : '' }} flex w-full cursor-pointer items-center justify-between py-1.5 text-left text-xs transition-colors">
                            <div class="flex items-center gap-2.5">
                                {{-- Ikon laporans --}}
                                <svg class="{{ request()->routeIs('koordinator.laporan-inventaris') ? 'w-3.5 h-3.5' : '' }} h-3.5 w-3.5"
                                    viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M18.18 8.03933L18.6435 7.57589C19.4113 6.80804 20.6563 6.80804 21.4241 7.57589C22.192 8.34374 22.192 9.58868 21.4241 10.3565L20.9607 10.82M18.18 8.03933C18.18 8.03933 18.238 9.02414 19.1069 9.89309C19.9759 10.762 20.9607 10.82 20.9607 10.82M18.18 8.03933L13.9194 12.2999C13.6308 12.5885 13.4865 12.7328 13.3624 12.8919C13.2161 13.0796 13.0906 13.2827 12.9882 13.4975C12.9014 13.6797 12.8368 13.8732 12.7078 14.2604L12.2946 15.5L12.1609 15.901M20.9607 10.82L16.7001 15.0806C16.4115 15.3692 16.2672 15.5135 16.1081 15.6376C15.9204 15.7839 15.7173 15.9094 15.5025 16.0118C15.3203 16.0986 15.1268 16.1632 14.7396 16.2922L13.5 16.7054L13.099 16.8391M13.099 16.8391L12.6979 16.9728C12.5074 17.0363 12.2973 16.9867 12.1553 16.8447C12.0133 16.7027 11.9637 16.4926 12.0272 16.3021L12.1609 15.901M13.099 16.8391L12.1609 15.901"
                                        stroke="#1C274C" stroke-width="1.5" />
                                    <path d="M8 13H10.5" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round" />
                                    <path d="M8 9H14.5" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round" />
                                    <path d="M8 17H9.5" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round" />
                                    <path
                                        d="M3 14V10C3 6.22876 3 4.34315 4.17157 3.17157C5.34315 2 7.22876 2 11 2H13C16.7712 2 18.6569 2 19.8284 3.17157M21 14C21 17.7712 21 19.6569 19.8284 20.8284M4.17157 20.8284C5.34315 22 7.22876 22 11 22H13C16.7712 22 18.6569 22 19.8284 20.8284M19.8284 20.8284C20.7715 19.8853 20.9554 18.4796 20.9913 16"
                                        stroke="#1C274C" stroke-width="1.5" stroke-linecap="round" />
                                </svg>

                                <span class="font-medium">{{ __('Laporan') }}</span>
                            </div>
                        </a>
                    @endcan
                    @can('isUser')
                        {{-- Label Grup Menu --}}
                        <div class="sidebar-group-label">{{ __('Manajemen') }}</div>
                        {{-- Peminjaman User --}}
                        <a href="{{ route('user.peminjaman') }}" wire:navigate
                            class="sidebar-item {{ request()->routeIs('user.peminjaman') ? 'active' : '' }} flex w-full cursor-pointer items-center justify-between py-1.5 text-left text-xs transition-colors">
                            <div class="flex items-center gap-2.5">
                                {{-- Ikon Peminjamans --}}
                                <svg fill="currentColor" version="1.1" id="Layer_1"
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="{{ request()->routeIs('user.peminjaman') ? 'w-3.5 h-3.5' : '' }} h-3 w-3"
                                    xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 502.56 502.56"
                                    xml:space="preserve">
                                    <g>
                                        <g>
                                            <g>
                                                <path
                                                    d="M467.28,467.496h24v-128h-24V129.328l12.088,3.888l23.192-46.392L251.28,3.064L0,86.824l23.192,46.392l12.088-3.888  v354.168h-32v16h32h432h32v-16h-32V467.496z M475.28,355.496v96h-8v-96H475.28z M215.6,483.496H107.28v-216h80v184h-64v16h80v-16 v-184v-16v-16h16v180.32c-9.792,8.792-16,21.504-16,35.68C203.28,463.8,207.968,474.992,215.6,483.496z M251.28,483.496 c-17.648,0-32-14.352-32-32s14.352-32,32-32s32,14.352,32,32S268.928,483.496,251.28,483.496z M296.936,309.84l-11.312,11.312 l10.344,10.344l-31.04,31.04l32.464-119.04h92.256l29.6,82.896l-97.728,45.104h-42.928l28.688-28.688l10.344,10.344 l11.312-11.312L296.936,309.84z M236.528,405.84c-0.424,0.136-0.832,0.304-1.248,0.456v-138.8h38.984L236.528,405.84z M286.96,483.496c4.144-4.624,7.4-10.032,9.52-16h77.6c2.12,5.968,5.376,11.376,9.52,16H286.96z M419.28,483.496 c-17.648,0-32-14.352-32-32s14.352-32,32-32s32,14.352,32,32S436.928,483.496,419.28,483.496z M451.28,415.816  c-8.504-7.632-19.696-12.32-32-12.32c-26.472,0-48,21.528-48,48h-72c0-25.648-20.232-46.592-45.56-47.88l4.4-16.12h66.92 l99.608-45.976l4.992,13.976h21.64V415.816z M451.28,339.496h-10.36l-34.288-96h12.648v-16h-152v16h13.528l-2.184,8H235.28v-16 h16v-16h-80v16h16v16h-96v232h-40v-288h400V339.496z M451.28,179.496h-400v-55.312l200-64.28l200,64.288V179.496z M251.28,43.088L31.368,113.776L22.56,96.168l228.72-76.24L480,96.168l-8.808,17.608L251.28,43.088z" />
                                                <rect x="243.28" y="91.496" width="16" height="16" />
                                                <rect x="275.28" y="91.496" width="16" height="16" />
                                                <rect x="211.28" y="91.496" width="16" height="16" />
                                            </g>
                                        </g>
                                    </g>
                                </svg>

                                <span class="font-medium">{{ __('Peminjaman') }}</span>
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
                <button class="header-icon-btn" onclick="toggleSidebar()" aria-label="Menu">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                </button>
                <span class="mobile-title">SMILE</span>
                <flux:dropdown position="top" align="end">
                    <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />
                    <flux:menu>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                        <flux:menu.separator />
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                                class="w-full cursor-pointer">
                                {{ __('Log out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </header>

            {{-- Desktop header --}}
            <header class="app-header">

                <div class="header-page-title">
                    @isset($heading)
                        {{ $heading }}
                    @else
                        {{ __('Dashboard') }}
                    @endisset
                </div>

                <div class="header-actions">

                    {{-- Dark mode toggle --}}
                    <button x-data variant="segmented" x-model="$flux.appearance"
                        class="cursor-pointer rounded-lg p-2 text-stone-500 transition-colors hover:bg-stone-100 dark:text-stone-400 dark:hover:bg-stone-800"
                        :aria-label="darkMode ? 'Dark Mode' : 'Light Mode'" @click="darkMode = !darkMode">
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
                    <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->initials()" />
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
