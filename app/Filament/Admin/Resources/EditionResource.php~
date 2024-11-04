<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EditionResource\Pages\CreateEdition;
use App\Filament\Admin\Resources\EditionResource\Pages\EditEdition;
use App\Filament\Admin\Resources\EditionResource\Pages\ListEditions;
use App\Filament\Admin\Resources\EditionResource\Pages\ViewEdition;
use App\Models\Edition;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EditionResource extends Resource
{
    protected static ?string $model = Edition::class;

    protected static ?string $slug = 'editions';

    protected static ?string $pluralModelLabel = 'طبعات';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'طبعة';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(EditionResource\Fields\Form::schema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(EditionResource\Fields\Table::columns())
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema(EditionResource\Fields\InfoList::schema());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEditions::route('/'),
            'create' => CreateEdition::route('/create'),
            'edit' => EditEdition::route('/{record}/edit'),
            'view' => ViewEdition::route('/{record}'),
        ];
    }
}
