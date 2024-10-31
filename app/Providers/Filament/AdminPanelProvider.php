<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Resources\AuthorResource;
use App\Filament\Admin\Resources\CategoryResource;
use App\Filament\Admin\Resources\PublisherResource;
use App\Filament\Admin\Resources\RevisorResource;
use App\Filament\Admin\Resources\SeriesResource;
use App\Filament\Admin\Resources\SubjectResource;
use App\Filament\Admin\Resources\TranslatorResource;
use App\Models\Author;
use App\Models\Category;
use App\Models\Publisher;
use App\Models\Revisor;
use App\Models\Series;
use App\Models\Subject;
use App\Models\Translator;
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
                                ->badge(Category::count(), color: 'warning'),

                            NavigationItem::make(__('Authors'))
                                ->url(AuthorResource::getUrl())
                                ->badge(Author::count(), color: 'warning'),

                            NavigationItem::make(__('Publishers'))
                                ->url(PublisherResource::getUrl())
                                ->badge(Publisher::count(), color: 'warning'),

                            NavigationItem::make(__('Translators'))
                                ->url(TranslatorResource::getUrl())
                                ->badge(Translator::count(), color: 'warning'),

                            NavigationItem::make(__('Revisors'))
                                ->url(RevisorResource::getUrl())
                                ->badge(Revisor::count(), color: 'warning'),

                            NavigationItem::make(__('Subjects'))
                                ->url(SubjectResource::getUrl())
                                ->badge(Subject::count(), color: 'warning'),

                            NavigationItem::make(__('Series'))
                                ->url(SeriesResource::getUrl())
                                ->badge(Series::count(), color: 'warning'),

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
