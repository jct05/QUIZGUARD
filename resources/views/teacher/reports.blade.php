@extends('shared_layouts.teacher')

@section('title', 'Quiz Reports')

@section('content')
    <div class="space-y-6">
        <!-- Top Header & Filtered Email Export Button -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Reports</h1>
                <p class="text-xs text-slate-500 mt-0.5">Filter quizzes by subject, section, or date range to export PDF
                    &amp; CSV reports.</p>
            </div>

            <form action="{{ url('/teacher/reports/export') }}" method="POST">
                @csrf
                <input type="hidden" name="subject_id" value="{{ $filters['subject_id'] ?? '' }}">
                <input type="hidden" name="section_name" value="{{ $filters['section_name'] ?? '' }}">
                <input type="hidden" name="from_date" value="{{ $filters['from_date'] ?? '' }}">
                <input type="hidden" name="to_date" value="{{ $filters['to_date'] ?? '' }}">

                <button type="submit" {{ !$hasFilter || count($quizSummaries) === 0 ? 'disabled' : '' }}
                    title="{{ !$hasFilter ? 'Apply a Subject, Section, or Date Range filter before exporting.' : 'Send PDF & CSV report to your email' }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-xs {{ $hasFilter && count($quizSummaries) > 0 ? 'bg-[#11422d] hover:bg-[#0d3423] text-white' : 'bg-slate-200 text-slate-400 cursor-not-allowed' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Export PDF &amp; CSV to Email</span>
                </button>
            </form>
        </div>

        @if (!$hasFilter)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3 text-xs text-amber-900 font-semibold">
                Showing all quizzes. Select a <strong>Subject</strong>, <strong>Section</strong>, or <strong>Date
                    Range</strong> below to enable exporting.
            </div>
        @endif

        <!-- Filter Controls Bar -->
        <form action="{{ url('/teacher/reports') }}" method="GET"
            class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex flex-wrap items-center gap-2 pb-2 border-b border-slate-100">
                <a href="{{ url('/teacher/reports') }}"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition {{ !$hasFilter ? 'bg-[#11422d] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    All Quizzes
                </a>
                <span class="text-[11px] text-slate-400">or narrow results using the dropdowns &amp; dates below:</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Subject</label>
                    <select name="subject_id"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs bg-white focus:border-[#11422d] focus:outline-none">
                        <option value="">All Subjects</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->subject_id }}"
                                {{ ($filters['subject_id'] ?? '') == $subject->subject_id ? 'selected' : '' }}>
                                {{ $subject->subject_code }} — {{ $subject->subject_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Section</label>
                    <select name="section_name"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs bg-white focus:border-[#11422d] focus:outline-none">
                        <option value="">All Sections</option>
                        @foreach ($sectionNames ?? [] as $secName)
                            <option value="{{ $secName }}"
                                {{ ($filters['section_name'] ?? '') === $secName ? 'selected' : '' }}>
                                Section {{ $secName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">From Date</label>
                    <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-[#11422d] focus:outline-none">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">To Date</label>
                    <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-[#11422d] focus:outline-none">
                </div>

                <div>
                    <button type="submit"
                        class="w-full py-2.5 px-4 bg-[#11422d] hover:bg-[#0d3423] text-white text-xs font-bold rounded-xl transition">
                        Apply Filter
                    </button>
                </div>
            </div>
        </form>

        <!-- Quizzes Report Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead
                        class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Quiz</th>
                            <th class="px-5 py-3.5">Subject &amp; Section</th>
                            <th class="px-5 py-3.5">Opened / Closed</th>
                            <th class="px-5 py-3.5">Submitted</th>
                            <th class="px-5 py-3.5">Passed</th>
                            <th class="px-5 py-3.5">Highest Score</th>
                            <th class="px-5 py-3.5">Avg Min</th>
                            <th class="px-5 py-3.5">Avg Max</th>
                            <th class="px-5 py-3.5">Average</th>
                            <th class="px-5 py-3.5 text-right">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($quizSummaries as $item)
                            @php $q = $item['quiz']; @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-4">
                                    <strong class="text-slate-900 font-bold text-sm block">{{ $q->title }}</strong>
                                    <span class="text-[11px] text-slate-400">Code: {{ $q->quiz_code }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="font-semibold text-slate-800 block">{{ $q->subject->subject_code ?? '' }}
                                        — {{ $q->subject->subject_name ?? '' }}</span>
                                    <span class="text-[11px] text-slate-400">Section
                                        {{ $q->section->section_name ?? 'All' }}</span>
                                </td>
                                <td class="px-5 py-4 text-[11px] text-slate-500">
                                    <div>Open:
                                        {{ $q->open_at ? \Carbon\Carbon::parse($q->open_at)->format('M d, Y g:i A') : 'N/A' }}
                                    </div>
                                    <div>Close:
                                        {{ $q->closed_at ? \Carbon\Carbon::parse($q->closed_at)->format('M d, Y g:i A') : 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-5 py-4 font-bold text-slate-800">{{ $item['submitted_count'] }}</td>
                                <td class="px-5 py-4 font-semibold text-emerald-700">{{ $item['passed_count'] }} /
                                    {{ $item['submitted_count'] }}</td>
                                <td class="px-5 py-4 font-bold text-[#11422d]">{{ $item['highest_score'] }} pts</td>
                                <td class="px-5 py-4 font-semibold text-slate-700">
                                    {{ $item['submitted_count'] > 0 ? $item['min_score'] . ' pts' : '—' }}
                                </td>
                                <td class="px-5 py-4 font-semibold text-slate-700">
                                    {{ $item['submitted_count'] > 0 ? $item['max_score'] . ' pts' : '—' }}
                                </td>
                                <td class="px-5 py-4 font-bold text-slate-800">
                                    @if ($item['submitted_count'] > 0)
                                        {{ $item['avg_score'] }} pts
                                        <span class="block text-[11px] font-medium text-slate-400">{{ $item['avg_pct'] }}%</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ url('/teacher/quizzes/' . $q->quiz_id . '/results') }}"
                                        class="text-xs font-bold text-[#11422d] hover:underline">
                                        View Results &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-5 py-10 text-center text-slate-400">
                                    No quizzes found matching your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
