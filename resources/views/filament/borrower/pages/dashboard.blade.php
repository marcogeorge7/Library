<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <x-filament::card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Member</div>
            <div class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $borrower->name }}</div>
            <div class="mt-1 text-sm text-gray-500">{{ $borrower->member_id }}</div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Borrows</div>
            <div class="mt-1 text-3xl font-bold text-primary-600">{{ $activeCount }}</div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Overdue Items</div>
            <div class="mt-1 text-3xl font-bold {{ $overdueCount > 0 ? 'text-danger-600' : 'text-gray-400' }}">
                {{ $overdueCount }}
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page>
