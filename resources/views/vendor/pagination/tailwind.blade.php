@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}"
        class="flex select-none items-center justify-between border-t border-stone-100 px-4 py-2.5 text-xs text-stone-600 dark:border-stone-800 dark:text-stone-400">

        {{-- Sisi Kiri: Meta Informasi Data (Lebih Besar & Jelas) --}}
        <div>
            {{ __('Menampilkan') }}
            @if ($paginator->firstItem())
                <span class="font-semibold text-stone-800 dark:text-stone-200">{{ $paginator->firstItem() }}</span>
                {{ __('sampai') }}
                <span class="font-semibold text-stone-800 dark:text-stone-200">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            {{ __('dari') }}
            <span class="font-semibold text-stone-800 dark:text-stone-200">{{ $paginator->total() }}</span>
            {{ __('data') }}
        </div>

        {{-- Sisi Kanan: Kontrol Navigasi Angka & Panah --}}
        <div class="flex items-center gap-1.5">

            {{-- Tombol Sebelumnya (Panah Kiri) --}}
            @if ($paginator->onFirstPage())
                <span class="cursor-not-allowed p-1.5 text-stone-300 dark:text-stone-700" aria-hidden="true">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </span>
            @else
                <button wire:click="previousPage" wire:loading.attr="disabled"
                    class="cursor-pointer rounded-md p-1.5 text-stone-600 transition-colors hover:bg-stone-100 dark:text-stone-400 dark:hover:bg-stone-800"
                    aria-label="{{ __('pagination.previous') }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </button>
            @endif

            {{-- Elemen Angka --}}
            <div class="flex items-center gap-1">
                @foreach ($elements as $element)
                    {{-- Pembatas Tiga Titik (...) --}}
                    @if (is_string($element))
                        <span
                            class="flex h-7 min-w-7 cursor-pointer items-center justify-center text-stone-400 dark:text-stone-600">
                            {{ $element }}
                        </span>
                    @endif

                    {{-- Link Angka Halaman --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                {{-- Halaman Aktif: BOLD & Berwarna Sage --}}
                                <span aria-current="page"
                                    class="text-sage-600 dark:text-sage-400 bg-sage-50 dark:bg-sage-950/60 border-sage-200/60 dark:border-sage-900/50 flex h-7 min-w-7 cursor-pointer items-center justify-center rounded-md border font-bold">
                                    {{ $page }}
                                </span>
                            @else
                                {{-- Halaman Biasa: Menggunakan wire:click untuk mencegah 404 --}}
                                <button wire:click="gotoPage({{ $page }})"
                                    class="flex h-7 min-w-7 cursor-pointer items-center justify-center rounded-md text-stone-600 transition-colors hover:bg-stone-100 dark:text-stone-400 dark:hover:bg-stone-800">
                                    {{ $page }}
                                </button>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Tombol Selanjutnya (Panah Kanan) --}}
            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" wire:loading.attr="disabled"
                    class="cursor-pointer rounded-md p-1.5 text-stone-600 transition-colors hover:bg-stone-100 dark:text-stone-400 dark:hover:bg-stone-800"
                    aria-label="{{ __('pagination.next') }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            @else
                <span class="cursor-not-allowed p-1.5 text-stone-300 dark:text-stone-700" aria-hidden="true">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </span>
            @endif

        </div>
    </nav>
@endif
