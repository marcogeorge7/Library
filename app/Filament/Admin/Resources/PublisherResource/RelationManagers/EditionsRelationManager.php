<?php

namespace App\Filament\Admin\Resources\PublisherResource\RelationManagers;

use App\Models\Edition;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class EditionsRelationManager extends RelationManager
{
    protected static string $relationship = 'editions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('book.name')
                    ->label(__('Book Name'))
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('book.name')
            ->columns([
                TextColumn::make('book.name')
                    ->label(__('Book Name')),

                TextColumn::make('partCode')
                    ->getStateUsing(fn($record) => $record->partNumber)
                    ->label(__('Part Number')),

                TextColumn::make('publish_year')
                    ->label(__('Publish Year'))
                    ->getStateUsing(fn($record) => $record->publish_year ?? '-'),

                TextColumn::make('translator_name')
                    ->label(__('Translator Name'))
                    ->getStateUsing(fn(Edition $record) => $record->translators?->first()?->name ?? '-'),

                TextColumn::make('lang')
                    ->label(__('Language'))
                    ->formatStateUsing(fn(Edition $record) => $record->lang == 'en' ? 'انجليزي' : 'عربي'),

                TextColumn::make('copies_no')
                    ->label(__('Copies Number'))
                    ->getStateUsing(fn(Edition $record) => $record->copies->count()),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
