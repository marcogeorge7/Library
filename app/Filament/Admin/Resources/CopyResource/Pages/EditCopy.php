<?php

namespace App\Filament\Admin\Resources\CopyResource\Pages;

use App\Filament\Admin\Resources\CopyResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCopy extends EditRecord
{
    protected static string $resource = CopyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
