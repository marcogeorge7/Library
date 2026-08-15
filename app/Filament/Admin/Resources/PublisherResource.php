<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms\Form;
use App\Models\Publisher;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use App\Filament\Admin\Resources\PublisherResource\Pages\EditPublisher;
use App\Filament\Admin\Resources\PublisherResource\Pages\ViewPublisher;
use App\Filament\Admin\Resources\PublisherResource\Pages\ListPublishers;
use App\Filament\Admin\Resources\PublisherResource\Pages\CreatePublisher;
use App\Filament\Admin\Resources\PublisherResource\RelationManagers\EditionsRelationManager;
use App\Filament\Concerns\HasNormalizedArabicGlobalSearch;
use App\Models\Book;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\Section as ComponentsSection;
use Filament\Tables\Actions\ViewAction as ActionsViewAction;
use Illuminate\Database\Eloquent\Model;

class PublisherResource extends Resource
{
    use HasNormalizedArabicGlobalSearch;

    protected static ?string $model = Publisher::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'الناشر';

    protected static ?string $pluralModelLabel = 'الناشرين';

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return ['Editions' => (string) $record->editions()->count()];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Publisher Details')
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Publisher Name'))
                            ->required()
                            ->unique(ignoreRecord: true),
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
                ActionsViewAction::make(),
                EditAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make(__('Publisher Details'))
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('Publisher Name')),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            EditionsRelationManager::class
        ];
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
