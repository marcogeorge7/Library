<?php

namespace App\Filament\Admin\Resources\BookResource\Pages;

use App\Filament\Admin\Resources\BookResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;

class EditBook extends EditRecord
{
    protected static string $resource = BookResource::class;

    protected array $requestData = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // The form also shows Edition-level fields (publisher, part, language,
        // borrowing, etc.) alongside the Book's own fields, so pre-fill them from
        // the book's first edition -- otherwise editing would show these blank
        // even for a book that already has them set.
        $edition = $this->record->editions()->first();

        if ($edition) {
            $data['publisher_id'] = $edition->publisher_id;
            $data['partCode'] = $edition->partCode;
            $data['part_name'] = $edition->part_name;
            $data['publish_year'] = $edition->publish_year;
            $data['lang'] = $edition->lang;
            $data['internal_borrowing'] = $edition->internal_borrowing;
        }

        $data['author_id'] = $this->record->author()->first()?->id;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->requestData = $data;

        // Same reasoning as CreateBook::mutateFormDataBeforeCreate() -- only these
        // are real `books` columns; the rest describe the Edition/Author, which are
        // updated separately in afterSave() below.
        return Arr::only($data, ['name', 'category_id', 'series_id']);
    }

    protected function afterSave(): void
    {
        $book = $this->record;
        $data = $this->requestData;

        $book->author()->sync(! empty($data['author_id']) ? [$data['author_id']] : []);

        $edition = $book->editions()->first();
        if (! $edition) {
            return;
        }

        $partName = trim((string) ($data['part_name'] ?? ''));

        $edition->update([
            'publisher_id' => $data['publisher_id'] ?? $edition->publisher_id,
            'partCode' => $data['partCode'] ?? $edition->partCode,
            'part_name' => $partName !== '' ? $partName : null,
            'publish_year' => $data['publish_year'] ?? null,
            'lang' => $data['lang'] ?? $edition->lang,
            'internal_borrowing' => (bool) ($data['internal_borrowing'] ?? false),
        ]);
    }
}
