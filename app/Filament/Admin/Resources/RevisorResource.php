<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RevisorResource\Pages;
use App\Filament\Concerns\HasNormalizedArabicGlobalSearch;
use App\Models\Revisor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RevisorResource extends Resource
{
    use HasNormalizedArabicGlobalSearch;

    protected static ?string $model = Revisor::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'المراجع';

    protected static ?string $pluralModelLabel = 'المراجعين';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Revisor Details'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('Revisor Name'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Revisor Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make(__('Translator Details'))
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('Revisor Name')),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRevisors::route('/'),
            'create' => Pages\CreateRevisor::route('/create'),
            'edit' => Pages\EditRevisor::route('/{record}/edit'),
            'view' => Pages\ViewRevisor::route('/{record}'),
        ];
    }
}
