<?php

namespace App\Filament\Admin\Resources\BookResource\Fields;

use App\Filament\Admin\Resources\AuthorResource;
use App\Filament\Admin\Resources\CategoryResource;
use App\Filament\Admin\Resources\SeriesResource;
use App\Models\Book;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

class Infolist
{
    public static function schema()
    {
        return [
            Section::make(__('Book Details'))
                ->icon('heroicon-o-book-open')
                ->columns(2)
                ->schema([
                    TextEntry::make('name')
                        ->label(__('Book Name')),
                    TextEntry::make('category.name')
                        ->label(__('Category Name'))
                        ->url(fn ($record) => CategoryResource::getUrl('view', ['record' => $record->category])),

                    TextEntry::make('series.name')
                        ->label(__('Series Name'))
                        ->url(rescue(callback: fn (Book $record) => SeriesResource::getUrl('view', ['record' => $record->series]), rescue : '#', report: false)),

                    TextEntry::make('author_name')
                        ->label(__('Author Name'))
                        ->getStateUsing(fn (Book $record) => $record->author()->first()->name)
                        ->url(rescue(callback: fn (Book $record) => AuthorResource::getUrl('view', ['record' => $record->author()->first()]), rescue : '#', report: false)),

                    TextEntry::make('copies_no')
                        ->label(__('Copies Number'))
                        ->getStateUsing(fn (Book $record) => $record->copies->count()),

                    TextEntry::make('old_code')
                        ->label(__('Old Code'))
                        ->getStateUsing(fn (Book $record) => $record->old_code),
                ]),
        ];
    }
}
