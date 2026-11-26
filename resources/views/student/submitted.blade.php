@php $hideNav = true; @endphp
@extends('shared_layouts.student')

@section('title', 'Quiz Submission Summary')

@section('content')
    <!-- Resolve attempt submission status, violation logs, and answer totals -->
    @php
        $activeQuiz = $quiz ?? ($attempt->quiz ?? null);
        $subjectTitle = $activeQuiz->subject->subject_name ?? ($activeQuiz->title ?? 'Quiz');
        $maxViolations = (int) ($activeQuiz->max_violation ?? 3);
        $violationCount = (int) ($attempt->violation_count ?? 0);
        $isAutoSubmitted = ($attempt->status ?? '') === 'auto_submitted' || $violationCount >= $maxViolations;

        $totalQuestions = (int) ($activeQuiz->number_of_questions ?? ($totalQuestions ?? 0));
        $answeredCount = (int) ($answeredCount ?? 0);
        $scoreValue = (int) ($attempt->score ?? 0);
        $totalPoints = (int) ($attempt->total_points ?? 0);
        $submittedAt = isset($attempt->submitted_at)
            ? \Carbon\Carbon::parse($attempt->submitted_at)->format('g:i A')
            : now()->format('g:i A');

        $violationList = collect($violations ?? []);
        $dashboardUrl = Route::has('student.dashboard') ? route('student.dashboard') : url('/student/dashboard');
        $quizzesUrl = Route::has('student.quizzes') ? route('student.quizzes') : url('/student/quizzes');
        $profileUrl = Route::has('student.profile') ? route('student.profile') : $dashboardUrl;
    @endphp

    <!-- ============================================================ -->
    <!--     QUIZ AUTO-SUBMITTED (RESPONSIVE: MOBILE & DESKTOP)       -->
    <!-- ============================================================ -->

    <div class="max-w-2xl mx-auto space-y-6">

        <!-- Top Dark Green Header -->
        <div class="bg-[#11422d] text-white px-6 py-4 rounded-2xl shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-xs">
                    <span class="text-[#11422d] font-black text-base">Q</span>
                </div>
                <span class="text-xs font-bold tracking-wider uppercase text-white">DWCC QUIZGUARD</span>
            </div>
            <span
                class="text-xs {{ $isAutoSubmitted ? 'text-rose-300' : 'text-emerald-300' }} font-semibold uppercase tracking-wider">
                {{ $isAutoSubmitted ? 'Exam Terminated' : 'Exam Submitted' }}
            </span>
        </div>

        <!-- Main Alert Container -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm space-y-6 text-center">

            <!-- Red Alert Icon -->
            <div
                class="w-16 h-16 rounded-full mx-auto flex items-center justify-center text-2xl font-bold shadow-xs border-4 {{ $isAutoSubmitted ? 'bg-rose-50 border-rose-100 text-rose-500' : 'bg-emerald-50 border-emerald-100 text-emerald-600' }}">
                @if ($isAutoSubmitted)
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                @else
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                @endif
            </div>

            <!-- Alert Title & Subtitle -->
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    {{ $isAutoSubmitted ? 'Quiz submitted automatically' : 'Quiz submitted successfully' }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1.5 max-w-md mx-auto">
                    @if ($isAutoSubmitted)
                        You reached the limit of <strong>{{ $maxViolations }} violations</strong>, so your quiz attempt was
                        automatically submitted and locked by the proctoring engine.
                    @else
                        Your answers have been recorded and locked. Review your attempt summary and proctoring status below.
                    @endif
                </p>
            </div>

            <!-- Two Columns on Tablet/Desktop for Details & Logs -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left">
                <!-- 1. Summary Details Card -->
                <div
                    class="bg-slate-50 p-5 rounded-2xl border border-slate-200/70 text-xs divide-y divide-slate-200/60 flex flex-col justify-between">
                    <div>
                        <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2.5">Attempt Summary</h2>
                        <div class="py-2.5 flex items-center justify-between gap-2">
                            <span class="text-slate-500">Quiz / Subject</span>
                            <span class="font-bold text-slate-800 text-right">{{ $subjectTitle }}</span>
                        </div>
                        <div class="py-2.5 flex items-center justify-between">
                            <span class="text-slate-500">Score</span>
                            <span class="font-bold text-[#11422d]">{{ $scoreValue }} / {{ $totalPoints }}</span>
                        </div>
                        <div class="py-2.5 flex items-center justify-between">
                            <span class="text-slate-500">Questions answered</span>
                            <span class="font-bold text-slate-800">{{ $answeredCount }} of {{ $totalQuestions }}</span>
                        </div>
                        <div class="py-2.5 flex items-center justify-between">
                            <span class="text-slate-500">Total Violations</span>
                            <span class="font-bold {{ $violationCount > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                                {{ $violationCount }} of {{ $maxViolations }}
                            </span>
                        </div>
                    </div>
                    <div class="pt-2.5 flex items-center justify-between">
                        <span class="text-slate-500">Submitted at</span>
                        <span class="font-bold text-slate-800">{{ $submittedAt }}</span>
                    </div>
                </div>

                <!-- 2. Violation Log Audit Card -->
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200/70 text-left space-y-2.5">
                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Violation log</h2>

                    <!-- Render recorded violation log entries or clean status -->
                    @if ($violationList->isNotEmpty())
                        <div class="divide-y divide-slate-200/60 text-xs">
                            @foreach ($violationList as $log)
                                <div class="py-2 flex items-center justify-between gap-2">
                                    <span class="text-slate-500 font-medium">
                                        {{ $log->occurred_at ? \Carbon\Carbon::parse($log->occurred_at)->format('g:i A') : 'Recorded' }}
                                    </span>
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100/70 text-rose-700 border border-rose-200">
                                        {{ $log->violation_type }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-xl bg-emerald-50/70 border border-emerald-200/60 p-4 text-center">
                            <p class="text-xs font-semibold text-emerald-800">0 violations recorded</p>
                            <p class="text-[11px] text-emerald-700/80 mt-0.5">Clean proctoring session verified.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Render submitted answer review when enabled by teacher -->
            @if (($activeQuiz->allow_review ?? false) && isset($reviewAnswers) && $reviewAnswers->isNotEmpty())
                <div class="text-left space-y-3 pt-2">
                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Answer Review</h2>
                    <div class="divide-y divide-slate-200/70 bg-slate-50 rounded-2xl border border-slate-200/70 px-5">
                        @foreach ($reviewAnswers as $idx => $ans)
                            <div class="py-3.5 text-xs space-y-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-bold text-slate-900">Q{{ $idx + 1 }}.
                                        {{ $ans->question_text }}</span>
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase shrink-0 {{ $ans->is_correct ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $ans->is_correct ? 'Correct' : 'Wrong' }}
                                    </span>
                                </div>
                                <p class="text-slate-600">
                                    Your answer: <strong class="text-slate-900">{{ $ans->student_answer ?: '—' }}</strong>
                                    @if (!$ans->is_correct)
                                        &bull; Correct answer: <strong
                                            class="text-emerald-700">{{ $ans->correct_answers }}</strong>
                                    @endif
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <a href="{{ $profileUrl }}"
                    class="flex-1 py-3.5 bg-[#11422d] hover:bg-[#0d3423] text-white font-bold text-xs sm:text-sm rounded-xl transition shadow-xs">
                    {{ Route::has('student.profile') ? 'View Result & Profile' : 'Back to Dashboard' }}
                </a>
                <a href="{{ $quizzesUrl }}"
                    class="flex-1 py-3.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs sm:text-sm rounded-xl transition shadow-xs">
                    Back to Quizzes
                </a>
            </div>

        </div>

    </div>
@endsection
