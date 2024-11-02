<?php

namespace App\Filament\Admin\Helpers;

trait RedirectToIndexPage
{
    protected function getRedirectUrl(): ?string
    {
        return static::getResource()::getUrl('index');
    }
}
