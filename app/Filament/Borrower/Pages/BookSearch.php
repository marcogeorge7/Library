<?php

namespace App\Filament\Borrower\Pages;

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

                TextColumn::make('book.authors.name')
                    ->label('Author')
                    ->listWithLineBreaks(),

                TextColumn::make('available_copies')
                    ->label('Available Copies')
                    ->getStateUsing(fn(Edition $record) => $record->copies->where('is_borrowed', false)->count()),
            ])
            ->actions([
                Action::make('request_borrow')
                    ->label('Request Borrow')
                    ->icon('heroicon-o-plus-circle')
                    ->visible(fn(Edition $record) => $record->copies->where('is_borrowed', false)->count() > 0)
                    ->form(fn(Edition $record) => [
                        Select::make('copy_id')
                            ->label('Select Copy')
                            ->options(
                                $record->copies
                                    ->where('is_borrowed', false)
                                    ->pluck('barcode', 'id')
                            )
                            ->required(),
                    ])
                    ->action(function (Edition $record, array $data) {
                        BorrowRequest::create([
                            'borrower_id'  => auth('borrowers')->id(),
                            'copy_id'      => $data['copy_id'],
                            'status'       => 'pending',
                            'requested_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Borrow request submitted')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
