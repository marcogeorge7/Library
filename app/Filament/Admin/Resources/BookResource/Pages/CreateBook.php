<?php

namespace App\Filament\Admin\Resources\BookResource\Pages;

use App\Filament\Admin\Resources\BookResource;
use App\Models\Book;
use App\Models\Copy;
use App\Models\Edition;
use App\Services\BarCode;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class CreateBook extends CreateRecord
{
    protected static string $resource = BookResource::class;

    protected array $requestData = []; // Store request data here

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // order is the group's shared category slot -- a series that already has a
        // member reuses its slot (this new book joins the existing group) instead
        // of claiming a fresh one; a brand new series or a standalone book claims
        // the next available slot, same as BooksImport::resolveSeriesAndOrder().
        $seriesId = $data['series_id'] ?? null;
        $existingOrder = $seriesId ? Book::where('series_id', $seriesId)->value('order') : null;
        $data['order'] = $existingOrder ?? Book::getNextOrderInCategory($data['category_id']);
        $this->requestData = $data;

        // Only these are real `books` columns -- the rest of the form (author_id,
        // publisher_id, partCode, part_name, publish_year, lang, internal_borrowing,
        // number_of_copy, is_series) describes the Author pivot / Edition / Copy that
        // get created in afterCreate(), not the Book itself. Passing them through
        // unfiltered used to crash every submission with an "unknown column" SQL error
        // since Book::$guarded = ['id'] mass-assigns whatever it's given.
        return Arr::only($data, ['name', 'category_id', 'series_id', 'order']);
    }

    protected function afterCreate(): void
    {
        $book = $this->record;
        $data = $this->requestData;

        if (! empty($data['author_id'])) {
            $book->author()->attach($data['author_id']);
        }

        $partName = trim((string) ($data['part_name'] ?? ''));

        $edition = Edition::query()->create([
            'book_id' => $book->id,
            'partCode' => $data['partCode'],
            'part_name' => $partName !== '' ? $partName : null,
            'publisher_id' => $data['publisher_id'],
            'publish_year' => $data['publish_year'] ?? null,
            'lang' => $data['lang'] ?? 'ar',
            'internal_borrowing' => (bool) ($data['internal_borrowing'] ?? false),
        ]);

        $bookBarCode = BarCode::generate($edition);
        $copiesCount = max(1, (int) ($data['number_of_copy'] ?? 1));

        for ($i = 1; $i <= $copiesCount; $i++) {
            $copy = Copy::query()->create([
                'edition_id' => $edition->id,
                'barcode' => "$bookBarCode$i",
                'is_borrowed' => false,
            ]);
            Log::info("Copy created: $copy->barcode for book [Book-$book->id]");
        }
    }
}
