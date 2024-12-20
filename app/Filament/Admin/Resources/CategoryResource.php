<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Admin\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Admin\Resources\CategoryResource\Pages\ListCategories;
use App\Filament\Admin\Resources\CategoryResource\Pages\ViewCategory;
use App\Filament\Admin\Resources\CategoryResource\RelationManagers\BooksRelationManager;
use App\Models\Category;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $modelLabel = 'الفئة';

    protected static ?string $pluralModelLabel = 'الفئات';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(__('Category Name'))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Category Name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('book_count')
                    ->label(__('Books Count'))
                    ->default(fn (Category $record) => $record->books->count())
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make(__('Category Details'))
                ->columns()
                ->schema([
                    TextEntry::make('name')
                        ->label(__('Category Name'))
                        ->icon('heroicon-o-tag'),

                    TextEntry::make('book_count')
                        ->label(__('Books Count'))
                        ->default(fn (Category $record) => $record->books->count())
                        ->icon('heroicon-o-book-open'),
                ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            BooksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
            'view' => ViewCategory::route('/{record}'),
        ];
    }
}
