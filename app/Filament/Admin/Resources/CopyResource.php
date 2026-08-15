<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Concerns\HasNormalizedArabicGlobalSearch;
use App\Models\Copy;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CopyResource extends Resource
{
    use HasNormalizedArabicGlobalSearch;

    protected static ?string $model = Copy::class;

    protected static ?string $slug = 'copies';

    protected static ?string $recordTitleAttribute = 'barcode';

    protected static ?string $modelLabel = 'النسخة';

    protected static ?string $pluralModelLabel = 'النسخ';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = ['Book' => $record->book?->name ?? '—'];

        $details['Status'] = $record->is_borrowed
            ? 'Borrowed by '.($record->activeBorrowRequest?->borrower?->name ?? 'unknown')
            : 'Available';

        return $details;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('barcode')
                    ->required()
                    ->unique(ignoreRecord: true),

                Select::make('edition_id')
                    ->relationship('edition', 'id')
                    ->getOptionLabelFromRecordUsing(
                        fn ($record) => trim($record->book?->name.' — '.$record?->publisher?->name, ' —')
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Placeholder::make('created_at')
                    ->label('Created Date')
                    ->content(fn(?Copy $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                Placeholder::make('updated_at')
                    ->label('Last Modified Date')
                    ->content(fn(?Copy $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('barcode'),

                TextColumn::make('book.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('book.series.name')
                    ->label(__('Series Name'))
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('edition.part_name')
                    ->label(__('Part Name'))
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_borrowed')
                    ->label(__('Is Borrowed'))
                    ->boolean(),

                IconColumn::make('is_printed')
                    ->label(__('Is Printed'))
                    ->boolean(),

                TextColumn::make('activeBorrowRequest.borrower.name')
                    ->label('Borrowed By')
                    ->placeholder('—'),
            ])
            // Eager-load relationships to avoid N+1 queries on the index and speed up previewing many records.
            ->modifyQueryUsing(function (Builder $query) {
                return $query->with(['book.series', 'edition', 'activeBorrowRequest.borrower']);
            })
            // Allow large pages to make it easy to preview many/all records at once.
            ->paginationPageOptions([25, 50, 100, 200, 500, 1000])
            // Make rows clickable to open the record view for quick preview.
            ->recordUrl(fn(Copy $record): string => EditionResource::getUrl('view', ['record' => $record->edition_id]))
            ->filters([])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->label('Edit Barcode')
                    ->icon('heroicon-o-pencil-square'),
                Action::make('view_edition')
                    ->label('View Edition')
                    ->icon('heroicon-o-eye')
                    ->url(fn(Copy $record): string => \App\Filament\Admin\Resources\EditionResource::getUrl('view', ['record' => $record->edition_id]))
                    ->openUrlInNewTab(false),
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
