@php $hideNav = true; @endphp
@extends('shared_layouts.student')

@section('title', $quiz->title ?? ($attempt->quiz->title ?? 'Exam Room'))

@section('content')
    <!-- Resolve active attempt, quiz metadata, questions, and answers -->
    @php
        $activeQuiz = $quiz ?? ($attempt->quiz ?? null);
        $attemptId = $attempt->attempt_id ?? null;
        $quizTitle = $activeQuiz->title ?? 'Exam';
        $subjectLabel = $activeQuiz->subject->subject_code ?? ($activeQuiz->subject->subject_name ?? 'Exam Mode');
        $maxViolations = (int) ($activeQuiz->max_violation ?? 3);
        $violationCount = (int) ($attempt->violation_count ?? 0);
        $disableBacktracking = (bool) ($activeQuiz->disable_backtracking ?? false);
        $questionList = collect($questions ?? []);
        $totalQuestions = $questionList->count();
        $savedAnswers = collect($answers ?? [])->mapWithKeys(fn($ans) => [$ans->question_id => $ans->student_answer]);
        $quizzesUrl = Route::has('student.quizzes') ? route('student.quizzes') : url('/student/quizzes');
        $remainingSec = (int) ($remainingSec ?? 1800);
    @endphp

    <!-- ============================================================ -->
    <!--     ACTIVE EXAM ROOM (RESPONSIVE: MOBILE & DESKTOP)          -->
    <!-- ============================================================ -->

    <!-- Top Dedicated Exam Header (Desktop & Mobile) -->
    <div class="bg-[#11422d] text-white px-6 py-4 rounded-2xl shadow-sm flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-white rounded-xl flex items-center justify-center shadow-xs">
                <span class="text-[#11422d] font-black text-lg">Q</span>
            </div>
            <div>
                <span class="text-xs font-black tracking-wider uppercase text-white block leading-tight">DWCC
                    QUIZGUARD</span>
                <p class="text-[10px] text-emerald-200 font-semibold uppercase tracking-wider">{{ $quizTitle }} &bull;
                    {{ $subjectLabel }}</p>
            </div>
        </div>

        <!-- Center Exam Indicators (Visible on tablet/desktop) -->
        <div class="hidden md:flex items-center gap-4">
            <div class="flex items-center gap-2 bg-white/10 px-3.5 py-1.5 rounded-xl border border-white/10 text-xs">
                <span class="text-emerald-200">Progress:</span>
                <span id="desktopProgressText" class="font-bold text-white">Question 1 of
                    {{ max(1, $totalQuestions) }}</span>
            </div>

            <div
                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-emerald-500/20 border border-emerald-400/30 text-emerald-100 font-bold text-xs">
                <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span id="desktopTimerText">--:-- left</span>
            </div>
        </div>

        <!-- Exit button triggers styled modal instead of browser popup -->
        <button type="button" onclick="openExitModal()"
            class="text-xs text-emerald-200 hover:text-white transition flex items-center gap-1.5 px-3 py-1.5 rounded-xl hover:bg-white/10 border border-white/10">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>Exit Quiz</span>
        </button>
    </div>

    <!-- Main Exam Body: 2-Column on Desktop, 1-Column on Mobile -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- LEFT COLUMN: Question & Answer (8 cols on desktop) -->
        <div class="lg:col-span-8 space-y-5">

            <!-- Mobile-Only Progress & Countdown Row -->
            <div class="flex md:hidden items-center justify-between">
                <div>
                    <h1 id="mobileProgressText" class="text-base font-extrabold text-slate-900 tracking-tight">Question 1 of
                        {{ max(1, $totalQuestions) }}</h1>
                    <p class="text-xs text-slate-500">{{ $quizTitle }}</p>
                </div>

                <!-- Mobile Timer Badge -->
                <div
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200/60 text-emerald-800 font-bold text-xs shadow-xs">
                    <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span id="mobileTimerText">--:-- left</span>
                </div>
            </div>

            <!-- Live Anti-Cheat Active Pill -->
            <div
                class="bg-emerald-50/80 border border-emerald-200/70 px-4 py-2.5 rounded-2xl flex items-center justify-between text-xs font-semibold text-emerald-800">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Anti-cheat monitoring is active</span>
                </div>
                <span id="saveStatusText" class="text-[11px] font-normal text-emerald-700 hidden sm:inline">All tabs and
                    answers are audited</span>
            </div>

            <!-- Render question cards dynamically by type and options -->
            @forelse($questionList as $index => $question)
                @php
                    $qId = $question->question_id;
                    $qType = strtolower($question->question_type ?? 'identification');
                    $qOptions = is_array($question->options)
                        ? $question->options
                        : json_decode($question->options ?? '', true) ?? [];
                    $currentValue = $savedAnswers->get($qId, '');
                @endphp
                <div class="question-card bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs space-y-5 {{ $index === 0 ? '' : 'hidden' }}"
                    data-index="{{ $index }}" data-question-id="{{ $qId }}">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <span
                            class="text-xs font-bold uppercase tracking-wider text-emerald-800 bg-emerald-50 px-3 py-1 rounded-full">
                            {{ str_replace('_', ' ', ucfirst($qType)) }} &bull; {{ $question->points ?? 1 }}
                            {{ ($question->points ?? 1) == 1 ? 'Point' : 'Points' }}
                        </span>
                        <span class="text-xs text-slate-400">Question {{ $index + 1 }}</span>
                    </div>

                    <!-- Question Text -->
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 leading-snug">
                        {{ $question->question_text }}
                    </h2>

                    <!-- Answer Field -->
                    <div class="space-y-2.5 pt-2">
                        @if (!empty($qOptions))
                            @foreach ($qOptions as $optKey => $optLabel)
                                @php $optionValue = is_string($optKey) ? $optKey : $optLabel; @endphp
                                <label
                                    class="flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition {{ $currentValue === (string) $optionValue ? 'border-[#11422d] bg-emerald-50/50' : 'border-slate-200 hover:border-slate-300 bg-slate-50/40' }}">
                                    <input type="radio" name="question_{{ $qId }}" value="{{ $optionValue }}"
                                        {{ $currentValue === (string) $optionValue ? 'checked' : '' }}
                                        onchange="handleAnswerChange({{ $qId }}, this.value, this)"
                                        class="w-4 h-4 text-[#11422d] focus:ring-emerald-600">
                                    <span class="text-sm font-medium text-slate-800">{{ $optLabel }}</span>
                                </label>
                            @endforeach
                        @else
                            <label for="studentAnswer_{{ $qId }}"
                                class="text-xs font-bold text-slate-700 block uppercase tracking-wider">
                                Your answer
                            </label>
                            <input type="text" id="studentAnswer_{{ $qId }}" value="{{ $currentValue }}"
                                oninput="handleTextAnswerChange({{ $qId }}, this.value)"
                                placeholder="Type your exact answer..."
                                class="w-full bg-slate-50/60 text-slate-900 text-sm sm:text-base font-medium px-4 py-3 rounded-xl border-2 border-slate-300 focus:bg-white focus:border-[#11422d] focus:ring-2 focus:ring-emerald-100 focus:outline-none transition">
                            <p class="text-xs text-slate-500">
                                Identification: type your exact answer in the box above. Spelling counts.
                            </p>
                        @endif

                        <p class="question-req-error hidden text-xs font-semibold text-rose-600 pt-1">
                            Please answer this question before moving to the next question.
                        </p>
                    </div>
                </div>
            @empty
                <div class="bg-white p-8 rounded-2xl border border-slate-200/80 text-center">
                    <p class="text-sm font-bold text-slate-700">No questions loaded for this attempt.</p>
                </div>
            @endforelse

            <!-- Question Navigation Buttons -->
            <div class="flex items-center justify-between gap-4 pt-2">
                @if (!$disableBacktracking)
                    <button type="button" id="prevQuestionBtn" onclick="prevQuestion()"
                        class="py-3 px-6 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-bold rounded-xl transition shadow-xs flex items-center gap-2 disabled:opacity-40">
                        <span>&larr;</span>
                        <span>Previous</span>
                    </button>
                @else
                    <div id="prevQuestionBtn" class="text-[11px] font-semibold text-slate-400">Backtracking disabled</div>
                @endif

                <button type="button" id="nextQuestionBtn" onclick="nextQuestion()"
                    class="py-3 px-6 bg-[#11422d] hover:bg-[#0d3423] text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-xs flex items-center gap-2">
                    <span id="nextBtnLabel">Next question</span>
                    <span>&rarr;</span>
                </button>
            </div>

        </div>

        <!-- RIGHT COLUMN: Anti-Cheat Checks & Question Palette -->
        <div class="lg:col-span-4 space-y-5">
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <p class="text-xs uppercase font-bold text-slate-500 tracking-wider">Anti-Cheat Checks</p>
                    <span id="violationCounterBadge"
                        class="inline-flex items-center gap-1 text-[11px] font-bold {{ $violationCount > 0 ? 'text-rose-700 bg-rose-50' : 'text-emerald-700 bg-emerald-50' }} px-2 py-0.5 rounded-full">
                        <span
                            class="w-1.5 h-1.5 rounded-full {{ $violationCount > 0 ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                        <span id="violationCounterText">{{ $violationCount }}
                            {{ $violationCount === 1 ? 'Violation' : 'Violations' }}</span>
                    </span>
                </div>

                <div class="space-y-2.5 text-xs text-slate-600">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50">
                        <span class="flex items-center gap-2"><span
                                class="text-emerald-600 font-bold">&check;</span><span>Tab switching</span></span>
                        <span class="text-emerald-700 font-semibold text-[11px]">Monitored</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50">
                        <span class="flex items-center gap-2"><span
                                class="text-emerald-600 font-bold">&check;</span><span>Window switching</span></span>
                        <span class="text-emerald-700 font-semibold text-[11px]">Monitored</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50">
                        <span class="flex items-center gap-2"><span
                                class="text-emerald-600 font-bold">&check;</span><span>Full-screen exit</span></span>
                        <span class="text-emerald-700 font-semibold text-[11px]">Monitored</span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Question Navigator</span>
                    <span id="answeredCountText" class="text-[11px] text-slate-400">0 / {{ $totalQuestions }}
                        Answered</span>
                </div>

                <div class="grid grid-cols-5 gap-2 text-center text-xs font-bold">
                    @foreach ($questionList as $index => $question)
                        <button type="button" onclick="showQuestion({{ $index }})"
                            data-palette-index="{{ $index }}" data-question-id="{{ $question->question_id }}"
                            class="palette-btn py-2 rounded-xl transition {{ $index === 0 ? 'bg-[#11422d] text-white ring-2 ring-emerald-600 shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            {{ $index + 1 }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- MODAL: FULLSCREEN REQUIRED ENTRY (Satisfies Chrome Gesture)  -->
    <!-- ============================================================ -->
    <div id="enterFullscreenModal"
        class="fixed inset-0 bg-slate-900/80 backdrop-blur-md flex items-center justify-center z-50 p-5">
        <div class="bg-white w-full max-w-sm rounded-3xl p-6 text-center shadow-2xl border border-slate-100 space-y-4">
            <div
                class="w-14 h-14 rounded-full bg-emerald-50 text-[#11422d] mx-auto flex items-center justify-center text-2xl font-black">
                🖥️
            </div>
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Full-Screen Mode Required</h3>
                <p class="text-xs text-slate-500 mt-1">QuizGuard requires full-screen mode to monitor academic integrity.
                </p>
            </div>
            <p id="sidePanelError"
                class="hidden text-xs font-semibold text-rose-600 bg-rose-50 border border-rose-200 p-3 rounded-xl"></p>
            <button type="button" onclick="grantFullscreenAndStart()"
                class="w-full py-3.5 bg-[#11422d] hover:bg-[#0d3423] text-white font-extrabold text-xs rounded-xl transition shadow-md">
                Enter Full-Screen &amp; Begin Exam
            </button>
        </div>
    </div>

    <!-- ============================================================ -->
    <!--  MODAL: CUSTOM EXIT QUIZ WARNING (Replaces Chrome popup)     -->
    <!-- ============================================================ -->
    <div id="exitQuizModal"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-5 hidden">
        <div class="bg-white w-full max-w-sm rounded-3xl p-6 text-center shadow-2xl border border-slate-100 space-y-4">
            <div
                class="w-12 h-12 rounded-full bg-amber-50 text-amber-600 mx-auto flex items-center justify-center text-xl font-bold">
                ⚠️
            </div>
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Exit Quiz Attempt?</h3>
                <p class="text-xs text-slate-500 mt-1">Your timer will continue running in the background. Are you sure you
                    want to leave?</p>
            </div>
            <div class="flex items-center gap-2 pt-2">
                <button type="button" onclick="closeExitModal()"
                    class="flex-1 py-3 bg-white border border-slate-200 text-slate-700 font-bold text-xs rounded-xl">
                    Stay in Quiz
                </button>
                <a href="{{ $quizzesUrl }}"
                    class="flex-1 py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl text-center">
                    Exit Anyway
                </a>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- MODAL: VIOLATION DETECTED WARNING                            -->
    <!-- ============================================================ -->
    <div id="violationModal"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-5 hidden">
        <div class="bg-white w-full max-w-sm rounded-3xl p-6 text-center shadow-2xl border border-slate-100 space-y-4">
            <div
                class="w-12 h-12 rounded-full bg-rose-50 text-rose-500 mx-auto flex items-center justify-center text-xl font-bold">
                ⚠️
            </div>
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Violation detected</h3>
                <p class="text-xs text-slate-500 mt-1">You left the quiz window. This has been recorded.</p>
            </div>
            <div
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                <span id="violationReasonText">Tab switching</span>
            </div>
            <div class="space-y-1.5 pt-1">
                <div id="violationMeterSegments" class="flex items-center gap-2 justify-center">
                    @for ($v = 1; $v <= $maxViolations; $v++)
                        <div class="w-12 h-2 rounded-full {{ $v <= $violationCount ? 'bg-rose-500' : 'bg-slate-100' }}">
                        </div>
                    @endfor
                </div>
                <p id="violationMeterText" class="text-xs font-bold text-rose-600">Violation {{ $violationCount }} of
                    {{ $maxViolations }} allowed</p>
            </div>
            <button type="button" onclick="closeViolationModal()"
                class="w-full py-3 bg-[#11422d] hover:bg-[#0d3423] text-white font-bold text-xs rounded-xl transition shadow-xs">
                Return to Quiz
            </button>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- MODAL: SUBMIT CONFIRMATION                                   -->
    <!-- ============================================================ -->
    <div id="submitModal"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-5 hidden">
        <div class="bg-white w-full max-w-sm rounded-3xl p-6 text-center shadow-2xl border border-slate-100 space-y-4">
            <div
                class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center text-xl font-bold">
                &check;
            </div>
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Submit your quiz?</h3>
                <p id="modalSummaryText" class="text-xs text-slate-500 mt-1">You can't change your answers after
                    submitting.</p>
            </div>
            <div class="flex items-center gap-2 pt-2">
                <button type="button" onclick="closeSubmitModal()"
                    class="flex-1 py-3 bg-white border border-slate-200 text-slate-700 font-bold text-xs rounded-xl">
                    Review answers
                </button>
                <form id="submitQuizForm"
                    action="{{ $attemptId ? url('/student/attempts/' . $attemptId . '/submit') : $quizzesUrl }}"
                    method="{{ $attemptId ? 'POST' : 'GET' }}" class="flex-1">
                    @if ($attemptId)
                        @csrf
                    @endif
                    <button type="submit"
                        class="w-full py-3 bg-[#11422d] hover:bg-[#0d3423] text-white font-bold text-xs rounded-xl">
                        Submit quiz
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Initialize exam state, timer, and question navigation
            const attemptId = @json($attemptId);
            const totalQuestions = {{ $totalQuestions }};
            const maxViolations = {{ $maxViolations }};
            const disableBacktracking = {{ $disableBacktracking ? 'true' : 'false' }};
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const answersMap = @json($savedAnswers);

            let currentIndex = 0;
            let remainingSeconds = Math.max(0, Math.floor(Number({{ $remainingSec }})));
            let violations = {{ $violationCount }};
            let timerInterval = null;
            let lastViolationId = null;
            let saveTimer = null;
            let isSubmitting = false;
            let isMonitoringActive = false;
            let lastViolationSentAt = 0;

            // Verify true fullscreen width and block active side panels
            function isFullScreenOccupied() {
                return window.innerWidth >= (window.screen.width * 0.92);
            }

            // Trigger full screen and activate proctor monitoring safely
            function grantFullscreenAndStart() {
                const errBox = document.getElementById('sidePanelError');
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen().then(() => {
                        setTimeout(() => {
                            if (!isFullScreenOccupied()) {
                                document.exitFullscreen?.().catch(() => {});
                                if (errBox) {
                                    errBox.textContent =
                                        'Side panel, split-screen, or overlay detected. Please close all side panels and external apps before starting.';
                                    errBox.classList.remove('hidden');
                                }
                                return;
                            }
                            if (errBox) errBox.classList.add('hidden');
                            document.getElementById('enterFullscreenModal').classList.add('hidden');
                            setTimeout(() => {
                                isMonitoringActive = true;
                            }, 1200);
                        }, 300);
                    }).catch(() => {
                        if (errBox) {
                            errBox.textContent =
                                'Unable to enter full-screen mode. Close external overlays and try again.';
                            errBox.classList.remove('hidden');
                        }
                    });
                } else {
                    document.getElementById('enterFullscreenModal').classList.add('hidden');
                    setTimeout(() => {
                        isMonitoringActive = true;
                    }, 1200);
                }
            }

            function openExitModal() {
                document.getElementById('exitQuizModal').classList.remove('hidden');
            }

            function closeExitModal() {
                document.getElementById('exitQuizModal').classList.add('hidden');
            }

            // Require answer on current question before advancing forward
            function isCurrentQuestionAnswered() {
                const activeCard = document.querySelector(`.question-card[data-index="${currentIndex}"]`);
                const qId = activeCard?.dataset.questionId;
                if (!qId) return true;
                const textInput = document.getElementById(`studentAnswer_${qId}`);
                if (textInput) {
                    answersMap[qId] = textInput.value;
                }
                const val = answersMap[qId];
                return val !== undefined && val !== null && String(val).trim() !== '';
            }

            function showQuestionError(show, message = 'Please answer this question before moving to the next question.') {
                const activeCard = document.querySelector(`.question-card[data-index="${currentIndex}"]`);
                const errEl = activeCard?.querySelector('.question-req-error');
                if (!errEl) return;
                errEl.textContent = message;
                errEl.classList.toggle('hidden', !show);
            }

            // Switch visible question card and update progress indicators
            function showQuestion(index) {
                if (totalQuestions === 0 || index < 0 || index >= totalQuestions) return;
                if (disableBacktracking && index < currentIndex) return;

                showQuestionError(false);
                currentIndex = index;

                document.querySelectorAll('.question-card').forEach(card => {
                    card.classList.toggle('hidden', Number(card.dataset.index) !== index);
                });

                const progressLabel = `Question ${index + 1} of ${totalQuestions}`;
                document.getElementById('desktopProgressText').textContent = progressLabel;
                document.getElementById('mobileProgressText').textContent = progressLabel;

                const prevBtn = document.getElementById('prevQuestionBtn');
                if (prevBtn && prevBtn.tagName === 'BUTTON') {
                    prevBtn.disabled = index === 0;
                }

                document.getElementById('nextBtnLabel').textContent = (index === totalQuestions - 1) ? 'Finish & Submit' :
                    'Next question';
                refreshPaletteUI();
            }

            function prevQuestion() {
                if (disableBacktracking) return;
                if (currentIndex > 0) showQuestion(currentIndex - 1);
            }

            function nextQuestion() {
                if (!isCurrentQuestionAnswered()) {
                    showQuestionError(true);
                    return;
                }
                showQuestionError(false);
                if (currentIndex < totalQuestions - 1) {
                    showQuestion(currentIndex + 1);
                } else {
                    openSubmitModal();
                }
            }

            function refreshPaletteUI() {
                let answeredCount = 0;

                document.querySelectorAll('.palette-btn').forEach(btn => {
                    const idx = Number(btn.dataset.paletteIndex);
                    const qId = btn.dataset.questionId;
                    const val = answersMap[qId];
                    const isAnswered = val !== undefined && val !== null && String(val).trim() !== '';
                    const isLockedPast = disableBacktracking && idx < currentIndex;

                    if (isAnswered) answeredCount++;

                    btn.disabled = isLockedPast;
                    btn.className = 'palette-btn py-2 rounded-xl transition ';

                    if (isLockedPast) {
                        btn.className += 'bg-slate-100 text-slate-400 opacity-50 cursor-not-allowed';
                    } else if (idx === currentIndex) {
                        btn.className += 'bg-[#11422d] text-white ring-2 ring-emerald-600 shadow-xs';
                    } else if (isAnswered) {
                        btn.className += 'bg-emerald-100/70 text-[#11422d] hover:bg-emerald-200';
                    } else {
                        btn.className += 'bg-slate-100 text-slate-600 hover:bg-slate-200';
                    }
                });

                document.getElementById('answeredCountText').textContent = `${answeredCount} / ${totalQuestions} Answered`;
                document.getElementById('modalSummaryText').textContent =
                    `You answered ${answeredCount} of ${totalQuestions} questions. You can't change your answers after submitting.`;
            }

            function persistAnswer(questionId, value) {
                answersMap[questionId] = value;
                if (String(value).trim() !== '') {
                    showQuestionError(false);
                }
                refreshPaletteUI();

                if (!attemptId) return;
                const statusEl = document.getElementById('saveStatusText');
                if (statusEl) statusEl.textContent = 'Saving answer...';

                fetch(`/student/attempts/${attemptId}/answer`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            question_id: questionId,
                            student_answer: value
                        }),
                    })
                    .then(() => {
                        if (statusEl) statusEl.textContent = 'Answer saved';
                    })
                    .catch(() => {
                        if (statusEl) statusEl.textContent = 'Save failed';
                    });
            }

            function handleAnswerChange(questionId, value) {
                persistAnswer(questionId, value);
            }

            function handleTextAnswerChange(questionId, value) {
                answersMap[questionId] = value;
                if (String(value).trim() !== '') {
                    showQuestionError(false);
                }
                refreshPaletteUI();
                clearTimeout(saveTimer);
                saveTimer = setTimeout(() => persistAnswer(questionId, value), 500);
            }

            // Format clean countdown timer without overlap or decimals
            function startExamTimer() {
                if (timerInterval) clearInterval(timerInterval);

                const updateDisplay = () => {
                    const pureSeconds = Math.max(0, Math.floor(remainingSeconds));
                    const mins = String(Math.floor(pureSeconds / 60)).padStart(2, '0');
                    const secs = String(pureSeconds % 60).padStart(2, '0');
                    const formatted = `${mins}:${secs} left`;

                    document.getElementById('desktopTimerText').textContent = formatted;
                    document.getElementById('mobileTimerText').textContent = formatted;
                };

                updateDisplay();
                timerInterval = setInterval(() => {
                    if (remainingSeconds <= 0) {
                        clearInterval(timerInterval);
                        if (!isSubmitting) {
                            isSubmitting = true;
                            document.getElementById('submitQuizForm').submit();
                        }
                        return;
                    }
                    remainingSeconds = Math.max(0, remainingSeconds - 1);
                    updateDisplay();
                }, 1000);
            }

            // Deduplicate rapid simultaneous violation triggers within 1.5 seconds
            function recordViolation(violationType) {
                if (isSubmitting || !isMonitoringActive) return;
                const nowMs = Date.now();
                if (nowMs - lastViolationSentAt < 1500) return;
                lastViolationSentAt = nowMs;

                const activeCard = document.querySelector(`.question-card[data-index="${currentIndex}"]`);
                const activeQuestionId = activeCard ? activeCard.dataset.questionId : null;

                if (attemptId) {
                    fetch(`/student/attempts/${attemptId}/violation`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                violation_type: violationType,
                                question_id: activeQuestionId
                            }),
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data?.deduplicated) return;
                            if (data?.violation_id) lastViolationId = data.violation_id;
                            if (data?.violation_count !== undefined) violations = data.violation_count;
                            if (data?.auto_submitted) {
                                isSubmitting = true;
                                isMonitoringActive = false;
                                window.location.href = `/student/attempts/${attemptId}/result`;
                                return;
                            }

                            openViolationModal(violationType);
                        })
                        .catch(() => {});
                }
            }

            // Auto submit only after exceeding allowed violation limit
            function openViolationModal(reason = 'Tab switching') {
                document.getElementById('violationReasonText').textContent = reason;
                document.getElementById('violationCounterText').textContent =
                    `${violations} ${violations === 1 ? 'Violation' : 'Violations'}`;
                document.getElementById('violationMeterText').textContent =
                    `Violation ${violations} of ${maxViolations} allowed`;

                const segments = document.getElementById('violationMeterSegments')?.children || [];
                Array.from(segments).forEach((seg, idx) => {
                    seg.className = `w-12 h-2 rounded-full ${idx < violations ? 'bg-rose-500' : 'bg-slate-100'}`;
                });

                document.getElementById('violationModal').classList.remove('hidden');

                if (violations > maxViolations && !isSubmitting) {
                    isSubmitting = true;
                    setTimeout(() => document.getElementById('submitQuizForm').submit(), 1200);
                }
            }

            function closeViolationModal() {
                if (attemptId && lastViolationId) {
                    fetch(`/student/attempts/${attemptId}/violation/${lastViolationId}/return`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                    }).catch(() => {});
                    lastViolationId = null;
                }
                document.getElementById('violationModal').classList.add('hidden');
                if (!document.fullscreenElement && document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen().catch(() => {});
                }
            }

            // Flush pending identification answer before opening submit modal
            function openSubmitModal() {
                const activeCard = document.querySelector(`.question-card[data-index="${currentIndex}"]`);
                const activeQId = activeCard?.dataset.questionId;
                const textInput = activeQId ? document.getElementById(`studentAnswer_${activeQId}`) : null;
                if (textInput) {
                    clearTimeout(saveTimer);
                    persistAnswer(activeQId, textInput.value);
                }

                // Jump to first unanswered question and block incomplete submission
                const cards = Array.from(document.querySelectorAll('.question-card'));
                const firstUnanswered = cards.find(card => {
                    const qId = card.dataset.questionId;
                    const val = answersMap[qId];
                    return val === undefined || val === null || String(val).trim() === '';
                });

                if (firstUnanswered) {
                    const targetIdx = Number(firstUnanswered.dataset.index);
                    showQuestion(targetIdx);
                    showQuestionError(true, 'Please answer all questions before submitting your quiz.');
                    return;
                }

                refreshPaletteUI();
                document.getElementById('submitModal').classList.remove('hidden');
            }

            function closeSubmitModal() {
                document.getElementById('submitModal').classList.add('hidden');
            }

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) recordViolation('Tab switching');
            });

            window.addEventListener('blur', () => {
                if (!document.hidden) recordViolation('Window switching');
            });

            document.addEventListener('fullscreenchange', () => {
                if (!document.fullscreenElement) recordViolation('Full-screen exit');
            });

            // Flag side panels or split screens opened during the exam
            window.addEventListener('resize', () => {
                if (isMonitoringActive && !isFullScreenOccupied()) {
                    recordViolation('Side panel / Split-screen');
                }
            });

            document.getElementById('submitQuizForm')?.addEventListener('submit', () => {
                isSubmitting = true;
            });

            refreshPaletteUI();
            showQuestion(0);
            startExamTimer();
        </script>
    @endpush
@endsection
