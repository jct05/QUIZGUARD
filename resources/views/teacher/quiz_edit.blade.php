@extends('shared_layouts.teacher')

@section('title', 'Edit Quiz - ' . $quiz->title)

@section('content')
    <form action="{{ url('/teacher/quizzes/' . $quiz->quiz_id) }}" method="POST" class="space-y-6">
        @csrf

        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ url('/teacher/quizzes') }}"
                    class="text-xs font-semibold text-slate-400 hover:text-slate-700">&larr; Quizzes / Edit</a>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Edit Quiz</h1>
                <p class="text-xs text-slate-500 mt-0.5">Update quiz settings, schedules, or fix errors in questions.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ url('/teacher/quizzes') }}"
                    class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-[#11422d] hover:bg-[#0d3423] text-white text-xs font-bold transition shadow-xs">
                    &check; Save Changes
                </button>
            </div>
        </div>

        <!-- Render editable quiz details and question builder cards -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left 8 Cols: Quiz Details -->
            <div class="lg:col-span-8 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-5">
                <h2 class="text-base font-extrabold text-slate-900 pb-3 border-b border-slate-100">Quiz Details</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Quiz Title</label>
                        <input type="text" name="title" value="{{ old('title', $quiz->title) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-[#11422d] focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Section</label>
                        <select name="section_id" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-[#11422d] focus:outline-none">
                            @foreach ($sections as $section)
                                <option value="{{ $section->section_id }}"
                                    {{ old('section_id', $quiz->section_id) == $section->section_id ? 'selected' : '' }}>
                                    {{ $section->section_name }} ({{ $section->subject->subject_code ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Subject</label>
                        <select name="subject_id" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-[#11422d] focus:outline-none">
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->subject_id }}"
                                    {{ old('subject_id', $quiz->subject_id) == $subject->subject_id ? 'selected' : '' }}>
                                    {{ $subject->subject_code }} - {{ $subject->subject_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Quiz Code</label>
                        <input type="text" value="{{ $quiz->quiz_code }}" readonly
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Time Limit (min)</label>
                        <input type="number" name="time_limit_min" min="1"
                            value="{{ old('time_limit_min', $quiz->time_limit_min) }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-[#11422d] focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Per Question (sec)</label>
                        <input type="number" name="time_per_question_sec" min="5" placeholder="Optional"
                            value="{{ old('time_per_question_sec', $quiz->time_per_question_sec) }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-[#11422d] focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Passing Score (pts)</label>
                        <input type="number" name="passing_score" min="0" required
                            value="{{ old('passing_score', $quiz->passing_score) }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-[#11422d] focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Attempts Allowed</label>
                        <input type="number" name="attempts_allowed" min="1" required
                            value="{{ old('attempts_allowed', $quiz->attempts_allowed) }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-[#11422d] focus:outline-none">
                    </div>
                </div>

                @php
                    $minDateTime = now()->format('Y-m-d\TH:i');
                @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Opens At</label>
                        <input type="datetime-local" id="open_at" name="open_at" required min="{{ $minDateTime }}"
                            onchange="syncEditQuizDateBounds()"
                            value="{{ old('open_at', $quiz->open_at ? \Carbon\Carbon::parse($quiz->open_at)->format('Y-m-d\TH:i') : '') }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-[#11422d] focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Closes At</label>
                        <input type="datetime-local" id="closed_at" name="closed_at" required min="{{ $minDateTime }}"
                            value="{{ old('closed_at', $quiz->closed_at ? \Carbon\Carbon::parse($quiz->closed_at)->format('Y-m-d\TH:i') : '') }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-[#11422d] focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Right 4 Cols: Randomization, Exam Options & Anti-Cheat -->
            <div class="lg:col-span-4 space-y-5">
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-3 text-xs">
                    <h2 class="text-base font-extrabold text-slate-900">Randomization</h2>
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="shuffle_questions" value="1"
                            {{ old('shuffle_questions', $quiz->shuffle_questions) ? 'checked' : '' }}
                            class="rounded text-[#11422d] focus:ring-emerald-600">
                        <span class="font-medium text-slate-700">Shuffle questions</span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="shuffle_answers" value="1"
                            {{ old('shuffle_answers', $quiz->shuffle_answers) ? 'checked' : '' }}
                            class="rounded text-[#11422d] focus:ring-emerald-600">
                        <span class="font-medium text-slate-700">Shuffle choices</span>
                    </label>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-3 text-xs">
                    <h2 class="text-base font-extrabold text-slate-900">Exam &amp; Review Options</h2>
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="allow_review" value="1"
                            {{ old('allow_review', $quiz->allow_review ?? false) ? 'checked' : '' }}
                            class="rounded text-[#11422d] focus:ring-emerald-600">
                        <span class="font-medium text-slate-700">Allow students to review answers after submitting</span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="disable_backtracking" value="1"
                            {{ old('disable_backtracking', $quiz->disable_backtracking ?? false) ? 'checked' : '' }}
                            class="rounded text-[#11422d] focus:ring-emerald-600">
                        <span class="font-medium text-slate-700">Prevent going back to previous questions</span>
                    </label>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-3 text-xs">
                    <h2 class="text-base font-extrabold text-slate-900">Anti-Cheat Settings</h2>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Allowed Violations</label>
                        <input type="number" name="max_violation" min="1" required
                            value="{{ old('max_violation', $quiz->max_violation) }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-[#11422d] focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Auto-submits the quiz when this limit is reached.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions Section -->
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Questions</h2>
                    <p class="text-xs text-slate-500">Edit existing questions or add new items below.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="addQuestionBlock('multiple_choice')"
                        class="px-4 py-2 rounded-xl bg-[#11422d] hover:bg-[#0d3423] text-white text-xs font-bold transition">
                        + Multiple Choice
                    </button>
                    <button type="button" onclick="addQuestionBlock('identification')"
                        class="px-4 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition">
                        + Identification
                    </button>
                </div>
            </div>

            <div id="transmutationStatusBanner" class="rounded-2xl border px-4 py-3 text-xs font-semibold"></div>

            <div id="editQuestionsContainer" class="space-y-4">
                @foreach ($questions as $idx => $q)
                    @php
                        $opts = is_array($q->options) ? $q->options : json_decode($q->options ?? '', true) ?? [];
                        $opts = array_values($opts);
                    @endphp
                    <div
                        class="question-item bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-3 text-xs">
                        <input type="hidden" name="questions[{{ $idx }}][question_id]"
                            value="{{ $q->question_id }}">
                        <input type="hidden" name="questions[{{ $idx }}][question_type]"
                            value="{{ $q->question_type }}">

                        <div class="flex items-center justify-between gap-3">
                            <span class="px-2.5 py-1 rounded-lg bg-[#11422d] text-white font-bold uppercase text-[10px]">
                                Q{{ $idx + 1 }} &bull; {{ str_replace('_', ' ', $q->question_type) }}
                            </span>
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-1.5 text-slate-600 font-semibold">
                                    <span>Points:</span>
                                    <input type="number" name="questions[{{ $idx }}][points]" min="1"
                                        value="{{ $q->points ?? 1 }}"
                                        class="w-16 px-2 py-1 rounded-lg border border-slate-200 text-center">
                                </label>
                                <button type="button" onclick="this.closest('.question-item').remove()"
                                    class="text-rose-600 hover:text-rose-800 font-bold">
                                    Remove
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Question Text</label>
                            <textarea name="questions[{{ $idx }}][question_text]" rows="2" required
                                class="w-full px-3.5 py-2 rounded-xl border border-slate-200 focus:border-[#11422d] focus:outline-none">{{ $q->question_text }}</textarea>
                        </div>

                        @if ($q->question_type === 'multiple_choice')
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                @for ($o = 0; $o < max(4, count($opts)); $o++)
                                    <input type="text" name="questions[{{ $idx }}][options][]"
                                        value="{{ $opts[$o] ?? '' }}" placeholder="Choice {{ $o + 1 }}"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-[#11422d] focus:outline-none">
                                @endfor
                            </div>
                        @endif

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Correct Answer</label>
                            <input type="text" name="questions[{{ $idx }}][correct_answers]"
                                value="{{ $q->correct_answers }}" required
                                placeholder="Enter exact correct answer or matching choice text..."
                                class="w-full px-3.5 py-2 rounded-xl border border-slate-200 focus:border-[#11422d] focus:outline-none">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </form>

    <script>
        let qCounter = {{ $questions->count() }};

        function getEditNowLocalIso() {
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            return now.toISOString().slice(0, 16);
        }

        // Keep calendar picker min dates in sync with current time and open_at
        function syncEditQuizDateBounds() {
            const openInput = document.getElementById('open_at');
            const closeInput = document.getElementById('closed_at');
            if (!openInput || !closeInput) return;

            const nowIso = getEditNowLocalIso();
            openInput.min = nowIso;

            const minClose = openInput.value && openInput.value > nowIso ? openInput.value : nowIso;
            closeInput.min = minClose;

            if (closeInput.value && closeInput.value <= minClose) {
                closeInput.value = '';
            }
        }

        // Validate total quiz points against SIT transmutation table
        function validateQuizTransmutationPoints() {
            const pointInputs = document.querySelectorAll('.question-item input[name$="[points]"]');
            let totalPts = 0;

            pointInputs.forEach(input => {
                totalPts += Math.max(0, parseInt(input.value || '0', 10));
            });

            const isValid = totalPts >= 10 && totalPts <= 100 && (totalPts % 5 === 0);
            const banner = document.getElementById('transmutationStatusBanner');
            const passingInput = document.querySelector('input[name="passing_score"]');

            if (isValid) {
                const suggestedPass = Math.ceil(totalPts / 2);
                if (passingInput) passingInput.value = suggestedPass;

                if (banner) {
                    banner.className =
                        'rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-xs font-semibold';
                    banner.innerHTML =
                        `&check; Valid SIT Transmutation Table: <strong>${totalPts} pts total</strong> (Passing score: <strong>${suggestedPass} pts</strong> = 75%).`;
                }
            } else {
                const nextValid = totalPts < 10 ? 10 : Math.min(100, Math.ceil(totalPts / 5) * 5);
                if (banner) {
                    banner.className =
                        'rounded-2xl border border-amber-200 bg-amber-50 text-amber-900 px-4 py-3 text-xs font-semibold';
                    banner.innerHTML =
                        `Total points: <strong>${totalPts} pts</strong>. SIT Transmutation requires multiples of 5 from <strong>10 to 100 pts</strong> (e.g., reach <strong>${nextValid} pts</strong> to save).`;
                }
            }

            return {
                isValid,
                totalPts
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            syncEditQuizDateBounds();
            validateQuizTransmutationPoints();
        });

        document.addEventListener('input', function(e) {
            if (e.target.matches('.question-item input[name$="[points]"]')) {
                validateQuizTransmutationPoints();
            }
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('button')) {
                setTimeout(validateQuizTransmutationPoints, 20);
            }
        });

        document.querySelector('form[action*="/teacher/quizzes"]')?.addEventListener('submit', function(e) {
            const check = validateQuizTransmutationPoints();
            if (!check.isValid) {
                e.preventDefault();
                document.getElementById('transmutationStatusBanner')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        });

        // Add new multiple choice or identification question block
        function addQuestionBlock(type) {
            const container = document.getElementById('editQuestionsContainer');
            const idx = qCounter++;
            const label = type === 'multiple_choice' ? 'Multiple Choice' : 'Identification';

            const choicesHtml = type === 'multiple_choice' ?
                `<div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <input type="text" name="questions[${idx}][options][]" placeholder="Choice 1" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-[#11422d] focus:outline-none">
                    <input type="text" name="questions[${idx}][options][]" placeholder="Choice 2" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-[#11422d] focus:outline-none">
                    <input type="text" name="questions[${idx}][options][]" placeholder="Choice 3" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-[#11422d] focus:outline-none">
                    <input type="text" name="questions[${idx}][options][]" placeholder="Choice 4" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-[#11422d] focus:outline-none">
               </div>` :
                '';

            const html = `
            <div class="question-item bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-3 text-xs">
                <input type="hidden" name="questions[${idx}][question_type]" value="${type}">
                <div class="flex items-center justify-between gap-3">
                    <span class="px-2.5 py-1 rounded-lg bg-[#11422d] text-white font-bold uppercase text-[10px]">
                        New &bull; ${label}
                    </span>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1.5 text-slate-600 font-semibold">
                            <span>Points:</span>
                            <input type="number" name="questions[${idx}][points]" min="1" value="1" class="w-16 px-2 py-1 rounded-lg border border-slate-200 text-center">
                        </label>
                        <button type="button" onclick="this.closest('.question-item').remove()" class="text-rose-600 hover:text-rose-800 font-bold">
                            Remove
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Question Text</label>
                    <textarea name="questions[${idx}][question_text]" rows="2" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 focus:border-[#11422d] focus:outline-none"></textarea>
                </div>
                ${choicesHtml}
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Correct Answer</label>
                    <input type="text" name="questions[${idx}][correct_answers]" required placeholder="Enter exact correct answer..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 focus:border-[#11422d] focus:outline-none">
                </div>
            </div>
        `;

            container.insertAdjacentHTML('beforeend', html);
            validateQuizTransmutationPoints();
        }
    </script>
@endsection
