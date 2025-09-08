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
            <flux:heading size="xl">{{ __('user.create_user') }}</flux:heading>
            <flux:card class="w-full lg:w-4/5">
                <div class="grid grid-cols-2 gap-y-6 items-start">
                    <flux:input :label="__('general.name')" wire:model="name" class="max-w-sm" required :badge="__('general.required')" />
                    <flux:input :label="__('general.email')" wire:model="email" class="max-w-sm" type="email" required :badge="__('general.required')" />
                    <flux:checkbox.group wire:model="roles" :label="__('general.roles')">
                        <flux:checkbox :value="\App\Enums\Tenant\Role::Administrator->value" :label="\App\Enums\Tenant\Role::Administrator->getLabel()"></flux:checkbox>
                    </flux:checkbox.group>
                </div>
            </flux:card>

            <flux:button type="submit" variant="primary">
                {{ __('user.create_user') }}
            </flux:button>

            <flux:callout class="w-full max-w-lg" variant="warning">
                <flux:callout.heading icon="exclamation-triangle">{{ __('general.important') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('messages.a_random_password_will_be_generated') }}
                </flux:callout.text>
            </flux:callout>
        </div>
    </form>
</div>
