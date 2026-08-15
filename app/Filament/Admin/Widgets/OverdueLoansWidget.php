<?php

namespace App\Filament\Admin\Widgets;

use App\Enum\BorrowRequestStatusEnum;
use App\Models\BorrowRequest;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class OverdueLoansWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Overdue Loans')
            ->query(
                BorrowRequest::query()
                    ->where('status', BorrowRequestStatusEnum::Approved)
                    ->whereNotNull('due_date')
                    ->whereDate('due_date', '<', today())
                    ->with(['borrower', 'copy.book'])
            )
            ->columns([
                TextColumn::make('borrower.name')->label('Borrower'),
                TextColumn::make('copy.book.name')->label('Book'),
                TextColumn::make('copy.barcode')->label('Barcode'),
                TextColumn::make('due_date')->date()->label('Due Date')->color('danger'),
                TextColumn::make('days_overdue')
                    ->label('Days Overdue')
                    ->getStateUsing(fn (BorrowRequest $record) => now()->diffInDays($record->due_date))
                    ->color('danger'),
            ])
            ->defaultSort('due_date')
            ->paginated([5, 10, 25])
            ->actions([])
            ->bulkActions([]);
    }
}
