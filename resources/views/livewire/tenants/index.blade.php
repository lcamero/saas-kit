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

    <flux:input icon="magnifying-glass" size="sm" :placeholder="__('Search')" wire:model.live="search" class="max-w-xs mb-6" />

    <flux:card class="w-full lg:w-4/5">
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
                            <flux:tooltip :content="$tenant->url" position="right">
                                <flux:button :href="$tenant->url" target="_blank" icon:trailing="arrow-up-right" size="sm">
                                    {{ __('Visit') }}
                                </flux:button>
                            </flux:tooltip>
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
