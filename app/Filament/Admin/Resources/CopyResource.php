<?php

namespace App\Filament\Admin\Resources;

use App\Models\Copy;
use App\Services\BarcodeLabelGenerator;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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

                TextColumn::make('status'),

                TextColumn::make('is_printed'),

                TextColumn::make('activeBorrowRequest.borrower.name')
                    ->label('Borrowed By')
                    ->placeholder('—'),
            ])
            // Eager-load relationships to avoid N+1 queries on the index and speed up previewing many records.
            ->modifyQueryUsing(function (Builder $query) {
                return $query->with(['book:name']);
            })
            // Allow large pages to make it easy to preview many/all records at once.
            ->paginationPageOptions([25, 50, 100, 200, 500, 1000])
            // Make rows clickable to open the record view for quick preview.
            ->recordUrl(fn(Copy $record): string => EditionResource::getUrl('view', ['record' => $record->edition_id]))
            ->filters([])
            ->actions([
                ViewAction::make(),
//                EditAction::make(),
//                DeleteAction::make(),
                Action::make('view_edition')
                    ->label('View Edition')
                    ->icon('heroicon-o-eye')
                    ->url(fn(Copy $record): string => \App\Filament\Admin\Resources\EditionResource::getUrl('view', ['record' => $record->edition_id]))
                    ->openUrlInNewTab(false),
            ])
            ->bulkActions([
                BulkAction::make('print_selected_barcodes')
                    ->label('Print Barcode Labels (40 per page)')
                    ->icon('heroicon-o-printer')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('count')
                            ->label('Labels per selected copy')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                    ])
                    ->action(function (Collection $records, array $data) {
                        $items = [];
                        $count = (int)($data['count'] ?? 1);
                        foreach ($records as $copy) {
                            for ($i = 0; $i < $count; $i++) {
                                $items[] = [
                                    'name' => $copy->book_name ?? 'Book',
                                    'barcode' => $copy->barcode,
                                ];
                            }
                        }

                        $generator = new BarcodeLabelGenerator();
                        $pdfContent = $generator->generate($items, 4, 10);

                        return response()->streamDownload(
                            fn() => print($pdfContent),
                            'copy-barcodes-' . now()->format('Ymd-His') . '.pdf',
                            ['Content-Type' => 'application/pdf']
                        );
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
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
