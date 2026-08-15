<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BorrowerResource\Pages;
use App\Filament\Admin\Resources\BorrowerResource\RelationManagers;
use App\Filament\Concerns\HasNormalizedArabicGlobalSearch;
use App\Models\Borrower;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BorrowerResource extends Resource
{
    use HasNormalizedArabicGlobalSearch;

    protected static ?string $model = Borrower::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Borrowing';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Member ID' => $record->member_id ?? '—',
            'Status' => $record->is_active ? 'Active' : 'Inactive',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required(),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('password')
                    ->password()
                    ->required(fn(string $operation) => $operation === 'create')
                    ->dehydrated(fn(?string $state) => filled($state))
                    ->label('Password'),

                TextInput::make('phone')
                    ->nullable(),

                Toggle::make('is_active')
                    ->default(true),

                Placeholder::make('member_id')
                    ->label('Member ID')
                    ->content(fn(?Borrower $record) => $record?->member_id ?? '(auto-generated after save)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable(),

                TextColumn::make('member_id')
                    ->searchable(),

                TextColumn::make('phone'),

                IconColumn::make('is_active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }

    public static function getRelationManagers(): array
    {
        return [
            RelationManagers\BorrowRequestsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBorrowers::route('/'),
            'create' => Pages\CreateBorrower::route('/create'),
            'edit'   => Pages\EditBorrower::route('/{record}/edit'),
            'view'   => Pages\ViewBorrower::route('/{record}'),
        ];
    }
}
