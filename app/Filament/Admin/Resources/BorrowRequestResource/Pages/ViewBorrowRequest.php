<?php

namespace App\Filament\Admin\Resources\BorrowRequestResource\Pages;

use App\Filament\Admin\Resources\BorrowRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBorrowRequest extends ViewRecord
{
    protected static string $resource = BorrowRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
