<?php

namespace App\Filament\Admin\Resources\PrintJobResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CopiesRelationManager extends RelationManager
{
    protected static string $relationship = 'copies';

    protected static ?string $title = 'Copies Printed in This Job';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('barcode')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('book'))
            ->columns([
                TextColumn::make('barcode'),
                TextColumn::make('book.name')->label('Book Title'),
                TextColumn::make('edition.part_name')->label('Part')->placeholder('—'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
