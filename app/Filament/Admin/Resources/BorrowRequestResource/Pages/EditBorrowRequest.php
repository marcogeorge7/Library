<?php

namespace App\Filament\Admin\Resources\BorrowRequestResource\Pages;

use App\Filament\Admin\Resources\BorrowRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBorrowRequest extends EditRecord
{
    protected static string $resource = BorrowRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
