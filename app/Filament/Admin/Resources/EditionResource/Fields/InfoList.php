<?php

namespace App\Filament\Admin\Resources\EditionResource\Fields;

use App\Filament\Admin\Resources\BookResource;
use App\Models\Edition;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

class InfoList
{
    public static function schema(): array
    {
        return [
            Section::make(__('Edition Details'))
                ->icon('heroicon-o-book-open')
                ->columns(2)
                ->schema([
                    TextEntry::make('book.name')
                        ->label(__('Book Name'))
                        ->url(fn ($record) => BookResource::getUrl('view', ['record' => $record->book])),

                    TextEntry::make('publisher.name')
                        ->label(__('Publisher Name'))
                        ->url(fn ($record) => BookResource::getUrl('view', ['record' => $record->publisher])),

                    TextEntry::make('lang')
                        ->label(__('Language')),

                    TextEntry::make('publish_year')
                        ->getStateUsing(fn ($record) => $record->publish_year ?? '-')
                        ->label(__('Publish Year')),

                    TextEntry::make('partCode')
                        ->label(__('Part Number'))
                        ->formatStateUsing(fn (Edition $record) => $record->partNumber),
                ]),
        ];
    }
}
