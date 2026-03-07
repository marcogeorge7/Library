<?php

namespace App\Filament\Admin\Resources\BorrowerResource\RelationManagers;

use App\Enum\BorrowRequestStatusEnum;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BorrowRequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'borrowRequests';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('copy.barcode')
                    ->label('Barcode'),

                TextColumn::make('copy.edition.book.name')
                    ->label('Book'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(BorrowRequestStatusEnum $state) => $state->color()),

                TextColumn::make('requested_at')
                    ->dateTime(),

                TextColumn::make('due_date')
                    ->date(),

                TextColumn::make('returned_at')
                    ->dateTime(),
            ])
            ->actions([])
            ->headerActions([]);
    }
}
