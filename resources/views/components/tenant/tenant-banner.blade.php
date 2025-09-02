@env('local', 'staging')
<div x-data="{display: true}" x-show="display" class="absolute bottom-0 z-10 w-full gap-4 flex items-center justify-end bg-amber-400 text-black text-xs p-1 opacity-30 hover:opacity-100 transition-opacity">
    <div class="flex gap-2 justify-end font-mono">
        <flux:label class="text-xs text-black">
            {{ __('Tenant Environment') }}:
        </flux:label>
        <span>
            {{ tenant()->name }}
        </span>
        <span class="hidden lg:block">
            ({{ tenant()->id }})
        </span>
    </div>
    
    <flux:button icon="x-mark" size="xs" variant="primary" color="yellow" x-on:click="display = false" />
</div>
@endenv