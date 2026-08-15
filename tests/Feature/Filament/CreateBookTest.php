<?php

namespace Tests\Feature\Filament;

use App\Filament\Admin\Resources\BookResource\Pages\CreateBook;
use App\Models\Author;
use App\Models\Category;
use App\Models\Book;
use App\Models\Publisher;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regression test for a real, verified bug: the admin "Create Book" form used to
 * collect fields that aren't `books` columns (publisher_id, partCode, lang,
 * internal_borrowing, number_of_copy, is_series) without stripping them before
 * Book::create($data), which crashed every submission with an "unknown column" SQL
 * error -- the default (1-copy) path then crashed a second time on
 * Copy::create(['internal_borrowing' => ...]), a column that only exists on editions.
 */
class CreateBookTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsAdmin(): User
    {
        $role = Role::create(['name' => 'Admin']);
        Permission::create(['name' => 'viewAnyBook', 'guard_name' => 'web', 'group' => 'Book']);
        Permission::create(['name' => 'createBook', 'guard_name' => 'web', 'group' => 'Book']);
        $role->givePermissionTo(Permission::all());

        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user, 'web');
        Filament::setCurrentPanel(Filament::getPanel('admins'));

        return $user;
    }

    public function test_creating_a_book_with_one_copy_does_not_crash(): void
    {
        $this->actingAsAdmin();

        $category = Category::factory()->create();
        $author = Author::factory()->create();
        $publisher = Publisher::factory()->create();

        Livewire::test(CreateBook::class)
            ->fillForm([
                'name' => 'Test Book',
                'category_id' => $category->id,
                'author_id' => $author->id,
                'publisher_id' => $publisher->id,
                'partCode' => 1,
                'lang' => 'ar',
                'internal_borrowing' => false,
                'number_of_copy' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $book = Book::where('name', 'Test Book')->first();

        $this->assertNotNull($book);
        $this->assertSame($category->id, $book->category_id);
        $this->assertCount(1, $book->editions);
        $this->assertCount(1, $book->copies);
        $this->assertTrue($book->author->pluck('id')->contains($author->id));

        $edition = $book->editions->first();
        $this->assertSame($publisher->id, $edition->publisher_id);
        $this->assertSame(1, $edition->partCode);

        // the fixed bug: no `internal_borrowing` column exists on copies at all
        $this->assertFalse($book->copies->first()->is_borrowed);
    }

    public function test_creating_a_book_with_multiple_copies_creates_all_of_them(): void
    {
        $this->actingAsAdmin();

        $category = Category::factory()->create();
        $publisher = Publisher::factory()->create();

        Livewire::test(CreateBook::class)
            ->fillForm([
                'name' => 'Multi Copy Book',
                'category_id' => $category->id,
                'publisher_id' => $publisher->id,
                'partCode' => 1,
                'number_of_copy' => 3,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $book = Book::where('name', 'Multi Copy Book')->first();

        $this->assertNotNull($book);
        $this->assertCount(3, $book->copies);
        $this->assertTrue($book->copies->every(fn ($copy) => ! $copy->is_borrowed));
    }
}
