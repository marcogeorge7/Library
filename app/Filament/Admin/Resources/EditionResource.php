<?php

namespace App\Filament\Admin\Resources;

use App\Services\BarcodeLabelGenerator;

use App\Filament\Admin\Resources\EditionResource\Pages\CreateEdition;
use App\Filament\Admin\Resources\EditionResource\Pages\EditEdition;
use App\Filament\Admin\Resources\EditionResource\Pages\ListEditions;
use App\Filament\Admin\Resources\EditionResource\Pages\ViewEdition;
use App\Models\Edition;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class EditionResource extends Resource
{
    protected static ?string $model = Edition::class;

    protected static ?string $slug = 'editions';

    protected static ?string $pluralModelLabel = 'طبعات';

    protected static ?string $modelLabel = 'طبعة';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(EditionResource\Fields\Form::schema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(EditionResource\Fields\Table::columns())
            ->filters([])
            ->actions([
                EditAction::make(),
                ViewAction::make(),
                Action::make('print_copy_barcodes')
                    ->label('Print Copy Barcode Labels')
                    ->icon('heroicon-o-printer')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('count')
                            ->label('Labels per copy')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                    ])
                    ->action(function (Edition $record, array $data) {
                        $count = (int) ($data['count'] ?? 1);
                        $items = [];
                        foreach ($record->copies as $copy) {
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
                            'edition-' . $record->id . '-copy-barcodes-' . now()->format('Ymd-His') . '.pdf',
                            ['Content-Type' => 'application/pdf']
                        );
                    })
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema(EditionResource\Fields\InfoList::schema());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEditions::route('/'),
            'create' => CreateEdition::route('/create'),
            'edit' => EditEdition::route('/{record}/edit'),
            'view' => ViewEdition::route('/{record}'),
        ];
    }
}
