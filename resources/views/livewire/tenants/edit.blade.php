<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Illuminate\Validation\Rule;

new class extends Component {
    #[Locked]
    public string $tenantId;

    public string $name = '';
    public string $email = '';  
    public string $domain = '';

    #[Computed]
    protected function tenant()
    {
        return Tenant::findOrFail($this->tenantId);
    }

    public function mount($tenantId)
    {
        $this->authorize(\App\Enums\Tenant\Permission::ManageApplicationUsers);

        $this->tenantId = $tenantId;

        $this->name = $this->tenant->name;
        $this->email = $this->tenant->email;
        $this->domain = $this->tenant->domains->first()->domain;
    }

    public function update(): void
    {
        $domain = $this->tenant->domains->first();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(Tenant::class)->ignore($this->tenantId)],
            'domain' => ['required', 'string', 'max:255', Rule::unique(Stancl\Tenancy\Database\Models\Domain::class)->ignore($domain->id)],
        ]);

        $this->tenant->update(['name' => $validated['name'], 'email' => $validated['email']]);
        $domain->update(['domain' => $validated['domain']]);

        $this->redirect(route('tenants.index'), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="update" class="space-y-4">
        <div class="space-y-12">
            <flux:heading size="xl">{{ __('tenant.update_tenant') }}</flux:heading>
            <flux:card class="w-full lg:w-4/5">
                <div class="grid grid-cols-2 gap-y-6">
                    <flux:input :label="__(__('general.name'))" wire:model="name" class="max-w-sm" required :badge="__('general.required')" />
                    <flux:input :label="__(__('general.email'))" wire:model="email" class="max-w-sm" type="email" required :badge="__('general.required')" />
                    <flux:field>
                        <flux:label :badge="__('general.required')">{{ __('general.domain') }}</flux:label>
                        <flux:input.group>
                            <flux:input wire:model="domain" class="col-span-2 max-w-sm" required />
                            <flux:input.group.suffix>.{{ parse_url(config('app.url'))['host'] }}</flux:input.group.suffix>
                        </flux:input.group>
                        <flux:error name="domain" />
                    </flux:field>
                </div>
            </flux:card>
            <flux:button type="submit" variant="primary">
                {{ __('tenant.update_tenant') }}
            </flux:button>
        </div>
    </form>
</div>
