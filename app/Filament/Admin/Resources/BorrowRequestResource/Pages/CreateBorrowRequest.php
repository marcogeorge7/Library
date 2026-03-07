<?php

namespace App\Filament\Admin\Resources\BorrowRequestResource\Pages;

use App\Filament\Admin\Resources\BorrowRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBorrowRequest extends CreateRecord
{
    protected static string $resource = BorrowRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requested_at'] = now();
        return $data;
    }
}
