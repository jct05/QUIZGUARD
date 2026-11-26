@extends('shared_layouts.teacher')

@section('title', 'Results - ' . ($quiz->title ?? 'Quiz'))

@section('content')
    <div class="space-y-6">
        <!-- Top Header & Unified Email Report Button -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ url('/teacher/quizzes') }}"
                    class="text-xs font-semibold text-slate-500 hover:text-slate-800">&larr; Quizzes</a>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">{{ $quiz->title }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ $quiz->subject->subject_code ?? '' }} &bull;
                    Section {{ $quiz->section->section_name ?? 'All' }} &bull;
                    Code: <strong class="text-slate-800">{{ $quiz->quiz_code }}</strong>
                </p>
            </div>

            <form action="{{ url('/teacher/quizzes/' . $quiz->quiz_id . '/results/email') }}" method="POST">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#11422d] hover:bg-[#0d3423] text-white text-xs font-bold rounded-xl transition shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Email PDF &amp; CSV Report</span>
                </button>
            </form>
        </div>

        <!-- Summary KPIs -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-2xl font-black text-slate-900">{{ $averagePct }}%</p>
                <p class="text-xs text-slate-500 mt-1">Average score</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-2xl font-black text-slate-900">{{ $submittedCount }}</p>
                <p class="text-xs text-slate-500 mt-1">Students submitted</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-2xl font-black text-[#11422d]">
                    {{ $highestScore }} {{ $highestScore === 1 ? 'point' : 'points' }}
                </p>
                <p class="text-xs text-slate-500 mt-1">Highest score (out of {{ $maxPoints }})</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-2xl font-black text-slate-900">{{ $overallAvgQLabel }}</p>
                <p class="text-xs text-slate-500 mt-1">Avg time / question</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs col-span-2 lg:col-span-1">
                <p class="text-2xl font-black text-slate-900">{{ $totalViolations }}</p>
                <p class="text-xs text-slate-500 mt-1">Total violations</p>
            </div>
        </div>

        <!-- Visual Charts: Scores & Correct Answers Per Question -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-extrabold text-slate-900">Student Scores Overview (pts)</h2>
                    <span class="text-[11px] font-semibold text-slate-400">Passing: {{ $quiz->passing_score ?? 0 }}
                        pts</span>
                </div>
                <div class="h-60">
                    <canvas id="scoresChartCanvas"></canvas>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-extrabold text-slate-900">Students Correct per Question</h2>
                    <span class="text-[11px] font-semibold text-slate-400">Out of {{ $submittedCount }}
                        {{ $submittedCount === 1 ? 'student' : 'students' }}</span>
                </div>
                <div class="h-60">
                    <canvas id="questionCorrectChartCanvas"></canvas>
                </div>
            </div>
        </div>

        <!-- Render question accuracy fraction pills and filter controls -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Question Accuracy Breakdown</h3>
                    <p class="text-xs text-slate-500">Click a question to inspect how many students answered it correctly
                    </p>
                </div>
                <button type="button" onclick="selectQuestionHighlight('all', null)" id="allQuestionsBtn"
                    class="px-3.5 py-1.5 rounded-xl bg-[#11422d] text-white text-xs font-bold self-start sm:self-auto">
                    All Questions
                </button>
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach ($questionStats as $qs)
                    <button type="button" onclick="selectQuestionHighlight('{{ $qs['question_id'] }}', this)"
                        class="q-pill-btn px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-emerald-50 text-xs font-semibold text-slate-700 transition flex items-center gap-2">
                        <span class="font-extrabold text-[#11422d]">{{ $qs['label'] }}:</span>
                        <span>{{ $qs['fraction'] }} correct</span>
                        <span class="text-[10px] text-slate-400">({{ $qs['accuracy_pct'] }}%)</span>
                    </button>
                @endforeach
            </div>

            <div id="selectedQuestionDetail"
                class="hidden rounded-xl bg-emerald-50/70 border border-emerald-200 px-4 py-3 text-xs text-slate-800"></div>
        </div>

        <!-- Student Submissions Matrix & Summary -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-9 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <h3 class="text-sm font-extrabold text-slate-900">Student Submissions (Highest Score per Student)</h3>

                    <!-- Filter by student name/ID and dynamic violation count -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <div class="relative w-full sm:w-56">
                            <span
                                class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input type="text" id="studentResultSearch" oninput="filterStudentResults()"
                                placeholder="Search name or ID..."
                                class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-[#11422d] focus:outline-none">
                        </div>

                        <select id="violationFilterSelect" onchange="filterStudentResults()"
                            class="px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:border-[#11422d] focus:outline-none font-semibold text-slate-700">
                            <option value="all">Violations: All</option>
                            @for ($v = 0; $v <= $maxViolations; $v++)
                                <option value="{{ $v }}">{{ $v }}
                                    {{ $v === 1 ? 'Violation' : 'Violations' }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead
                            class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 whitespace-nowrap">Student</th>
                                <th class="px-4 py-3 whitespace-nowrap">Student ID</th>
                                @foreach ($questionStats as $qs)
                                    <th class="px-3 py-3 text-center whitespace-nowrap q-col-header"
                                        data-qid="{{ $qs['question_id'] }}">
                                        {{ $qs['label'] }}
                                    </th>
                                @endforeach
                                <th class="px-4 py-3 whitespace-nowrap">Total</th>
                                <th class="px-4 py-3 whitespace-nowrap">Score</th>
                                <th class="px-4 py-3 whitespace-nowrap">Avg / Q</th>
                                <th class="px-4 py-3 whitespace-nowrap">Violations</th>
                                <th class="px-4 py-3 whitespace-nowrap">Tries</th>
                                <th class="px-4 py-3 whitespace-nowrap">Submitted</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($studentRows as $rowIdx => $row)
                                <tr class="student-result-row hover:bg-slate-50/60 transition"
                                    data-search="{{ strtolower($row['student_name'] . ' ' . $row['school_id_num']) }}"
                                    data-violations="{{ $row['violation_count'] }}">
                                    <td class="px-4 py-3.5 font-bold text-slate-900 whitespace-nowrap">
                                        <a href="{{ url('/teacher/attempts/' . $row['attempt_id']) }}"
                                            class="hover:text-[#11422d]">
                                            {{ $row['student_name'] }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">{{ $row['school_id_num'] }}</td>

                                    @foreach ($questionStats as $qs)
                                        @php $res = $row['question_results'][$qs['question_id']] ?? 'unanswered'; @endphp
                                        <td class="px-3 py-3.5 text-center whitespace-nowrap q-col-cell"
                                            data-qid="{{ $qs['question_id'] }}">
                                            @if ($res === 'correct')
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    Correct
                                                </span>
                                            @elseif($res === 'incorrect')
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    Wrong
                                                </span>
                                            @else
                                                <span class="text-slate-400">&mdash;</span>
                                            @endif
                                        </td>
                                    @endforeach

                                    <td class="px-4 py-3.5 font-extrabold text-slate-900 whitespace-nowrap">
                                        {{ $row['score'] }}/{{ $row['total_points'] }}
                                    </td>
                                    <td
                                        class="px-4 py-3.5 font-bold {{ $row['passed'] ? 'text-emerald-700' : 'text-rose-600' }} whitespace-nowrap">
                                        {{ $row['percentage'] }}%
                                    </td>
                                    <td class="px-4 py-3.5 font-semibold text-emerald-800 whitespace-nowrap">
                                        {{ $row['avg_time_label'] }}</td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">{{ $row['violation_count'] }}</td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <button type="button" onclick="openStudentTriesModal({{ $rowIdx }})"
                                            class="inline-flex items-center gap-1.5 font-bold text-[#11422d] hover:underline">
                                            <span>{{ $row['tries'] }}</span>
                                            <span
                                                class="px-2 py-0.5 rounded-lg bg-emerald-50 border border-emerald-200 text-[10px]">View</span>
                                        </button>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-500 whitespace-nowrap">{{ $row['submitted_label'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 8 + count($questionStats) }}"
                                        class="px-5 py-8 text-center text-slate-400">
                                        No student submissions yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Summary Box -->
            <div class="lg:col-span-3 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-3 text-xs">
                <h3 class="text-sm font-extrabold text-slate-900">Summary</h3>
                <p class="text-slate-600">Passing score: <strong class="text-slate-900">{{ $quiz->passing_score ?? 0 }}
                        pts</strong></p>
                <p class="text-slate-600">Passed: <strong class="text-slate-900">{{ $passedCount }} of
                        {{ $submittedCount }}</strong></p>
                <p class="text-slate-600">Passing rate: <strong class="text-slate-900">{{ $passingRate }}%</strong></p>
                <p class="text-slate-600">Max violations allowed: <strong
                        class="text-slate-900">{{ $maxViolations }}</strong></p>
            </div>
        </div>
    </div>

    <!-- Render student tries modal listing all recorded attempts -->
    <div id="studentTriesModal"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-5 hidden">
        <div
            class="bg-white w-full max-w-3xl rounded-3xl p-6 shadow-2xl border border-slate-100 space-y-4 max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 id="triesModalStudentName" class="text-base font-extrabold text-slate-900">Student Attempts</h3>
                    <p id="triesModalSubtitle" class="text-xs text-slate-500">All recorded tries for this quiz</p>
                </div>
                <button type="button" onclick="closeStudentTriesModal()"
                    class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
            </div>

            <div class="overflow-y-auto flex-1">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-2.5">Try</th>
                            <th class="px-4 py-2.5">Total</th>
                            <th class="px-4 py-2.5">Score</th>
                            <th class="px-4 py-2.5">Avg / Q</th>
                            <th class="px-4 py-2.5">Violations</th>
                            <th class="px-4 py-2.5">Submitted</th>
                            <th class="px-4 py-2.5 text-right">Details</th>
                        </tr>
                    </thead>
                    <tbody id="studentTriesTableBody" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>

            <div class="pt-2 border-t border-slate-100 flex justify-end">
                <button type="button" onclick="closeStudentTriesModal()"
                    class="px-5 py-2 rounded-xl bg-[#11422d] hover:bg-[#0d3423] text-white text-xs font-bold">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const studentRows = @json($studentRows);
        const questionStats = @json($questionStats);
        const submittedTotal = {{ (int) $submittedCount }};

        // Render Chart.js visualizations for scores and correct counts
        new Chart(document.getElementById('scoresChartCanvas'), {
            type: 'bar',
            data: {
                labels: studentRows.length ? studentRows.map(r => r.student_name) : ['No submissions'],
                datasets: [{
                    label: 'Highest Score (pts)',
                    data: studentRows.length ? studentRows.map(r => r.score) : [0],
                    backgroundColor: studentRows.map(r => r.passed ? '#11422d' : '#e11d48'),
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: Math.max(1, {{ (int) $maxPoints }})
                    }
                }
            }
        });

        new Chart(document.getElementById('questionCorrectChartCanvas'), {
            type: 'bar',
            data: {
                labels: questionStats.length ? questionStats.map(q => q.label) : ['Q1'],
                datasets: [{
                    label: 'Students Correct',
                    data: questionStats.length ? questionStats.map(q => q.correct_count) : [0],
                    backgroundColor: '#185c3f',
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const stat = questionStats[ctx.dataIndex];
                                if (!stat) return ctx.formattedValue;
                                return `Students Correct: ${stat.correct_count}/${stat.total_attempts} (${stat.accuracy_pct}%)`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: Math.max(1, submittedTotal),
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Highlight selected question column and display accuracy details
        function selectQuestionHighlight(qId, btn) {
            document.querySelectorAll('.q-pill-btn').forEach(b => {
                b.classList.remove('bg-[#11422d]', 'text-white', 'border-[#11422d]');
                b.classList.add('bg-slate-50', 'text-slate-700', 'border-slate-200');
                const lbl = b.querySelector('span:first-child');
                if (lbl) lbl.className = 'font-extrabold text-[#11422d]';
            });

            const allBtn = document.getElementById('allQuestionsBtn');
            const detailBox = document.getElementById('selectedQuestionDetail');

            if (qId === 'all') {
                allBtn.className =
                    'px-3.5 py-1.5 rounded-xl bg-[#11422d] text-white text-xs font-bold self-start sm:self-auto';
                detailBox.classList.add('hidden');
            } else {
                allBtn.className =
                    'px-3.5 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold self-start sm:self-auto';
                if (btn) {
                    btn.classList.remove('bg-slate-50', 'text-slate-700', 'border-slate-200');
                    btn.classList.add('bg-[#11422d]', 'text-white', 'border-[#11422d]');
                    const lbl = btn.querySelector('span:first-child');
                    if (lbl) lbl.className = 'font-extrabold text-emerald-200';
                }

                const found = questionStats.find(q => String(q.question_id) === String(qId));
                if (found) {
                    detailBox.innerHTML =
                        `<strong>${found.label}:</strong> ${found.question_text} &mdash; <strong>${found.fraction}</strong> students got this correct (${found.accuracy_pct}%), avg time: <strong>${found.avg_label}</strong>.`;
                    detailBox.classList.remove('hidden');
                }
            }

            document.querySelectorAll('.q-col-header, .q-col-cell').forEach(cell => {
                const match = qId === 'all' || String(cell.dataset.qid) === String(qId);
                cell.classList.toggle('hidden', !match);
            });
        }

        // Filter student rows by name, ID, and violations
        function filterStudentResults() {
            const query = (document.getElementById('studentResultSearch')?.value || '').toLowerCase().trim();
            const violationFilter = document.getElementById('violationFilterSelect')?.value || 'all';

            document.querySelectorAll('.student-result-row').forEach(row => {
                const matchesSearch = !query || (row.dataset.search || '').includes(query);
                const matchesViolation = violationFilter === 'all' || String(row.dataset.violations) === String(
                    violationFilter);
                row.classList.toggle('hidden', !(matchesSearch && matchesViolation));
            });
        }

        // Open modal displaying all attempts for selected student
        function openStudentTriesModal(rowIdx) {
            const student = studentRows[rowIdx];
            if (!student) return;

            document.getElementById('triesModalStudentName').textContent = student.student_name;
            document.getElementById('triesModalSubtitle').textContent =
                `Student ID: ${student.school_id_num} • ${student.tries} total ${student.tries === 1 ? 'try' : 'tries'}`;

            const tbody = document.getElementById('studentTriesTableBody');
            tbody.innerHTML = (student.all_tries || []).map(t => `
            <tr class="hover:bg-slate-50/60">
                <td class="px-4 py-3 font-bold text-slate-900">
                    Try #${t.try_number}
                    ${t.is_best ? '<span class="ml-1 px-2 py-0.5 rounded-full text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200">Highest</span>' : ''}
                </td>
                <td class="px-4 py-3 font-bold text-slate-900">${t.score}/${t.total_points}</td>
                <td class="px-4 py-3 font-bold ${t.passed ? 'text-emerald-700' : 'text-rose-600'}">${t.percentage}%</td>
                <td class="px-4 py-3">${t.avg_time_label}</td>
                <td class="px-4 py-3">${t.violation_count}</td>
                <td class="px-4 py-3 text-slate-500">${t.submitted_label}</td>
                <td class="px-4 py-3 text-right">
                    <a href="/teacher/attempts/${t.attempt_id}" class="font-bold text-[#11422d] hover:underline">Inspect &rarr;</a>
                </td>
            </tr>
        `).join('');

            document.getElementById('studentTriesModal').classList.remove('hidden');
        }

        function closeStudentTriesModal() {
            document.getElementById('studentTriesModal').classList.add('hidden');
        }
    </script>
@endsection
