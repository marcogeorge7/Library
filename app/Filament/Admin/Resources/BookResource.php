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
            ->schema(BookResource\Fields\Form::schema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(BookResource\Fields\Table::columns())
            ->modifyQueryUsing(function (Builder $query) {
                return $query->with(['category', 'author', 'editions', 'series', 'editions.publisher', 'copies']);
            })
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
