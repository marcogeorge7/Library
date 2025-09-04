<?php

namespace App\Filament\Admin\Resources\EditionResource\RelationManagers;

use App\Services\BarcodeLabelGenerator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class CopiesRelationManager extends RelationManager
{
    protected static string $relationship = 'Copies';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('barcode')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('barcode')
            ->columns(\App\Filament\Admin\Resources\CopyResource\Fields\Table::columns($this->getOwnerRecord()))
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('print_selected_barcodes')
                        ->label('Print Barcode Labels')
                        ->icon('heroicon-o-printer')
                        ->form([
                            Forms\Components\TextInput::make('count')
                                ->label('Labels per selected')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $items = [];
                            $count = (int) ($data['count'] ?? 1);
                            foreach ($records as $copy) {
                                for ($i = 0; $i < $count; $i++) {
                                    $items[] = [
                                        'name' => $copy->book_name ?? 'Book',
                                        'barcode' => $copy->barcode,
                                    ];
                                }
                            }

                            $generator = new BarcodeLabelGenerator();
                            $pdfContent = $generator->generate($items, 4, 10);

                            return response()->streamDownload(
                                fn () => print($pdfContent),
                                'copy-barcodes-' . now()->format('Ymd-His') . '.pdf',
                                ['Content-Type' => 'application/pdf']
                            );
                        }),
                ]),
            ]);
    }
}
