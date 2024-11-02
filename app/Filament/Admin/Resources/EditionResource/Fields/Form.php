<?php

namespace App\Filament\Admin\Resources\EditionResource\Fields;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class Form
{
    public static function schema(): array
    {
        return [
            Section::make(__('Edition Details'))
                ->icon('heroicon-o-book-open')
                ->columns(2)
                ->schema(
                    [
                        Select::make('book_id')
                            ->relationship('book', 'name')
                            ->label(__('Book Name'))
                            ->searchable()
                            ->required(),

                        TextInput::make('partCode')
                            ->label(__('Part Number'))
                            ->required()
                            ->integer(),

                        Select::make('publisher_id')
                            ->relationship('publisher', 'name')
                            ->label(__('Publisher Name'))
                            ->searchable()
                            ->required(),

                        TextInput::make('publish_year')
                            ->label(__('Publish Year'))
                            ->numeric()
                            ->required(),

                        Select::make('lang')
                            ->label(__('Language'))
                            ->options([
                                'en' => 'انجليزي',
                                'ar' => 'عربي',
                            ]),

                    ]
                ),
        ];
    }
}
