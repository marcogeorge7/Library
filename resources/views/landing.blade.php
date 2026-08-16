<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <meta name="description" content="مكتبة الكنيسة الاستعارية — تصفّحوا الفهرس، اطلبوا استعارة الكتب، وتابعوا طلباتكم بسهولة.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cairo:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-paper text-brand-navy font-sans antialiased">

    <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-brand-paper/90 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-6 py-4">
            <a href="#top" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-emerald-light text-brand-emerald">
                    <x-heroicon-o-building-library class="h-6 w-6" />
                </span>
                <span class="text-lg font-bold leading-tight">{{ config('app.name') }}</span>
            </a>

            <nav class="hidden items-center gap-8 text-sm font-medium text-slate-600 lg:flex">
                <a href="#stats" class="transition hover:text-brand-navy">المكتبة بالأرقام</a>
                <a href="#recent" class="transition hover:text-brand-navy">أحدث الإضافات</a>
                <a href="#how-it-works" class="transition hover:text-brand-navy">كيف تستعير كتابًا</a>
            </nav>

            <div class="flex items-center gap-3">
                <a href="/admin/login" class="hidden text-sm font-medium text-slate-500 transition hover:text-brand-navy sm:inline">
                    دخول الموظفين
                </a>
                <a href="/borrower/login" class="rounded-lg bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-blue-dark">
                    تسجيل دخول الأعضاء
                </a>
            </div>
        </div>
    </header>

    <main id="top">
        {{-- Hero --}}
        <section class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-x-0 -top-24 -z-10 flex justify-center blur-3xl">
                <div class="aspect-[1155/678] w-[60rem] bg-gradient-to-tr from-brand-blue/20 via-brand-emerald/10 to-transparent"
                     style="clip-path: polygon(74% 44%, 100% 61%, 97% 26%, 85% 0%, 80% 2%, 72% 32%, 60% 62%, 32% 25%, 0% 100%, 41% 87%)"></div>
            </div>

            <div class="mx-auto max-w-4xl px-6 py-20 text-center sm:py-28">
                <p class="mb-4 inline-flex items-center gap-2 rounded-full bg-brand-emerald-light px-4 py-1.5 text-sm font-medium text-brand-emerald">
                    <x-heroicon-o-sparkles class="h-4 w-4" />
                    نظام استعارة الكتب الإلكتروني
                </p>

                <h1 class="text-4xl font-extrabold leading-tight tracking-tight text-brand-navy sm:text-5xl">
                    مكتبتكم الروحية... في متناول يدكم
                </h1>

                <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-slate-600">
                    تصفّحوا فهرس {{ config('app.name') }} إلكترونيًا، اطلبوا استعارة الكتب التي تحتاجونها من مكانكم،
                    وتابعوا حالة طلباتكم بسهولة من خلال حساب العضوية الخاص بكم.
                </p>

                <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                    <a href="/borrower/login"
                       class="inline-flex items-center gap-2 rounded-lg bg-brand-blue px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-brand-blue-dark">
                        تسجيل دخول الأعضاء
                        <x-heroicon-o-arrow-left class="h-5 w-5 rtl:rotate-180" />
                    </a>
                    <a href="#recent"
                       class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-6 py-3 text-base font-semibold text-brand-navy transition hover:border-slate-400">
                        تصفّح أحدث الإضافات
                        <x-heroicon-o-arrow-down class="h-5 w-5" />
                    </a>
                </div>
            </div>
        </section>

        {{-- Live stats --}}
        <section id="stats" class="border-y border-slate-200 bg-white">
            <div class="mx-auto grid max-w-6xl grid-cols-2 gap-px overflow-hidden rounded-none bg-slate-200 sm:grid-cols-4">
                @foreach ([
                    ['icon' => 'book-open', 'value' => $totalBooks, 'label' => 'كتاب في الفهرس'],
                    ['icon' => 'check-circle', 'value' => $availableCopies, 'label' => 'نسخة متاحة للاستعارة الآن'],
                    ['icon' => 'tag', 'value' => $totalCategories, 'label' => 'تصنيف موضوعي'],
                    ['icon' => 'user-group', 'value' => $totalAuthors, 'label' => 'مؤلف ومؤلفة'],
                ] as $stat)
                    <div class="flex flex-col items-center gap-2 bg-white px-4 py-10 text-center">
                        <x-dynamic-component :component="'heroicon-o-' . $stat['icon']" class="h-6 w-6 text-brand-emerald" />
                        <span class="text-3xl font-extrabold text-brand-navy">{{ number_format($stat['value']) }}</span>
                        <span class="text-sm text-slate-500">{{ $stat['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Recently added --}}
        <section id="recent" class="mx-auto max-w-6xl px-6 py-20">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-brand-navy">أحدث الإضافات إلى المكتبة</h2>
                <p class="mx-auto mt-3 max-w-xl text-slate-600">تعرّفوا على آخر الكتب التي أُضيفت إلى فهرس المكتبة.</p>
            </div>

            @if ($recentEditions->isNotEmpty())
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($recentEditions as $edition)
                        <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                            @if ($edition->book?->category)
                                <span class="inline-flex w-fit items-center rounded-full bg-brand-emerald-light px-3 py-1 text-xs font-medium text-brand-emerald">
                                    {{ $edition->book->category->name }}
                                </span>
                            @endif

                            <h3 class="text-lg font-bold leading-snug text-brand-navy">
                                {{ $edition->book?->name ?? 'كتاب بلا عنوان' }}
                            </h3>

                            <p class="flex items-center gap-1.5 text-sm text-slate-500">
                                <x-heroicon-o-pencil class="h-4 w-4 shrink-0" />
                                {{ $edition->book?->author?->pluck('name')->filter()->implode('، ') ?: 'غير معروف' }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center text-slate-500">
                    سيتم عرض الإضافات الجديدة هنا قريبًا.
                </p>
            @endif
        </section>

        {{-- How it works --}}
        <section id="how-it-works" class="border-t border-slate-200 bg-white">
            <div class="mx-auto max-w-6xl px-6 py-20">
                <div class="mb-12 text-center">
                    <h2 class="text-3xl font-bold text-brand-navy">كيف تستعير كتابًا؟</h2>
                    <p class="mx-auto mt-3 max-w-xl text-slate-600">ثلاث خطوات بسيطة، من الطلب إلى الاستلام.</p>
                </div>

                <div class="grid grid-cols-1 gap-10 md:grid-cols-3">
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
                        <div class="text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand-blue/10 text-brand-blue">
                                <x-dynamic-component :component="'heroicon-o-' . $step['icon']" class="h-7 w-7" />
                            </div>
                            <p class="mt-4 text-sm font-semibold text-brand-emerald">الخطوة {{ $index + 1 }}</p>
                            <h3 class="mt-1 text-lg font-bold text-brand-navy">{{ $step['title'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $step['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-brand-navy text-slate-300">
        <div class="mx-auto flex max-w-6xl flex-col items-center gap-6 px-6 py-12 text-center sm:flex-row sm:justify-between sm:text-right">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-white">
                    <x-heroicon-o-building-library class="h-5 w-5" />
                </span>
                <span class="font-bold text-white">{{ config('app.name') }}</span>
            </div>

            <div class="flex items-center gap-6 text-sm">
                <a href="/borrower/login" class="transition hover:text-white">تسجيل دخول الأعضاء</a>
                <a href="/admin/login" class="transition hover:text-white">دخول الموظفين</a>
            </div>

            <p class="text-sm text-slate-400">
                &copy; {{ date('Y') }} {{ config('app.name') }} — جميع الحقوق محفوظة
            </p>
        </div>
    </footer>

</body>
</html>
