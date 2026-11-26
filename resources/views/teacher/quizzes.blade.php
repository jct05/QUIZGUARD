@extends('shared_layouts.teacher')

@section('title', 'Manage Quizzes')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Quizzes</h1>
                <p class="text-xs text-slate-500 mt-0.5">Manage your active and inactive quizzes.</p>
            </div>
            <a href="{{ url('/teacher/quizzes/create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#11422d] hover:bg-[#0d3423] text-white text-xs font-bold rounded-xl transition shadow-xs self-start sm:self-auto">
                <span>+</span>
                <span>Create Quiz</span>
            </a>
        </div>

        <!-- Search and Filter Controls -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" id="teacherQuizSearch" oninput="filterTeacherQuizzes()"
                    placeholder="Search quizzes by title, code, section, or subject..."
                    class="w-full bg-white text-xs text-slate-700 pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-[#11422d] focus:outline-none transition shadow-xs">
            </div>

            <div class="flex items-center gap-2 shrink-0 text-xs font-semibold">
                <button type="button" onclick="setQuizFilter('all', this)"
                    class="teacher-filter-btn px-3.5 py-2 rounded-xl bg-[#11422d] text-white shadow-xs">
                    All
                </button>
                <button type="button" onclick="setQuizFilter('open', this)"
                    class="teacher-filter-btn px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-slate-900">
                    Open
                </button>
                <button type="button" onclick="setQuizFilter('closed', this)"
                    class="teacher-filter-btn px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-slate-900">
                    Closed
                </button>
            </div>
        </div>

        <!-- Quizzes Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead
                        class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Quiz</th>
                            <th class="px-5 py-3.5">Code</th>
                            <th class="px-5 py-3.5">Section</th>
                            <th class="px-5 py-3.5">Items</th>
                            <th class="px-5 py-3.5">Opens</th>
                            <th class="px-5 py-3.5">Closes</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="teacherQuizzesTbody" class="divide-y divide-slate-100">
                        @forelse($quizzes ?? [] as $quiz)
                            @php
                                $now = now();
                                $isOpen =
                                    $quiz->status === 'active' &&
                                    (!$quiz->open_at || \Carbon\Carbon::parse($quiz->open_at)->lte($now)) &&
                                    (!$quiz->closed_at || \Carbon\Carbon::parse($quiz->closed_at)->gte($now));
                                $searchHaystack = strtolower(
                                    ($quiz->title ?? '') .
                                        ' ' .
                                        ($quiz->quiz_code ?? '') .
                                        ' ' .
                                        ($quiz->section->section_name ?? ($quiz->section_name ?? '')) .
                                        ' ' .
                                        ($quiz->subject->subject_code ?? '') .
                                        ' ' .
                                        ($quiz->subject->subject_name ?? ''),
                                );
                            @endphp
                            <tr class="teacher-quiz-row hover:bg-slate-50/60 transition" data-search="{{ $searchHaystack }}"
                                data-status="{{ $isOpen ? 'open' : 'closed' }}">
                                <td class="px-5 py-4">
                                    <strong class="text-slate-900 block font-bold text-sm">{{ $quiz->title }}</strong>
                                    <span class="text-[10px] text-slate-400 uppercase font-semibold">
                                        {{ $quiz->subject->subject_code ?? ($quiz->subject->subject_name ?? 'GENERAL') }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 font-bold text-slate-800">
                                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 tracking-wider">
                                        {{ $quiz->quiz_code }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 font-semibold text-slate-700">
                                    {{ $quiz->section->section_name ?? ($quiz->section_name ?? 'All') }}
                                </td>
                                <td class="px-5 py-4 font-bold text-slate-800">
                                    {{ $quiz->number_of_questions ?? ($quiz->questions_count ?? '-') }}
                                </td>
                                <td class="px-5 py-4 text-slate-500">
                                    {{ $quiz->open_at ? \Carbon\Carbon::parse($quiz->open_at)->format('M d, g:i A') : 'Immediate' }}
                                </td>
                                <td class="px-5 py-4 text-slate-500">
                                    {{ $quiz->closed_at ? \Carbon\Carbon::parse($quiz->closed_at)->format('M d, g:i A') : 'No limit' }}
                                </td>
                                <td class="px-5 py-4">
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $isOpen ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $isOpen ? 'Open' : 'Closed' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex items-center justify-end gap-3">
                                        <a href="{{ url('/teacher/quizzes/' . $quiz->quiz_id . '/edit') }}"
                                            title="Edit Quiz" class="text-emerald-700 hover:text-emerald-900 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        <a href="{{ url('/teacher/quizzes/' . $quiz->quiz_id . '/results') }}"
                                            class="text-xs font-bold text-emerald-700 hover:text-emerald-900 transition">
                                            Results
                                        </a>

                                        @if ($quiz->status === 'active')
                                            <form action="{{ url('/teacher/quizzes/' . $quiz->quiz_id . '/deactivate') }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                                                    Deactivate
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ url('/teacher/quizzes/' . $quiz->quiz_id . '/activate') }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="text-xs font-semibold text-emerald-600 hover:text-emerald-800">
                                                    Activate
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-8 text-center text-slate-400">
                                    No quizzes created yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let activeFilter = 'all';

        function setQuizFilter(status, clickedBtn) {
            activeFilter = status;
            document.querySelectorAll('.teacher-filter-btn').forEach(btn => {
                const isSelected = btn === clickedBtn;
                btn.className = 'teacher-filter-btn px-3.5 py-2 rounded-xl transition ' +
                    (isSelected ? 'bg-[#11422d] text-white shadow-xs' :
                        'bg-white border border-slate-200 text-slate-600 hover:text-slate-900');
            });
            filterTeacherQuizzes();
        }

        function filterTeacherQuizzes() {
            const query = (document.getElementById('teacherQuizSearch')?.value || '').toLowerCase().trim();
            document.querySelectorAll('.teacher-quiz-row').forEach(row => {
                const matchesSearch = !query || (row.dataset.search || '').includes(query);
                const matchesStatus = activeFilter === 'all' || row.dataset.status === activeFilter;
                row.classList.toggle('hidden', !(matchesSearch && matchesStatus));
            });
        }
    </script>
@endsection
