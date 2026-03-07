<?php

namespace App\Filament\Admin\Resources\BorrowerResource\Pages;

use App\Filament\Admin\Resources\BorrowerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBorrower extends ViewRecord
{
    protected static string $resource = BorrowerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
