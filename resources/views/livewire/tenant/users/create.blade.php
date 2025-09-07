<?php

use App\Models\Tenant\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;

new #[Layout('components.tenant.layouts.app')] class extends Component {
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255|unique:users,email')]
    public string $email = '';

    #[Validate([
        'roles' => 'array',
        'roles.*' => ['string', 'max:255']
    ])]
    public array $roles = [];

    public function mount()
    {
        $this->authorize(\App\Enums\Tenant\Permission::ManageApplicationUsers);
    }

    public function create(): void
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make(Str::random(16)),
            'email_verified_at' => now(),
        ]);

        // Remap to valid roles
        if (!empty($this->roles)) {
            $user->assignRole(collect($this->roles)
                ->map(fn (string $role) => \App\Enums\Tenant\Role::tryFrom($role))
                ->filter()
                ->map(fn (\App\Enums\Tenant\Role $role) => $role->value)
                ->all()
            );
        }

        $user->notify(new \App\Notifications\Tenant\UserInvitation(route('tenant.password.request')));

        $this->redirect(route('users.index'), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="create" class="space-y-4">
        <div class="space-y-12">
            <flux:heading size="xl">{{ __('Create User') }}</flux:heading>
            <flux:card class="w-full lg:w-4/5">
                <div class="grid grid-cols-2 gap-y-6">
                    <flux:input :label="__('Name')" wire:model="name" class="max-w-sm" required :badge="__('Required')" />
                    <flux:input :label="__('Email')" wire:model="email" class="max-w-sm" type="email" required :badge="__('Required')" />
                    <flux:checkbox.group wire:model="roles" :label="__('Roles')">
                        <flux:checkbox :value="\App\Enums\Tenant\Role::Administrator->value" :label="\App\Enums\Tenant\Role::Administrator->getLabel()"></flux:checkbox>
                    </flux:checkbox.group>
                </div>
            </flux:card>

            <flux:button type="submit" variant="primary">
                {{ __('Create User') }}
            </flux:button>

            <flux:callout class="w-full max-w-lg" variant="warning">
                <flux:callout.heading icon="exclamation-triangle">{{ __('Important') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('A random password will be generated and the user will need to reset it in order to login.') }}
                </flux:callout.text>
            </flux:callout>
        </div>
    </form>
</div>
