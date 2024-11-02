<?php

namespace App\Filament\Admin\Helpers;

trait RedirectToViewPage
{
    protected function getRedirectUrl(): ?string
    {
        return static::getResource()::getUrl('view', ['record' => $this->record->id]);
    }
}
