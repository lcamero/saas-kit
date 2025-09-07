<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Models\User;
use \Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    #[Url] 
    public $search = '';

    public ?int $userToDelete = null;

    public function mount()
    {
        $this->authorize(\App\Enums\Permission::ManageApplicationUsers);
    }

    #[Computed]
    public function users()
    {
        return User::search($this->search)
            ->query(fn ($query) => $query->with('roles')->latest())
            ->paginate();
    }

    public function confirmUserDeletion($userId): void
    {
        $this->userToDelete = $userId;
        $this->dispatch('open-modal', 'confirm-user-deletion');
    }

    public function deleteUser(): void
    {
        if ($this->userToDelete) {
            $user = User::findOrFail($this->userToDelete);
            $user->delete();
        }
        
        $this->redirect(route('users.index'), navigate: true);
    }
}; ?>

<div>
    <div class="relative mb-6 w-full">
        <div class="flex justify-between items-center">
            <flux:heading size="xl" level="1" class="mb-6">{{ __('Users') }}</flux:heading>
            <flux:button :href="route('users.create')" variant="primary" size="sm">
                {{ __('New user') }}
            </flux:button>
        </div>
        <flux:separator variant="subtle" />
    </div>

    <flux:input icon="magnifying-glass" size="sm" :placeholder="__('Search')" wire:model.live.debounce.300ms="search" class="max-w-xs mb-6" />

    <flux:card>
        <flux:table :paginate="$this->users">
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Email') }}</flux:table.column>
                <flux:table.column>{{ __('Created at') }}</flux:table.column>
                <flux:table.column>{{ __('Roles') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->users as $user)
                    <flux:table.row :key="$user->id">
                        <flux:table.cell class="whitespace-nowrap">{{ $user->name }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $user->email }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $user->created_at }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $user->roles->pluck('name')->join(', ') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button icon="ellipsis-horizontal" size="sm"></flux:button>
    
                                <flux:menu>
                                    <flux:menu.item icon="pencil-square" href="{{ route('users.edit', ['userId' => $user->id]) }}" wire:navigate>
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                    <flux:modal.trigger name="confirm-user-deletion">
                                        <flux:menu.item icon="trash" variant="danger" wire:click="confirmUserDeletion('{{ $user->id }}')">{{ __('Delete') }}</flux:menu.item>
                                    </flux:modal.trigger>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <flux:modal name="confirm-user-deletion" focusable class="max-w-lg">
        <form method="POST" wire:submit="deleteUser" class="space-y-6 p-6">
            <div>
                <flux:heading size="lg">{{ __('Are you sure you want to delete this user?') }}</flux:heading>

                <flux:subheading>
                    {{ __('Once this user is deleted, all of its resources and data will be permanently deleted. This action cannot be undone') }}
                </flux:subheading>

                @if(auth()->user()->id === $userToDelete)
                <flux:callout
                    variant="warning"
                    icon="exclamation-triangle"
                    :heading="__('If you delete your account you will be logged out immediately.')"
                    class="mt-6 text-sm!"
                />
                @endif
            </div>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">{{ __('Delete user') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>

