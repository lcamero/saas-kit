<x-tenant.layouts.app.sidebar :title="$title ?? null">
    <x-tenant.tenant-banner />

    <flux:main container>
        {{ $slot }}
    </flux:main>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist
</x-tenant.layouts.app.sidebar>
