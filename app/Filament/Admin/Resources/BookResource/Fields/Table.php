<?php

namespace App\Filament\Admin\Resources\BookResource\Fields;

use App\Models\Book;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class Table
{
    public static function columns($categoryId = null, $seriesId = null): array
    {
        return [
            TextColumn::make('name')
                ->label(__('Book Name'))
                ->searchable()
                ->sortable(),

            TextColumn::make('category.name')
                ->label(__('Category Name'))
                ->hidden(fn () => $categoryId)
                ->searchable()
                ->sortable(),

            TextColumn::make('order')
                ->label(__('Book Order'))
                ->sortable(),

            TextColumn::make('series.name')
                ->label(__('Series Name'))
                ->hidden(fn () => $seriesId)
                ->searchable()
                ->sortable(),

            TextColumn::make('editions_no')
                ->label(__('Editions Number'))
                ->getStateUsing(fn (Book $record) => $record->editions->count())
                ->sortable(query: function (Builder $query, string $direction): Builder {
                    return $query->withCount('editions')->orderBy('editions_count', $direction);
                }),

            TextColumn::make('copies_no')
                ->label(__('Copies Number'))
                ->getStateUsing(fn (Book $record) => $record->copies->count())
                ->sortable(query: function (Builder $query, string $direction): Builder {
                    return $query->withCount('copies')->orderBy('copies_count', $direction);
                }),

            TextColumn::make('old_code')
                ->label(__('Old Code'))
                ->searchable()
                ->sortable(),

            TextColumn::make('barcode')
                ->label(__('Barcode'))
                ->getStateUsing(fn (Book $record) => $record->copies->first()->barcode)
                ->sortable(),
        ];
    }
}
