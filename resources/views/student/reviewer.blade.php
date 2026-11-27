@extends('shared_layouts.student')

@section('title', 'Reviewer Sets')

@section('content')
    <!-- Resolve teacher question banks and distinct subject filter tags -->
    @php
        $reviewerSets = collect($reviewerSets ?? ($banks ?? ($questionBanks ?? [])));
        $subjectTags = $reviewerSets
            ->map(fn($bank) => $bank->subject->subject_name ?? ($bank->subject->subject_code ?? null))
            ->filter()
            ->unique()
            ->values();
    @endphp

    <!-- ============================================================ -->
    <!--      REVIEWER PRACTICE SETS (RESPONSIVE: MOBILE & DESKTOP)   -->
    <!-- ============================================================ -->

    <!-- Top Dark Green Banner -->
    <div class="bg-[#11422d] text-white p-6 sm:p-8 rounded-2xl sm:rounded-3xl shadow-sm mb-6">
        <div class="flex items-center gap-2.5 mb-4 md:hidden">
            <img src="{{ asset('images/quizguard-logo.webp') }}" alt="DWCC QuizGuard Logo"
                class="w-8 h-8 rounded-lg object-cover shadow-xs shrink-0 bg-[#0f3422]">
            <span class="text-xs font-bold tracking-wider uppercase text-white">DWCC QUIZGUARD</span>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">Reviewer</h1>
                <p class="text-xs sm:text-sm text-emerald-100/80 mt-1">Practice with teacher-made question banks anytime</p>
            </div>
            <div
                class="hidden sm:inline-flex bg-white/10 px-4 py-2 rounded-xl text-xs font-semibold text-emerald-200 border border-white/10">
                {{ $reviewerSets->count() }} {{ $reviewerSets->count() === 1 ? 'Reviewer Set' : 'Reviewer Sets' }} Available
            </div>
        </div>
    </div>

    <!-- Main Body -->
    <div class="space-y-6">

        <!-- Search Box and Filter Pills Row -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" id="reviewerSearchInput" oninput="filterReviewerSets()"
                    placeholder="Search reviewer sets by topic or professor..."
                    class="w-full bg-white text-xs sm:text-sm text-slate-700 pl-10 pr-4 py-3 rounded-2xl border border-slate-200/80 focus:border-[#11422d] focus:outline-none transition shadow-xs placeholder-slate-400">
            </div>

            <!-- Filter Tags -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 text-xs font-semibold shrink-0">
                <button type="button" onclick="selectReviewerCategory('all', this)"
                    class="reviewer-filter-btn px-3.5 py-2.5 rounded-xl bg-[#11422d] text-white shadow-xs">
                    All
                </button>
                @foreach ($subjectTags as $tag)
                    <button type="button" onclick="selectReviewerCategory('{{ strtolower($tag) }}', this)"
                        class="reviewer-filter-btn px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-slate-900 transition">
                        {{ $tag }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Reviewer Sets: 1 Column on Mobile, 2 or 3 Columns on Tablet/Desktop -->
        <!-- Render dynamic reviewer set cards with subject metadata -->
        @if ($reviewerSets->isNotEmpty())
            <div id="reviewerSetsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($reviewerSets as $bank)
                    @php
                        $subjectLabel = $bank->subject->subject_name ?? ($bank->subject->subject_code ?? 'General');
                        $subjectCode = $bank->subject->subject_code ?? 'BANK';
                        $teacherName = isset($bank->teacher)
                            ? trim($bank->teacher->first_name . ' ' . $bank->teacher->last_name)
                            : 'Faculty';
                        $questionTotal =
                            $bank->questions_count ?? (isset($bank->questions) ? $bank->questions->count() : 0);
                        $practiceUrl = Route::has('student.reviewer.practice')
                            ? route('student.reviewer.practice', $bank->bank_id)
                            : url('/student/reviewer/' . $bank->bank_id);
                    @endphp
                    <div class="reviewer-card bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition flex flex-col justify-between"
                        data-category="{{ strtolower($subjectLabel) }}"
                        data-search="{{ strtolower($bank->bank_title . ' ' . $subjectLabel . ' ' . $teacherName) }}">
                        <div class="flex items-start gap-3.5 mb-4">
                            <div
                                class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-sm font-bold text-slate-900 leading-snug">{{ $bank->bank_title }}</h2>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $questionTotal }}
                                    {{ $questionTotal === 1 ? 'question' : 'questions' }}</p>
                                <p class="text-[11px] text-emerald-800 font-semibold mt-0.5">by {{ $teacherName }}</p>
                            </div>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                            <span class="text-[11px] text-slate-400 truncate">{{ $subjectCode }} ·
                                {{ $subjectLabel }}</span>
                            <a href="{{ $practiceUrl }}"
                                class="px-4 py-2 rounded-xl bg-[#11422d] hover:bg-[#0d3423] text-white text-xs font-semibold transition shadow-xs shrink-0">
                                Practice
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white p-10 rounded-2xl border border-dashed border-slate-200 text-center">
                <p class="text-sm font-bold text-slate-700">No reviewer sets available yet</p>
                <p class="text-xs text-slate-400 mt-1">Practice sets published by your teachers will appear here.</p>
            </div>
        @endif

    </div>

    @push('scripts')
        <script>
            let activeCategory = 'all';

            function selectReviewerCategory(category, clickedBtn) {
                activeCategory = category;
                document.querySelectorAll('.reviewer-filter-btn').forEach(btn => {
                    const isSelected = btn === clickedBtn;
                    btn.className = 'reviewer-filter-btn px-3.5 py-2.5 rounded-xl transition ' +
                        (isSelected ?
                            'bg-[#11422d] text-white shadow-xs' :
                            'bg-white border border-slate-200 text-slate-600 hover:text-slate-900');
                });
                filterReviewerSets();
            }

            // Filter reviewer cards by search input and selected subject
            function filterReviewerSets() {
                const query = (document.getElementById('reviewerSearchInput')?.value || '').toLowerCase().trim();
                document.querySelectorAll('.reviewer-card').forEach(card => {
                    const matchesCategory = activeCategory === 'all' || card.dataset.category === activeCategory;
                    const matchesSearch = !query || (card.dataset.search || '').includes(query);
                    card.classList.toggle('hidden', !(matchesCategory && matchesSearch));
                });
            }
        </script>
    @endpush
@endsection
