<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Models\Tenant;
use \Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    #[Url] 
    public $search = '';

    public ?string $tenantToDisable = null;
    public ?string $tenantLoginEmail = null;

    public function mount()
    {
        $this->authorize(\App\Enums\Tenant\Permission::ManageApplicationUsers);
    }

    #[Computed]
    public function tenants()
    {
        return Tenant::search($this->search)
                ->withTrashed()
                ->query(fn ($query) => $query->latest())
                ->paginate();
    }

    public function confirmTenantDisable($tenantId): void
    {
        $this->tenantToDisable = $tenantId;
        $this->dispatch('open-modal', 'confirm-tenant-disable');
    }

    public function enableTenant($tenantId): void
    {
        $tenant = Tenant::withTrashed()->findOrFail($tenantId);

        $tenant->restore();

        // $this->redirect(route('tenants.index'), navigate: true);
    }

    public function disableTenant(): void
    {
        if ($this->tenantToDisable) {
            $tenant = Tenant::findOrFail($this->tenantToDisable);
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
            Flux::toast(__('tenant.no_user_id_found_to_login_with'), variant: 'danger');
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
            <flux:heading size="xl" level="1" class="mb-6">{{ __('tenant.tenants') }}</flux:heading>
            <flux:button :href="route('tenants.create')" variant="primary" size="sm">
                {{ __('tenant.new_tenant') }}
            </flux:button>
        </div>
        <flux:separator variant="subtle" />
    </div>

    <flux:input icon="magnifying-glass" size="sm" :placeholder="__('general.search')" wire:model.live.debounce.300ms="search" class="max-w-xs mb-6" />

    <flux:card class="w-full">
        <flux:table :paginate="$this->tenants">
            <flux:table.columns>
                <flux:table.column>{{ __('general.name') }}</flux:table.column>
                <flux:table.column>{{ __('general.email') }}</flux:table.column>
                <flux:table.column>{{ __('general.created_at') }}</flux:table.column>
            </flux:table.columns>
        
            <flux:table.rows>
                @foreach ($this->tenants as $tenant)
                    <flux:table.row :key="$tenant->id">
                        <flux:table.cell class="whitespace-nowrap" variant="strong">
                            {{ $tenant->name }}
                            <flux:subheading size="sm" class="opacity-50">
                                {{ $tenant->id }}
                            </flux:subheading>
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $tenant->email }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $tenant->created_at }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            @if($tenant->trashed())
                            <flux:badge color="amber" size="sm">{{ __('general.disabled') }}</flux:badge>
                            @else
                            <flux:button.group>
                                <flux:tooltip :content="$tenant->url" position="top">
                                    <flux:button wire:click="tenantLogin('{{ $tenant->id }}')" icon:trailing="arrow-up-right" size="sm">
                                        {{ __('general.login') }}
                                    </flux:button>
                                </flux:tooltip>
                                <flux:dropdown align="left">
                                    <flux:button icon="chevron-down" size="sm">
                                    </flux:button>
                                    <flux:popover class="w-72">
                                        <flux:input wire:model="tenantLoginEmail" wire:keyup.enter="tenantLogin('{{ $tenant->id }}')" :label="__('forms.optionally_log_in_as_an_existing_account')" :placeholder="__('forms.admin_example_com')" type="email" size="sm" clearable />
                                        <flux:subheading size='sm' class="mt-2">
                                            {{ __('forms.enter_to_login') }}
                                        </flux:subheading>
                                    </flux:popover>
                                </flux:dropdown>
                            </flux:button.group>
                            @endif 
                        </flux:table.cell>
    
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button icon="ellipsis-horizontal" size="sm"></flux:button>
    
                                <flux:menu>
                                    <flux:menu.item icon="pencil-square" href="{{ route('tenants.edit', ['tenantId' => $tenant->id]) }}" wire:navigate>
                                        {{ __('general.edit') }}
                                    </flux:menu.item>
                                    @unless($tenant->trashed())
                                    <flux:modal.trigger name="confirm-tenant-disable">
                                        <flux:menu.item icon="trash" variant="danger" wire:click="confirmTenantDisable('{{ $tenant->id }}')">{{ __('general.delete') }}</flux:menu.item>
                                    </flux:modal.trigger>
                                    @endunless
                                    @if($tenant->trashed())
                                    <flux:menu.item icon="arrow-left-end-on-rectangle" wire:click="enableTenant('{{ $tenant->id }}')">{{ __('general.enable') }}</flux:menu.item>
                                    @endif
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <flux:modal name="confirm-tenant-disable" focusable class="max-w-lg">
        <form method="POST" wire:submit="disableTenant" class="space-y-6 p-6">
            <div>
                <flux:heading size="lg">{{ __('tenant.are_you_sure_you_want_to_delete_tenant') }}</flux:heading>

                <flux:subheading>
                    {{ __('tenant.once_disabled_users_will_lose_access') }}
                </flux:subheading>
                <flux:subheading>
                    {{ __('general.proceed_with_caution') }}
                </flux:subheading>
            </div>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('general.cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">{{ __('tenant.disable_tenant') }}</flux:button>
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
