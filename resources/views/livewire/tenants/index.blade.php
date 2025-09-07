<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Models\Tenant;
use \Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    #[Url] 
    public $search = '';

    public ?string $tenantToDelete = null;
    public ?string $tenantLoginEmail = null;

    public function mount()
    {
        $this->authorize(\App\Enums\Tenant\Permission::ManageApplicationUsers);
    }

    #[Computed]
    public function tenants()
    {
        return $this->search
            ? Tenant::search($this->search)
                ->query(fn ($query) => $query->latest())
                ->paginate(10)
            : Tenant::query()
                ->latest()
                ->paginate(10);
    }

    public function confirmTenantDeletion($tenantId): void
    {
        $this->tenantToDelete = $tenantId;
        $this->dispatch('open-modal', 'confirm-tenant-deletion');
    }

    public function deleteTenant(): void
    {
        if ($this->tenantToDelete) {
            $tenant = Tenant::findOrFail($this->tenantToDelete);
            $tenant->delete();
        }
        
        $this->redirect(route('tenants.index'), navigate: true);
    }

    public function tenantLogin(string $tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $email = $this->tenantLoginEmail;

        $userId = null;
        $tenant->run(function () use (&$userId, $email) {
            $userId = $email
                ? \App\Models\Tenant\User::where('email', $email)->first()?->id
                : \App\Models\Tenant\User::role(\App\Enums\Tenant\Role::CentralAdministrator->value)->first()?->id;
        });

        if (!$userId) {
            Flux::toast(__('No user id found to login with'), variant: 'danger');
            return;
        }

        // On provisioned systems, administrator account is always the first one.
        $redirectUrl = '/dashboard';
        $token = tenancy()->impersonate($tenant, $userId, $redirectUrl, 'tenant');

        $this->dispatch('open-in-new-tab', url: $tenant->url.'/impersonate/'.$token->token);
    }
}; ?>

<div>
    <div class="relative mb-6 w-full">
        <div class="flex justify-between items-center">
            <flux:heading size="xl" level="1" class="mb-6">{{ __('Tenants') }}</flux:heading>
            <flux:button :href="route('tenants.create')" variant="primary" size="sm">
                {{ __('New tenant') }}
            </flux:button>
        </div>
        <flux:separator variant="subtle" />
    </div>

    <flux:input icon="magnifying-glass" size="sm" :placeholder="__('Search')" wire:model.live.debounce.300ms="search" class="max-w-xs mb-6" />

    <flux:card class="w-full">
        <flux:table :paginate="$this->tenants">
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Email') }}</flux:table.column>
                <flux:table.column>{{ __('Created at') }}</flux:table.column>
            </flux:table.columns>
        
            <flux:table.rows>
                @foreach ($this->tenants as $tenant)
                    <flux:table.row :key="$tenant->id">    
                        <flux:table.cell class="whitespace-nowrap" variant="strong">{{ $tenant->name }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $tenant->email }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $tenant->created_at }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            <flux:button.group>
                                <flux:tooltip :content="$tenant->url" position="top">
                                    <flux:button wire:click="tenantLogin('{{ $tenant->id }}')" icon:trailing="arrow-up-right" size="sm">
                                        {{ __('Login') }}
                                    </flux:button>
                                </flux:tooltip>
                                <flux:dropdown align="left">
                                    <flux:button icon="chevron-down" size="sm">
                                    </flux:button>
                                    <flux:popover class="w-72">
                                        <flux:input wire:model="tenantLoginEmail" wire:keyup.enter="tenantLogin('{{ $tenant->id }}')" :label="__('Optionally log in as a specific account')" :placeholder="__('admin@example.com')" type="email" size="sm" clearable />
                                    </flux:popover>
                                </flux:dropdown>
                            </flux:button.group>
                        </flux:table.cell>
    
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button icon="ellipsis-horizontal" size="sm"></flux:button>
    
                                <flux:menu>
                                    <flux:menu.item icon="pencil-square" href="{{ route('tenants.edit', ['tenantId' => $tenant->id]) }}" wire:navigate>
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                    <flux:modal.trigger name="confirm-tenant-deletion">
                                        <flux:menu.item icon="trash" variant="danger" wire:click="confirmTenantDeletion('{{ $tenant->id }}')">{{ __('Delete') }}</flux:menu.item>
                                    </flux:modal.trigger>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <flux:modal name="confirm-tenant-deletion" focusable class="max-w-lg">
        <form method="POST" wire:submit="deleteTenant" class="space-y-6 p-6">
            <div>
                <flux:heading size="lg">{{ __('Are you sure you want to delete this tenant?') }}</flux:heading>

                <flux:subheading>
                    {{ __('Once this tenant is deleted, all of its resources and data will be permanently deleted. This action cannot be undone') }}
                </flux:subheading>
            </div>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">{{ __('Delete tenant') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
@script
<script>
    $wire.on('open-in-new-tab', ({ url }) => {
        window.open(url, '_blank');
    });
</script>
@endscript
