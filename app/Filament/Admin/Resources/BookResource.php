<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BookResource\Pages\CreateBook;
use App\Filament\Admin\Resources\BookResource\Pages\EditBook;
use App\Filament\Admin\Resources\BookResource\Pages\ListBooks;
use App\Filament\Admin\Resources\BookResource\Pages\ViewBook;
use App\Models\Book;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                TextInput::make('name')
                    ->required(),

                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->label(__('Category Name'))
                    ->searchable()
                    ->required(),

                Select::make('series_id')
                    ->relationship('series', 'name')
                    ->label(__('Series Name'))
                    ->searchable(),

                Placeholder::make('created_at')
                    ->label('Created Date')
                    ->content(fn (?Book $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                Placeholder::make('updated_at')
                    ->label('Last Modified Date')
                    ->content(fn (?Book $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Book Name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label(__('Category Name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('series.name')
                    ->label(__('Series Name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('editions_no')
                    ->label(__('Editions Number'))
                    ->getStateUsing(fn (Book $record) => $record->editions->count())
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withCount('editions')->orderBy('editions_count', $direction);
                    }),

            ])
            ->filters([
                TrashedFilter::make(),
            ])
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
