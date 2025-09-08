<?php

use App\Models\Tenant\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;

new #[Layout('components.tenant.layouts.app')] class extends Component {
    #[Locked]
    public string $userId;

    public string $name = '';
    public string $email = '';  
    public array $roles = [];

    #[Computed]
    protected function user()
    {
        return User::findOrFail($this->userId);
    }

    public function mount($userId)
    {
        $this->authorize(\App\Enums\Tenant\Permission::ManageApplicationUsers);

        $this->userId = $userId;

        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->roles = $this->user->getRoleNames()->all();
    }

    public function update(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($this->userId)],
            'roles' => 'array',
            'roles.*' => ['string', 'max:255']
        ]);

        $this->user->update(['name' => $validated['name'], 'email' => $validated['email']]);

        // Remap to valid roles
        $this->user->syncRoles(collect($this->roles)
            ->map(fn (string $role) => \App\Enums\Tenant\Role::tryFrom($role))
            ->filter()
            ->map(fn (\App\Enums\Tenant\Role $role) => $role->value)
            ->all()
        );

        $this->redirect(route('users.index'), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="update" class="space-y-4">
        <div class="space-y-12">
            <flux:heading size="xl">{{ __('user.update_user') }}</flux:heading>
            <flux:card class="w-full lg:w-4/5">
                <div class="grid grid-cols-2 gap-y-6">
                    <flux:input :label="__('general.name')" wire:model="name" class="max-w-sm" required :badge="__('general.required')" />
                    <flux:input :label="__('general.email')" wire:model="email" class="max-w-sm" type="email" required :badge="__('general.required')" />
                    <flux:checkbox.group wire:model="roles" :label="__('general.roles')">
                        <flux:checkbox :value="\App\Enums\Tenant\Role::Administrator->value" :label="\App\Enums\Tenant\Role::Administrator->getLabel()"></flux:checkbox>
                    </flux:checkbox.group>
                </div>
            </flux:card>
            <flux:button type="submit" variant="primary">
                {{ __('user.update_user') }}
            </flux:button>
        </div>
    </form>
</div>
