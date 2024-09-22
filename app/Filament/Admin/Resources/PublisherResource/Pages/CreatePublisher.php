<?php

namespace App\Filament\Admin\Resources\PublisherResource\Pages;

use App\Filament\Admin\Resources\PublisherResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePublisher extends CreateRecord
{
    protected static string $resource = PublisherResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
