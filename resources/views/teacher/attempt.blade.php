@extends('shared_layouts.teacher')

@section('title', 'Attempt Details - ' . ($attempt->quiz->title ?? 'Quiz'))

@section('content')
    @php
        $quiz = $attempt->quiz;
        $student = $attempt->student;
        $studentName = $student
            ? trim($student->first_name . ' ' . $student->last_name)
            : 'Student #' . $attempt->student_id;
        $pct = $attempt->total_points > 0 ? round(($attempt->score / $attempt->total_points) * 100) : 0;
        $passing = (int) ($quiz->passing_score ?? 0);
        $passed =
            $passing <= 0
                ? true
                : ($attempt->total_points > 0 && $passing <= $attempt->total_points
                    ? $attempt->score >= $passing
                    : $pct >= $passing);
        $violations = $attempt->violationLogs ?? collect();
    @endphp

    <div class="space-y-6">
        <!-- Top Back Link & Summary Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ url('/teacher/quizzes/' . $attempt->quiz_id . '/results') }}"
                    class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                    &larr; Back to {{ $quiz->title ?? 'Quiz' }} Results
                </a>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">{{ $studentName }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Student ID: <strong class="text-slate-800">{{ $student->school_id_num ?? 'N/A' }}</strong> &bull;
                    {{ $quiz->subject->subject_code ?? '' }} &bull;
                    {{ $quiz->title ?? '' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold uppercase border {{ $passed ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                    {{ $passed ? 'Passed' : 'Failed' }}
                </span>
                @if ($attempt->status === 'auto_submitted')
                    <span
                        class="px-3.5 py-1.5 rounded-full text-xs font-bold uppercase bg-amber-50 text-amber-800 border border-amber-200">
                        Auto-Submitted
                    </span>
                @endif
            </div>
        </div>

        <!-- Summary KPIs -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <p class="text-2xl font-black text-[#11422d] leading-tight">
                    {{ $attempt->score }}/{{ $attempt->total_points }}</p>
                <p class="text-xs text-slate-500 mt-1.5">Score ({{ $pct }}%)</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <p
                    class="text-2xl font-black leading-tight {{ $attempt->violation_count > 0 ? 'text-rose-600' : 'text-slate-900' }}">
                    {{ $attempt->violation_count ?? 0 }}
                </p>
                <p class="text-xs text-slate-500 mt-1.5">Violations Recorded</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <p class="text-lg font-black text-slate-900 leading-8">
                    {{ $attempt->started_at ? \Carbon\Carbon::parse($attempt->started_at)->format('M d, g:i A') : '-' }}
                </p>
                <p class="text-xs text-slate-500 mt-1.5">Started At</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <p class="text-lg font-black text-slate-900 leading-8">
                    {{ $attempt->submitted_at ? \Carbon\Carbon::parse($attempt->submitted_at)->format('M d, g:i A') : '-' }}
                </p>
                <p class="text-xs text-slate-500 mt-1.5">Submitted At</p>
            </div>
        </div>

        <!-- Render student answer breakdown and violation audit cards -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left 8 Cols: Question-by-Question Answers -->
            <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100">
                    <h2 class="text-sm font-extrabold text-slate-900">Submitted Answers</h2>
                    <p class="text-xs text-slate-500">Detailed comparison of student answers vs answer key</p>
                </div>

                <div class="divide-y divide-slate-100 text-xs">
                    @forelse($answers as $idx => $ans)
                        <div class="p-5 space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-extrabold text-[#11422d]">Question {{ $idx + 1 }} &bull;
                                    {{ $ans->points ?? 1 }} pts</span>
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase border {{ $ans->is_correct ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                    {{ $ans->is_correct ? 'Correct' : 'Wrong' }}
                                </span>
                            </div>
                            <p class="text-sm font-bold text-slate-900">{{ $ans->question_text }}</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                                <div class="rounded-xl bg-slate-50 border border-slate-200/70 px-3 py-2">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Student Answer</span>
                                    <span class="font-semibold text-slate-800">{{ $ans->student_answer ?: '—' }}</span>
                                </div>
                                <div class="rounded-xl bg-emerald-50/50 border border-emerald-200/60 px-3 py-2">
                                    <span class="text-[10px] uppercase font-bold text-emerald-700 block">Correct
                                        Answer</span>
                                    <span class="font-semibold text-emerald-900">{{ $ans->correct_answers }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400">
                            No answers recorded for this attempt.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right 4 Cols: Violation Log Audit -->
            <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100">
                    <h2 class="text-sm font-extrabold text-slate-900">Proctoring Violation Log</h2>
                    <p class="text-xs text-slate-500">Recorded anti-cheat events</p>
                </div>

                <div class="p-5">
                    @if ($violations->isNotEmpty())
                        <ul class="divide-y divide-slate-100 text-xs">
                            @foreach ($violations as $log)
                                <li class="py-3 flex items-center justify-between gap-2">
                                    <div>
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            {{ $log->violation_type }}
                                        </span>
                                        <p class="text-[11px] text-slate-400 mt-1">
                                            Duration: {{ abs((int) ($log->duration_seconds ?? 0)) }}s
                                        </p>
                                    </div>
                                    <span class="text-[11px] font-medium text-slate-500">
                                        {{ $log->occurred_at ? \Carbon\Carbon::parse($log->occurred_at)->format('g:i:s A') : '-' }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="rounded-xl bg-emerald-50/70 border border-emerald-200/60 p-4 text-center">
                            <p class="text-xs font-semibold text-emerald-800">No violations recorded</p>
                            <p class="text-[11px] text-emerald-700/80 mt-0.5">Clean proctoring session verified.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
