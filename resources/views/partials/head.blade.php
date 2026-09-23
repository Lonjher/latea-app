<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title . ' — ' . config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

{{-- <link rel="icon" href="/favicon.ico" sizes="any"> --}}
<link rel="icon" type="image/png" href="{{ asset('assets/logo.webp') }}">
{{-- <link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png"> --}}

<script>
    const applyStoredTheme = () => {
        const savedTheme = localStorage.getItem('darkMode');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.classList.toggle('dark', savedTheme === null ? prefersDark : savedTheme === 'true');
    };

    const themeObserver = new MutationObserver(applyStoredTheme);

    applyStoredTheme();
    themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    document.addEventListener('livewire:navigate', applyStoredTheme);
    document.addEventListener('livewire:navigating', () => {
        applyStoredTheme();
        document.documentElement.dataset.themeNavigation = 'true';
    });
    document.addEventListener('livewire:navigated', () => {
        applyStoredTheme();
        requestAnimationFrame(() => delete document.documentElement.dataset.themeNavigation);
    });
</script>

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
