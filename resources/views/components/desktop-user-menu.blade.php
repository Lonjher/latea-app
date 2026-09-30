<flux:dropdown position="bottom" align="end">
    <button class="header-user">
        <flux:avatar
            circle
            :name="auth()->user()->name"
            :src="auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : null"
            size="sm"
            class="shrink-0"
        />

        <div class="text-left">
            <div class="header-user-name">{{ auth()->user()->name }}</div>
            <div class="header-user-role">{{ __(auth()->user()->role?->nama_role ?? '') }}</div>
        </div>

        <div class="header-chevron">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round"
                class="w-3.5 h-3.5 text-stone-400 dark:text-stone-600">
                <polyline points="6 9 12 15 18 9" />
            </svg>
        </div>
    </button>

    <flux:menu>
        {{-- Header: info user --}}
        <div class="flex items-center gap-2 px-1 py-1.5">
            <flux:avatar
                :name="auth()->user()->name"
                :src="auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : null"
            />
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
