<x-layouts.app :title="__('general.dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <flux:heading size="xl">
            {{ __('general.welcome') }}
        </flux:heading>

        @unless ($tenantsExist)
        <flux:callout class="w-full max-w-lg" variant="warning">
            <flux:callout.heading icon="exclamation-triangle">
                {{ __('messages.begin_by_creating_a_tenant') }}
            </flux:callout.heading>

            <x-slot name="actions">
                <flux:button :href="route('tenants.create')" size="sm">{{ __('general.start') }}</flux:button>
            </x-slot>
        </flux:callout>
        @endunless
    </div>
</x-layouts.app>
