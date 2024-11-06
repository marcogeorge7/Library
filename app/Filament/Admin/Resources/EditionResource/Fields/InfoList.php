<?php

namespace App\Filament\Admin\Resources\EditionResource\Fields;

use App\Filament\Admin\Resources\BookResource;
use App\Filament\Admin\Resources\PublisherResource;
use App\Filament\Admin\Resources\SeriesResource;
use App\Models\Edition;
use Filament\Infolists\Components\IconEntry;
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

                    TextEntry::make('book.series.name')
                        ->label(__('Series Name'))
                        ->url(rescue(fn ($record) => SeriesResource::getUrl('view', ['record' => $record->book->series]), '#', report: false)),

                    TextEntry::make('publisher.name')
                        ->label(__('Publisher Name'))
                        ->url(fn ($record) => PublisherResource::getUrl('view', ['record' => $record->publisher])),

                    TextEntry::make('translator_name')
                        ->label(__('Translator Name'))
                        ->getStateUsing(fn (Edition $record) => $record->translators?->first()?->name ?? '-'),

                    TextEntry::make('lang')
                        ->label(__('Language'))
                        ->getStateUsing(fn (Edition $record) => $record->lang == 'en' ? 'انجليزي' : 'عربي'),

                    TextEntry::make('publish_year')
                        ->getStateUsing(fn ($record) => $record->publish_year ?? '-')
                        ->label(__('Publish Year')),

                    TextEntry::make('partCode')
                        ->label(__('Part Number'))
                        ->getStateUsing(fn (Edition $record) => $record->partNumber),

                    IconEntry::make('internal_borrowing')
                        ->label(__('Internal Borrowing'))
                        ->boolean(),
                ]),
        ];
    }
}
