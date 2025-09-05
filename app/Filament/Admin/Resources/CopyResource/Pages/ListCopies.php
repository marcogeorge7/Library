<?php

namespace App\Filament\Admin\Resources\CopyResource\Pages;

use App\Filament\Admin\Resources\CopyResource;
use App\Models\Copy;
use App\Services\BarcodeLabelGenerator;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;

class ListCopies extends ListRecords
{
    protected static string $resource = CopyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('print_all_barcodes')
                ->label('Print All Barcode Labels')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->requiresConfirmation()
                ->form([
                    TextInput::make('count')
                        ->label('Labels per copy')
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $count = (int)($data['count'] ?? 1);

                    $items = [];
                    Copy::with('book')
                        ->select(['id', 'barcode', 'edition_id'])
                        ->orderBy('id')
                        ->chunk(1000, function ($copies) use (&$items, $count) {
                            foreach ($copies as $copy) {
                                for ($i = 0; $i < $count; $i++) {
                                    $items[] = [
                                        'name' => optional($copy->book)->name ?? 'Book',
                                        'barcode' => $copy->barcode,
                                    ];
                                }
                            }
                        });

                    $generator = new BarcodeLabelGenerator();
                    $pdfContent = $generator->generate($items, 4, 10);

                    return response()->streamDownload(
                        fn() => print($pdfContent),
                        'all-copy-barcodes-' . now()->format('Ymd-His') . '.pdf',
                        ['Content-Type' => 'application/pdf']
                    );
                }),
        ];
    }
}
