@props([
    'title' => 'Hello',
    'leading' => 'Header',
])
<div class="rounded-xl border-b border-stone-200 bg-white px-5 py-3.5 dark:border-stone-800 dark:bg-stone-900">
    <div class="mx-auto flex max-w-7xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="mb-0.5 flex items-center gap-2">
                <span
                    class="text-sage-600 dark:text-sage-400 font-mono text-[9px] uppercase tracking-widest">Manajemen</span>
            </div>
            <h1 class="font-display text-xl font-semibold text-stone-800 dark:text-stone-100">{{ $title }}</h1>
            <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">{{ $leading }}</p>
        </div>
    </div>
</div>
