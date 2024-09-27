<?php

namespace App\Filament\Admin\Resources\TranslatorResource\Pages;

use App\Filament\Admin\Resources\TranslatorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTranslator extends EditRecord
{
    protected static string $resource = TranslatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
