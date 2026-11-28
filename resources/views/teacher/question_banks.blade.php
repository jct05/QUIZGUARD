@extends('shared_layouts.teacher')

@section('title', 'My Library')

@section('content')
    <!-- Top Header & Action Buttons -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">My Workspace</p>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">My Library</h1>
            <p class="text-xs text-slate-500 mt-0.5">Upload and organize question banks by subject.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ url('/teacher/subjects') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-medium transition shadow-xs">
                Manage Subjects
            </a>

            <!-- Upload Question Bank Dropdown -->
            <details class="relative">
                <summary
                    class="list-none cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-[#11422d] hover:bg-[#0d3423] text-white rounded-xl text-xs font-medium transition shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Upload Question Bank
                </summary>
                <form method="POST" action="{{ url('/teacher/question-banks') }}" enctype="multipart/form-data"
                    onsubmit="disableImportSubmit(this)"
                    class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-2xl shadow-lg p-4 space-y-2.5 z-30">
                    @csrf
                    <input name="bank_title" value="{{ old('bank_title') }}" placeholder="Bank title (e.g. Midterm Exam)"
                        required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">

                    <select name="subject_id" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white">
                        <option value="">Select Subject</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->subject_id }}"
                                {{ old('subject_id') == $subject->subject_id ? 'selected' : '' }}>
                                {{ $subject->subject_code }} — {{ $subject->subject_name }}
                            </option>
                        @endforeach
                    </select>

                    <div>
                        <p class="text-[11px] text-slate-500 mb-1">Upload .docx, .pdf, or .txt (Format: Q:, A.-D., ANSWER:)
                        </p>
                        <input type="file" name="file" accept=".docx,.pdf,.txt" required class="text-xs w-full">
                    </div>

                    <button type="submit"
                        class="w-full px-4 py-2 bg-[#11422d] hover:bg-[#0d3423] text-white rounded-xl text-xs font-medium">
                        Import Questions
                    </button>
                </form>
            </details>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex items-center gap-6 border-b border-slate-200 mb-6 text-xs font-medium">
        <a href="{{ url('/teacher/question-banks') }}"
            class="pb-3 border-b-2 {{ !request('status') ? 'border-[#11422d] text-[#11422d] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            All Banks ({{ $counts['all'] }})
        </a>
        <a href="{{ url('/teacher/question-banks?status=active') }}"
            class="pb-3 border-b-2 {{ request('status') === 'active' ? 'border-[#11422d] text-[#11422d] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Active ({{ $counts['active'] }})
        </a>
        <a href="{{ url('/teacher/question-banks?status=inactive') }}"
            class="pb-3 border-b-2 {{ request('status') === 'inactive' ? 'border-[#11422d] text-[#11422d] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Inactive ({{ $counts['inactive'] }})
        </a>
    </div>

    <!-- Subject Folders -->
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Subject Folders</h2>
        <a href="{{ url('/teacher/sections') }}" class="text-xs font-medium text-emerald-700 hover:underline">
            View sections &rarr;
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        @forelse ($subjects as $subject)
            <a href="{{ url('/teacher/question-banks?subject_id=' . $subject->subject_id) }}"
                class="bg-white p-4 rounded-2xl border {{ request('subject_id') == $subject->subject_id ? 'border-[#11422d] ring-1 ring-[#11422d]' : 'border-slate-100 hover:border-slate-300' }} shadow-xs flex items-center gap-3 transition">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#11422d] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-900 truncate">{{ $subject->subject_code }} —
                        {{ $subject->subject_name }}</p>
                    <p class="text-[11px] text-slate-500">
                        {{ $subject->question_banks_count }} {{ Str::plural('bank', $subject->question_banks_count) }}
                        &middot;
                        {{ $subject->sections_count }} {{ Str::plural('section', $subject->sections_count) }}
                    </p>
                </div>
            </a>
        @empty
            <div class="col-span-full bg-white p-4 rounded-2xl border border-slate-100 text-xs text-slate-400">
                No subjects created yet. Click <strong>Manage Subjects</strong> to add one first.
            </div>
        @endforelse
    </div>

    <!-- Search, Filter, and Question Banks Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <form method="GET" action="{{ url('/teacher/question-banks') }}"
            class="p-4 border-b border-slate-100 flex flex-col sm:flex-row items-center gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search in my library..."
                class="w-full sm:flex-1 bg-slate-50 text-xs text-slate-700 px-4 py-2 rounded-xl border border-slate-200 focus:border-emerald-600 focus:outline-none">

            <select name="subject_id"
                class="w-full sm:w-auto bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs">
                <option value="">All Subjects</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->subject_id }}"
                        {{ request('subject_id') == $subject->subject_id ? 'selected' : '' }}>
                        {{ $subject->subject_code }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="w-full sm:w-auto bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>

            <button type="submit"
                class="w-full sm:w-auto px-4 py-2 bg-[#11422d] text-white rounded-xl text-xs font-medium">
                Filter
            </button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-100 bg-slate-50/50">
                        <th class="py-3.5 px-6 font-medium">Question Bank</th>
                        <th class="py-3.5 px-6 font-medium">Subject</th>
                        <th class="py-3.5 px-6 font-medium">Questions</th>
                        <th class="py-3.5 px-6 font-medium">Quizzes</th>
                        <th class="py-3.5 px-6 font-medium">Uploaded</th>
                        <th class="py-3.5 px-6 font-medium">Status</th>
                        <th class="py-3.5 px-6 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($banks as $bank)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-6">
                                <p class="font-bold text-slate-900">{{ $bank->bank_title }}</p>
                                <p class="text-[11px] text-slate-400">{{ $bank->source_file_name }}</p>
                            </td>
                            <td class="py-3.5 px-6 text-slate-600">{{ $bank->subject?->subject_code ?? '—' }}</td>
                            <td class="py-3.5 px-6 font-medium">{{ $bank->questions_count }}</td>
                            <td class="py-3.5 px-6 text-slate-600">{{ $bank->quizzes_count }}</td>
                            <td class="py-3.5 px-6 text-slate-500">{{ $bank->uploaded_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="py-3.5 px-6">
                                @if ($bank->status === 'active')
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">Active</span>
                                @else
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200">Inactive</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="inline-flex items-center justify-end gap-3">
                                    @if ($bank->status === 'active')
                                        <a href="{{ url('/teacher/question-banks/' . $bank->bank_id) }}"
                                            class="text-xs font-medium text-emerald-700 hover:text-emerald-900">
                                            View
                                        </a>
                                        <form method="POST"
                                            action="{{ url('/teacher/question-banks/' . $bank->bank_id . '/archive') }}"
                                            class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="text-xs font-medium text-slate-600 hover:text-slate-900 cursor-pointer">
                                                Deactivate
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST"
                                            action="{{ url('/teacher/question-banks/' . $bank->bank_id . '/activate') }}"
                                            class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="text-xs font-medium text-emerald-700 hover:text-emerald-900 cursor-pointer">
                                                Activate
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 px-6 text-center text-slate-400">No question banks found in
                                your library yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <div>Showing {{ $banks->firstItem() ?? 0 }}-{{ $banks->lastItem() ?? 0 }} of {{ $banks->total() }} banks
            </div>
            <div>{{ $banks->links() }}</div>
        </div>
    </div>

    <script>
        // Disable import button immediately to prevent duplicate uploads
        function disableImportSubmit(form) {
            const btn = form.querySelector('button[type="submit"]');
            if (!btn) return true;
            if (btn.dataset.submitted === 'true') return false;
            btn.dataset.submitted = 'true';
            btn.disabled = true;
            btn.classList.add('opacity-60', 'cursor-not-allowed');
            btn.textContent = 'Importing...';
            return true;
        }
    </script>
@endsection
