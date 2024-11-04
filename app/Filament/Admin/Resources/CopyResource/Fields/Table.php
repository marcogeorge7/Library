<?php

namespace App\Filament\Admin\Resources\CopyResource\Fields;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class Table
{
    public static function columns($editionId = null)
    {
        return [
            TextColumn::make('barcode')
                ->label(__('Barcode'))
                ->copyable()
                ->searchable(),
            IconColumn::make('is_borrowed')
                ->boolean()
                ->label(__('Is Borrowed')),
            TextColumn::make('edition.name')
                ->visible(fn () => ! $editionId)
                ->label(__('Edition Name')),
        ];
    }
}
