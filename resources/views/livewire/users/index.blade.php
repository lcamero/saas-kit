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
            <flux:heading size="xl" level="1" class="mb-6">{{ __('navigation.users') }}</flux:heading>
            <flux:button :href="route('users.create')" variant="primary" size="sm">
                {{ __('user.new_user') }}
            </flux:button>
        </div>
        <flux:separator variant="subtle" />
    </div>

    <flux:input icon="magnifying-glass" size="sm" :placeholder="__('general.search')" wire:model.live.debounce.300ms="search" class="max-w-xs mb-6" />

    <flux:card>
        <flux:table :paginate="$this->users">
            <flux:table.columns>
                <flux:table.column>{{ __('general.name') }}</flux:table.column>
                <flux:table.column>{{ __('general.email') }}</flux:table.column>
                <flux:table.column>{{ __('general.created_at') }}</flux:table.column>
                <flux:table.column>{{ __('general.roles') }}</flux:table.column>
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
                                        {{ __('general.edit') }}
                                    </flux:menu.item>
                                    <flux:modal.trigger name="confirm-user-deletion">
                                        <flux:menu.item icon="trash" variant="danger" wire:click="confirmUserDeletion('{{ $user->id }}')">{{ __('general.delete') }}</flux:menu.item>
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
                <flux:heading size="lg">{{ __('user.are_you_sure_you_want_to_delete_user') }}</flux:heading>

                <flux:subheading>
                    {{ __('user.once_deleted_all_resources_cannot_be_undone') }}
                </flux:subheading>

                @if(auth()->user()->id === $userToDelete)
                <flux:callout
                    variant="warning"
                    icon="exclamation-triangle"
                    :heading="__('user.if_you_delete_your_account_you_will_be_logged_out')"
                    class="mt-6 text-sm!"
                />
                @endif
            </div>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('general.cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">{{ __('user.delete_user') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>

