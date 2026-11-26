@extends('shared_layouts.student')

@section('title', 'Student Profile')

@section('content')
    <!-- Resolve authenticated student details and quiz history metrics -->
    @php
        $loggedStudent = auth()->user();
        $fullName = $loggedStudent
            ? trim(
                $loggedStudent->first_name .
                    ($loggedStudent->middle_name ? ' ' . $loggedStudent->middle_name : '') .
                    ' ' .
                    $loggedStudent->last_name,
            )
            : 'Student';
        $initials = $loggedStudent
            ? strtoupper(substr($loggedStudent->first_name, 0, 1) . substr($loggedStudent->last_name, 0, 1))
            : 'ST';
        $schoolIdNum = $loggedStudent->school_id_num ?? 'N/A';
        $department = $loggedStudent->department ?? 'Student';
        $dwccEmail = $loggedStudent->dwcc_email ?? '';

        $historyAttempts = collect($attempts ?? ($recentAttempts ?? []));
        $quizzesTaken = $historyAttempts->count();

        $getTransmuted = function ($attempt) {
            return \App\Services\TransmutationService::transmute(
                (int) ($attempt->score ?? 0),
                (int) ($attempt->total_points ?? 0),
            );
        };

        $isAttemptPassed = function ($attempt) use ($getTransmuted) {
            return $getTransmuted($attempt) >= 75;
        };

        $passedCount = $historyAttempts->filter(fn($a) => $isAttemptPassed($a))->count();
        $passRate = $quizzesTaken > 0 ? round(($passedCount / $quizzesTaken) * 100) : 0;

        // Load reviewable student answers directly if missing from controller
        $resolvedReviewMap = $reviewMap ?? [];
        $needDirectLoad = empty($resolvedReviewMap) && $historyAttempts->isNotEmpty();

        if ($needDirectLoad) {
            $reviewIds = $historyAttempts->filter(fn($a) => $a->quiz && $a->quiz->allow_review)->pluck('attempt_id');

            if ($reviewIds->isNotEmpty()) {
                $loadedAnswers = \Illuminate\Support\Facades\DB::table('student_answers')
                    ->join('questions', 'student_answers.question_id', '=', 'questions.question_id')
                    ->whereIn('student_answers.attempt_id', $reviewIds)
                    ->select(
                        'student_answers.*',
                        'questions.question_text',
                        'questions.correct_answers',
                        'questions.options',
                        'questions.points',
                    )
                    ->orderBy('student_answers.answer_id')
                    ->get()
                    ->groupBy('attempt_id');

                foreach ($loadedAnswers as $attId => $rows) {
                    $resolvedReviewMap[$attId] = $rows->values()->all();
                }
            }
        }

        $historyModalData = $historyAttempts
            ->map(function ($attempt) use ($isAttemptPassed, $getTransmuted, $resolvedReviewMap) {
                $rawAnswers =
                    $resolvedReviewMap[$attempt->attempt_id] ?? ($attempt->review_answers ?? ($attempt->answers ?? []));

                $formattedAnswers = collect($rawAnswers)
                    ->map(function ($ans) {
                        $arr = (array) $ans;
                        $opts = $arr['options'] ?? null;
                        $arr['formatted_student_answer'] = \App\Services\TransmutationService::formatOptionLabel(
                            $arr['student_answer'] ?? null,
                            $opts,
                        );
                        $arr['formatted_correct_answer'] = \App\Services\TransmutationService::formatOptionLabel(
                            $arr['correct_answers'] ?? null,
                            $opts,
                        );
                        return $arr;
                    })
                    ->values()
                    ->all();

                $transmuted = $getTransmuted($attempt);

                return [
                    'attempt_id' => $attempt->attempt_id,
                    'pdf_url' => url('/student/attempts/' . $attempt->attempt_id . '/pdf'),
                    'email_pdf_url' => url('/student/attempts/' . $attempt->attempt_id . '/email-pdf'),
                    'title' => $attempt->quiz->title ?? 'Quiz Attempt',
                    'subject' => $attempt->quiz->subject->subject_code ?? '',
                    'score' => $attempt->score . '/' . $attempt->total_points . ' (' . $transmuted . '%)',
                    'transmuted' => $transmuted,
                    'passed' => $isAttemptPassed($attempt),
                    'violations' => (int) ($attempt->violation_count ?? 0),
                    'max_violation' => (int) ($attempt->quiz->max_violation ?? 3),
                    'submitted_at' => $attempt->submitted_at
                        ? \Carbon\Carbon::parse($attempt->submitted_at)->format('M d, Y g:i A')
                        : '-',
                    'allow_review' => (bool) ($attempt->quiz->allow_review ?? false),
                    'answers' => $formattedAnswers,
                ];
            })
            ->values();

        $isPrompted = (bool) session('prompt_password_change');
        $shouldOpenPasswordModal =
            $isPrompted || $errors->hasAny(['current_password', 'new_password', 'new_password_confirmation']);
    @endphp

    <!-- Top Dark Green Curved Banner -->
    <div class="bg-[#11422d] text-white p-6 sm:p-8 rounded-2xl sm:rounded-3xl shadow-sm mb-6">
        <div class="flex items-center gap-2.5 mb-4 md:hidden">
            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-xs">
                <span class="text-[#11422d] font-black text-base">Q</span>
            </div>
            <span class="text-xs font-bold tracking-wider uppercase text-white">DWCC QUIZGUARD</span>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">Profile</h1>
                <p class="text-xs sm:text-sm text-emerald-100/80 mt-1">Your account and quiz performance history</p>
            </div>
            <div class="hidden sm:flex items-center gap-4 bg-white/10 px-5 py-3 rounded-2xl border border-white/10 text-xs">
                <div class="text-center">
                    <span class="text-lg font-bold text-white block">{{ $quizzesTaken }}</span>
                    <span class="text-[10px] text-emerald-200">Quizzes Taken</span>
                </div>
                <div class="w-px h-8 bg-white/20"></div>
                <div class="text-center">
                    <span class="text-lg font-bold text-emerald-300 block">{{ $passRate }}%</span>
                    <span class="text-[10px] text-emerald-200">Pass Rate</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Body -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT COLUMN -->
        <div class="lg:col-span-1 space-y-5">
            <div
                class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs text-center sm:text-left flex flex-col sm:flex-row lg:flex-col items-center sm:items-start lg:items-center gap-4">
                <div
                    class="w-16 h-16 rounded-full bg-[#11422d] text-white font-extrabold text-xl flex items-center justify-center shrink-0 shadow-md ring-4 ring-emerald-50">
                    {{ $initials }}
                </div>
                <div class="text-center sm:text-left lg:text-center min-w-0">
                    <h2 class="text-base font-bold text-slate-900 leading-snug">{{ $fullName }}</h2>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Student ID: {{ $schoolIdNum }}</p>
                    <p class="text-xs font-semibold text-emerald-800 mt-0.5">{{ $department }}</p>
                    <p class="text-xs text-slate-400 mt-1 truncate">{{ $dwccEmail }}</p>
                </div>
            </div>

            <div
                class="bg-white rounded-2xl border border-slate-200/80 shadow-xs divide-y divide-slate-100 overflow-hidden text-xs text-slate-700 font-medium">
                <button type="button" onclick="togglePrivacyModal(true)"
                    class="w-full px-5 py-3.5 flex items-center justify-between hover:bg-slate-50 transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Data Privacy Notice</span>
                    </div>
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                <button type="button" onclick="togglePasswordModal(true)"
                    class="w-full px-5 py-3.5 flex items-center justify-between hover:bg-slate-50 transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        <span>Change Password</span>
                    </div>
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                <a href="{{ url('/student/quizzes') }}"
                    class="px-5 py-3.5 flex items-center justify-between hover:bg-slate-50 transition">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Browse My Quizzes</span>
                    </div>
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <div>
                <form action="{{ url('/logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="w-full py-3 bg-white border border-rose-200 hover:bg-rose-50 text-rose-600 font-bold text-xs rounded-2xl transition shadow-xs flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Log out</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- RIGHT COLUMN: Quiz History Only -->
        <div class="lg:col-span-2">
            <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 tracking-wide uppercase">Quiz History</h3>
                        <p class="text-xs text-slate-400">Past quiz scores and recorded violation metrics</p>
                    </div>
                    <a href="{{ url('/student/quizzes') }}"
                        class="text-xs font-semibold text-emerald-800 hover:text-emerald-950 transition">My Quizzes</a>
                </div>

                @if ($historyAttempts->isNotEmpty())
                    <div class="divide-y divide-slate-100 text-xs">
                        @foreach ($historyAttempts as $attempt)
                            @php
                                $transmuted = $getTransmuted($attempt);
                                $passed = $isAttemptPassed($attempt);
                                $violations = (int) ($attempt->violation_count ?? 0);
                                $allowReview = (bool) ($attempt->quiz->allow_review ?? false);
                                $submittedAt = $attempt->submitted_at
                                    ? \Carbon\Carbon::parse($attempt->submitted_at)->format('M d, Y')
                                    : 'Submitted';
                            @endphp

                            @if ($allowReview)
                                <button type="button" onclick="openHistoryReviewModal({{ $loop->index }})"
                                    class="w-full py-4 flex items-center justify-between gap-4 text-left hover:bg-slate-50/70 px-2 rounded-xl transition">
                                    <div class="min-w-0">
                                        <span class="font-bold text-slate-900 text-sm block">
                                            {{ $attempt->quiz->title ?? 'Quiz Attempt' }}
                                        </span>
                                        <p class="text-[11px] text-slate-400 mt-0.5">
                                            {{ $submittedAt }} &bull;
                                            <span
                                                class="{{ $violations === 0 ? 'text-emerald-700' : 'text-amber-700' }} font-medium">
                                                {{ $violations }} {{ $violations === 1 ? 'violation' : 'violations' }}
                                            </span>
                                            &bull; <span class="text-[#11422d] font-semibold">Click to view details</span>
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span
                                            class="font-black text-slate-900 text-base">{{ $attempt->score }}/{{ $attempt->total_points }}
                                            ({{ $transmuted }}%)
                                        </span>
                                        <span
                                            class="px-3 py-1 rounded-full text-[11px] font-bold uppercase border {{ $passed ? 'bg-emerald-50 text-emerald-700 border-emerald-200/60' : 'bg-rose-50 text-rose-700 border-rose-200/60' }}">
                                            {{ $passed ? 'Passed' : 'Failed' }}
                                        </span>
                                    </div>
                                </button>
                            @else
                                <div
                                    class="w-full py-4 flex items-center justify-between gap-4 text-left px-2 rounded-xl opacity-75 cursor-not-allowed">
                                    <div class="min-w-0">
                                        <span class="font-bold text-slate-900 text-sm block">
                                            {{ $attempt->quiz->title ?? 'Quiz Attempt' }}
                                        </span>
                                        <p class="text-[11px] text-slate-400 mt-0.5">
                                            {{ $submittedAt }} &bull;
                                            <span
                                                class="{{ $violations === 0 ? 'text-emerald-700' : 'text-amber-700' }} font-medium">
                                                {{ $violations }} {{ $violations === 1 ? 'violation' : 'violations' }}
                                            </span>
                                            &bull; <span class="text-slate-400 font-semibold">Review disabled by
                                                teacher</span>
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span
                                            class="font-black text-slate-900 text-base">{{ $attempt->score }}/{{ $attempt->total_points }}
                                            ({{ $transmuted }}%)</span>
                                        <span
                                            class="px-3 py-1 rounded-full text-[11px] font-bold uppercase border {{ $passed ? 'bg-emerald-50 text-emerald-700 border-emerald-200/60' : 'bg-rose-50 text-rose-700 border-rose-200/60' }}">
                                            {{ $passed ? 'Passed' : 'Failed' }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl bg-slate-50 border border-dashed border-slate-200 p-8 text-center">
                        <p class="text-sm font-bold text-slate-700">No completed quiz attempts yet</p>
                        <p class="text-xs text-slate-400 mt-1">Your submitted quiz scores and violation summaries will
                            appear here.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Render attempt summary and answer review modal popup -->
    <div id="historyReviewModal"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-5 hidden">
        <div
            class="bg-white w-full max-w-2xl rounded-3xl p-6 shadow-2xl border border-slate-100 space-y-4 max-h-[85vh] flex flex-col">
            <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 id="reviewModalTitle" class="text-lg font-black text-slate-900">Quiz Details</h3>
                    <p id="reviewModalDate" class="text-xs text-slate-400 mt-0.5"></p>
                </div>
                <button type="button" onclick="closeHistoryReviewModal()"
                    class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
            </div>

            <div id="reviewModalSummary"
                class="rounded-2xl border border-slate-200/80 bg-slate-50/70 px-5 py-3.5 grid grid-cols-3 gap-3 text-xs">
            </div>

            <div id="reviewModalBody" class="overflow-y-auto flex-1 space-y-3 text-xs pr-1"></div>

            <div id="reviewModalFooter" class="pt-2 border-t border-slate-100 flex flex-wrap items-center gap-2">
                <a id="downloadReviewPdfLink" href="#"
                    class="hidden px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-800 font-bold text-xs rounded-xl transition">
                    Download PDF
                </a>
                <form id="emailReviewPdfForm" action="#" method="POST" class="hidden">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2.5 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 text-[#11422d] font-bold text-xs rounded-xl transition">
                        Email PDF
                    </button>
                </form>
                <button type="button" onclick="closeHistoryReviewModal()"
                    class="flex-1 min-w-[120px] py-2.5 bg-[#11422d] hover:bg-[#0d3423] text-white font-bold text-xs rounded-xl transition">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Data Privacy Modal -->
    <div id="privacyModal"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-5 hidden">
        <div class="bg-white w-full max-w-md rounded-3xl p-6 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-extrabold text-slate-900">Data Privacy Notice</h3>
                <button type="button" onclick="togglePrivacyModal(false)"
                    class="text-slate-400 hover:text-slate-700 text-lg font-bold">&times;</button>
            </div>
            <p class="text-xs leading-relaxed text-slate-600">
                Your quiz activity is recorded strictly for academic evaluation in compliance with the <strong>Data Privacy
                    Act of 2012 (RA 10173)</strong>.
            </p>
            <button type="button" onclick="togglePrivacyModal(false)"
                class="w-full py-2.5 bg-[#11422d] hover:bg-[#0d3423] text-white text-xs font-bold rounded-xl transition">
                Close
            </button>
        </div>
    </div>

    <!-- Render change password modal matching data privacy modal style -->
    <div id="changePasswordModal"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-5 {{ $shouldOpenPasswordModal ? '' : 'hidden' }}">
        <div class="bg-white w-full max-w-md rounded-3xl p-6 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Change Password</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Copy &amp; paste is disabled on password fields.</p>
                </div>
                <button type="button" onclick="togglePasswordModal(false)"
                    class="text-slate-400 hover:text-slate-700 text-lg font-bold">&times;</button>
            </div>

            @if ($isPrompted)
                <div
                    class="rounded-xl bg-amber-50 border border-amber-200 px-3.5 py-2.5 text-[11px] text-amber-900 font-semibold">
                    Your email is verified. Set your personal password below.
                </div>
            @endif

            <form id="studentPasswordForm" action="{{ url('/student/profile/password') }}" method="POST" novalidate
                class="space-y-3.5">
                @csrf

                <div>
                    <label for="student_current_password" class="block text-xs font-semibold text-slate-700 mb-1">Current
                        password</label>
                    <input type="password" id="student_current_password" name="current_password"
                        value="{{ $isPrompted ? '••••••••' : '' }}" {{ $isPrompted ? 'readonly' : '' }}
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-[#11422d] focus:outline-none {{ $isPrompted ? 'bg-slate-50 text-slate-500 cursor-not-allowed' : '' }}">
                    <p id="student_current_error"
                        class="text-[11px] text-rose-600 font-semibold mt-1 {{ $errors->has('current_password') ? '' : 'hidden' }}">
                        {{ $errors->first('current_password') }}
                    </p>
                </div>

                <div>
                    <label for="student_new_password" class="block text-xs font-semibold text-slate-700 mb-1">New
                        password</label>
                    <div class="relative">
                        <input type="password" id="student_new_password" name="new_password"
                            oninput="evaluateStudentPassword()"
                            class="no-clipboard w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-[#11422d] focus:outline-none">
                        <button type="button" onclick="togglePasswordVisibility('student_new_password', this)"
                            class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-700"
                            title="Show/Hide password">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>

                    <!-- Render live password requirement indicators and validation errors -->
                    <div class="grid grid-cols-2 gap-1.5 mt-2 text-[11px] font-medium">
                        <span id="stu_req_len" class="text-slate-400">&bull; Min 8 characters</span>
                        <span id="stu_req_upper" class="text-slate-400">&bull; 1 capital letter (A-Z)</span>
                        <span id="stu_req_num" class="text-slate-400">&bull; 1 number (0-9)</span>
                        <span id="stu_req_sym" class="text-slate-400 col-span-2">&bull; 1 special
                            (!@#$%^&amp;()_+?.,)</span>
                    </div>
                    <p id="student_new_error"
                        class="text-[11px] text-rose-600 font-semibold mt-1 {{ $errors->has('new_password') ? '' : 'hidden' }}">
                        {{ $errors->first('new_password') }}
                    </p>
                </div>

                <div>
                    <label for="student_confirm_password" class="block text-xs font-semibold text-slate-700 mb-1">Confirm
                        new password</label>
                    <div class="relative">
                        <input type="password" id="student_confirm_password" name="new_password_confirmation"
                            oninput="evaluateStudentPassword()"
                            class="no-clipboard w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-[#11422d] focus:outline-none">
                        <button type="button" onclick="togglePasswordVisibility('student_confirm_password', this)"
                            class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-700"
                            title="Show/Hide password">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    <p id="student_confirm_error" class="text-[11px] text-rose-600 font-semibold mt-1 hidden"></p>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="button" onclick="togglePasswordModal(false)"
                        class="flex-1 py-2.5 bg-white border border-slate-200 text-slate-700 font-bold text-xs rounded-xl">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-[#11422d] hover:bg-[#0d3423] text-white font-bold text-xs rounded-xl transition">
                        Save password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const historyModalItems = @json($historyModalData);

        function escapeHtml(str) {
            return String(str ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        // Open modal displaying quiz summary and answer review cards
        function openHistoryReviewModal(index) {
            const item = historyModalItems[index];
            if (!item) return;

            document.getElementById('reviewModalTitle').textContent = item.title;
            document.getElementById('reviewModalDate').textContent = item.submitted_at;

            const statusBadge = item.passed ?
                '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">PASSED</span>' :
                '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-100 text-rose-700">FAILED</span>';

            document.getElementById('reviewModalSummary').innerHTML = `
                <div>
                    <span class="text-slate-400 block text-[11px]">Score</span>
                    <strong class="text-sm font-black text-slate-900">${escapeHtml(item.score)}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Violations</span>
                    <strong class="text-sm font-black text-slate-900">${Number(item.violations)} (Max ${Number(item.max_violation)} allowed)</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px] mb-0.5">Status</span>
                    ${statusBadge}
                </div>
            `;

            const pdfLink = document.getElementById('downloadReviewPdfLink');
            const emailForm = document.getElementById('emailReviewPdfForm');
            const canExport = Boolean(item.allow_review && item.answers && item.answers.length > 0);

            if (canExport) {
                pdfLink.href = item.pdf_url;
                pdfLink.classList.remove('hidden');
                emailForm.action = item.email_pdf_url;
                emailForm.classList.remove('hidden');
            } else {
                pdfLink.classList.add('hidden');
                emailForm.classList.add('hidden');
            }

            const body = document.getElementById('reviewModalBody');
            if (!item.allow_review) {
                body.innerHTML = `
                    <div class="rounded-2xl bg-slate-50 border border-slate-200 p-6 text-center space-y-1">
                        <p class="font-bold text-slate-700 text-sm">Answer review is disabled</p>
                        <p class="text-slate-500">Your teacher has not enabled post-quiz answer review for this quiz.</p>
                    </div>`;
            } else if (!item.answers || item.answers.length === 0) {
                body.innerHTML =
                    `<p class="text-slate-500 text-center py-6 font-semibold">No answered questions recorded for this attempt.</p>`;
            } else {
                body.innerHTML = `
                    <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-800">Answer Review</p>
                    <div class="divide-y divide-slate-200/70 rounded-2xl border border-slate-200/80 bg-white px-4">
                        ${item.answers.map((ans, i) => {
                            const yourAns = ans.formatted_student_answer || ans.student_answer || '—';
                            const rightAns = ans.formatted_correct_answer || ans.correct_answers || '—';
                            return `
                                    <div class="py-3.5 space-y-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="font-bold text-slate-900">Q${i + 1}. ${escapeHtml(ans.question_text)}</span>
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase shrink-0 ${Number(ans.is_correct) === 1 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'}">
                                                ${Number(ans.is_correct) === 1 ? 'CORRECT' : 'WRONG'}
                                            </span>
                                        </div>
                                        <p class="text-slate-600">
                                            Your answer: <strong class="text-slate-900">${escapeHtml(yourAns)}</strong>
                                            ${Number(ans.is_correct) !== 1 ? ` &bull; Correct answer: <strong class="text-emerald-700">${escapeHtml(rightAns)}</strong>` : ''}
                                        </p>
                                    </div>
                                `;
                        }).join('')}
                    </div>
                `;
            }

            document.getElementById('historyReviewModal').classList.remove('hidden');
        }

        function closeHistoryReviewModal() {
            document.getElementById('historyReviewModal').classList.add('hidden');
        }

        function togglePrivacyModal(show) {
            const modal = document.getElementById('privacyModal');
            if (modal) modal.classList.toggle('hidden', !show);
        }

        function togglePasswordModal(show) {
            const modal = document.getElementById('changePasswordModal');
            if (modal) modal.classList.toggle('hidden', !show);
        }

        // Toggle password input visibility between hidden and plain
        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            btn.classList.toggle('text-[#11422d]', isHidden);
        }

        // Block copy, cut, and paste on password inputs
        document.querySelectorAll('.no-clipboard').forEach(el => {
            ['copy', 'cut', 'paste', 'drop'].forEach(evt => {
                el.addEventListener(evt, e => e.preventDefault());
            });
        });

        // Evaluate password rules using whitelisted special characters
        function evaluateStudentPassword() {
            const pw = document.getElementById('student_new_password').value;
            const confirmPw = document.getElementById('student_confirm_password').value;
            const allowedSpecialRegex = /[!@#$%^&()_+?.,]/;
            const onlyAllowedCharsRegex = /^[A-Za-z0-9!@#$%^&()_+?.,]+$/;

            const rules = [{
                    id: 'stu_req_len',
                    ok: pw.length >= 8,
                    label: 'at least 8 characters'
                },
                {
                    id: 'stu_req_upper',
                    ok: /[A-Z]/.test(pw),
                    label: '1 capital letter'
                },
                {
                    id: 'stu_req_num',
                    ok: /[0-9]/.test(pw),
                    label: '1 number'
                },
                {
                    id: 'stu_req_sym',
                    ok: allowedSpecialRegex.test(pw),
                    label: '1 allowed special character (!@#$%^&()_+?.,)'
                },
            ];

            const missing = [];
            rules.forEach(r => {
                const el = document.getElementById(r.id);
                if (el) {
                    el.className = (r.ok ? 'text-emerald-700 font-semibold' : 'text-slate-400') + (r.id ===
                        'stu_req_sym' ? ' col-span-2' : '');
                }
                if (!r.ok) missing.push(r.label);
            });

            if (pw !== '' && !onlyAllowedCharsRegex.test(pw)) {
                missing.push('remove unsupported symbols or spaces');
            }

            const confirmErr = document.getElementById('student_confirm_error');
            if (confirmPw && pw !== confirmPw) {
                confirmErr.textContent = 'Passwords do not match.';
                confirmErr.classList.remove('hidden');
            } else {
                confirmErr.classList.add('hidden');
            }

            return missing;
        }

        // Validate password complexity and matching fields before submission
        document.getElementById('studentPasswordForm')?.addEventListener('submit', function(e) {
            const current = document.getElementById('student_current_password').value;
            const pw = document.getElementById('student_new_password').value;
            const confirmPw = document.getElementById('student_confirm_password').value;

            const currentErr = document.getElementById('student_current_error');
            const newErr = document.getElementById('student_new_error');
            const confirmErr = document.getElementById('student_confirm_error');

            let hasError = false;

            if (!current.trim()) {
                currentErr.textContent = 'Please enter your current password.';
                currentErr.classList.remove('hidden');
                hasError = true;
            } else {
                currentErr.classList.add('hidden');
            }

            const missing = evaluateStudentPassword();
            if (missing.length > 0) {
                newErr.textContent = 'Missing requirement(s): ' + missing.join(', ') + '.';
                newErr.classList.remove('hidden');
                hasError = true;
            } else {
                newErr.classList.add('hidden');
            }

            if (!confirmPw) {
                confirmErr.textContent = 'Please confirm your new password.';
                confirmErr.classList.remove('hidden');
                hasError = true;
            } else if (pw !== confirmPw) {
                confirmErr.textContent = 'Passwords do not match.';
                confirmErr.classList.remove('hidden');
                hasError = true;
            } else {
                confirmErr.classList.add('hidden');
            }

            if (hasError) e.preventDefault();
        });
    </script>
@endsection
