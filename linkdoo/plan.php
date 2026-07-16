<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Your plan is free — upgrade on LinkDooni.">
    <title>Plan — LinkDooni</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600&family=Vazirmatn:wght@400;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --ink: #1a1f1c;
            --ink-soft: #3d4a44;
            --cream: #faf8f5;
            --cream-dark: #f0ebe3;
            --gold: #c4a962;
            --gold-light: #e8dcc0;
            --sage: #6b8e7f;
            --sage-dark: #4a6b5c;
            --white: #ffffff;
        }

        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
            background:
                radial-gradient(ellipse 80% 50% at 50% -10%, rgba(107, 142, 127, 0.18), transparent 55%),
                linear-gradient(180deg, var(--cream-dark) 0%, var(--cream) 40%, #e8f0eb 100%);
            color: var(--ink);
            line-height: 1.7;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a { color: var(--sage-dark); text-decoration: none; transition: color 0.2s; }
        a:hover { color: var(--gold); }

        .nav {
            padding: 20px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(196, 169, 98, 0.15);
            background: rgba(250, 248, 245, 0.92);
            backdrop-filter: blur(12px);
        }

        .nav-logo {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--ink);
            letter-spacing: -0.02em;
        }

        .nav-logo span { color: var(--sage); }

        .page {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 24px 64px;
        }

        .card {
            width: 100%;
            max-width: 560px;
            text-align: center;
        }

        .label {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--sage);
            margin-bottom: 28px;
        }

        .langs {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-bottom: 40px;
        }

        .langs p {
            font-size: clamp(1.25rem, 3vw, 1.65rem);
            font-weight: 500;
            color: var(--ink);
        }

        .langs p[lang="fa"],
        .langs p[lang="ar"] {
            font-family: 'Vazirmatn', 'Outfit', sans-serif;
            font-weight: 600;
        }

        .langs p[dir="rtl"] {
            direction: rtl;
        }

        .but {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(1.75rem, 4vw, 2.25rem);
            font-weight: 600;
            font-style: italic;
            color: var(--ink-soft);
            margin-bottom: 28px;
        }

        .btn {
            display: inline-block;
            padding: 16px 40px;
            border-radius: 999px;
            font-family: inherit;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.03em;
            background: linear-gradient(135deg, var(--sage) 0%, var(--sage-dark) 100%);
            color: var(--white);
            box-shadow: 0 4px 24px rgba(107, 142, 127, 0.35);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(107, 142, 127, 0.45);
            color: var(--white);
        }

        .footer {
            padding: 24px;
            text-align: center;
            font-size: 14px;
            color: var(--ink-soft);
            border-top: 1px solid var(--cream-dark);
        }

        @media (max-width: 640px) {
            .nav { padding: 16px 20px; }
        }
    </style>
</head>
<body>

    <nav class="nav">
        <a href="/" class="nav-logo">Link<span>Dooni</span></a>
    </nav>

    <main class="page">
        <div class="card">
            <p class="label">Your plan</p>

            <div class="langs">
                <p lang="en">Your plan is free.</p>
                <p lang="fa" dir="rtl">پلن شما رایگان است.</p>
                <p lang="ar" dir="rtl">خطتك مجانية.</p>
                <p lang="tr">Planınız ücretsiz.</p>
            </div>

            <p class="but">but</p>

            <a class="btn" href="https://boy.bio/buy">Buy</a>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> LinkDooni</p>
    </footer>

</body>
</html>
