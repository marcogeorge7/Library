<?php

namespace App\Services;

use App\Enum\BorrowRequestStatusEnum;
use App\Models\BorrowRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BorrowRequestService
{
    public function approve(BorrowRequest $request, ?Carbon $dueDate = null): void
    {
        DB::transaction(function () use ($request, $dueDate) {
            $request->update([
                'status'      => BorrowRequestStatusEnum::Approved,
                'approved_at' => now(),
                'due_date'    => $dueDate,
            ]);

            $request->copy->update(['is_borrowed' => true]);
        });
    }

    public function reject(BorrowRequest $request, ?string $notes = null): void
    {
        DB::transaction(function () use ($request, $notes) {
            $request->update([
                'status' => BorrowRequestStatusEnum::Rejected,
                'notes'  => $notes,
            ]);
        });
    }

    public function markReturned(BorrowRequest $request): void
    {
        DB::transaction(function () use ($request) {
            $request->update([
                'status'      => BorrowRequestStatusEnum::Returned,
                'returned_at' => now(),
            ]);

            $request->copy->update(['is_borrowed' => false]);
        });
    }
}
