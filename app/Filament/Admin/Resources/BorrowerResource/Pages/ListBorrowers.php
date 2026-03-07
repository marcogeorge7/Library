<?php

namespace App\Filament\Admin\Resources\BorrowerResource\Pages;

use App\Filament\Admin\Resources\BorrowerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBorrowers extends ListRecords
{
    protected static string $resource = BorrowerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
