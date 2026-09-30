<x-layouts::app :title="__('Dashboard')">
    @can('isAdmin')
        <livewire:dashboard />
    @endcan
    @can('isCashier')
        <livewire:user.dashboard/>
    @endcan
</x-layouts::app>
