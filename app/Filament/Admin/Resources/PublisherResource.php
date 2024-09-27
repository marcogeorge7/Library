<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PublisherResource\Pages\CreatePublisher;
use App\Filament\Admin\Resources\PublisherResource\Pages\EditPublisher;
use App\Filament\Admin\Resources\PublisherResource\Pages\ListPublishers;
use App\Filament\Admin\Resources\PublisherResource\Pages\ViewPublisher;
use App\Models\Publisher;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
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

class PublisherResource extends Resource
{
    protected static ?string $model = Publisher::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'الناشر';

    protected static ?string $pluralModelLabel = 'الناشرين';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Publisher Details')
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Publisher Name'))
                            ->required(),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Publisher Name'))
                    ->searchable()
                    ->sortable(),
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make(__('Translator Details'))
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('Publisher Name')),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPublishers::route('/'),
            'create' => CreatePublisher::route('/create'),
            'edit' => EditPublisher::route('/{record}/edit'),
            'view' => ViewPublisher::route('/{record}'),
        ];
    }
}
