<?php

namespace App\Filament\Admin\Widgets;

use App\Enum\BorrowRequestStatusEnum;
use App\Models\BorrowRequest;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentBorrowRequestsWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent Activity')
            ->query(
                BorrowRequest::query()
                    ->with(['borrower', 'copy.book'])
            )
            ->columns([
                TextColumn::make('borrower.name')->label('Borrower'),
                TextColumn::make('copy.book.name')->label('Book'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (BorrowRequestStatusEnum $state) => $state->label())
                    ->color(fn (BorrowRequestStatusEnum $state) => $state->color()),
                TextColumn::make('requested_at')->dateTime()->label('Requested'),
            ])
            ->defaultSort('requested_at', 'desc')
            ->paginated([5, 10, 25])
            ->actions([])
            ->bulkActions([]);
    }
}
