<?php

namespace App\Filament\Admin\Resources\CopyResource\Pages;

use App\Filament\Admin\Resources\CopyResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCopy extends ViewRecord
{
    protected static string $resource = CopyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
