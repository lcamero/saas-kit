<x-tenant.layouts.app.sidebar :title="$title ?? null">
    <x-tenant.tenant-banner />

    <flux:main container>
        {{ $slot }}
    </flux:main>
</x-tenant.layouts.app.sidebar>
