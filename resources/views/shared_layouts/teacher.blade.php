<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Teacher') - DWCC QuizGuard</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/quizguard-logo.webp') }}">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (Using CDN for easy student preview without requiring node build step) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        dwcc: {
                            green: '#11422d',
                            dark: '#0d3423',
                            light: '#185c3f',
                            sage: '#e2ece7',
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

    @php
        $initials = strtoupper(substr(Auth::user()->first_name, 0, 1) . substr(Auth::user()->last_name, 0, 1));
    @endphp

    <!-- ========================================== -->
    <!--    LEFT SIDEBAR (DWCC Forest Green Theme)  -->
    <!-- ========================================== -->
    <aside
        class="w-64 bg-[#11422d] text-white h-screen sticky top-0 flex flex-col justify-between p-4 shrink-0 shadow-lg">
        <div>
            <!-- DWCC Brand / Logo Section -->
            <div class="flex items-center gap-3 px-2 py-3 mb-6">
                <img src="{{ asset('images/quizguard-logo.webp') }}" alt="DWCC QuizGuard Logo"
                    class="w-10 h-10 rounded-xl object-cover shadow-md shrink-0 bg-[#0f3422]">
                <div>
                    <h1 class="text-sm font-bold tracking-wide uppercase leading-tight text-white">DWCC QuizGuard</h1>
                    <p class="text-[9px] text-emerald-200/70 font-medium tracking-wider uppercase mt-0.5">
                        Divine Word College of Calapan
                    </p>
                </div>
            </div>

            <!-- Navigation Menu -->
            <div>
                <p class="text-[10px] uppercase font-bold text-emerald-300/50 tracking-wider px-3 mb-2">Menu</p>
                <nav class="space-y-1">
                    <!-- Dashboard -->
                    <a href="{{ url('/teacher/dashboard') }}"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->is('teacher/dashboard') ? 'bg-white/15 text-white shadow-xs' : 'text-emerald-100/70 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>

                    <!-- Subjects -->
                    <a href="{{ url('/teacher/subjects') }}"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->is('teacher/subjects*') ? 'bg-white/15 text-white shadow-xs' : 'text-emerald-100/70 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10" />
                        </svg>
                        Subjects
                    </a>

                    <!-- Sections -->
                    <a href="{{ url('/teacher/sections') }}"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->is('teacher/sections*') ? 'bg-white/15 text-white shadow-xs' : 'text-emerald-100/70 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Sections
                    </a>

                    <!-- My Library -->
                    <a href="{{ url('/teacher/question-banks') }}"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->is('teacher/question-banks*') ? 'bg-white/15 text-white shadow-xs' : 'text-emerald-100/70 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        My Library
                    </a>

                    <!-- Quizzes -->
                    <a href="{{ url('/teacher/quizzes') }}"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->is('teacher/quizzes*', 'teacher/attempts*') ? 'bg-white/15 text-white shadow-xs' : 'text-emerald-100/70 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Quizzes
                    </a>

                    <!-- Reports -->
                    <a href="{{ url('/teacher/reports') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->is('teacher/reports*') ? 'bg-white/15 text-white' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Reports</span>
                    </a>

                    <!-- Settings -->
                    <a href="{{ url('/teacher/settings') }}"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->is('teacher/settings*') ? 'bg-white/15 text-white shadow-xs' : 'text-emerald-100/70 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        Settings
                    </a>
                </nav>
            </div>
        </div>

        <!-- Bottom User Profile Card -->
        <div class="bg-white/10 rounded-2xl p-3 flex items-center justify-between border border-white/10">
            <div class="flex items-center gap-2.5 min-w-0">
                <div
                    class="w-9 h-9 rounded-full bg-white text-[#11422d] font-bold text-xs flex items-center justify-center shrink-0 shadow-xs">
                    {{ $initials }}
                </div>
                <div class="truncate">
                    <p class="text-xs font-semibold text-white leading-tight truncate">
                        {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
                    </p>
                    <p class="text-[10px] text-emerald-200/70 font-medium truncate">
                        {{ ucfirst(Auth::user()->role) }}
                    </p>
                </div>
            </div>

            <!-- Logout Action -->
            <form action="{{ url('/logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" title="Logout"
                    class="text-emerald-200/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- ========================================== -->
    <!--    MAIN APPLICATION CONTENT AREA           -->
    <!-- ========================================== -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- Top Navigation Header -->
        <header
            class="bg-white border-b border-gray-100 px-8 py-3.5 flex items-center justify-between sticky top-0 z-20">
            <p class="text-sm text-slate-500">Teacher portal</p>

            <!-- Right Controls: Teacher Name & Initials Avatar -->
            <div class="flex items-center gap-3">
                <div class="hidden sm:block text-right leading-tight">
                    <p class="text-xs font-semibold text-slate-800">
                        {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
                    </p>
                    <p class="text-[10px] text-slate-500">{{ ucfirst(Auth::user()->role) }}</p>
                </div>
                <div
                    class="w-9 h-9 rounded-full bg-[#11422d] text-white font-bold text-xs flex items-center justify-center shadow-xs shrink-0">
                    {{ $initials }}
                </div>
            </div>
        </header>

        <!-- Page Specific Body Content -->
        <main class="p-8">
            @if (session('success'))
                <div
                    class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-700 text-xs border border-emerald-200">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 px-4 py-3 rounded-xl bg-rose-50 text-rose-700 text-xs border border-rose-200">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </main>
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
