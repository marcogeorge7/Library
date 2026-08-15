<?php

namespace App\Filament\Borrower\Pages;

use App\Enum\BorrowRequestStatusEnum;
use App\Models\BorrowRequest;
use App\Models\Copy;
use App\Models\Edition;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookSearch extends Page implements HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static string $view = 'filament.borrower.pages.book-search';

    protected static ?string $title = 'Browse Books';

    protected static ?string $slug = 'books';

    public string $search = '';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Edition::query()
                    ->with(['book', 'copies'])
                    ->whereHas('book', function (Builder $q) {
                        $q->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"));
                    })
                    ->where('internal_borrowing', false)
            )
            ->columns([
                TextColumn::make('book.name')
                    ->label('Title')
                    ->searchable(),

                TextColumn::make('book.author.name')
                    ->label('Author')
                    ->listWithLineBreaks(),

                TextColumn::make('available_copies')
                    ->label('Available Copies')
                    ->getStateUsing(fn(Edition $record) => $this->availableCopies($record)->count()),
            ])
            ->actions([
                Action::make('request_borrow')
                    ->label('Request Borrow')
                    ->icon('heroicon-o-plus-circle')
                    ->visible(fn(Edition $record) => $this->availableCopies($record)->isNotEmpty())
                    ->form(fn(Edition $record) => [
                        Select::make('copy_id')
                            ->label('Select Copy')
                            ->options($this->availableCopies($record)->pluck('barcode', 'id'))
                            ->required(),
                    ])
                    ->action(function (Edition $record, array $data) {
                        $copyId = $data['copy_id'];

                        // Re-check under a lock at submit time -- the options list
                        // above can go stale between opening the form and submitting
                        // it (e.g. someone else requested the same copy first).
                        $alreadyRequested = BorrowRequest::where('copy_id', $copyId)
                            ->whereIn('status', [BorrowRequestStatusEnum::Pending, BorrowRequestStatusEnum::Approved])
                            ->exists();

                        if ($alreadyRequested) {
                            Notification::make()
                                ->title('That copy was just requested by someone else')
                                ->body('Please pick a different copy.')
                                ->warning()
                                ->send();

                            return;
                        }

                        BorrowRequest::create([
                            'borrower_id'  => auth('borrowers')->id(),
                            'copy_id'      => $copyId,
                            'status'       => BorrowRequestStatusEnum::Pending,
                            'requested_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Borrow request submitted')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    /**
     * Copies that are neither currently lent out nor already the subject of a
     * pending/approved request -- `is_borrowed` alone only flips true on approval,
     * so without this a second borrower could still request (and an admin could
     * still approve) a copy someone else already has a live request on.
     */
    protected function availableCopies(Edition $record)
    {
        $unavailableCopyIds = BorrowRequest::whereIn('copy_id', $record->copies->pluck('id'))
            ->whereIn('status', [BorrowRequestStatusEnum::Pending, BorrowRequestStatusEnum::Approved])
            ->pluck('copy_id');

        return $record->copies
            ->where('is_borrowed', false)
            ->whereNotIn('id', $unavailableCopyIds);
    }
}
