<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new class extends Component {
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255|unique:tenants,email')]
    public string $email = '';
    
    #[Validate('required|string|max:255|unique:domains,domain')]
    public string $domain = '';

    public function mount()
    {
        $this->authorize(\App\Enums\Tenant\Permission::ManageApplicationUsers);
    }

    public function create(): void
    {
        $this->validate();

        $tenant = Tenant::create([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        // Create a domain for the tenant
        $tenant->domains()->create(['domain' => $this->domain]);

        $this->redirect(route('tenants.index'), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="create" class="space-y-4">
        <div class="space-y-12">
            <flux:heading size="xl">{{ __('Create Tenant') }}</flux:heading>
            <flux:card class="w-full lg:w-4/5">
                <div class="grid grid-cols-2 gap-y-6">
                    <flux:input :label="__('Name')" wire:model="name" class="max-w-sm" required :badge="__('Required')" />
                    <flux:input :label="__('Email')" wire:model="email" class="max-w-sm" type="email" required :badge="__('Required')" />
                    <flux:field>
                        <flux:label :badge="__('Required')">{{ __('Domain') }}</flux:label>
                        <flux:input.group>
                            <flux:input wire:model="domain" class="col-span-2 max-w-sm" required />
                            <flux:input.group.suffix>.{{ parse_url(config('app.url'))['host'] }}</flux:input.group.suffix>
                        </flux:input.group>
                        <flux:error name="domain" />
                    </flux:field>
                </div>
            </flux:card>

            <flux:button type="submit" variant="primary">
                {{ __('Create Tenant') }}
            </flux:button>

            @env('local')
            <flux:callout class="w-full max-w-lg" variant="warning">
                <flux:callout.heading icon="exclamation-triangle">{{ __('Important') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Background jobs will create the tenant database once you submit this form, make sure your queues are running.') }}
                </flux:callout.text>
            </flux:callout>
            @endenv
        </div>
    </form>
</div>
