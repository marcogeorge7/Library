<?php

namespace Tests\Feature\Services;

use App\Models\Book;
use App\Models\Category;
use App\Models\Edition;
use App\Models\Series;
use App\Services\BarCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarCodeGenerateTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_without_series(): void
    {
        $category = Category::factory()->create();
        $book = Book::factory()->create([
            'category_id' => $category->id,
            'series_id' => null,
            'order' => 5,
        ]);
        $edition = Edition::factory()->create([
            'book_id' => $book->id,
            'partCode' => 3,
        ]);

        $barcode = BarCode::generate($edition);

        $categoryCode = BarCode::toAlphabeticCode($category->id, 1);
        // category + groupOrder(005) + memberPosition("01" -- standalone) + part(03) + edition#(1)
        $this->assertSame($categoryCode.'005'.'01'.'03'.'1', $barcode);
    }

    public function test_generate_with_series_shares_group_order_and_ranks_members(): void
    {
        $category = Category::factory()->create();
        $series = Series::factory()->create();

        // Both books in the same series share the same group order (007) --
        // inherited by the 2nd book from the 1st, as BooksImport/CreateBook do.
        $book1 = Book::factory()->create([
            'category_id' => $category->id,
            'series_id' => $series->id,
            'order' => 7,
            'created_at' => now(),
        ]);
        $edition1 = Edition::factory()->create(['book_id' => $book1->id, 'partCode' => 1]);

        $book2 = Book::factory()->create([
            'category_id' => $category->id,
            'series_id' => $series->id,
            'order' => 7,
            'created_at' => now()->addSecond(),
        ]);
        $edition2 = Edition::factory()->create(['book_id' => $book2->id, 'partCode' => 2]);

        $barcode1 = BarCode::generate($edition1);
        $barcode2 = BarCode::generate($edition2);

        $categoryCode = BarCode::toAlphabeticCode($category->id, 1);
        $this->assertSame($categoryCode.'007'.'01'.'01'.'1', $barcode1);
        $this->assertSame($categoryCode.'007'.'02'.'02'.'1', $barcode2);
    }

    public function test_two_different_categories_can_both_have_group_order_001(): void
    {
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        $bookA = Book::factory()->create(['category_id' => $categoryA->id, 'series_id' => null, 'order' => 1]);
        $bookB = Book::factory()->create(['category_id' => $categoryB->id, 'series_id' => null, 'order' => 1]);

        $editionA = Edition::factory()->create(['book_id' => $bookA->id, 'partCode' => 1]);
        $editionB = Edition::factory()->create(['book_id' => $bookB->id, 'partCode' => 1]);

        $barcodeA = BarCode::generate($editionA);
        $barcodeB = BarCode::generate($editionB);

        $this->assertStringContainsString('001', $barcodeA);
        $this->assertStringContainsString('001', $barcodeB);
        // different categories -> different barcodes despite both group-ordering "001"
        $this->assertNotSame($barcodeA, $barcodeB);
    }
}
