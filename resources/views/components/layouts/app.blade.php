<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main container>
        {{ $slot }}
    </flux:main>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist
</x-layouts.app.sidebar>
