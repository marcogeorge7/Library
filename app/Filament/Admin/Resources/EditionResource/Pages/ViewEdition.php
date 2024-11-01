<?php

namespace App\Filament\Admin\Resources\EditionResource\Pages;

use App\Filament\Admin\Resources\EditionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEdition extends ViewRecord
{
    protected static string $resource = EditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
