@extends('shared_layouts.teacher')

@section('title', 'My Sections')

@section('content')
    <!-- Top Title and Add Section Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">My Sections</h1>
            <p class="text-xs text-slate-500 mt-1">Create class sections under your subjects and manage student rosters.</p>
        </div>

        <details class="relative">
            <summary
                class="list-none cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-[#11422d] hover:bg-[#0d3423] text-white rounded-xl text-xs font-medium transition shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Add Section
            </summary>
            <form method="POST" action="{{ route('teacher.sections.store') }}"
                class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-2xl shadow-lg p-4 space-y-2.5 z-30">
                @csrf
                <select name="subject_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white">
                    <option value="">Select Subject</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->subject_id }}" {{ request('subject_id') == $subject->subject_id ? 'selected' : '' }}>
                            {{ $subject->subject_code }} - {{ $subject->subject_name }}
                        </option>
                    @endforeach
                </select>
                <input name="section_name" value="{{ old('section_name') }}" placeholder="Section name (e.g. BSIT 3A)" required
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                <button type="submit"
                    class="w-full px-4 py-2 bg-[#11422d] hover:bg-[#0d3423] text-white rounded-xl text-xs font-medium">
                    Create Section
                </button>
            </form>
        </details>
    </div>

    <!-- Search & Filter Bar -->
    <form method="GET" action="{{ route('teacher.sections') }}"
        class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search section name..."
            class="w-full lg:w-80 bg-white text-xs text-slate-700 px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-600 focus:outline-none shadow-xs">

        <div class="flex flex-wrap items-center gap-3">
            <select name="subject_id" class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs">
                <option value="">Subject: All</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->subject_id }}" {{ request('subject_id') == $subject->subject_id ? 'selected' : '' }}>
                        {{ $subject->subject_code }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs">
                <option value="">Status: All</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-[#11422d] text-white rounded-xl text-xs font-medium">Filter</button>
        </div>
    </form>

    <!-- Sections Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-100 bg-slate-50/50">
                        <th class="py-3.5 px-6 font-medium">Section Name</th>
                        <th class="py-3.5 px-6 font-medium">Subject</th>
                        <th class="py-3.5 px-6 font-medium">Students</th>
                        <th class="py-3.5 px-6 font-medium">Lock</th>
                        <th class="py-3.5 px-6 font-medium">Status</th>
                        <th class="py-3.5 px-6 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($sections as $section)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-6 font-bold text-slate-900">{{ $section->section_name }}</td>
                            <td class="py-3.5 px-6 text-slate-600">
                                {{ $section->subject?->subject_code }} — {{ $section->subject?->subject_name }}
                            </td>
                            <td class="py-3.5 px-6">
                                <a href="{{ route('teacher.sections.students', $section->section_id) }}"
                                    class="inline-flex items-center gap-1 text-emerald-700 hover:text-emerald-900 font-semibold">
                                    {{ $section->students_count }} {{ Str::plural('student', $section->students_count) }} &rarr;
                                </a>
                            </td>
                            <td class="py-3.5 px-6">
                                @if ($section->is_locked)
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200/60">Locked</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">Open</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6">
                                @if ($section->status === 'active')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">Active</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200">Inactive</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="inline-flex items-center justify-end gap-3">
                                    <button type="button"
                                        onclick="openEditSectionModal(this)"
                                        title="Edit section"
                                        aria-label="Edit section"
                                        data-id="{{ $section->section_id }}"
                                        data-subject-id="{{ $section->subject_id }}"
                                        data-name="{{ $section->section_name }}"
                                        class="p-1.5 text-emerald-700 hover:text-emerald-900 hover:bg-emerald-50 rounded-lg transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </button>

                                    <form method="POST" action="{{ route('teacher.sections.lock', $section->section_id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs font-medium text-amber-700 hover:text-amber-900 cursor-pointer">
                                            {{ $section->is_locked ? 'Unlock' : 'Lock' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('teacher.sections.toggle', $section->section_id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs font-medium text-slate-600 hover:text-slate-900 cursor-pointer">
                                            {{ $section->status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 px-6 text-center text-slate-400">No sections found yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <div>Showing {{ $sections->firstItem() ?? 0 }}-{{ $sections->lastItem() ?? 0 }} of {{ $sections->total() }} sections</div>
            <div>{{ $sections->links() }}</div>
        </div>
    </div>

    <!-- Edit Section Modal -->
    <div id="editSectionModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
        <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl shadow-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-slate-900">Edit Section</h2>
                <button type="button" onclick="closeEditSectionModal()" class="text-slate-400 hover:text-slate-700 text-lg leading-none cursor-pointer">&times;</button>
            </div>

            <form id="editSectionForm" method="POST" action="" class="space-y-3">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-[11px] font-medium text-slate-600 mb-1">Subject</label>
                    <select id="edit_section_subject_id" name="subject_id" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white">
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->subject_id }}">{{ $subject->subject_code }} - {{ $subject->subject_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-slate-600 mb-1">Section Name</label>
                    <input id="edit_section_name" name="section_name" type="text" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeEditSectionModal()"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium cursor-pointer">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 bg-[#11422d] hover:bg-[#0d3423] text-white rounded-xl text-xs font-medium cursor-pointer">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Open the edit modal and populate section details.
        function openEditSectionModal(button) {
            const modal = document.getElementById('editSectionModal');
            const form = document.getElementById('editSectionForm');

            form.action = '/teacher/sections/' + button.dataset.id;
            document.getElementById('edit_section_subject_id').value = button.dataset.subjectId || '';
            document.getElementById('edit_section_name').value = button.dataset.name || '';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        // Close and hide the edit section modal.
        function closeEditSectionModal() {
            const modal = document.getElementById('editSectionModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
@endsection