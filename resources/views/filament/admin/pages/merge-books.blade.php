<x-filament-panels::page>
    <div class="mb-4">
        <x-filament::input.wrapper>
            <x-filament::input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="Search by book title..."
            />
        </x-filament::input.wrapper>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
