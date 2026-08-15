<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PrintJobResource\Pages;
use App\Filament\Admin\Resources\PrintJobResource\RelationManagers\CopiesRelationManager;
use App\Models\PrintJob;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PrintJobResource extends Resource
{
    protected static ?string $model = PrintJob::class;

    protected static ?string $slug = 'print-jobs';

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $modelLabel = 'Print Job';

    protected static ?string $pluralModelLabel = 'Print History';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('id')->label('Print Job #'),
                TextEntry::make('user.name')->label('Printed By')->placeholder('System'),
                TextEntry::make('created_at')->label('Printed At')->dateTime(),
                TextEntry::make('copies_count')
                    ->label('Total Copies')
                    ->state(fn (PrintJob $record) => $record->copies()->count()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('copies')->with('user'))
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('user.name')->label('Printed By')->placeholder('System'),
                TextColumn::make('copies_count')->label('Copies Printed')->sortable(),
                TextColumn::make('created_at')->label('Printed At')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            CopiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrintJobs::route('/'),
            'view' => Pages\ViewPrintJob::route('/{record}'),
        ];
    }
}
