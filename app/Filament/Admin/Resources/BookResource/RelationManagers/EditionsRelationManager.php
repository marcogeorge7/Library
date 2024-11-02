<?php

namespace App\Filament\Admin\Resources\BookResource\RelationManagers;

use App\Filament\Admin\Resources\EditionResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EditionsRelationManager extends RelationManager
{
    protected static string $relationship = 'editions';

    protected static ?string $title = 'طبعات';

    protected static ?string $pluralModelLabel = 'طبعات';

    protected static ?string $modelLabel = 'طبعة';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('partCode')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('partCode')
            ->columns(EditionResource\Fields\Table::columns($this->getOwnerRecord()))
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => EditionResource::getUrl('view', ['record' => $record])),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
