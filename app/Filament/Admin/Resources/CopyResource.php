<?php

namespace App\Filament\Admin\Resources;

use App\Models\Copy;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CopyResource extends Resource
{
    protected static ?string $model = Copy::class;

    protected static ?string $slug = 'copies';

    protected static ?string $recordTitleAttribute = 'barcode';

    protected static ?string $modelLabel = 'النسخة';

    protected static ?string $pluralModelLabel = 'النسخ';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('barcode')
                    ->required(),

                Select::make('edition_id')
                    ->relationship('edition', 'name')
                    ->searchable()
                    ->required(),

                TextInput::make('status')
                    ->required(),

                TextInput::make('is_printed')
                    ->required(),

                Placeholder::make('created_at')
                    ->label('Created Date')
                    ->content(fn (?Copy $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                Placeholder::make('updated_at')
                    ->label('Last Modified Date')
                    ->content(fn (?Copy $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('barcode'),

                TextColumn::make('edition.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status'),

                TextColumn::make('is_printed'),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Admin\Resources\CopyResource\Pages\ListCopies::route('/'),
            'create' => \App\Filament\Admin\Resources\CopyResource\Pages\CreateCopy::route('/create'),
            'edit' => \App\Filament\Admin\Resources\CopyResource\Pages\EditCopy::route('/{record}/edit'),
            'view' => \App\Filament\Admin\Resources\CopyResource\Pages\ViewCopy::route('/{record}'),
        ];
    }
}
