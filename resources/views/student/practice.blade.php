@extends('shared_layouts.student')

@section('title', 'Practice - ' . ($bank->bank_title ?? 'Reviewer'))

@section('content')
    <!-- Resolve practice set questions, teacher author, and subject metadata -->
    @php
        $totalQuestions = $questions->count();
        $teacherName = $bank->teacher ? trim($bank->teacher->first_name . ' ' . $bank->teacher->last_name) : 'Faculty';
    @endphp

    <div class="space-y-6 max-w-4xl mx-auto">
        <!-- Top Header Banner -->
        <div
            class="bg-[#11422d] text-white p-6 sm:p-7 rounded-3xl shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span
                        class="px-2.5 py-0.5 rounded-full bg-emerald-800 text-emerald-200 text-[10px] font-extrabold uppercase tracking-wider">
                        Practice Mode &bull; Ungraded
                    </span>
                    <span class="text-xs text-emerald-200/80">by {{ $teacherName }}</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">{{ $bank->bank_title }}</h1>
                <p class="text-xs text-emerald-100/80 mt-0.5">
                    {{ $bank->subject->subject_code ?? '' }} &bull; {{ $bank->subject->subject_name ?? '' }}
                </p>
            </div>
            <a href="{{ route('student.reviewer') }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-bold transition shrink-0 self-start sm:self-center">
                &larr; Exit Practice
            </a>
        </div>

        @if ($totalQuestions > 0)
            <!-- Practice Question Deck -->
            @foreach ($questions as $index => $q)
                @php
                    $options = is_array($q->options) ? $q->options : json_decode($q->options ?? '', true);
                    $decodedCorrect = json_decode($q->correct_answers ?? '', true);
                    $correct = is_array($decodedCorrect) ? implode(', ', $decodedCorrect) : $q->correct_answers ?? '';
                    $correctDisplay = \App\Services\TransmutationService::formatOptionLabel($correct, $options);
                @endphp
                <div class="practice-card bg-white p-6 sm:p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4 {{ $index === 0 ? '' : 'hidden' }}"
                    data-index="{{ $index }}" data-correct="{{ strtolower(trim($correct)) }}">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <span
                            class="text-xs font-bold uppercase tracking-wider text-emerald-800 bg-emerald-50 px-3 py-1 rounded-full">
                            Question {{ $index + 1 }} of {{ $totalQuestions }}
                        </span>
                        <span class="text-xs font-semibold text-slate-400 capitalize">
                            {{ str_replace('_', ' ', $q->question_type ?? 'Question') }}
                        </span>
                    </div>

                    <!-- Question Text -->
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 leading-snug">
                        {{ $q->question_text }}
                    </h2>

                    <!-- Options / Answer Input -->
                    @if (!empty($options))
                        <div class="space-y-2 pt-1">
                            @foreach ($options as $optKey => $optVal)
                                @php
                                    $val = is_string($optKey) ? $optKey : $optVal;
                                    $displayOpt = is_string($optKey) ? strtoupper($optKey) . ' - ' . $optVal : $optVal;
                                @endphp
                                <label
                                    class="practice-option flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 bg-slate-50/50 cursor-pointer transition">
                                    <input type="radio" name="opt_{{ $q->question_id }}" value="{{ $val }}"
                                        class="text-[#11422d] focus:ring-emerald-600">
                                    <span class="text-sm font-medium text-slate-800">{{ $displayOpt }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="space-y-2 pt-1">
                            <input type="text" id="txt_{{ $q->question_id }}" placeholder="Type your answer here..."
                                class="w-full bg-slate-50 text-slate-900 text-sm font-medium px-4 py-3 rounded-xl border border-slate-300 focus:bg-white focus:border-[#11422d] focus:outline-none transition">
                        </div>
                    @endif

                    <!-- Feedback Panel (Hidden until user checks answer) -->
                    <div id="feedback_{{ $index }}"
                        class="hidden p-4 rounded-xl border text-xs font-semibold space-y-1">
                        <p id="feedback_status_{{ $index }}"></p>
                        <p class="text-slate-600">Correct Answer: <strong
                                class="text-slate-900">{{ $correctDisplay }}</strong></p>
                    </div>

                    <!-- Action Controls -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                        <button type="button"
                            onclick="checkPracticeAnswer({{ $index }}, '{{ $q->question_id }}', {{ !empty($options) ? 'true' : 'false' }})"
                            class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition shadow-xs">
                            Check Answer
                        </button>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="prevPracticeQuestion()" {{ $index === 0 ? 'disabled' : '' }}
                                class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition disabled:opacity-30">
                                &larr; Prev
                            </button>
                            <button type="button" onclick="nextPracticeQuestion()"
                                class="px-4 py-2.5 rounded-xl bg-[#11422d] hover:bg-[#0d3423] text-white text-xs font-bold transition shadow-xs">
                                <span>{{ $index === $totalQuestions - 1 ? 'Finish' : 'Next →' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="bg-white p-12 rounded-3xl border border-dashed border-slate-200 text-center space-y-3">
                <p class="text-base font-bold text-slate-700">No questions available in this bank yet.</p>
                <a href="{{ route('student.reviewer') }}"
                    class="inline-block px-5 py-2 rounded-xl bg-[#11422d] text-white text-xs font-bold">
                    Return to Reviewer
                </a>
            </div>
        @endif
    </div>

    <!-- Display completion confirmation modal with reviewer session results -->
    <div id="finishPracticeModal"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-5 hidden">
        <div class="bg-white w-full max-w-sm rounded-3xl p-6 text-center shadow-2xl border border-slate-100 space-y-4">
            <!-- Success Icon -->
            <div
                class="w-14 h-14 rounded-full bg-emerald-50 text-[#11422d] mx-auto flex items-center justify-center text-2xl font-bold shadow-xs border-4 border-emerald-100">
                🎉
            </div>

            <div>
                <h3 class="text-lg font-extrabold text-slate-900">Practice Complete!</h3>
                <p class="text-xs text-slate-500 mt-1">You reached the end of this reviewer practice set.</p>
            </div>

            <!-- Session Summary Box -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/70 text-xs flex items-center justify-around">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Questions</span>
                    <span class="text-base font-black text-slate-900">{{ $totalQuestions }}</span>
                </div>
                <div class="w-px h-8 bg-slate-200"></div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Correct</span>
                    <span id="modalCorrectCount" class="text-base font-black text-emerald-700">0</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 pt-2">
                <button type="button" onclick="restartPractice()"
                    class="flex-1 py-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition">
                    Practice Again
                </button>
                <a href="{{ route('student.reviewer') }}"
                    class="flex-1 py-3 bg-[#11422d] hover:bg-[#0d3423] text-white font-bold text-xs rounded-xl transition shadow-xs text-center">
                    Back to Reviewer
                </a>
            </div>
        </div>
    </div>

    <script>
        let currentIdx = 0;
        const total = {{ $totalQuestions }};
        const checkedCorrect = new Set();

        function showPracticeCard(idx) {
            if (idx < 0 || idx >= total) return;
            currentIdx = idx;
            document.querySelectorAll('.practice-card').forEach(card => {
                card.classList.toggle('hidden', Number(card.dataset.index) !== idx);
            });
        }

        function prevPracticeQuestion() {
            if (currentIdx > 0) showPracticeCard(currentIdx - 1);
        }

        // Advance to next question or open friendly finish modal
        function nextPracticeQuestion() {
            if (currentIdx < total - 1) {
                showPracticeCard(currentIdx + 1);
            } else {
                openFinishModal();
            }
        }

        function openFinishModal() {
            document.getElementById('modalCorrectCount').textContent = checkedCorrect.size;
            document.getElementById('finishPracticeModal').classList.remove('hidden');
        }

        // Reset practice state, inputs, and clear answer feedback
        function restartPractice() {
            checkedCorrect.clear();
            document.querySelectorAll('.practice-card').forEach((card, idx) => {
                const feedback = document.getElementById(`feedback_${idx}`);
                if (feedback) feedback.classList.add('hidden');
                const textInput = card.querySelector('input[type="text"]');
                if (textInput) textInput.value = '';
                const radios = card.querySelectorAll('input[type="radio"]');
                radios.forEach(r => r.checked = false);
            });
            document.getElementById('finishPracticeModal').classList.add('hidden');
            showPracticeCard(0);
        }

        // Verify student answer against correct answer and reveal solution
        function checkPracticeAnswer(idx, qId, isRadio) {
            const card = document.querySelector(`.practice-card[data-index="${idx}"]`);
            const correct = (card?.dataset.correct || '').trim().toLowerCase();
            let given = '';

            if (isRadio) {
                const checked = card.querySelector(`input[name="opt_${qId}"]:checked`);
                given = (checked?.value || '').trim().toLowerCase();
            } else {
                const input = document.getElementById(`txt_${qId}`);
                given = (input?.value || '').trim().toLowerCase();
            }

            const feedback = document.getElementById(`feedback_${idx}`);
            const status = document.getElementById(`feedback_status_${idx}`);
            feedback.classList.remove('hidden');

            if (given && given === correct) {
                feedback.className =
                    'p-4 rounded-xl border border-emerald-200 bg-emerald-50 text-xs font-semibold space-y-1';
                status.textContent = '🎉 Correct! Well done.';
                status.className = 'text-emerald-800 font-bold';
                checkedCorrect.add(idx);
            } else {
                feedback.className = 'p-4 rounded-xl border border-rose-200 bg-rose-50 text-xs font-semibold space-y-1';
                status.textContent = given ? '❌ Not quite right.' : '⚠️ Please select or type an answer first.';
                status.className = 'text-rose-800 font-bold';
                checkedCorrect.delete(idx);
            }
        }
    </script>
@endsection
