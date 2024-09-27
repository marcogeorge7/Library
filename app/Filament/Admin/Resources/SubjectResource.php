<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources;
use App\Models\Subject;
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
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $modelLabel = 'الموضوع';

    protected static ?string $pluralModelLabel = 'المواضيع';

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Subject Details'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Subject Name'))
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Subject Name'))
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
                ViewAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
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
                            ->label(__('Subject Name')),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Resources\SubjectResource\Pages\ListSubjects::route('/'),
            'create' => Resources\SubjectResource\Pages\CreateSubject::route('/create'),
            'edit' => Resources\SubjectResource\Pages\EditSubject::route('/{record}/edit'),
            'view' => Resources\SubjectResource\Pages\ViewSubject::route('/{record}'),
        ];
    }
}
