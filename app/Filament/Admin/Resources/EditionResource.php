<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EditionResource\Pages\CreateEdition;
use App\Filament\Admin\Resources\EditionResource\Pages\EditEdition;
use App\Filament\Admin\Resources\EditionResource\Pages\ListEditions;
use App\Filament\Admin\Resources\EditionResource\Pages\ViewEdition;
use App\Models\Edition;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
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
            ->schema([
                TextInput::make('name'),

                Select::make('book_id')
                    ->relationship('book', 'name')
                    ->searchable()
                    ->required(),

                TextInput::make('partCode')
                    ->required()
                    ->integer(),

                Select::make('publisher_id')
                    ->relationship('publisher', 'name')
                    ->searchable()
                    ->required(),

                TextInput::make('publish_year')
                    ->required(),

                TextInput::make('lang')
                    ->required(),

                TextInput::make('cover')
                    ->required(),

                Placeholder::make('created_at')
                    ->label('Created Date')
                    ->content(fn (?Edition $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                Placeholder::make('updated_at')
                    ->label('Last Modified Date')
                    ->content(fn (?Edition $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('book.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('partCode'),

                TextColumn::make('publisher.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('publish_year'),

                TextColumn::make('lang'),

                TextColumn::make('cover'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
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
