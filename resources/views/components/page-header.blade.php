@props([
    'title' => 'Hello',
    'leading' => 'Header',
    'time' => false,
])

<div class="rounded-xl border-b border-stone-200 bg-white px-5 py-3.5 dark:border-stone-800 dark:bg-stone-900">
    <div class="mx-auto max-w-7xl">

        {{-- Baris atas: Manajemen + Jam --}}
        <div class="flex items-center justify-between gap-3"
            x-data="{
                now: new Date(),
                init() {
                    setInterval(() => {
                        this.now = new Date();
                    }, 1000);
                },
                get formattedTime() {
                    return this.now.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false,
                    }).replace(/\./g, ':');
                },
            }">

            {{-- Label Manajemen --}}
            <span
                class="text-sage-600 dark:text-sage-400 font-mono text-[9px] uppercase tracking-widest">Manajemen</span>

            {{-- Jam dalam pill modern --}}
            @if ($time)
                <div
                    class="inline-flex items-center gap-1.5 rounded-full border border-stone-200 bg-stone-50 px-2.5 py-1 dark:border-stone-700 dark:bg-stone-800/60">
                    {{-- Dot indicator (pulse) --}}
                    <span class="relative flex h-1.5 w-1.5">
                        <span
                            class="bg-sage-400 dark:bg-sage-500 absolute inline-flex h-full w-full animate-ping rounded-full opacity-75"></span>
                        <span
                            class="bg-sage-500 dark:bg-sage-400 relative inline-flex h-1.5 w-1.5 rounded-full"></span>
                    </span>

                    {{-- Jam --}}
                    <span
                        class="font-mono text-[11px] font-semibold tabular-nums tracking-tight text-stone-700 dark:text-stone-200"
                        x-text="formattedTime"></span>
                </div>
            @endif
        </div>

        {{-- Title & Leading --}}
        <h1 class="font-display mt-1 text-xl font-semibold text-stone-800 dark:text-stone-100">{{ $title }}</h1>
        <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">{{ $leading }}</p>
    </div>
</div>
