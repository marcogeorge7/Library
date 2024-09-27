<?php

namespace App\Filament\Admin\Resources\RevisorResource\Pages;

use App\Filament\Admin\Resources\RevisorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRevisors extends ListRecords
{
    protected static string $resource = RevisorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
