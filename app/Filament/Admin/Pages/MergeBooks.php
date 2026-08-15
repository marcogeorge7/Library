<?php

namespace App\Filament\Admin\Pages;

use App\Models\Book;
use App\Services\BarCode;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Throwable;

class MergeBooks extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-pointing-in';

    protected static string $view = 'filament.admin.pages.merge-books';

    protected static ?string $title = 'Merge Books';

    protected static ?string $slug = 'merge-books';

    public string $search = '';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Book::query()
                    ->with(['category', 'series'])
                    ->withCount('editions')
                    ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            )
            ->columns([
                TextColumn::make('name')->label('Book Title')->sortable(),
                TextColumn::make('category.name')->label('Category')->placeholder('—'),
                TextColumn::make('series.name')->label('Series')->placeholder('—'),
                TextColumn::make('editions_count')->label('Editions'),
            ])
            ->actions([
                Action::make('merge_into')
                    ->label('Merge Into...')
                    ->icon('heroicon-o-arrows-pointing-in')
                    ->visible(fn (Book $record) => $record->editions()->exists())
                    ->requiresConfirmation()
                    ->modalDescription('This moves all of this book\'s edition(s) onto the target book as additional parts, regenerates barcodes for their copies (previously-printed labels become invalid), and removes this now-empty book.')
                    ->form([
                        Select::make('target_book_id')
                            ->label('Merge into')
                            ->options(fn (Book $record) => Book::where('id', '!=', $record->id)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                $set('starting_part_code', (Book::find($state)?->editions()->max('partCode') ?? 0) + 1);
                            }),
                        Placeholder::make('category_warning')
                            ->label('')
                            ->content(fn (Get $get, Book $record) => 'Note: the target book is in a different category ("'
                                .(Book::find($get('target_book_id'))?->category?->name).'") than this book ("'
                                .$record->category?->name.'").')
                            ->visible(fn (Get $get, Book $record) => $get('target_book_id')
                                && Book::find($get('target_book_id'))?->category_id !== $record->category_id),
                        TextInput::make('starting_part_code')
                            ->label('Starting Part Code')
                            ->helperText('If this book has more than one edition, they\'re numbered sequentially from here.')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])
                    ->action(function (Book $record, array $data) {
                        try {
                            $summary = DB::transaction(function () use ($record, $data) {
                                $target = Book::findOrFail($data['target_book_id']);
                                $editions = $record->editions()->orderBy('created_at')->get();
                                $partCode = (int) $data['starting_part_code'];
                                $copiesRelabeled = 0;

                                foreach ($editions as $edition) {
                                    $edition->update(['book_id' => $target->id, 'partCode' => $partCode]);
                                    $edition->setRelation('book', $target);
                                    $partCode++;

                                    $newBarcode = BarCode::generate($edition);
                                    foreach ($edition->copies as $copy) {
                                        $index = $edition->copyOrderInEdition($copy->id);
                                        $copy->update([
                                            'barcode' => $newBarcode.$index,
                                            'is_printed' => false,
                                            'printed_at' => null,
                                        ]);
                                        $copiesRelabeled++;
                                    }
                                }

                                $target->author()->syncWithoutDetaching($record->author->pluck('id'));
                                $record->delete();

                                return [$editions->count(), $copiesRelabeled];
                            });

                            [$editionCount, $copyCount] = $summary;

                            Notification::make()
                                ->title("Merged {$editionCount} edition(s) and relabeled {$copyCount} copy(ies)")
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Merge failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }
}
