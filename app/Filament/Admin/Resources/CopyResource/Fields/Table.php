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
            TextColumn::make('book.name')
                ->label(__('Book Name'))
                ->visible(fn () => ! $editionId),
            TextColumn::make('edition.name')
                ->visible(fn () => ! $editionId)
                ->label(__('Edition Name')),

            IconColumn::make('is_borrowed')
                ->boolean()
                ->label(__('Is Borrowed')),
            IconColumn::make('is_printed')
                ->boolean()
                ->label(__('Is Printed')),
        ];
    }
}
