<?php

namespace Tests\Feature\Filament;

use App\Enum\BorrowRequestStatusEnum;
use App\Filament\Admin\Widgets\LibraryStatsOverview;
use App\Filament\Admin\Widgets\OverdueLoansWidget;
use App\Filament\Admin\Widgets\RecentBorrowRequestsWidget;
use App\Helpers\GetResourcesForPermissions;
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
use Livewire\Livewire;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
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

    protected function makeOverdueLoan(): array
    {
        $category = Category::factory()->create();
        $publisher = Publisher::factory()->create();
        $book = Book::factory()->create(['name' => 'كتاب متأخر', 'category_id' => $category->id, 'series_id' => null]);
        $edition = Edition::factory()->create(['book_id' => $book->id, 'publisher_id' => $publisher->id]);
        $copy = Copy::factory()->create(['edition_id' => $edition->id, 'is_borrowed' => true]);
        $borrower = Borrower::factory()->create(['name' => 'مستعير متأخر']);

        $request = BorrowRequest::create([
            'copy_id' => $copy->id,
            'borrower_id' => $borrower->id,
            'status' => BorrowRequestStatusEnum::Approved,
            'requested_at' => now()->subMonth(),
            'due_date' => now()->subWeek(),
        ]);

        return [$book, $borrower, $request];
    }

    public function test_dashboard_route_loads_successfully(): void
    {
        $this->get('/admin')->assertSuccessful();
    }

    public function test_stats_overview_widget_shows_real_counts(): void
    {
        [$book, $borrower] = $this->makeOverdueLoan();

        Livewire::test(LibraryStatsOverview::class)
            ->assertSuccessful()
            ->assertSee('Total Books')
            ->assertSee('Currently Borrowed')
            ->assertSee('Pending Requests')
            ->assertSee('Overdue Loans');
    }

    public function test_overdue_loans_widget_lists_the_overdue_loan(): void
    {
        [$book, $borrower] = $this->makeOverdueLoan();

        Livewire::test(OverdueLoansWidget::class)
            ->assertSuccessful()
            ->assertSee('Overdue Loans')
            ->assertSee($book->name)
            ->assertSee($borrower->name);
    }

    public function test_recent_borrow_requests_widget_lists_the_request(): void
    {
        [$book, $borrower] = $this->makeOverdueLoan();

        Livewire::test(RecentBorrowRequestsWidget::class)
            ->assertSuccessful()
            ->assertSee('Recent Activity')
            ->assertSee($book->name)
            ->assertSee($borrower->name)
            ->assertSee(BorrowRequestStatusEnum::Approved->label());
    }
}
