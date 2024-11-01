<?php

namespace App\Filament\Admin\Resources\EditionResource\Pages;

use App\Filament\Admin\Resources\EditionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEdition extends CreateRecord
{
    protected static string $resource = EditionResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
