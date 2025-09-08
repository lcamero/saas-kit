<?php

use \App\Auth\Sanctum;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;

new #[Layout('components.tenant.layouts.app')] class extends Component {
    public string $tokenName = '';
    public array $permissions = [];
    public ?string $plainTextToken = null;
    public bool $showTokenModal = false;
    public ?int $tokenToDeleteId = null;
    public bool $showDeleteModal = false;
    public Collection $tokens;

    public function mount(): void
    {
        $this->tokens = Auth::user()->tokens;
        $this->permissions = Sanctum::getDefaultPermissions();
    }

    public function createToken(): void
    {
        if (! Sanctum::apiTokensEnabled()) {
            return;
        }

        $this->validate([
            'tokenName' => ['required', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $this->plainTextToken = Auth::user()->createToken(
            $this->tokenName,
            empty($this->permissions) ? ['*'] : $this->permissions
        )->plainTextToken;

        $this->tokens = Auth::user()->tokens;
        $this->tokenName = '';
        $this->permissions = [];
        $this->showTokenModal = true;
    }

    public function confirmDeleteToken(int $tokenId): void
    {
        $this->tokenToDeleteId = $tokenId;
        $this->showDeleteModal = true;
    }

    public function deleteToken(): void
    {
        if (! Sanctum::apiTokensEnabled()) {
            return;
        }

        if (! $this->tokenToDeleteId) {
            return;
        }

        Auth::user()->tokens()->where('id', $this->tokenToDeleteId)->delete();
        $this->tokens = Auth::user()->tokens;

        $this->tokenToDeleteId = null;
        $this->showDeleteModal = false;
    }
}; ?>

<section class="w-full">
    <x-tenant.settings.layout :heading="__(__('general.api_tokens'))" :subheading="__('api.manage_api_tokens_for_account')">
        @if (Sanctum::apiTokensEnabled())
        <div class="my-6 w-full space-y-6">
            <div class="flex items-center justify-between">
                <flux:heading level="3">{{ __('api.create_api_token') }}</flux:heading>
            </div>

            <div class="grid grid-cols-1 gap-y-6">
                <flux:input wire:model="tokenName" :label="__('general.token_name')" type="text" required />

                @if (Sanctum::getPermissions())
                <flux:label>{{ __('general.permissions') }}</flux:label>
                <flux:checkbox.group class="">
                    <flux:checkbox.all :label="__('general.check_all')" />
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                        @foreach (Sanctum::getPermissions() as $permission)
                            <label class="flex items-center">
                                <flux:checkbox wire:model="permissions" :value="$permission" :label="$permission"/>
                            </label>
                        @endforeach
                    </div>
                </flux:checkbox.group>
                @endif
            </div>

            <div class="flex items-center justify-end">
                <flux:button variant="primary" wire:click="createToken">{{ __('api.create_token') }}</flux:button>
            </div>
        </div>

        @if ($plainTextToken)
            <flux:modal wire:model="showTokenModal" max-width="lg">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">
                            {{ __('general.api_token') }}
                        </flux:heading>

                        <flux:text class="mt-2">
                            <p>{{ __('api.here_is_your_new_token') }}</p>
                        </flux:text>
                    </div>
                    <div class="mt-4 rounded-md bg-zinc-100 p-4 font-mono text-sm dark:bg-zinc-700">
                        {{ $plainTextToken }}
                    </div>
                    <div class="flex gap-2">
                        <flux:spacer />

                        <flux:modal.close>
                            <flux:button>{{ __('general.close') }}</flux:button>
                        </flux:modal.close>
                    </div>
                </div>
            </flux:modal>
        @endif

        <flux:modal wire:model="showDeleteModal" max-width="lg">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">
                        {{ __('api.delete_api_token') }}
                    </flux:heading>

                    <flux:text class="mt-2">
                        <p>{{ __('api.are_you_sure_you_want_to_delete_api_token') }}</p>
                    </flux:text>
                </div>
                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button>{{ __(__('general.cancel')) }}</flux:button>
                    </flux:modal.close>

                    <flux:button variant="danger" wire:click="deleteToken">{{ __(__('general.delete')) }}</flux:button>
                </div>
            </div>
        </flux:modal>

        <div class="my-6 w-full space-y-6">
            <div class="flex items-center justify-between">
                <flux:heading level="3">{{ __('api.manage_api_tokens') }}</flux:heading>
            </div>

            <div class="space-y-4">
                @forelse ($tokens as $token)
                    <div class="flex items-center justify-between rounded-md p-4 bg-zinc-100 dark:bg-zinc-700">
                        <div class="">
                            <flux:heading>
                                {{ $token->name }} - {{ __('user.last_used') }} {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : __('general.never') }}
                            </flux:heading>
                            
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4 mt-2">
                                @foreach ($token->abilities as $permission)
                                    <span class="text-xs">
                                        {{ $permission }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <flux:button variant="danger" wire:click="confirmDeleteToken({{ $token->id }})">{{ __(__('general.delete')) }}</flux:button>
                    </div>
                @empty
                    <p>{{ __('api.you_have_no_api_tokens') }}</p>
                @endforelse
            </div>
        </div>
        @else
        <flux:callout icon="exclamation-triangle" color="amber" :heading="__('api.api_token_management_is_disabled')"></flux:callout>
        @endif
    </x-tenant.settings.layout>
</section>