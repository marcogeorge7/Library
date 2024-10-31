<?php

namespace App\Filament\Admin\Resources\SeriesResource\Pages;

use App\Filament\Admin\Resources\SeriesResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSeries extends ViewRecord
{
    protected static string $resource = SeriesResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';

    protected static ?string $modelLabel = 'تسلسل';

    protected static ?string $pluralModelLabel = 'تسلسلات';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
