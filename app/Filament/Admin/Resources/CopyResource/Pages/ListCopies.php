<?php

namespace App\Filament\Admin\Resources\CopyResource\Pages;

use App\Filament\Admin\Resources\CopyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCopies extends ListRecords
{
    protected static string $resource = CopyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
