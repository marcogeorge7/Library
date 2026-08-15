<?php

namespace Tests\Feature\Services;

use App\Enum\BorrowRequestStatusEnum;
use App\Models\Borrower;
use App\Models\BorrowRequest;
use App\Models\Copy;
use App\Services\BorrowRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class BorrowRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_approve_marks_the_copy_as_borrowed(): void
    {
        $copy = Copy::factory()->create(['is_borrowed' => false]);
        $borrower = Borrower::factory()->create();
        $request = BorrowRequest::create([
            'borrower_id' => $borrower->id,
            'copy_id' => $copy->id,
            'status' => BorrowRequestStatusEnum::Pending,
            'requested_at' => now(),
        ]);

        (new BorrowRequestService())->approve($request, now()->addWeek());

        $this->assertTrue($copy->fresh()->is_borrowed);
        $this->assertSame(BorrowRequestStatusEnum::Approved, $request->fresh()->status);
    }

    public function test_approving_a_second_request_for_an_already_borrowed_copy_throws(): void
    {
        $copy = Copy::factory()->create(['is_borrowed' => false]);
        $borrowerA = Borrower::factory()->create();
        $borrowerB = Borrower::factory()->create();

        $requestA = BorrowRequest::create([
            'borrower_id' => $borrowerA->id,
            'copy_id' => $copy->id,
            'status' => BorrowRequestStatusEnum::Pending,
            'requested_at' => now(),
        ]);
        $requestB = BorrowRequest::create([
            'borrower_id' => $borrowerB->id,
            'copy_id' => $copy->id,
            'status' => BorrowRequestStatusEnum::Pending,
            'requested_at' => now(),
        ]);

        $service = new BorrowRequestService();
        $service->approve($requestA, now()->addWeek());

        $this->expectException(RuntimeException::class);
        $service->approve($requestB, now()->addWeek());

        // the first request's approval must stand -- unaffected by the second's failure
        $this->assertSame(BorrowRequestStatusEnum::Approved, $requestA->fresh()->status);
        $this->assertSame(BorrowRequestStatusEnum::Pending, $requestB->fresh()->status);
    }

    public function test_reject_and_mark_returned(): void
    {
        $copy = Copy::factory()->create(['is_borrowed' => false]);
        $borrower = Borrower::factory()->create();
        $request = BorrowRequest::create([
            'borrower_id' => $borrower->id,
            'copy_id' => $copy->id,
            'status' => BorrowRequestStatusEnum::Pending,
            'requested_at' => now(),
        ]);

        $service = new BorrowRequestService();
        $service->approve($request, now()->addWeek());
        $service->markReturned($request);

        $this->assertFalse($copy->fresh()->is_borrowed);
        $this->assertSame(BorrowRequestStatusEnum::Returned, $request->fresh()->status);
    }
}
