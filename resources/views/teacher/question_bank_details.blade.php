@extends('shared_layouts.teacher')

@section('title', $bank->bank_title)

@section('content')
    <!-- Header & Back Link -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <a href="{{ url('/teacher/question-banks') }}" class="text-xs font-medium text-emerald-700 hover:underline">
                &larr; Back to My Library
            </a>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight mt-1">{{ $bank->bank_title }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ $bank->subject?->subject_code ?? 'No Subject' }}
                @if ($bank->subject?->subject_name)
                    — {{ $bank->subject->subject_name }}
                @endif
                &middot; Source: <span class="font-medium text-slate-700">{{ $bank->source_file_name ?? 'N/A' }}</span>
                &middot; {{ $questions->count() }} {{ Str::plural('question', $questions->count()) }}
            </p>
        </div>

        <a href="{{ url('/teacher/quizzes/create') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[#11422d] hover:bg-[#0d3423] text-white rounded-xl text-xs font-medium transition shadow-xs self-start md:self-auto">
            + Create Quiz from Library
        </a>
    </div>

    <!-- Questions List -->
    <div class="space-y-4">
        @forelse ($questions as $index => $question)
            @php
                $isMc = $question->question_type === 'multiple_choice';
                $options = is_string($question->options) ? json_decode($question->options, true) : $question->options;
            @endphp

            <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-5">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-lg bg-[#11422d] text-white text-xs font-bold">
                            #{{ $index + 1 }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600">
                            {{ $isMc ? 'Multiple Choice' : 'Identification' }}
                        </span>
                    </div>

                    <span class="text-xs font-semibold text-slate-500">
                        {{ $question->points }} {{ Str::plural('pt', $question->points) }}
                    </span>
                </div>

                <p class="text-sm font-medium text-slate-900 mb-3">{{ $question->question_text }}</p>

                @if ($isMc && is_array($options))
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        @foreach ($options as $letter => $choiceText)
                            @php
                                $isCorrect = strtoupper($letter) === strtoupper($question->correct_answers);
                            @endphp
                            <div
                                class="px-3 py-2 rounded-xl text-xs border {{ $isCorrect ? 'bg-emerald-50 border-emerald-300 text-emerald-800 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-600' }}">
                                <span class="font-bold">{{ strtoupper($letter) }}.</span> {{ $choiceText }}
                                @if ($isCorrect)
                                    <span class="ml-1">✓</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div
                        class="inline-block px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                        Answer: {{ $question->correct_answers }}
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-8 text-center text-xs text-slate-400">
                No active questions found in this question bank.
            </div>
        @endforelse
    </div>
@endsection
