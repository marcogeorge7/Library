<?php

namespace App\Filament\Borrower\Pages;

use App\Enum\BorrowRequestStatusEnum;
use App\Models\BorrowRequest;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MyRequests extends Page implements HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string $view = 'filament.borrower.pages.my-requests';

    protected static ?string $title = 'My Requests';

    protected static ?string $slug = 'requests';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BorrowRequest::query()
                    ->where('borrower_id', auth('borrowers')->id())
                    ->with(['copy.edition.book'])
            )
            ->columns([
                TextColumn::make('copy.edition.book.name')
                    ->label('Book'),

                TextColumn::make('copy.barcode')
                    ->label('Barcode'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(BorrowRequestStatusEnum $state) => $state->color()),

                TextColumn::make('requested_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('due_date')
                    ->date(),

                TextColumn::make('returned_at')
                    ->dateTime(),
            ])
            ->defaultSort('requested_at', 'desc');
    }
}
