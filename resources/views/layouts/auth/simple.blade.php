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

<body class="dark:bg-linear-to-b bg-white antialiased dark:from-neutral-950 dark:to-neutral-900">
    {{ $slot }}

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>

</html>
