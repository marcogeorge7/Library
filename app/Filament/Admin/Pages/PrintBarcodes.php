<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Resources\CopyResource;
use App\Models\Copy;
use App\Models\Edition;
use App\Services\PrintJobService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Throwable;

class PrintBarcodes extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-printer';

    protected static string $view = 'filament.admin.pages.print-barcodes';

    protected static ?string $title = 'Print Barcodes';

    protected static ?string $slug = 'print-barcodes';

    public string $mode = 'editions'; // 'editions' | 'copies'

    public string $search = '';

    public function updatedMode(): void
    {
        $this->search = '';
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $this->mode === 'copies'
            ? $this->copiesTable($table)
            : $this->editionsTable($table);
    }

    protected function editionsTable(Table $table): Table
    {
        return $table
            ->query(
                Edition::query()
                    ->with(['book.series', 'publisher'])
                    ->withCount('copies')
                    ->withCount(['copies as printed_copies_count' => fn (Builder $q) => $q->where('is_printed', true)])
                    ->when($this->search, fn ($q) => $q->whereHas(
                        'book',
                        fn ($q2) => $q2->where('name', 'like', "%{$this->search}%")
                    ))
            )
            ->columns([
                TextColumn::make('book.name')->label('Book Title')->sortable(),
                TextColumn::make('book.series.name')->label('Series')->placeholder('—'),
                TextColumn::make('part_name')->label('Part')->placeholder('—'),
                TextColumn::make('publisher.name')->label('Publisher')->placeholder('—'),
                TextColumn::make('copies_count')->label('Total Copies'),
                TextColumn::make('printed_copies_count')->label('Already Printed'),
            ])
            ->actions([
                Action::make('print_all_copies')
                    ->label('Print All Copies')
                    ->icon('heroicon-o-printer')
                    ->requiresConfirmation()
                    ->visible(fn (Edition $record) => $record->copies()->exists())
                    ->action(fn (Edition $record) => $this->handlePrint($record->copies()->with('book')->get())),
            ])
            ->bulkActions([
                BulkAction::make('print_selected_editions')
                    ->label('Print All Copies of Selected')
                    ->icon('heroicon-o-printer')
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $this->handlePrint(
                        Copy::query()->whereIn('edition_id', $records->pluck('id'))->with('book')->get()
                    ))
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    protected function copiesTable(Table $table): Table
    {
        return $table
            ->query(
                Copy::query()
                    ->with(['book.series', 'edition', 'activeBorrowRequest.borrower'])
                    ->when($this->search, fn ($q) => $q
                        ->where('barcode', 'like', "%{$this->search}%")
                        ->orWhereHas('book', fn ($q2) => $q2->where('name', 'like', "%{$this->search}%")))
            )
            ->columns([
                ...CopyResource\Fields\Table::columns(),
                TextColumn::make('printed_at')->label('Last Printed')->dateTime()->placeholder('Never'),
            ])
            ->actions([
                Action::make('reprint')
                    ->label(fn (Copy $record) => $record->is_printed ? 'Reprint' : 'Print')
                    ->icon('heroicon-o-printer')
                    ->action(fn (Copy $record) => $this->handlePrint(collect([$record]))),
            ])
            ->bulkActions([
                BulkAction::make('print_selected_copies')
                    ->label('Print Selected Copies')
                    ->icon('heroicon-o-printer')
                    ->action(fn (Collection $records) => $this->handlePrint($records))
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    /**
     * @param  Collection<int, Copy>  $copies
     */
    protected function handlePrint(Collection $copies): void
    {
        if ($copies->isEmpty()) {
            Notification::make()->title('No copies to print')->warning()->send();

            return;
        }

        try {
            app(PrintJobService::class)->print($copies, auth()->user());

            Notification::make()->title('Printed '.$copies->count().' label(s)')->success()->send();
        } catch (Throwable $e) {
            Notification::make()->title('Print failed')->body($e->getMessage())->danger()->send();
        }

        $this->resetTable();
    }
}
