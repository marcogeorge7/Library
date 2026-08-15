<?php

namespace Tests\Feature\Filament;

use App\Filament\Admin\Pages\MergeBooks;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Copy;
use App\Models\Edition;
use App\Models\Publisher;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MergeBooksTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsAdmin(): User
    {
        $role = Role::create(['name' => 'Admin']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user, 'web');
        Filament::setCurrentPanel(Filament::getPanel('admins'));

        return $user;
    }

    public function test_merging_moves_edition_regenerates_barcode_and_soft_deletes_source(): void
    {
        $this->actingAsAdmin();

        $category = Category::factory()->create();
        $author = Author::factory()->create();
        $publisher = Publisher::factory()->create();

        $target = Book::factory()->create(['category_id' => $category->id, 'series_id' => null, 'order' => 1]);
        $targetEdition = Edition::factory()->create(['book_id' => $target->id, 'publisher_id' => $publisher->id, 'partCode' => 1]);

        $source = Book::factory()->create(['category_id' => $category->id, 'series_id' => null, 'order' => 2]);
        $source->author()->attach($author);
        $sourceEdition = Edition::factory()->create(['book_id' => $source->id, 'publisher_id' => $publisher->id, 'partCode' => 1]);
        $sourceCopy = Copy::factory()->create(['edition_id' => $sourceEdition->id, 'barcode' => 'OLD-BARCODE', 'is_printed' => true]);

        Livewire::test(MergeBooks::class)
            ->assertSuccessful()
            ->callTableAction('merge_into', $source, data: [
                'target_book_id' => $target->id,
                'starting_part_code' => 2,
            ])
            ->assertSuccessful();

        $sourceEdition->refresh();
        $this->assertSame($target->id, $sourceEdition->book_id);
        $this->assertSame(2, $sourceEdition->partCode);

        $sourceCopy->refresh();
        $this->assertNotSame('OLD-BARCODE', $sourceCopy->barcode);
        $this->assertFalse($sourceCopy->is_printed);
        $this->assertNull($sourceCopy->printed_at);

        $target->refresh();
        $this->assertTrue($target->author->pluck('id')->contains($author->id));
        $this->assertCount(2, $target->editions);

        $this->assertSoftDeleted('books', ['id' => $source->id]);
    }
}
