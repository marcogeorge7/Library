<?php

namespace App\Filament\Admin\Resources\BookResource\Fields;

use App\Models\Book;
use App\Models\Publisher;
use App\Models\Series;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;

class Form
{
    public static function schema()
    {
        return [
            Section::make(__('Book Main Details'))
                ->columns(3)
                ->schema([
                    TextInput::make('name')
                        ->label(__('Book Name'))
                        ->rules(['required', 'string'])
                        ->disabled(fn ($record) => $record ?? false)
                        ->required(),
                    Select::make('category_id')
                        ->relationship('category', 'name')
                        ->label(__('Category Name'))
                        ->preload()
                        ->searchable()
                        ->required(),

                    Select::make('author_id')
                        ->relationship('author', 'name')
                        ->label(__('Author'))
                        ->preload()
                        ->searchable(),

                    Checkbox::make('is_series')
                        ->live()
                        ->label(__('Is Series'))
                        ->default(false)
                        ->afterStateHydrated(fn (Checkbox $component, ?Book $record) => ($record) ? $component->state($record->hasSeries()) : null)
                        ->disabled(fn ($record) => $record ?? false),

                    Select::make('series_id')
                        ->relationship('series', 'name')
                        ->preload()
                        ->label(__('Series'))
                        ->searchable()
                        ->hintAction(
                            Actions\Action::make('add_new_series')
                                ->label(__('Add Series'))
                                ->form([
                                    TextInput::make('name')
                                        ->required(),
                                ])->action(function (array $data) {
                                    Series::create($data);
                                    Notification::make()
                                        ->title('New Series')
                                        ->body('New Series Is Created')->send();
                                })

                        )
                        ->visible(fn (Get $get) => $get('is_series') === true),
                ]),
            Section::make(__('Edition Details'))
                ->columns()
                ->schema([
                    Select::make('publisher_id')
                        ->label(__('Publisher'))
                        ->options(Publisher::pluck('name', 'id'))
                        ->preload()->searchable()
                        ->required()
                        ->hintAction(
                            Actions\Action::make('add_new_publisher')
                                ->label(__('Add Publisher'))
                                ->form([
                                    TextInput::make('name')
                                        ->required(),
                                ])->action(function (array $data) {
                                    Publisher::create($data);
                                    Notification::make()
                                        ->title('New Publisher')
                                        ->body('New Publisher Is Created')->send();
                                })
                        ),

                    TextInput::make('partCode')
                        ->label(__('Part Code'))
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(100)
                        ->required(),
                    TextInput::make('number_of_copy')
                        ->label(__('Copies Number'))
                        ->numeric()
                        ->minValue(1)
                        ->default(fn () => 1),

                ]),
        ];
    }
}
