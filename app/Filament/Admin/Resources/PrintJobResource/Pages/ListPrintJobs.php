<?php

namespace App\Filament\Admin\Resources\PrintJobResource\Pages;

use App\Filament\Admin\Resources\PrintJobResource;
use Filament\Resources\Pages\ListRecords;

class ListPrintJobs extends ListRecords
{
    protected static string $resource = PrintJobResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
