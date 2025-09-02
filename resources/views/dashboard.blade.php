<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <flux:heading size="xl">
            {{ __('Welcome') }}
        </flux:heading>

        @unless ($tenantsExist)
        <flux:callout class="w-full max-w-lg" variant="warning">
            <flux:callout.heading icon="exclamation-triangle">
                {{ __('Begin by creating a tenant to provision your first system.') }}
            </flux:callout.heading>

            <x-slot name="actions">
                <flux:button :href="route('tenants.create')" size="sm">{{ __('Start') }}</flux:button>
            </x-slot>
        </flux:callout>
        @endunless
    </div>
</x-layouts.app>
