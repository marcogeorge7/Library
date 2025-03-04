<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BookResource\Pages\CreateBook;
use App\Filament\Admin\Resources\BookResource\Pages\EditBook;
use App\Filament\Admin\Resources\BookResource\Pages\ListBooks;
use App\Filament\Admin\Resources\BookResource\Pages\ViewBook;
use App\Models\Book;
use App\Models\Publisher;
use App\Models\Series;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Table;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $slug = 'books';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'كتاب';

    protected static ?string $pluralModelLabel = 'كتب';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Book Main Details')
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')
                            ->rules(['required', 'string'])
                            ->disabled(fn($record) => $record ?? false)
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
                            ->searchable(),

                        Checkbox::make('is_series')
                            ->live()
                            ->label(__('Is Series'))
                            ->default(false)
                            ->afterStateHydrated(fn(Checkbox $component, Book $record) => $component->state($record->hasSeries()))
                            ->disabled(fn($record) => $record ?? false),

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
                            ->visible(fn(Get $get) => $get('is_series') === true),
                    ]),
                Section::make('Edition Details')
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
                            ->label(__('Number Of Copy'))
                            ->numeric()
                            ->minValue(1)
                            ->default(fn() => 1)

                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(BookResource\Fields\Table::columns())
            ->filters([])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema(BookResource\Fields\Infolist::schema());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBooks::route('/'),
            'create' => CreateBook::route('/create'),
            'edit' => EditBook::route('/{record}/edit'),
            'view' => ViewBook::route('/{record}'),
        ];
    }
}
