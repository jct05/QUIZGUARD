<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login') - DWCC QuizGuard</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/quizguard-logo.webp') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    },
                    colors: {
                        dwcc: {
                            green: '#11422d',
                            dark: '#0d3423',
                            light: '#185c3f',
                            sage: '#e2ece7'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-[#f8fafc] text-slate-800 antialiased min-h-screen flex">

    <div class="hidden lg:flex w-1/2 bg-[#11422d] text-white flex-col justify-between p-12">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/quizguard-logo.webp') }}" alt="DWCC QuizGuard Logo"
                class="w-11 h-11 rounded-xl object-cover shadow-md shrink-0 bg-[#0f3422]">
            <div>
                <h1 class="text-sm font-bold tracking-wide uppercase leading-tight">DWCC QuizGuard</h1>
                <p class="text-[9px] text-emerald-200/70 font-medium tracking-wider uppercase mt-0.5">Divine Word
                    College of Calapan</p>
            </div>
        </div>

        <div>
            <h2 class="text-3xl font-bold leading-tight tracking-tight">Fair quizzes,<br>honest results.</h2>
            <p class="text-sm text-emerald-100/70 mt-4 max-w-sm">Online quizzes for DWCC students, with randomized
                questions and monitored sessions.</p>
            <ul class="mt-8 space-y-3 text-sm text-emerald-100/80">
                <li>Randomized questions and choices for every student</li>
                <li>Monitored sessions with automatic submission</li>
                <li>Scores and violation reports for teachers</li>
            </ul>
        </div>

        <p class="text-[11px] text-emerald-200/50">&copy; {{ date('Y') }} Divine Word College of Calapan</p>
    </div>

    <div class="flex-1 flex items-center justify-center p-8">
        <div class="w-full max-w-sm">

            <!-- logo for small screens -->
            <div class="lg:hidden flex items-center gap-3 mb-8">
                <img src="{{ asset('images/quizguard-logo.webp') }}" alt="DWCC QuizGuard Logo"
                    class="w-10 h-10 rounded-xl object-cover shadow-md shrink-0 bg-[#0f3422]">
                <h1 class="text-sm font-bold tracking-wide uppercase text-slate-900">DWCC QuizGuard</h1>
            </div>

            @if (session('success'))
                <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-700 text-xs border border-emerald-200">
                    {{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 px-4 py-3 rounded-xl bg-rose-50 text-rose-700 text-xs border border-rose-200">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script>
        // Block browser context menu right click across pages
        document.addEventListener('contextmenu', function(event) {
            event.preventDefault();
        });

        // Block keyboard shortcuts for developer tools and source
        document.addEventListener('keydown', function(event) {
            const key = (event.key || '').toUpperCase();
            const ctrlOrMeta = event.ctrlKey || event.metaKey;

            // Block F12
            if (key === 'F12' || event.keyCode === 123) {
                event.preventDefault();
                return false;
            }

            // Block Ctrl+Shift+I/J/C/K (Windows/Linux) and Cmd+Opt+I/J/C/U (macOS)
            if ((ctrlOrMeta && event.shiftKey && ['I', 'J', 'C', 'K'].includes(key)) ||
                (event.metaKey && event.altKey && ['I', 'J', 'C', 'U'].includes(key))) {
                event.preventDefault();
                return false;
            }

            // Block Ctrl+U / Cmd+U (View Source) and Ctrl+S / Cmd+S (Save Page)
            if (ctrlOrMeta && ['U', 'S'].includes(key)) {
                event.preventDefault();
                return false;
            }
        });
    </script>

</body>

</html>
