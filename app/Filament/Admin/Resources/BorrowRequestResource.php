<?php

namespace App\Filament\Admin\Resources;

use App\Enum\BorrowRequestStatusEnum;
use App\Filament\Admin\Resources\BorrowRequestResource\Pages;
use App\Filament\Concerns\HasNormalizedArabicGlobalSearch;
use App\Models\BorrowRequest;
use App\Models\Borrower;
use App\Models\Copy;
use App\Services\BorrowRequestService;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BorrowRequestResource extends Resource
{
    use HasNormalizedArabicGlobalSearch;

    protected static ?string $model = BorrowRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationGroup = 'Borrowing';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $modelLabel = 'Borrow Request';

    protected static ?string $pluralModelLabel = 'Borrow Requests';

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [
            'Borrower' => $record->borrower?->name ?? '—',
            'Status' => $record->status->label(),
        ];

        if (filled($record->due_date)) {
            $details['Due Date'] = $record->due_date->toFormattedDateString();
        }

        return $details;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('borrower_id')
                    ->label('Borrower')
                    ->options(Borrower::pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Select::make('copy_id')
                    ->label('Copy (Barcode)')
                    ->options(Copy::with('edition.book')->get()->mapWithKeys(fn($c) => [$c->id => $c->barcode . ' — ' . ($c->edition?->book?->name ?? '?')]))
                    ->searchable()
                    ->required(),

                Select::make('status')
                    ->options(collect(BorrowRequestStatusEnum::cases())->mapWithKeys(fn($e) => [$e->value => $e->label()]))
                    ->required(),

                Textarea::make('notes'),

                DatePicker::make('due_date'),

                DatePicker::make('returned_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('borrower.name')
                    ->label('Borrower')
                    ->searchable(),

                TextColumn::make('copy.barcode')
                    ->label('Barcode'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(BorrowRequestStatusEnum $state) => $state->color()),

                TextColumn::make('requested_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('due_date')
                    ->date(),

                TextColumn::make('returned_at')
                    ->dateTime(),
            ])
            ->actions([
                ViewAction::make(),

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(BorrowRequest $record) => $record->status === BorrowRequestStatusEnum::Pending)
                    ->form([
                        DatePicker::make('due_date')
                            ->label('Due Date')
                            ->required(),
                    ])
                    ->action(function (BorrowRequest $record, array $data) {
                        try {
                            app(BorrowRequestService::class)->approve(
                                $record,
                                Carbon::parse($data['due_date'])
                            );
                            Notification::make()->title('Request approved')->success()->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title('Could not approve request')->body($e->getMessage())->danger()->send();
                        }
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(BorrowRequest $record) => $record->status === BorrowRequestStatusEnum::Pending)
                    ->form([
                        Textarea::make('notes')
                            ->label('Reason (optional)'),
                    ])
                    ->action(function (BorrowRequest $record, array $data) {
                        app(BorrowRequestService::class)->reject($record, $data['notes'] ?? null);
                        Notification::make()->title('Request rejected')->warning()->send();
                    }),

                Action::make('mark_returned')
                    ->label('Mark Returned')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('gray')
                    ->visible(fn(BorrowRequest $record) => $record->status === BorrowRequestStatusEnum::Approved)
                    ->requiresConfirmation()
                    ->action(function (BorrowRequest $record) {
                        app(BorrowRequestService::class)->markReturned($record);
                        Notification::make()->title('Marked as returned')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBorrowRequests::route('/'),
            'create' => Pages\CreateBorrowRequest::route('/create'),
            'edit'   => Pages\EditBorrowRequest::route('/{record}/edit'),
            'view'   => Pages\ViewBorrowRequest::route('/{record}'),
        ];
    }
}
