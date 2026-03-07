<?php

namespace App\Filament\Borrower\Pages;

use App\Enum\BorrowRequestStatusEnum;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.borrower.pages.dashboard';

    protected static ?string $title = 'Dashboard';

    protected static ?string $slug = '/';

    public function getViewData(): array
    {
        $borrower = auth('borrowers')->user();

        $activeCount = $borrower->borrowRequests()
            ->where('status', BorrowRequestStatusEnum::Approved->value)
            ->count();

        $overdueCount = $borrower->borrowRequests()
            ->where('status', BorrowRequestStatusEnum::Approved->value)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->count();

        return [
            'borrower'     => $borrower,
            'activeCount'  => $activeCount,
            'overdueCount' => $overdueCount,
        ];
    }
}
