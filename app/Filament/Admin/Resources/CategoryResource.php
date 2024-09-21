<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Admin\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Admin\Resources\CategoryResource\Pages\ListCategories;
use App\Filament\Admin\Resources\CategoryResource\Pages\ViewCategory;
use App\Models\Category;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $modelLabel = "الفئة";

    protected static ?string $pluralModelLabel = "الفئات";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make("name")
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
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make()
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
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
