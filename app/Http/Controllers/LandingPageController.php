<?php

namespace App\Http\Controllers;

use App\Enum\BorrowRequestStatusEnum;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Copy;
use App\Models\Edition;
use Illuminate\Support\Facades\Cache;

class LandingPageController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('landing_stats', 300, function () {
            return [
                'totalBooks' => Book::count(),

                // Same "available to borrow" rule as
                // App\Filament\Borrower\Pages\BookSearch::availableCopies():
                // not borrowed, the edition allows external borrowing, and not
                // already tied to a pending/approved borrow request.
                'availableCopies' => Copy::query()
                    ->where('is_borrowed', false)
                    ->whereHas('edition', fn ($q) => $q->where('internal_borrowing', false))
                    ->whereDoesntHave('borrowRequests', fn ($q) => $q->whereIn('status', [
                        BorrowRequestStatusEnum::Pending,
                        BorrowRequestStatusEnum::Approved,
                    ]))
                    ->count(),

                'totalCategories' => Category::count(),
                'totalAuthors' => Author::count(),

                'recentEditions' => Edition::query()
                    ->where('internal_borrowing', false)
                    ->with(['book.category', 'book.author'])
                    ->latest()
                    ->take(6)
                    ->get(),
            ];
        });

        return view('landing', $stats);
    }
}
