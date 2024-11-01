<?php

namespace App\Filament\Admin\Resources\CopyResource\Pages;

use App\Filament\Admin\Resources\CopyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCopy extends CreateRecord
{
    protected static string $resource = CopyResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
