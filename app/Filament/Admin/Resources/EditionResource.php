<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EditionResource\Pages\CreateEdition;
use App\Filament\Admin\Resources\EditionResource\Pages\EditEdition;
use App\Filament\Admin\Resources\EditionResource\Pages\ListEditions;
use App\Filament\Admin\Resources\EditionResource\Pages\ViewEdition;
use App\Filament\Concerns\HasNormalizedArabicGlobalSearch;
use App\Models\Edition;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EditionResource extends Resource
{
    use HasNormalizedArabicGlobalSearch;

    protected static ?string $model = Edition::class;

    protected static ?string $slug = 'editions';

    protected static ?string $pluralModelLabel = 'طبعات';

    protected static ?string $modelLabel = 'طبعة';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'book.name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['book.name', 'part_name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [
            'Book' => $record->book?->name ?? '—',
            'Publisher' => $record->publisher?->name ?? '—',
        ];

        if (filled($record->part_name)) {
            $details['Part'] = $record->part_name;
        }

        return $details;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(EditionResource\Fields\Form::schema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(EditionResource\Fields\Table::columns())
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['book', 'publisher', 'translators', 'copies']))
            ->filters([])
            ->actions([
                EditAction::make(),
                ViewAction::make(),
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
