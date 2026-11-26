@extends('shared_layouts.student')

@section('title', 'My Quizzes')

@section('content')
    <!-- Partition quizzes into available, upcoming, and completed collections -->
    @php
        $now = now();
        $codeFilter = strtoupper(trim((string) request('code', '')));
        $allQuizzes = collect($quizzes ?? []);

        if ($codeFilter !== '') {
            $allQuizzes = $allQuizzes->filter(fn($q) => str_contains(strtoupper($q->quiz_code ?? ''), $codeFilter));
        }

        $availableList = isset($availableQuizzes)
            ? collect($availableQuizzes)
            : $allQuizzes->filter(
                fn($q) => ($q->status ?? 'active') === 'active' &&
                    (!$q->open_at || \Carbon\Carbon::parse($q->open_at)->lte($now)) &&
                    (!$q->closed_at || \Carbon\Carbon::parse($q->closed_at)->gte($now)),
            );

        $upcomingList = isset($upcomingQuizzes)
            ? collect($upcomingQuizzes)
            : $allQuizzes->filter(
                fn($q) => ($q->status ?? 'active') === 'active' &&
                    $q->open_at &&
                    \Carbon\Carbon::parse($q->open_at)->gt($now),
            );

        $completedList = collect($completedAttempts ?? ($attempts ?? []));
        $ongoingMap = collect($ongoingAttempts ?? (isset($ongoing) && $ongoing ? [$ongoing] : []))->keyBy('quiz_id');
    @endphp

    <!-- ============================================================ -->
    <!--     MY QUIZZES (RESPONSIVE: MOBILE & DESKTOP)                -->
    <!-- ============================================================ -->

    <!-- Top Dark Green Banner -->
    <div class="bg-[#11422d] text-white p-6 sm:p-8 rounded-2xl sm:rounded-3xl shadow-sm mb-6">
        <!-- Mobile-only brand badge -->
        <div class="flex items-center gap-2.5 mb-4 md:hidden">
            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-xs">
                <span class="text-[#11422d] font-black text-base">Q</span>
            </div>
            <span class="text-xs font-bold tracking-wider uppercase text-white">DWCC QUIZGUARD</span>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">My Quizzes</h1>
                <p class="text-xs sm:text-sm text-emerald-100/80 mt-1">Take graded quizzes and track your attempts</p>
            </div>
            <div
                class="hidden sm:inline-flex bg-white/10 px-4 py-2 rounded-xl text-xs font-semibold text-emerald-200 border border-white/10">
                {{ auth()->user()->department ?? 'DWCC' }} &bull; Student Portal
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="space-y-6">

        <!-- Segmented Tab Bar -->
        <div
            class="bg-white p-1 rounded-2xl border border-slate-200/80 shadow-xs flex items-center max-w-md text-xs font-medium">
            <button type="button" onclick="switchQuizTab('available')" data-tab-btn="available"
                class="quiz-tab-btn flex-1 py-2 text-center rounded-xl bg-[#11422d] text-white font-semibold shadow-xs transition">
                Available <span class="ml-1 opacity-90">{{ $availableList->count() }}</span>
            </button>
            <button type="button" onclick="switchQuizTab('upcoming')" data-tab-btn="upcoming"
                class="quiz-tab-btn flex-1 py-2 text-center rounded-xl text-slate-600 hover:text-slate-900 transition">
                Upcoming <span class="ml-1 text-slate-400">{{ $upcomingList->count() }}</span>
            </button>
            <button type="button" onclick="switchQuizTab('completed')" data-tab-btn="completed"
                class="quiz-tab-btn flex-1 py-2 text-center rounded-xl text-slate-600 hover:text-slate-900 transition">
                Completed <span class="ml-1 text-slate-400">{{ $completedList->count() }}</span>
            </button>
        </div>

        <!-- Available Quizzes Panel -->
        <div id="tab-panel-available" class="quiz-tab-panel">
            @if ($availableList->isNotEmpty())
                <!-- Quiz Cards: Compact max width to keep badge and button close -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($availableList as $quiz)
                        @php
                            $subjectCode = strtoupper(
                                substr($quiz->subject->subject_code ?? ($quiz->title ?? 'QZ'), 0, 2),
                            );
                            $questionTotal = (int) ($quiz->number_of_questions ?? 0);
                            $closedAt = $quiz->closed_at ? \Carbon\Carbon::parse($quiz->closed_at) : null;
                            $closingSoon = $closedAt && $closedAt->diffInHours($now) <= 24;
                            $ongoingQuiz = $ongoingMap->get($quiz->quiz_id);
                        @endphp
                        <div
                            class="w-full max-w-[390px] bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition flex flex-col justify-between">
                            <div>
                                <div class="flex items-start gap-3 mb-3">
                                    <div
                                        class="w-11 h-11 rounded-xl bg-emerald-100/70 text-[#11422d] font-black text-sm flex items-center justify-center shrink-0">
                                        {{ $subjectCode }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h2 class="text-base font-bold text-slate-900 leading-snug truncate">
                                            {{ $quiz->title }}</h2>
                                        <p class="text-xs text-slate-500 mt-0.5">
                                            {{ $questionTotal }} {{ $questionTotal === 1 ? 'question' : 'questions' }}
                                            &bull; {{ $quiz->time_limit_min ? $quiz->time_limit_min . ' mins' : 'Untimed' }}
                                        </p>
                                        <p class="text-[11px] text-slate-400">
                                            {{ $quiz->attempts_allowed ?? 1 }}
                                            {{ ($quiz->attempts_allowed ?? 1) == 1 ? 'attempt' : 'attempts' }} &bull; Code:
                                            {{ $quiz->quiz_code }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Compact bottom row: Badge and Button nicely proportioned -->
                            <div class="mt-4 pt-3.5 border-t border-slate-100 flex items-center justify-between gap-3">
                                <div class="min-w-0 shrink-0">
                                    @if ($closingSoon)
                                        <span
                                            class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-amber-800 bg-amber-50 border border-amber-200/70 px-3 py-1.5 rounded-full whitespace-nowrap">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                            <span>Closing soon: {{ $closedAt->format('M j, g:i A') }}</span>
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center text-[11px] font-medium text-slate-500 bg-slate-100 px-3 py-1.5 rounded-full whitespace-nowrap">
                                            {{ $closedAt ? 'Closes ' . $closedAt->format('M j, g:i A') : 'Open' }}
                                        </span>
                                    @endif
                                </div>

                                <div class="shrink-0">
                                    @if ($ongoingQuiz)
                                        <a href="{{ url('/student/attempts/' . $ongoingQuiz->attempt_id) }}"
                                            class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition shadow-xs whitespace-nowrap">
                                            Resume
                                        </a>
                                    @elseif(Route::has('student.instructions'))
                                        <a href="{{ route('student.instructions', $quiz->quiz_id) }}"
                                            class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-[#11422d] hover:bg-[#0d3423] text-white text-xs font-bold transition shadow-xs whitespace-nowrap">
                                            Start quiz
                                        </a>
                                    @else
                                        <form action="{{ url('/student/quizzes/' . $quiz->quiz_id . '/start') }}"
                                            method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-[#11422d] hover:bg-[#0d3423] text-white text-xs font-bold transition shadow-xs whitespace-nowrap">
                                                Start quiz
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white p-10 rounded-2xl border border-dashed border-slate-200 text-center">
                    <p class="text-sm font-bold text-slate-700">No available quizzes right now</p>
                    <p class="text-xs text-slate-400 mt-1">Check the Upcoming tab or confirm your section enrollment with
                        your teacher.</p>
                </div>
            @endif
        </div>
        <!-- Upcoming Quizzes Panel -->
        <div id="tab-panel-upcoming" class="quiz-tab-panel hidden">
            @if ($upcomingList->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($upcomingList as $quiz)
                        <div
                            class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                            <div>
                                <h2 class="text-base font-bold text-slate-900">{{ $quiz->title }}</h2>
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ $quiz->number_of_questions ?? 0 }} questions ·
                                    {{ $quiz->time_limit_min ? $quiz->time_limit_min . ' minutes' : 'Untimed' }}
                                </p>
                            </div>
                            <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-[11px] font-medium text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                                    Opens {{ \Carbon\Carbon::parse($quiz->open_at)->format('M j, g:i A') }}
                                </span>
                                <span class="text-xs font-semibold text-slate-400">Locked</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white p-10 rounded-2xl border border-dashed border-slate-200 text-center">
                    <p class="text-sm font-bold text-slate-700">No upcoming quizzes scheduled</p>
                </div>
            @endif
        </div>

        <!-- Completed Quizzes Panel -->
        <div id="tab-panel-completed" class="quiz-tab-panel hidden">
            @if ($completedList->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($completedList as $attempt)
                        <div
                            class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                            <div>
                                <h2 class="text-base font-bold text-slate-900">
                                    {{ $attempt->quiz->title ?? 'Completed Quiz' }}</h2>
                                <p class="text-xs text-slate-500 mt-1">
                                    Score: <strong
                                        class="text-slate-900">{{ $attempt->score }}/{{ $attempt->total_points }}</strong>
                                    · {{ $attempt->violation_count ?? 0 }} violations
                                </p>
                            </div>
                            <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-[11px] text-slate-400">
                                    {{ $attempt->submitted_at ? \Carbon\Carbon::parse($attempt->submitted_at)->format('M j, g:i A') : 'Submitted' }}
                                </span>
                                <a href="{{ url('/student/attempts/' . $attempt->attempt_id . '/result') }}"
                                    class="px-4 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-[#11422d] text-xs font-semibold transition">
                                    View result
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white p-10 rounded-2xl border border-dashed border-slate-200 text-center">
                    <p class="text-sm font-bold text-slate-700">No completed quizzes yet</p>
                </div>
            @endif
        </div>

    </div>

    @push('scripts')
        <script>
            // Switch visible quiz tab panels and button states
            function switchQuizTab(tabName) {
                document.querySelectorAll('.quiz-tab-panel').forEach(panel => {
                    panel.classList.toggle('hidden', panel.id !== `tab-panel-${tabName}`);
                });

                document.querySelectorAll('.quiz-tab-btn').forEach(btn => {
                    const isActive = btn.dataset.tabBtn === tabName;
                    btn.className = 'quiz-tab-btn flex-1 py-2 text-center rounded-xl transition ' +
                        (isActive ? 'bg-[#11422d] text-white font-semibold shadow-xs' :
                            'text-slate-600 hover:text-slate-900');
                });
            }
        </script>
    @endpush
@endsection
