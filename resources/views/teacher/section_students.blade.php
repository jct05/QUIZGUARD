@extends('shared_layouts.teacher')

@section('title', 'Section Students')

@section('content')
    <!-- Header and Back Link -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('teacher.sections') }}" class="text-xs font-medium text-emerald-700 hover:underline">&larr; Back to Sections</a>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight mt-1">
                {{ $section->section_name }} — Enrolled Students
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Subject: {{ $section->subject?->subject_code }} ({{ $section->subject?->subject_name }})
            </p>
        </div>

        <!-- Enroll Student Form -->
        @if (!$section->is_locked)
            <form method="POST" action="{{ route('teacher.sections.enroll', $section->section_id) }}"
                class="flex items-center gap-2">
                @csrf
                <select name="student_id" required class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs min-w-[240px]">
                    <option value="">Select student to enroll...</option>
                    @foreach ($availableStudents as $student)
                        <option value="{{ $student->user_id }}">
                            {{ $student->school_id_num }} — {{ $student->last_name }}, {{ $student->first_name }} ({{ $student->department }})
                        </option>
                    @endforeach
                </select>
                <button type="submit"
                    class="px-4 py-2 bg-[#11422d] hover:bg-[#0d3423] text-white rounded-xl text-xs font-medium">
                    + Enroll
                </button>
            </form>
        @else
            <span class="px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 text-xs font-medium">
                Section Locked (Unlock to add students)
            </span>
        @endif
    </div>

    <!-- Search Enrolled Students -->
    <form method="GET" action="{{ route('teacher.sections.students', $section->section_id) }}" class="mb-6 flex items-center gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search enrolled student by name, ID, or email..."
            class="w-full sm:w-80 bg-white text-xs text-slate-700 px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-600 focus:outline-none shadow-xs">
        <button type="submit" class="px-4 py-2 bg-[#11422d] text-white rounded-xl text-xs font-medium">Search</button>
    </form>

    <!-- Enrolled Students Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-100 bg-slate-50/50">
                        <th class="py-3.5 px-6 font-medium">School ID</th>
                        <th class="py-3.5 px-6 font-medium">Student Name</th>
                        <th class="py-3.5 px-6 font-medium">DWCC Email</th>
                        <th class="py-3.5 px-6 font-medium">Department</th>
                        <th class="py-3.5 px-6 font-medium text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($enrolledStudents as $student)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-6 font-semibold text-slate-900">{{ $student->school_id_num }}</td>
                            <td class="py-3.5 px-6 font-bold text-slate-900">{{ $student->last_name }}, {{ $student->first_name }}</td>
                            <td class="py-3.5 px-6 text-slate-600">{{ $student->dwcc_email }}</td>
                            <td class="py-3.5 px-6 text-slate-600">{{ $student->department }}</td>
                            <td class="py-3.5 px-6 text-right">
                                <form method="POST"
                                    action="{{ route('teacher.sections.remove', [$section->section_id, $student->user_id]) }}"
                                    onsubmit="return confirm('Remove this student from the section?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-rose-600 hover:text-rose-800 cursor-pointer">
                                        Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 px-6 text-center text-slate-400">No students enrolled in this section yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <div>Showing {{ $enrolledStudents->firstItem() ?? 0 }}-{{ $enrolledStudents->lastItem() ?? 0 }} of {{ $enrolledStudents->total() }} students</div>
            <div>{{ $enrolledStudents->links() }}</div>
        </div>
    </div>
@endsection