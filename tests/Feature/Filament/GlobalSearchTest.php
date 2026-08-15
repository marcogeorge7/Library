<?php

namespace Tests\Feature\Filament;

use App\Filament\Admin\Resources\BookResource;
use App\Filament\Admin\Resources\EditionResource;
use App\Filament\Admin\Resources\PublisherResource;
use App\Filament\Admin\Resources\RevisorResource;
use App\Filament\Admin\Resources\SeriesResource;
use App\Filament\Admin\Resources\SubjectResource;
use App\Filament\Admin\Resources\TranslatorResource;
use App\Helpers\GetResourcesForPermissions;
use App\Models\Book;
use App\Models\Category;
use App\Models\Edition;
use App\Models\Publisher;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Global search filters out any result whose canEdit()/canView() the
        // user fails (see Resource::getGlobalSearchResults() -> ->filter()) --
        // a bare Admin role with no granted permissions would silently drop
        // every result, same permission bootstrap as DatabaseSeeder.
        GetResourcesForPermissions::fetchResources()->each(
            fn ($resource) => GetResourcesForPermissions::createCrudPermissions($resource)
        );
        GetResourcesForPermissions::syncPermissionsToSuperadmin();

        $user = User::factory()->create();
        $user->assignRole('Admin');

        $this->actingAs($user, 'web');
        Filament::setCurrentPanel(Filament::getPanel('admins'));
    }

    /**
     * Every case is checked in BOTH directions -- typing the canonical letter
     * must find a record stored with the variant, AND typing the variant must
     * find a record stored with the canonical letter (or a different variant
     * entirely), since normalization is applied symmetrically to the query and
     * the column. Regressing to a one-directional fix (e.g. only normalizing
     * the column, or only the search term) would silently break one of these.
     */
    public static function letterVariantCases(): array
    {
        return [
            'alef: hamza stored, bare typed' => ['أحمد في المدينة', 'احمد'],
            'alef: bare stored, hamza typed' => ['احمد في المدينة', 'أحمد'],
            'alef: madda stored, hamza-below typed' => ['آحمد في المدينة', 'إحمد'],
            'yeh/alef-maksura: maksura stored, yeh typed' => ['قصة مصطفى', 'مصطفي'],
            'yeh/alef-maksura: yeh stored, maksura typed' => ['قصة مصطفي', 'مصطفى'],
            'heh/teh-marbuta: marbuta stored, heh typed' => ['تاريخ المكتبة', 'المكتبه'],
            'heh/teh-marbuta: heh stored, marbuta typed' => ['تاريخ المكتبه', 'المكتبة'],
        ];
    }

    #[DataProvider('letterVariantCases')]
    public function test_book_search_matches_across_letter_variants_in_both_directions(string $storedName, string $searchTerm): void
    {
        $book = Book::factory()->create(['name' => $storedName, 'series_id' => null]);

        $results = BookResource::getGlobalSearchResults($searchTerm);

        $this->assertTrue($results->pluck('title')->contains($book->name));
    }

    public function test_previously_missing_resources_are_now_globally_searchable(): void
    {
        $this->assertNotEmpty(BookResource::getGloballySearchableAttributes());
        $this->assertNotEmpty(EditionResource::getGloballySearchableAttributes());
        $this->assertNotEmpty(PublisherResource::getGloballySearchableAttributes());
        $this->assertNotEmpty(SeriesResource::getGloballySearchableAttributes());
        $this->assertNotEmpty(TranslatorResource::getGloballySearchableAttributes());
        $this->assertNotEmpty(RevisorResource::getGloballySearchableAttributes());
        $this->assertNotEmpty(SubjectResource::getGloballySearchableAttributes());
    }

    public function test_edition_search_matches_book_name_across_variants(): void
    {
        $category = Category::factory()->create();
        $publisher = Publisher::factory()->create();
        $book = Book::factory()->create(['name' => 'إخوة يوسف', 'category_id' => $category->id, 'series_id' => null]);
        Edition::factory()->create(['book_id' => $book->id, 'publisher_id' => $publisher->id]);

        $results = EditionResource::getGlobalSearchResults('اخوة');

        $this->assertNotEmpty($results);
    }
}
