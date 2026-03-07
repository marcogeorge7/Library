<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Resources\AuthorResource;
use App\Filament\Admin\Resources\BookResource;
use App\Filament\Admin\Resources\BorrowRequestResource;
use App\Filament\Admin\Resources\BorrowerResource;
use App\Filament\Admin\Resources\CategoryResource;
use App\Filament\Admin\Resources\CopyResource;
use App\Filament\Admin\Resources\EditionResource;
use App\Filament\Admin\Resources\PublisherResource;
use App\Filament\Admin\Resources\RevisorResource;
use App\Filament\Admin\Resources\SeriesResource;
use App\Filament\Admin\Resources\SubjectResource;
use App\Filament\Admin\Resources\TranslatorResource;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {

        return $panel
            ->id('admins')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->navigation(function (NavigationBuilder $builder) {
                $tables = [
                    'books',
                    'categories',
                    'publishers',
                    'authors',
                    'series',
                    'subjects',
                    'translators',
                    'revisors',
                    'editions',
                    'copies',
                    'borrowers',
                    'borrow_requests',
                ];
                $results = Cache::remember('admin_tables_count', 60, function () use ($tables) {
                    $parts = [];
                    foreach ($tables as $table) {
                        $where = Schema::hasColumn($table, 'deleted_at') ? " WHERE `deleted_at` IS NULL" : '';
                        $parts[] = "SELECT '" . $table . "' AS table_name, COUNT(*) AS total FROM `" . $table . "`" . $where;
                    }

                    $query = implode(' UNION ALL ', $parts);

                    return collect(DB::select($query))->pluck('total', 'table_name');
                });

                return $builder->groups([
                    NavigationGroup::make(__('Dashboard'))
                        ->items([
                            NavigationItem::make(__('Dashboard'))
                                ->url(Pages\Dashboard::getUrl())
                                ->icon(null),

                        ])
                        ->icon('heroicon-o-chart-pie'),
                    NavigationGroup::make(__('Operations'))
                        ->icon('heroicon-o-cog-8-tooth')
                        ->items([
                            NavigationItem::make(__('Categories'))
                                ->url(CategoryResource::getUrl())
                                ->badge($results['categories'], color: 'warning'),

                            NavigationItem::make(__('Authors'))
                                ->url(AuthorResource::getUrl())
                                ->badge($results['authors'], color: 'warning'),

                            NavigationItem::make(__('Publishers'))
                                ->url(PublisherResource::getUrl())
                                ->badge($results['publishers'], color: 'warning'),

                            NavigationItem::make(__('Translators'))
                                ->url(TranslatorResource::getUrl())
                                ->badge($results['translators'], color: 'warning'),

                            NavigationItem::make(__('Revisors'))
                                ->url(RevisorResource::getUrl())
                                ->badge($results['revisors'], color: 'warning'),

                            NavigationItem::make(__('Subjects'))
                                ->url(SubjectResource::getUrl())
                                ->badge($results['subjects'], color: 'warning'),

                            NavigationItem::make(__('Series'))
                                ->url(SeriesResource::getUrl())
                                ->badge($results['series'], color: 'warning'),

                        ]),
                    NavigationGroup::make(__('Books'))
                        ->icon('heroicon-o-book-open')
                        ->items([
                            NavigationItem::make(__('Books'))
                                ->url(BookResource::getUrl())
                                ->badge($results['books'], color: 'warning'),

                            NavigationItem::make(__('Editions'))
                                ->url(EditionResource::getUrl())
                                ->badge($results['editions'], color: 'warning'),

                            NavigationItem::make('النسخ')
                                ->url(CopyResource::getUrl())
                                ->badge($results['copies'], color: 'warning'),
                        ]),

                    NavigationGroup::make(__('Borrowing'))
                        ->icon('heroicon-o-arrow-path-rounded-square')
                        ->items([
                            NavigationItem::make(__('Borrowers'))
                                ->url(BorrowerResource::getUrl())
                                ->badge($results['borrowers'] ?? 0, color: 'warning'),

                            NavigationItem::make(__('Borrow Requests'))
                                ->url(BorrowRequestResource::getUrl())
                                ->badge($results['borrow_requests'] ?? 0, color: 'warning'),
                        ]),
                ]);

            })
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])->authGuard('web');
    }

    public function register(): void
    {
        parent::register();

        \Filament\Actions\CreateAction::configureUsing(function (Action $action) {
            return $action
                ->icon('heroicon-o-plus');
        });

        \Filament\Tables\Actions\ViewAction::configureUsing(function (\Filament\Tables\Actions\ViewAction $action) {
            return $action
                ->icon('heroicon-o-eye');
        });
    }
}
