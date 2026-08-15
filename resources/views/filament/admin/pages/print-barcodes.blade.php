<x-filament-panels::page>
    <div class="flex gap-2 mb-4">
        <x-filament::button
            color="{{ $mode === 'editions' ? 'primary' : 'gray' }}"
            wire:click="$set('mode', 'editions')"
        >
            Browse Books
        </x-filament::button>
        <x-filament::button
            color="{{ $mode === 'copies' ? 'primary' : 'gray' }}"
            wire:click="$set('mode', 'copies')"
        >
            Pick Individual Copies
        </x-filament::button>
    </div>

    <div class="mb-4">
        <x-filament::input.wrapper>
            <x-filament::input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="{{ $mode === 'editions' ? 'Search by book title...' : 'Search by barcode or book title...' }}"
            />
        </x-filament::input.wrapper>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
