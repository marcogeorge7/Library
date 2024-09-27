<?php

namespace App\Filament\Admin\Resources\RevisorResource\Pages;

use App\Filament\Admin\Resources\PublisherResource;
use App\Filament\Admin\Resources\RevisorResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRevisor extends ViewRecord
{
    protected static string $resource = RevisorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
