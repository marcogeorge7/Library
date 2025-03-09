<?php

namespace App\Filament\Admin\Resources\BookResource\Pages;

use App\Filament\Admin\Resources\BookResource;
use App\Models\Book;
use App\Models\Copy;
use App\Models\Edition;
use App\Services\BarCode;
use Filament\Resources\Pages\CreateRecord;
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

        $data['order'] = Book::getNextOrderInCategory($data['category_id']);
        $this->requestData = $data;

        return $data;
    }

    protected function afterCreate(): void
    {
        $book = $this->record;
        $data = $this->requestData;

        $edition = Edition::query()->create([
            'book_id' => $book->id,
            'partCode' => $data['partCode'],
            'publisher_id' => $data['publisher_id'],
//            'lang' => $translator ? 'en' : 'ar',
            'internal_borrowing' => 0,
        ]);

        $bookBarCode = BarCode::generate($edition);

        if ((int) $data['number_of_copy'] > 1) {
            for ($i = 1; $i <= (int) $data['number_of_copy']; $i++) {
                $copy = Copy::query()->create([
                    'edition_id' => $edition->id,
                    'barcode' => "$bookBarCode$i",
                    'is_borrowed' => false,
                ]);
                Log::info("Copy created: $copy->barcode for book [Book-$book->id]");
            }
        } else {
            Copy::query()->create([
                'edition_id' => $edition->id,
                'barcode' => $bookBarCode.'1',
                'internal_borrowing' => "YES",
                'is_borrowed' => true,
            ]);
            Log::info("Copy Borrowed created: $bookBarCode for book [Book-$book->id]");
        }
    }
}
