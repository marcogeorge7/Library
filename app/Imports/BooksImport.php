<?php

namespace App\Imports;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Copy;
use App\Models\Edition;
use App\Models\Publisher;
use App\Models\Series;
use App\Models\Translator;
use App\Services\BarCode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Throwable;

class BooksImport implements ToCollection, WithChunkReading
{
    use Importable;

    private const UNKNOWN_PUBLISHER_NAME = '----';

    private bool $dryRun;

    /** @var array<string,int> "name|category_id" => series_id */
    private array $seriesIdsByNameAndCategory = [];

    /** @var array<string,int> "groupTitle|category_id" => book_id */
    private array $bookIdsByGroupTitleAndCategory = [];

    private bool $headerRowSkipped = false;

    public int $totalRows = 0;

    public int $importedRows = 0;

    public int $skippedRows = 0;

    public int $failedRows = 0;

    public int $newCategories = 0;

    public int $newAuthors = 0;

    public int $newPublishers = 0;

    public int $newTranslators = 0;

    public int $newSeries = 0;

    public int $newEditions = 0;

    public int $newCopies = 0;

    public function __construct(bool $dryRun = false)
    {
        $this->dryRun = $dryRun;
    }

    public function collection(Collection $collection)
    {
        // WithChunkReading resets each chunk's Collection to start at index 0
        // regardless of the chunk's actual position in the sheet, so forget(0)
        // must only run once -- otherwise every chunk after the first silently
        // drops a real data row instead of the (already-consumed) header row.
        if (! $this->headerRowSkipped) {
            $collection->forget(0);
            $this->headerRowSkipped = true;
        }

        $this->totalRows += $collection->count();

        $collection->each(function ($row, $key) {
            $this->importRow($row, $key);
        });
    }

    private function importRow($row, int $key): void
    {
        $code = trim((string) ($row[0] ?? ''));
        $name = trim((string) ($row[1] ?? ''));

        if ($name === '') {
            $this->skippedRows++;
            Log::warning("Import: skipping row $key — empty book name");
            return;
        }

        $groupTitle = trim((string) ($row[11] ?? '')) ?: null;

        if ($this->bookAlreadyImported($code, $name, $row[7] ?? null, $groupTitle)) {
            $this->skippedRows++;
            return;
        }

        try {
            DB::transaction(function () use ($row, $code, $name, $groupTitle) {
                $categoryName = trim((string) ($row[5] ?? ''));
                if ($categoryName === '') {
                    throw new \RuntimeException("Empty category for book '$name'");
                }

                $category = $this->resolveCategory($categoryName);
                $publisher = $this->resolvePublisher($row[4] ?? null);
                $author = $this->resolveAuthor($row[7] ?? null);
                $translator = $this->resolveTranslator($row[6] ?? null);

                [$seriesId, $order] = $this->resolveSeriesAndOrder($row, $category->id);

                $bookName = $groupTitle ?? $name;
                $partName = $groupTitle !== null ? $name : null;

                $book = $this->resolveBook($bookName, $groupTitle, $category->id, $seriesId, $order, $code);

                if ($author) {
                    $book->author()->syncWithoutDetaching($author);
                }

                $edition = Edition::create([
                    'book_id' => $book->id,
                    'publisher_id' => $publisher->id,
                    'partCode' => $this->resolvePartCode($row, $name),
                    'part_name' => $partName,
                    'old_code' => $code !== '' ? $code : null,
                    'lang' => 'ar',
                    'internal_borrowing' => ($row[3] ?? null) === 'YES',
                ]);
                $this->newEditions++;

                if ($translator) {
                    $edition->translators()->attach($translator);
                }

                $this->createCopies($edition, (int) ($row[2] ?? 0), $row[3] ?? null, $book);

                if ($this->dryRun) {
                    throw new DryRunRollback;
                }
            });

            $this->importedRows++;
        } catch (DryRunRollback) {
            $this->importedRows++;
        } catch (Throwable $e) {
            $this->failedRows++;
            Log::error("Import: row $key (code=$code) failed: ".$e->getMessage());
        }
    }

    private function resolveBook(string $bookName, ?string $groupTitle, int $categoryId, ?int $seriesId, int $order, string $code): Book
    {
        if ($groupTitle === null) {
            // Unambiguous for a standalone book -- it's the only row that will ever
            // represent it, so the row's own code can safely live on the book too
            // (unlike a grouped book, where multiple parts have different codes and
            // none of them uniquely represents the book as a whole).
            return Book::create([
                'name' => $bookName,
                'category_id' => $categoryId,
                'old_code' => $code !== '' ? $code : null,
                'series_id' => $seriesId,
                'order' => $order,
            ]);
        }

        $cacheKey = $groupTitle.'|'.$categoryId;

        // Mirrors getOrCreateSeries(): bypass the cache in dry-run, since each row's
        // transaction is rolled back individually and a cached id from an earlier
        // (already-rolled-back) row would be dangling.
        if (! $this->dryRun && isset($this->bookIdsByGroupTitleAndCategory[$cacheKey])) {
            $book = Book::find($this->bookIdsByGroupTitleAndCategory[$cacheKey]);
            if ($book) {
                return $book;
            }
        }

        // Only reuse a Book that was itself already built from grouped parts (has at
        // least one edition with part_name set). Without this guard, a group's first
        // part could silently attach itself onto an unrelated standalone Book whose
        // name happens to equal this group's title -- books.name has no DB-level
        // uniqueness.
        $book = Book::where('name', $groupTitle)
            ->where('category_id', $categoryId)
            ->whereHas('editions', fn ($q) => $q->whereNotNull('part_name'))
            ->first();

        if (! $book) {
            $book = Book::create([
                'name' => $groupTitle,
                'category_id' => $categoryId,
                'old_code' => null,
                'series_id' => $seriesId,
                'order' => $order,
            ]);
        }

        if (! $this->dryRun) {
            $this->bookIdsByGroupTitleAndCategory[$cacheKey] = $book->id;
        }

        return $book;
    }

    private function bookAlreadyImported(string $code, string $name, ?string $authorName, ?string $groupTitle): bool
    {
        if ($code !== '') {
            return Edition::where('old_code', $code)->exists();
        }

        if (! $authorName) {
            return false;
        }

        $author = Author::where('name', trim($authorName))->first();
        if (! $author) {
            return false;
        }

        if ($groupTitle === null) {
            return Book::where('name', $name)
                ->whereHas('author', fn ($q) => $q->where('author_id', $author->id))
                ->exists();
        }

        return Edition::where('part_name', $name)
            ->whereHas('book', fn ($q) => $q->where('name', $groupTitle)
                ->whereHas('author', fn ($q2) => $q2->where('author_id', $author->id)))
            ->exists();
    }

    private function resolveCategory(string $name): Category
    {
        $existing = Category::where('name', $name)->first();
        if ($existing) {
            return $existing;
        }

        $this->newCategories++;
        return Category::create(['name' => $name]);
    }

    private function resolvePublisher($name): Publisher
    {
        // editions.publisher_id is NOT NULL, so a blank cell can't resolve to null --
        // that would throw a raw SQL constraint error and silently drop the whole row
        // (book+editions+copies) via the generic catch in importRow(). Fall back to a
        // canonical placeholder instead, same convention already used when generating
        // the source spreadsheets themselves (see database/data build scripts).
        $name = trim((string) $name);
        if ($name === '') {
            $name = self::UNKNOWN_PUBLISHER_NAME;
        }

        $existing = Publisher::where('name', $name)->first();
        if ($existing) {
            return $existing;
        }

        $this->newPublishers++;
        return Publisher::create(['name' => $name]);
    }

    private function resolveAuthor($name): ?Author
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $existing = Author::where('name', $name)->first();
        if ($existing) {
            return $existing;
        }

        $this->newAuthors++;
        return Author::create(['name' => $name]);
    }

    private function resolveTranslator($name): ?Translator
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $existing = Translator::whereRaw('LOWER(name) = ?', [Str::lower($name)])->first();
        if ($existing) {
            return $existing;
        }

        // Unlike author/publisher/category, this used to silently drop any translator
        // name not already an exact match -- the edition still imported, just with no
        // translator attached and nothing surfacing that it happened. Create on miss
        // for consistency with the other resolvers.
        $this->newTranslators++;
        return Translator::create(['name' => $name]);
    }

    private function resolvePartCode($row, string $bookName): int
    {
        $value = $row[10] ?? null;
        if (is_numeric($value)) {
            return (int) $value;
        }

        return $this->extractPartCode($bookName);
    }

    private function extractPartCode(string $bookName): int
    {
        if (preg_match('/\bج(\d+)\b(?!\s*-)/u', $bookName, $m)) {
            return (int) $m[1];
        }

        if (preg_match('/\bجزء\s*(\d+)/u', $bookName, $m)) {
            return (int) $m[1];
        }

        if (preg_match('/\s(I{1,3}|IV|V|VI{0,3}|IX|X)\s*$/', $bookName, $m)) {
            return $this->romanToInt($m[1]);
        }

        return 1;
    }

    private function romanToInt(string $roman): int
    {
        $map = ['I' => 1, 'V' => 5, 'X' => 10];
        $value = 0;
        $prev = 0;
        for ($i = strlen($roman) - 1; $i >= 0; $i--) {
            $current = $map[$roman[$i]] ?? 0;
            $value += $current < $prev ? -$current : $current;
            $prev = $current;
        }

        return $value ?: 1;
    }

    /**
     * @return array{0:?int,1:int} [series_id, order]
     */
    private function resolveSeriesAndOrder($row, int $categoryId): array
    {
        $seriesName = trim((string) ($row[8] ?? ''));

        if ($seriesName === '') {
            return [null, $this->fallbackOrder($categoryId)];
        }

        $series = $this->getOrCreateSeries($seriesName, $categoryId);

        // order is the group's shared category slot -- every book in a series
        // shares the same value, inherited from whichever book first claimed it.
        // A series with no members yet claims a fresh slot the same way a
        // standalone book does; one that already has a member reuses its slot.
        $existingOrder = Book::where('series_id', $series->id)->value('order');
        $order = $existingOrder ?? $this->fallbackOrder($categoryId);

        return [$series->id, $order];
    }

    private function getOrCreateSeries(string $name, int $categoryId): Series
    {
        // Scoped to (name, category): a series is resolved within its own category,
        // not by name alone -- two different code-based series families in different
        // categories that happen to share an identical title stay distinct series
        // instead of silently merging into one row.
        $cacheKey = $name.'|'.$categoryId;

        // In dry-run mode each row's DB changes are rolled back individually, so a
        // cached id from an earlier (already-rolled-back) row would be dangling —
        // always re-check the DB instead of trusting the in-memory cache.
        if (! $this->dryRun && isset($this->seriesIdsByNameAndCategory[$cacheKey])) {
            $series = Series::find($this->seriesIdsByNameAndCategory[$cacheKey]);
            if ($series) {
                return $series;
            }
        }

        $series = Series::where('name', $name)
            ->whereHas('books', fn ($q) => $q->where('category_id', $categoryId))
            ->first();

        if (! $series) {
            $series = Series::create(['name' => $name]);
            $this->newSeries++;
        }

        if (! $this->dryRun) {
            $this->seriesIdsByNameAndCategory[$cacheKey] = $series->id;
        }

        return $series;
    }

    private function fallbackOrder(int $categoryId): int
    {
        $max = Book::where('category_id', $categoryId)->max('order') ?? 0;
        return $max + 1;
    }

    private function createCopies(Edition $edition, int $copiesCount, $internalBorrowing, Book $book): void
    {
        $bookBarCode = BarCode::generate($edition);

        if ($copiesCount > 0) {
            for ($i = 1; $i <= $copiesCount; $i++) {
                Copy::create([
                    'edition_id' => $edition->id,
                    'barcode' => "$bookBarCode$i",
                    'is_borrowed' => false,
                ]);
                $this->newCopies++;
            }
            return;
        }

        Copy::create([
            'edition_id' => $edition->id,
            'barcode' => $bookBarCode.'1',
            'is_borrowed' => $internalBorrowing === 'YES',
        ]);
        $this->newCopies++;
    }

    public function chunkSize(): int
    {
        return 8163;
    }
}
