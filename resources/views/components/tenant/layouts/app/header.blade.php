<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('tenant.partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:brand
                href="{{ route('tenant.dashboard') }}"
                name="{{ config('app.name') }}"
                class="ml-4"
            >
                <x-slot name="logo">
                    <div class="flex aspect-square size-6 p-1 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
                        <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
                    </div>
                </x-slot>
            </flux:brand>

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="layout-grid" :href="route('tenant.dashboard')" :current="request()->routeIs('tenant.dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:navbar.item>
                @can(\App\Enums\Tenant\Permission::ManageApplicationUsers)
                <flux:navbar.item icon="users" :href="route('tenant.users.index')" :current="request()->routeIs('tenant.users.*')" wire:navigate>
                    {{ __('Users') }}
                </flux:navbar.item>
                @endcan
                @can(\App\Enums\Tenant\Permission::ManageApplicationSettings)
                <flux:navbar.item icon="wrench-screwdriver" :href="route('tenant.settings.general')" :current="request()->routeIs('tenant.settings.general')" wire:navigate>
                    {{ __('Configuration') }}
                </flux:navbar.item>
                @endcan
            </flux:navbar>

            <flux:spacer />

            <!-- Desktop User Menu -->
            <flux:dropdown position="top" align="end">
                <flux:profile
                    class="cursor-pointer"
                    :initials="auth()->user()->initials()"
                    :avatar="auth()->user()->avatar"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:radio.group x-data variant="segmented" x-model="$flux.appearance" class="my-2">
                        <flux:radio value="light" icon="sun"></flux:radio>
                        <flux:radio value="dark" icon="moon"></flux:radio>
                        <flux:radio value="system" icon="computer-desktop"></flux:radio>
                    </flux:radio.group>

                    <flux:menu.separator />
                    
                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('tenant.settings.profile')" icon="cog" wire:navigate>{{ __('Preferences') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('tenant.logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar stashable sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <flux:sidebar.brand
                href="{{ route('tenant.dashboard') }}"
                name="{{ config('app.name') }}"
            >
                <x-slot name="logo">
                    <div class="flex aspect-square size-6 p-1 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
                        <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
                    </div>
                </x-slot>
            </flux:sidebar.brand>

            <flux:navlist variant="outline">
                <flux:navlist.group>
                    <flux:navlist.item icon="layout-grid" :href="route('tenant.dashboard')" :current="request()->routeIs('tenant.dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                    </flux:navlist.item>
                    @can(\App\Enums\Tenant\Permission::ManageApplicationUsers)
                    <flux:navlist.item icon="users" :href="route('tenant.users.index')" :current="request()->routeIs('tenant.users.*')" wire:navigate>
                        {{ __('Users') }}
                    </flux:navlist.item>
                    @endcan
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <flux:navlist variant="outline">
                @can(\App\Enums\Tenant\Permission::ManageApplicationSettings)
                <flux:navlist.item icon="wrench-screwdriver" :href="route('tenant.settings.general')" :current="request()->routeIs('tenant.settings.general')" wire:navigate>
                    {{ __('Configuration') }}
                </flux:navlist.item>
                @endcan
            </flux:navlist>
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
