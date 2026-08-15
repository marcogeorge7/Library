<?php

namespace App\Filament\Admin\Resources\EditionResource\Fields;

use App\Models\Edition;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class Table
{
    public static function columns($bookId = null): array
    {
        return [
            TextColumn::make('book.name')
                ->searchable()
                ->label(__('Book Name'))
                ->visible(fn () => ! $bookId)
                ->sortable(),

            TextColumn::make('partCode')
                ->getStateUsing(fn ($record) => $record->partNumber)
                ->label(__('Part Number')),

            TextColumn::make('part_name')
                ->label(__('Part Name'))
                ->placeholder('—')
                ->searchable(),

            TextColumn::make('publisher.name')
                ->label(__('Publisher Name'))
                ->searchable()
                ->sortable(),

            TextColumn::make('publish_year')
                ->label(__('Publish Year'))
                ->getStateUsing(fn ($record) => $record->publish_year ?? '-'),

            TextColumn::make('translator_name')
                ->label(__('Translator Name'))
                ->getStateUsing(fn (Edition $record) => $record->translators?->first()?->name ?? '-'),

            TextColumn::make('lang')
                ->label(__('Language'))
                ->formatStateUsing(fn (Edition $record) => $record->lang == 'en' ? 'انجليزي' : 'عربي'),

            TextColumn::make('copies_no')
                ->label(__('Copies Number'))
                ->getStateUsing(fn (Edition $record) => $record->copies->count()),

            IconColumn::make('internal_borrowing')
                ->label(__('Internal Borrowing'))
                ->boolean(),
        ];
    }
}
