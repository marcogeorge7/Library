<?php

namespace App\Filament\Admin\Widgets;

use App\Enum\BorrowRequestStatusEnum;
use App\Filament\Admin\Resources\BorrowRequestResource;
use App\Models\Book;
use App\Models\BorrowRequest;
use App\Models\Copy;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LibraryStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $overdueCount = BorrowRequest::query()
            ->where('status', BorrowRequestStatusEnum::Approved)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->count();

        return [
            Stat::make('Total Books', Book::count())
                ->icon('heroicon-o-book-open'),

            Stat::make('Total Copies', Copy::count())
                ->icon('heroicon-o-rectangle-stack'),

            Stat::make('Currently Borrowed', Copy::borrowed()->count())
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->color('warning'),

            Stat::make('Pending Requests', BorrowRequest::where('status', BorrowRequestStatusEnum::Pending)->count())
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->url(BorrowRequestResource::getUrl()),

            Stat::make('Overdue Loans', $overdueCount)
                ->icon('heroicon-o-exclamation-triangle')
                ->color($overdueCount > 0 ? 'danger' : 'success')
                ->url(BorrowRequestResource::getUrl()),
        ];
    }
}
