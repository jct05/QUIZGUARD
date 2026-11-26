@extends('shared_layouts.student')

@section('title', 'Dashboard')

@section('content')
    <!-- Resolve authenticated student display name and dashboard metrics -->
    @php
        $loggedStudent = auth()->user();
        $firstName = $loggedStudent->first_name ?? 'Student';
        $studentDepartment = $loggedStudent->department ?? 'DWCC';
        $activeQuizzesCount = $activeQuizzesCount ?? 0;
        $completedQuizzesCount = $completedQuizzesCount ?? 0;
        $averageScore = $averageScore ?? 0;
        $integrityStatus = $integrityStatus ?? 'Clear';
    @endphp

    <div class="space-y-6">
        <!-- Render welcome banner and quick quiz join form -->
        <section
            class="bg-gradient-to-br from-[#11422d] to-[#185c3f] rounded-3xl p-6 sm:p-8 text-white shadow-sm relative overflow-hidden">
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="space-y-2 max-w-xl">
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 text-[11px] font-bold uppercase tracking-wider text-emerald-100">
                        <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                        {{ $studentDepartment }} &bull; Student Dashboard
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Welcome back, {{ $firstName }}!
                    </h2>
                    <p class="text-xs sm:text-sm text-emerald-100/90 leading-relaxed">
                        Stay on top of your upcoming assessments, resume active attempts, and track your academic
                        performance.
                    </p>
                </div>

                <!-- Validate and submit quiz access code form -->
                <form id="quick-join-form" action="{{ url('/student/quizzes') }}" method="GET"
                    class="w-full lg:w-auto bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-3 sm:p-4">
                    <label for="quiz_code"
                        class="block text-[11px] font-semibold uppercase tracking-wider text-emerald-100 mb-2">
                        Have a Quiz Code?
                    </label>
                    <div class="flex items-center gap-2">
                        <input id="quiz_code" name="code" type="text" maxlength="12" placeholder="e.g. DWCC402"
                            oninput="formatQuizCode(this)"
                            class="w-full sm:w-44 px-3.5 py-2.5 rounded-xl bg-white text-slate-900 placeholder:text-slate-400 text-xs font-bold tracking-wider uppercase focus:outline-none focus:ring-2 focus:ring-emerald-300" />
                        <button type="submit"
                            class="px-4 py-2.5 rounded-xl bg-emerald-300 hover:bg-emerald-200 text-[#0d3423] text-xs font-extrabold transition shrink-0">
                            Find Quiz
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Display ongoing quiz attempt resume alert if active -->
        @if (!empty($ongoing))
            <section
                class="bg-amber-50 border border-amber-200 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-wider text-amber-800">Quiz In Progress</p>
                    <p class="text-sm font-bold text-slate-900 mt-0.5">
                        {{ $ongoing->quiz->title ?? 'Ongoing Quiz Attempt' }}
                    </p>
                    <p class="text-xs text-slate-600">You have an unfinished attempt waiting to be submitted.</p>
                </div>
                <a href="{{ url('/student/attempts/' . $ongoing->attempt_id) }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition shrink-0">
                    Resume Attempt &rarr;
                </a>
            </section>
        @endif

        <!-- Display student performance metrics and integrity status -->
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Open Quizzes</p>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-2xl font-black text-slate-900">{{ $activeQuizzesCount }}</span>
                    <span
                        class="text-[11px] font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">Available</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Completed</p>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-2xl font-black text-slate-900">{{ $completedQuizzesCount }}</span>
                    <span class="text-[11px] font-semibold text-slate-500">Submitted</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Average Score</p>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-2xl font-black text-[#11422d]">{{ $averageScore }}%</span>
                    <span
                        class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">Overall</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">QuizGuard Status</p>
                <div class="mt-2 flex items-baseline justify-between">
                    <span
                        class="text-2xl font-black {{ $integrityStatus === 'Clear' ? 'text-emerald-700' : 'text-amber-600' }}">{{ $integrityStatus }}</span>
                    <span
                        class="text-[11px] font-semibold {{ $integrityStatus === 'Clear' ? 'text-emerald-700 bg-emerald-50' : 'text-amber-700 bg-amber-50' }} px-2 py-0.5 rounded-full">
                        {{ $integrityStatus === 'Clear' ? 'Verified' : 'Check Logs' }}
                    </span>
                </div>
            </div>
        </section>

        <!-- Organize assigned quizzes and helpful student sidebar -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <!-- Render active quizzes available for student section enrollment -->
            <div class="lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Available Quizzes</h3>
                        <p class="text-xs text-slate-500">Active assessments for your enrolled sections</p>
                    </div>
                    <a href="{{ url('/student/quizzes') }}" class="text-xs font-bold text-[#11422d] hover:underline">
                        View All &rarr;
                    </a>
                </div>

                @if (isset($quizzes) && $quizzes->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($quizzes as $quiz)
                            <div
                                class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="px-2.5 py-0.5 rounded-full bg-[#e2ece7] text-[#11422d] text-[10px] font-extrabold uppercase">
                                            {{ $quiz->subject->subject_code ?? 'QUIZ' }}
                                        </span>
                                        <span class="text-[11px] font-semibold text-slate-400">
                                            Code: {{ $quiz->quiz_code }}
                                        </span>
                                    </div>
                                    <h4 class="text-sm font-extrabold text-slate-900">{{ $quiz->title }}</h4>
                                    <p class="text-xs text-slate-500">
                                        {{ $quiz->time_limit_min ? $quiz->time_limit_min . ' mins' : 'Untimed' }}
                                        &bull; Closes {{ \Carbon\Carbon::parse($quiz->closed_at)->format('M d, g:i A') }}
                                    </p>
                                </div>

                                <form action="{{ url('/student/quizzes/' . $quiz->quiz_id . '/start') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-[#11422d] hover:bg-[#0d3423] text-white text-xs font-bold transition">
                                        Start Quiz
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white rounded-2xl border border-dashed border-slate-200 p-8 text-center">
                        <p class="text-sm font-bold text-slate-700">No active quizzes right now</p>
                        <p class="text-xs text-slate-400 mt-1">Check back later or open My Quizzes to view upcoming
                            schedules.</p>
                    </div>
                @endif
            </div>

            <!-- Display reviewer shortcut and recent activity -->
            <aside class="space-y-4">
                <div class="bg-[#e2ece7] rounded-2xl border border-emerald-900/10 p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-[#11422d] text-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-extrabold text-[#0d3423]">My Quizzes</h4>
                            <p class="text-[11px] text-emerald-900/80">Browse all enrolled assessments</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-700 leading-relaxed mb-4">
                        View open, upcoming, and completed quizzes across all your enrolled DWCC sections.
                    </p>
                    <a href="{{ url('/student/quizzes') }}"
                        class="block w-full text-center py-2.5 rounded-xl bg-[#11422d] hover:bg-[#0d3423] text-white text-xs font-bold transition">
                        Go to My Quizzes
                    </a>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-extrabold text-slate-900">Recent Results</h4>
                    </div>

                    @if (isset($recentAttempts) && $recentAttempts->isNotEmpty())
                        <ul class="divide-y divide-slate-100">
                            @foreach ($recentAttempts as $attempt)
                                <li class="py-2.5 flex items-center justify-between text-xs gap-2">
                                    <div class="min-w-0">
                                        <a href="{{ url('/student/attempts/' . $attempt->attempt_id . '/result') }}"
                                            class="font-bold text-slate-800 hover:text-[#11422d] truncate block">
                                            {{ $attempt->quiz->title ?? 'Quiz Attempt' }}
                                        </a>
                                        <p class="text-[11px] text-slate-400">
                                            {{ $attempt->submitted_at ? \Carbon\Carbon::parse($attempt->submitted_at)->diffForHumans() : 'Submitted' }}
                                        </p>
                                    </div>
                                    <span class="font-extrabold text-[#11422d] shrink-0">
                                        {{ $attempt->score }}/{{ $attempt->total_points }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="rounded-xl bg-slate-50 border border-dashed border-slate-200 p-4 text-center">
                            <p class="text-xs font-semibold text-slate-600">No recent attempts yet</p>
                            <p class="text-[11px] text-slate-400 mt-1">Completed quiz scores will appear here.</p>
                        </div>
                    @endif
                </div>

                <!-- Display QuizGuard academic integrity reminder notice card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 mb-2">
                        QuizGuard Reminders
                    </h4>
                    <ul class="space-y-2 text-xs text-slate-600">
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-700 font-bold">&bull;</span>
                            <span>Avoid switching tabs or minimizing the browser during active quizzes.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-700 font-bold">&bull;</span>
                            <span>Ensure a stable internet connection before starting a timed exam.</span>
                        </li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Format entered quiz access code into uppercase characters
        function formatQuizCode(input) {
            input.value = input.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
        }

        // Handle quick quiz code submission and validation
        document.getElementById('quick-join-form')?.addEventListener('submit', function(event) {
            const codeInput = document.getElementById('quiz_code');
            if (codeInput && codeInput.value.trim().length === 0) {
                event.preventDefault();
                codeInput.focus();
            }
        });
    </script>
@endpush
