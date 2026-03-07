<?php

namespace App\Filament\Admin\Resources\BorrowRequestResource\Pages;

use App\Filament\Admin\Resources\BorrowRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBorrowRequests extends ListRecords
{
    protected static string $resource = BorrowRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
