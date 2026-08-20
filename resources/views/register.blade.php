<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل عضوية جديدة — {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=markazi-text:400,500,600,700&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>

    <header class="site-header">
        <div class="wrap">
            <a href="/" class="brand">
                <span class="brand-mark">
                    <x-heroicon-o-building-library />
                </span>
                {{ config('app.name') }}
            </a>

            <div class="header-actions">
                <a href="/" class="link-muted">العودة للرئيسية</a>
                <a href="/borrower/login" class="btn btn-outline">لديك حساب؟ تسجيل الدخول</a>
            </div>
        </div>
    </header>

    <main>
        <section class="section">
            <div class="wrap">
                <div class="form-shell">
                    @if (session('registered'))
                        <div class="form-success">
                            <span class="form-success-icon">
                                <x-heroicon-o-check-circle />
                            </span>
                            <h2>تم استلام طلب عضويتكم</h2>
                            <p>
                                شكرًا لتسجيلكم في {{ config('app.name') }}. سيقوم أمين المكتبة بمراجعة بياناتكم،
                                وبمجرد الموافقة على طلبكم ستتمكنون من تسجيل الدخول باستخدام رقم هاتفكم أو بريدكم الإلكتروني.
                            </p>
                            <a href="/" class="btn btn-primary">العودة إلى الرئيسية</a>
                        </div>
                    @else
                        <div class="section-heading">
                            <span class="bar"></span>
                            <h2>تسجيل عضوية جديدة</h2>
                        </div>
                        <p class="section-sub">
                            املؤوا البيانات التالية لطلب عضوية في المكتبة. سيتم تفعيل حسابكم بعد مراجعة أمين المكتبة للطلب.
                        </p>

                        @if ($errors->any())
                            <div class="form-errors">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ url('/register') }}" class="form-card">
                            @csrf

                            <div class="form-group">
                                <label class="form-label" for="name">الاسم بالكامل</label>
                                <input class="form-control" type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="phone">رقم الهاتف</label>
                                    <input class="form-control" type="tel" id="phone" name="phone" value="{{ old('phone') }}" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="email">البريد الإلكتروني <span class="form-optional">(اختياري)</span></label>
                                    <input class="form-control" type="email" id="email" name="email" value="{{ old('email') }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="national_id">الرقم القومي</label>
                                <input class="form-control" type="text" id="national_id" name="national_id" value="{{ old('national_id') }}" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="address">العنوان</label>
                                <input class="form-control" type="text" id="address" name="address" value="{{ old('address') }}" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="password">كلمة المرور</label>
                                    <input class="form-control" type="password" id="password" name="password" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="password_confirmation">تأكيد كلمة المرور</label>
                                    <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg form-submit">إرسال طلب العضوية</button>
                        </form>
                    @endif
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <p class="footer-bottom">
            &copy; {{ date('Y') }} {{ config('app.name') }} — جميع الحقوق محفوظة
        </p>
    </footer>

</body>
</html>
