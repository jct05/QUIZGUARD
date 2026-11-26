@extends('shared_layouts.teacher')

@section('title', 'Dashboard')

@section('content')
    @php
        $typeNames = [
            'tab_switch' => 'Switched tab',
            'third_party_app' => 'Used another app',
            'fullscreen_exit' => 'Exited fullscreen',
            'copy_paste' => 'Copy / paste',
            'window_blur' => 'Left the window',
        ];
        $now = now();
    @endphp

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Teacher Dashboard</h1>
            <p class="text-xs text-slate-500 mt-1">Monitor your quizzes today.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="/teacher/question-banks"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-medium transition shadow-xs">Upload
                Question Bank</a>
            <a href="/teacher/quizzes/create"
                class="inline-flex items-center gap-2 px-4 py-2 bg-[#11422d] hover:bg-[#0d3423] text-white rounded-xl text-xs font-medium transition shadow-xs">+
                Create Quiz</a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        @foreach ([['Active quizzes', $quizzes], ['Question banks', $banks], ['Submissions today', $submissionsToday], ['Violations today', $violationsToday]] as [$label, $value])
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
                <p class="text-2xl font-bold text-slate-900 tracking-tight">{{ $value }}</p>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- My Quizzes -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
            <div class="p-6 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">My Quizzes</h2>
                    <p class="text-xs text-slate-500">Closing soonest first</p>
                </div>
                <a href="/teacher/quizzes" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900">View all
                    &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-y border-slate-100 bg-slate-50/50">
                            <th class="py-3 px-6 font-medium">Quiz</th>
                            <th class="py-3 px-6 font-medium">Section</th>
                            <th class="py-3 px-6 font-medium">Items</th>
                            <th class="py-3 px-6 font-medium">Closes</th>
                            <th class="py-3 px-6 font-medium">Status</th>
                            <th class="py-3 px-6 font-medium text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($quizList as $quiz)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3.5 px-6"><span
                                        class="font-bold text-slate-900">{{ $quiz->title }}</span><br><span
                                        class="text-slate-400">{{ $quiz->subject->subject_code ?? '' }}</span></td>
                                <td class="py-3.5 px-6 text-slate-600">{{ $quiz->section?->section_name ?? '—' }}</td>
                                <td class="py-3.5 px-6 text-slate-600">{{ $quiz->number_of_questions }}</td>
                                <td class="py-3.5 px-6 text-slate-600 whitespace-nowrap">
                                    {{ $quiz->closed_at?->format('M d, g:i A') }}</td>
                                <td class="py-3.5 px-6">
                                    @if ($quiz->open_at && $now->lt($quiz->open_at))
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200/60">Scheduled</span>
                                    @elseif ($quiz->closed_at && $now->gt($quiz->closed_at))
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200">Closed</span>
                                    @else
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">Open</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-6 text-right"><a href="/teacher/quizzes/{{ $quiz->quiz_id }}/results"
                                        class="font-medium text-emerald-700 hover:text-emerald-900">Results</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 px-6 text-center text-slate-400">No quizzes yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Violations -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-6">
            <h2 class="text-base font-bold text-slate-900">Recent Violations</h2>
            <p class="text-xs text-slate-500 mb-4">Across your quizzes</p>

            @forelse ($recentViolations as $v)
                <div class="py-3 border-b border-slate-100 last:border-0">
                    <p class="text-sm font-semibold text-slate-900">{{ $v->attempt->student->first_name }}
                        {{ $v->attempt->student->last_name }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $typeNames[$v->violation_type] ?? $v->violation_type }}
                        &middot; {{ $v->attempt->quiz->title }}</p>
                    <p class="text-[11px] text-slate-400">{{ $v->occurred_at->format('M d, g:i A') }}</p>
                </div>
            @empty
                <p class="text-xs text-slate-400 text-center py-6">No violations yet.</p>
            @endforelse
        </div>
    </div>
@endsection
