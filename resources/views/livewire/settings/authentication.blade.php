<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    // Password properties
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $passwordIsSet = false;

    public function mount(): void
    {
        $this->passwordIsSet = ! empty(auth()->user()->password);
    }

    // Password methods
    public function updatePassword(): void
    {
        try {
            $rules = [
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ];
            if ($this->passwordIsSet) {
                $rules['current_password'] = ['required', 'string', 'current_password'];
            }
            $validated = $this->validate($rules);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update(['password' => Hash::make($validated['password'])]);
        $this->reset('current_password', 'password', 'password_confirmation');
        $this->passwordIsSet = true;
        $this->dispatch('password-updated');
    }
}; ?>

<section class="w-full">
    <x-settings.layout heading="{{ __('general.authentication') }}" subheading="{{ __('settings.manage_account_password') }}">
        <div class="space-y-12">
            {{-- Password Section --}}
            <section>
                <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">
                    {{ $passwordIsSet ? __(__('general.update_password')) : __('settings.set_password') }}
                </h2>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('settings.ensure_your_account_is_using_a_long_random_password') }}
                </p>

                <form method="POST" wire:submit="updatePassword" class="mt-6 space-y-6">
                    @if($passwordIsSet)
                        <flux:input
                            wire:model="current_password"
                            :label="__('general.current_password')"
                            type="password"
                            required
                            autocomplete="current-password"
                        />
                    @else
                        <flux:callout icon="users" color="sky">
                            <flux:callout.text>
                                {{ __('settings.you_signed_up_using_social_login') }}
                            </flux:callout.text>
                        </flux:callout>
                    @endif

                    <flux:input
                        wire:model="password"
                        :label="__('general.new_password')"
                        type="password"
                        required
                        autocomplete="new-password"
                        viewable
                    />
                    
                    <flux:input
                        wire:model="password_confirmation"
                        :label="__(__('general.confirm_password'))"
                        type="password"
                        required
                        autocomplete="new-password"
                        viewable
                    />

                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-end gap-4">
                            <flux:button variant="primary" type="submit" class="w-full">
                                {{ $passwordIsSet ? __(__('general.update_password')) : __('settings.set_password_button') }}
                            </flux:button>
                        </div>

                        <x-action-message class="me-3" on="password-updated">
                            {{ __('general.saved') }}
                        </x-action-message>
                    </div>
                </form>
            </section>
        </div>
    </x-settings.layout>
</section>
