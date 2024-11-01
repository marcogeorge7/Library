<?php

namespace App\Filament\Admin\Resources\TranslatorResource\Pages;

use App\Filament\Admin\Resources\TranslatorResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTranslator extends ViewRecord
{
    protected static string $resource = TranslatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
