<?php

namespace App\Services;

use App\Enum\BorrowRequestStatusEnum;
use App\Models\BorrowRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BorrowRequestService
{
    /**
     * @throws RuntimeException if the copy is already lent out (e.g. another
     *         pending request for the same copy was approved first).
     */
    public function approve(BorrowRequest $request, ?Carbon $dueDate = null): void
    {
        DB::transaction(function () use ($request, $dueDate) {
            $copy = $request->copy()->lockForUpdate()->first();

            if ($copy->is_borrowed) {
                throw new RuntimeException('This copy is already borrowed and cannot be approved for another request.');
            }

            $request->update([
                'status'      => BorrowRequestStatusEnum::Approved,
                'approved_at' => now(),
                'due_date'    => $dueDate,
            ]);

            $copy->update(['is_borrowed' => true]);
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
