@extends('shared_layouts.student')

@section('title', 'Quiz Rules & Instructions')

@section('content')
    <!-- Resolve quiz metrics and safe navigation route URLs -->
    @php
        $quizTitle = $quiz->title ?? 'Quiz Room';
        $subjectName = $quiz->subject->subject_name ?? '';
        $questionCount = $quiz->number_of_questions ?? 1;
        $timeLimitMin = $quiz->time_limit_min ?? 30;
        $attemptsAllowed = $quiz->attempts_allowed ?? 1;
        $passingScore = $quiz->passing_score ?? 75;
        $maxViolations = $quiz->max_violation ?? 3;
        $quizId = $quiz->quiz_id ?? null;
        $quizzesUrl = Route::has('student.quizzes') ? route('student.quizzes') : url('/student/quizzes');
    @endphp

    <!-- Top Dark Green Banner -->
    <div class="bg-[#11422d] text-white p-6 sm:p-8 rounded-2xl sm:rounded-3xl shadow-sm mb-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-xs">
                    <span class="text-[#11422d] font-black text-base">Q</span>
                </div>
                <span class="text-xs font-bold tracking-wider uppercase text-white">DWCC QUIZGUARD</span>
            </div>

            <a href="{{ $quizzesUrl }}"
                class="text-xs text-emerald-200 hover:text-white transition flex items-center gap-1 font-medium">
                <span>&larr;</span> Back to Quizzes
            </a>
        </div>

        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">{{ $quizTitle }}</h1>
            <p class="text-xs sm:text-sm text-emerald-100/80 mt-1">
                {{ $subjectName ? $subjectName . ' · ' : '' }}Read the rules and requirements before you start
            </p>
        </div>
    </div>

    <!-- Main Body -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- LEFT COLUMN: Quiz Metrics & Anti-Cheat Rules -->
        <div class="lg:col-span-7 space-y-6">
            <div
                class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900">{{ $questionCount }}</p>
                    <p class="text-xs font-semibold text-slate-500 mt-1">
                        {{ $questionCount == 1 ? 'Question' : 'Questions' }}</p>
                </div>
                <div>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900">
                        {{ $timeLimitMin ?: '&infin;' }}
                        @if ($timeLimitMin)
                            <span class="text-base font-bold text-slate-600">min</span>
                        @endif
                    </p>
                    <p class="text-xs font-semibold text-slate-500 mt-1">Time limit</p>
                </div>
                <div class="pt-2 sm:pt-0 border-t sm:border-t-0 sm:border-l border-slate-100 sm:pl-4">
                    <p class="text-2xl sm:text-3xl font-black text-slate-900">{{ $attemptsAllowed }}</p>
                    <p class="text-xs font-semibold text-slate-500 mt-1">
                        {{ $attemptsAllowed == 1 ? 'Attempt allowed' : 'Attempts allowed' }}</p>
                </div>
                <div class="pt-2 sm:pt-0 border-t sm:border-t-0 sm:border-l border-slate-100 sm:pl-4">
                    <p class="text-2xl sm:text-3xl font-black text-[#11422d]">
                        {{ $passingScore }} <span class="text-base font-bold text-emerald-800">{{ $passingScore == 1 ? 'pt' : 'pts' }}</span>
                    </p>
                    <p class="text-xs font-semibold text-slate-500 mt-1">Passing score</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center gap-2.5 text-slate-900 font-bold text-sm sm:text-base">
                    <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <span>Anti-Cheat Proctoring Rules</span>
                </div>

                <ul class="space-y-3 text-xs sm:text-sm text-slate-700">
                    <li class="flex items-start gap-3">
                        <span
                            class="w-6 h-6 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">&bull;</span>
                        <div>
                            <strong class="text-slate-900 block">Stay on the quiz screen</strong>
                            <span class="text-slate-500 text-xs">Do not click outside of the quiz interface or minimize the
                                window.</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span
                            class="w-6 h-6 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">&bull;</span>
                        <div>
                            <strong class="text-slate-900 block">Do not switch tabs or windows</strong>
                            <span class="text-slate-500 text-xs">Switching to another browser tab will trigger an immediate
                                violation notice.</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span
                            class="w-6 h-6 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">&bull;</span>
                        <div>
                            <strong class="text-slate-900 block">Keep full-screen mode on</strong>
                            <span class="text-slate-500 text-xs">Exiting full-screen mode is strictly monitored and
                                flagged.</span>
                        </div>
                    </li>
                </ul>

                <div
                    class="pt-3 border-t border-slate-100 flex items-center gap-2 text-rose-700 bg-rose-50/60 p-3 rounded-xl border border-rose-200/50">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-xs font-semibold">
                        Warning: After {{ $maxViolations }} violations, your quiz is submitted automatically.
                    </p>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Privacy Notice & Launch Button -->
        <div class="lg:col-span-5 space-y-6">
            <div
                class="bg-emerald-50/70 border border-emerald-200/70 p-6 rounded-2xl text-xs sm:text-sm text-emerald-950 space-y-2.5">
                <div class="flex items-center gap-2 font-bold text-emerald-900 text-sm">
                    <svg class="w-5 h-5 text-emerald-700 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Data Privacy Notice</span>
                </div>
                <p class="text-xs leading-relaxed text-emerald-900/90">
                    Your quiz activity is recorded in line with the <strong>Data Privacy Act of 2012 (RA 10173)</strong>.
                </p>
            </div>

            <!-- Submit form via POST to start quiz attempt -->
            <form id="startQuizForm" action="{{ $quizId ? url('/student/quizzes/' . $quizId . '/start') : $quizzesUrl }}"
                method="{{ $quizId ? 'POST' : 'GET' }}"
                class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-5">
                @if ($quizId)
                    @csrf
                @endif

                <label class="flex items-start gap-3 cursor-pointer select-none">
                    <input type="checkbox" id="agreeCheckbox"
                        class="w-5 h-5 mt-0.5 rounded text-[#11422d] focus:ring-emerald-600 border-slate-300">
                    <div class="text-xs text-slate-800">
                        <span class="font-bold block text-sm text-slate-900">I understand and agree to be monitored</span>
                        <span class="text-slate-500">I agree to follow the examination code of conduct and anti-cheating
                            guidelines.</span>
                    </div>
                </label>

                <p id="agreeError" class="hidden text-xs font-semibold text-rose-600">
                    Please confirm that you agree to the monitoring rules before starting.
                </p>

                <button type="submit"
                    class="w-full py-4 px-6 bg-[#11422d] hover:bg-[#0d3423] text-white text-center font-bold text-sm sm:text-base rounded-2xl transition shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                    <span>Start Quiz</span>
                    <span>&rarr;</span>
                </button>
            </form>
        </div>

    </div>

    @push('scripts')
        <script>
            // Validate agreement checkbox prior to beginning attempt
            document.getElementById('startQuizForm')?.addEventListener('submit', function(event) {
                const checkbox = document.getElementById('agreeCheckbox');
                const errorBox = document.getElementById('agreeError');

                if (!checkbox?.checked) {
                    event.preventDefault();
                    errorBox?.classList.remove('hidden');
                    return;
                }

                errorBox?.classList.add('hidden');
            });
        </script>
    @endpush
@endsection
