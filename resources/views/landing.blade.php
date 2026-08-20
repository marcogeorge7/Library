<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <meta name="description" content="مكتبة الكنيسة الاستعارية — تصفّحوا الفهرس، اطلبوا استعارة الكتب، وتابعوا طلباتكم بسهولة.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=markazi-text:400,500,600,700&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>

    <div class="utility-bar">
        <div class="wrap utility-bar-center">
            <span>نظام الاستعارة الإلكتروني لـ {{ config('app.name') }}</span>
        </div>
    </div>

    <header class="site-header">
        <div class="wrap">
            <a href="#top" class="brand">
                <span class="brand-mark">
                    <x-heroicon-o-building-library />
                </span>
                {{ config('app.name') }}
            </a>

            <nav class="main-nav">
                <a href="#stats">المكتبة بالأرقام</a>
                <a href="#recent">أحدث الإضافات</a>
                <a href="#explore">استكشفوا الفهرس</a>
                <a href="#how-it-works">كيف تستعير كتابًا</a>
            </nav>

            <div class="header-actions">
                <a href="/register" class="btn btn-outline">تسجيل عضوية جديدة</a>
                <a href="/borrower/login" class="btn btn-primary">تسجيل الدخول</a>
            </div>
        </div>
    </header>

    <main id="top">
        {{-- Hero --}}
        <section class="hero">
            <div class="hero-inner">
                <span class="hero-badge-ring">
                    <x-heroicon-o-building-library />
                </span>

                <h1>مكتبتكم الروحية... في متناول يدكم</h1>

                <p>
                    تصفّحوا فهرس {{ config('app.name') }} إلكترونيًا، اطلبوا استعارة الكتب التي تحتاجونها من مكانكم،
                    وتابعوا حالة طلباتكم بسهولة من خلال حساب العضوية الخاص بكم.
                </p>

                <div class="hero-actions">
                    <a href="/register" class="btn btn-primary btn-lg">
                        تسجيل عضوية جديدة
                        <x-heroicon-o-arrow-left />
                    </a>
                    <a href="/borrower/login" class="btn btn-ghost-light btn-lg">
                        تسجيل الدخول
                    </a>
                </div>
            </div>
        </section>

        {{-- Live stats --}}
        <section id="stats" class="section-alt">
            <div class="wrap" style="padding-block: 56px;">
                <div class="stats-grid">
                    @foreach ([
                        ['icon' => 'book-open', 'value' => $totalBooks, 'label' => 'كتاب في الفهرس'],
                        ['icon' => 'check-circle', 'value' => $availableCopies, 'label' => 'نسخة متاحة للاستعارة الآن'],
                        ['icon' => 'tag', 'value' => $totalCategories, 'label' => 'تصنيف موضوعي'],
                        ['icon' => 'user-group', 'value' => $totalAuthors, 'label' => 'مؤلف ومؤلفة'],
                    ] as $stat)
                        <div class="stat-card">
                            <x-dynamic-component :component="'heroicon-o-' . $stat['icon']" />
                            <span class="stat-number">{{ number_format($stat['value']) }}</span>
                            <span class="stat-label">{{ $stat['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Recently added --}}
        <section id="recent" class="section">
            <div class="wrap">
                <div class="section-heading">
                    <span class="bar"></span>
                    <h2>أحدث الإضافات إلى المكتبة</h2>
                </div>
                <p class="section-sub">تعرّفوا على آخر الكتب التي أُضيفت إلى فهرس المكتبة.</p>

                @if ($recentEditions->isNotEmpty())
                    <div class="cards-grid">
                        @foreach ($recentEditions as $edition)
                            <div class="card">
                                @if ($edition->book?->category)
                                    <span class="pill">{{ $edition->book->category->name }}</span>
                                @endif

                                <h3>{{ $edition->book?->name ?? 'كتاب بلا عنوان' }}</h3>

                                <p class="author">
                                    <x-heroicon-o-pencil />
                                    {{ $edition->book?->author?->pluck('name')->filter()->implode('، ') ?: 'غير معروف' }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="empty-note">سيتم عرض الإضافات الجديدة هنا قريبًا.</p>
                @endif
            </div>
        </section>

        {{-- Explore the catalog --}}
        <section id="explore" class="section">
            <div class="wrap">
                <div class="section-heading">
                    <span class="bar"></span>
                    <h2>استكشفوا فهرس المكتبة</h2>
                </div>
                <p class="section-sub">تصفّحوا المكتبة عبر أبرز تصنيفاتها الموضوعية وأبرز مؤلفيها.</p>

                <div class="explore-grid">
                    <div class="list-block">
                        <div class="list-block-heading">
                            <span class="bar"></span>
                            <h3>أبرز التصنيفات</h3>
                        </div>
                        <ul class="list-rows">
                            @foreach ($topCategories as $category)
                                <li class="list-row">
                                    <span class="list-row-name">{{ $category->name }}</span>
                                    <span class="list-row-count">{{ number_format($category->books_count) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="list-block">
                        <div class="list-block-heading">
                            <span class="bar"></span>
                            <h3>أبرز المؤلفين</h3>
                        </div>
                        <ul class="list-rows">
                            @foreach ($topAuthors as $author)
                                <li class="list-row">
                                    <span class="list-row-name">{{ $author->name }}</span>
                                    <span class="list-row-count">{{ number_format($author->books_count) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        {{-- How it works --}}
        <section id="how-it-works" class="section section-alt">
            <div class="wrap">
                <div class="section-head-block">
                    <div class="section-heading">
                        <span class="bar"></span>
                        <h2>كيف تستعير كتابًا؟</h2>
                    </div>
                    <p class="section-sub">ثلاث خطوات بسيطة، من الطلب إلى الاستلام.</p>
                </div>

                <div class="steps-grid">
                    @foreach ([
                        [
                            'icon' => 'magnifying-glass',
                            'title' => 'سجّلوا الدخول واطلبوا',
                            'text' => 'ادخلوا إلى حسابكم كعضو، ابحثوا عن الكتاب الذي تريدونه، وأرسلوا طلب استعارة لإحدى نسخه المتاحة.',
                        ],
                        [
                            'icon' => 'shield-check',
                            'title' => 'موافقة أمين المكتبة',
                            'text' => 'يراجع أمين المكتبة طلبكم، يوافق عليه، ويحدّد لكم موعد الاستلام المناسب.',
                        ],
                        [
                            'icon' => 'arrow-uturn-left',
                            'title' => 'استلموه وأعيدوه في موعده',
                            'text' => 'استلموا نسختكم من أمين المكتبة، استمتعوا بالقراءة، ثم أعيدوا الكتاب في الموعد المحدد.',
                        ],
                    ] as $index => $step)
                        <div class="step">
                            <span class="step-icon">
                                <x-dynamic-component :component="'heroicon-o-' . $step['icon']" />
                            </span>
                            <p class="step-eyebrow">الخطوة {{ $index + 1 }}</p>
                            <h3>{{ $step['title'] }}</h3>
                            <p>{{ $step['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="wrap footer-top">
            <div class="footer-brand">
                <span class="brand-mark">
                    <x-heroicon-o-building-library />
                </span>
                {{ config('app.name') }}
            </div>

            <div class="footer-links">
                <a href="/register">تسجيل عضوية جديدة</a>
                <a href="/borrower/login">تسجيل الدخول</a>
            </div>
        </div>

        <p class="footer-bottom">
            &copy; {{ date('Y') }} {{ config('app.name') }} — جميع الحقوق محفوظة
        </p>
    </footer>

</body>
</html>
