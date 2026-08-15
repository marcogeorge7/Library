<?php

namespace Tests\Feature\Filament;

use App\Enum\BorrowRequestStatusEnum;
use App\Filament\Admin\Resources\BookResource;
use App\Filament\Admin\Resources\BorrowRequestResource;
use App\Filament\Admin\Resources\CopyResource;
use App\Helpers\GetResourcesForPermissions;
use App\Models\Author;
use App\Models\Book;
use App\Models\BorrowRequest;
use App\Models\Borrower;
use App\Models\Category;
use App\Models\Copy;
use App\Models\Edition;
use App\Models\Publisher;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSearchDetailsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        GetResourcesForPermissions::fetchResources()->each(
            fn ($resource) => GetResourcesForPermissions::createCrudPermissions($resource)
        );
        GetResourcesForPermissions::syncPermissionsToSuperadmin();

        $user = User::factory()->create();
        $user->assignRole('Admin');

        $this->actingAs($user, 'web');
        Filament::setCurrentPanel(Filament::getPanel('admins'));
    }

    public function test_book_search_result_details_show_category_and_author(): void
    {
        $category = Category::factory()->create(['name' => 'اللاهوت']);
        $author = Author::factory()->create(['name' => 'أثناسيوس']);
        $book = Book::factory()->create(['name' => 'كتاب الاختبار', 'category_id' => $category->id, 'series_id' => null]);
        $book->author()->attach($author);

        $results = BookResource::getGlobalSearchResults('الاختبار');
        $details = $results->first()->details;

        $this->assertSame($category->name, $details['Category']);
        $this->assertSame($author->name, $details['Author']);
    }

    public function test_copy_search_result_details_show_book_and_borrowed_status(): void
    {
        $category = Category::factory()->create();
        $publisher = Publisher::factory()->create();
        $book = Book::factory()->create(['name' => 'كتاب النسخة', 'category_id' => $category->id, 'series_id' => null]);
        $edition = Edition::factory()->create(['book_id' => $book->id, 'publisher_id' => $publisher->id]);
        $copy = Copy::factory()->create(['edition_id' => $edition->id, 'barcode' => 'TESTBARCODE123', 'is_borrowed' => true]);

        $borrower = Borrower::factory()->create(['name' => 'مستعير الاختبار']);
        BorrowRequest::create([
            'copy_id' => $copy->id,
            'borrower_id' => $borrower->id,
            'status' => BorrowRequestStatusEnum::Approved,
            'requested_at' => now(),
        ]);

        $results = CopyResource::getGlobalSearchResults('TESTBARCODE123');
        $details = $results->first()->details;

        $this->assertSame($book->name, $details['Book']);
        $this->assertStringContainsString($borrower->name, $details['Status']);
    }

    public function test_borrow_request_search_result_details_show_borrower_and_status(): void
    {
        $category = Category::factory()->create();
        $publisher = Publisher::factory()->create();
        $book = Book::factory()->create(['category_id' => $category->id, 'series_id' => null]);
        $edition = Edition::factory()->create(['book_id' => $book->id, 'publisher_id' => $publisher->id]);
        $copy = Copy::factory()->create(['edition_id' => $edition->id]);
        $borrower = Borrower::factory()->create(['name' => 'طالب الاختبار']);

        $borrowRequest = BorrowRequest::create([
            'copy_id' => $copy->id,
            'borrower_id' => $borrower->id,
            'status' => BorrowRequestStatusEnum::Pending,
            'requested_at' => now(),
        ]);

        $results = BorrowRequestResource::getGlobalSearchResults((string) $borrowRequest->id);
        $details = $results->first()->details;

        $this->assertSame($borrower->name, $details['Borrower']);
        $this->assertSame(BorrowRequestStatusEnum::Pending->label(), $details['Status']);
    }
}
