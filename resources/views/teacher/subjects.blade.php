@extends('shared_layouts.teacher')

@section('title', 'My Subjects')

@section('content')
    <!-- Top Title and Add Subject Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">My Subjects</h1>
            <p class="text-xs text-slate-500 mt-1">Manage the subjects you handle and open their sections.</p>
        </div>

        <details class="relative">
            <summary
                class="list-none cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-[#11422d] hover:bg-[#0d3423] text-white rounded-xl text-xs font-medium transition shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Add Subject
            </summary>
            <form method="POST" action="{{ route('teacher.subjects.store') }}"
                class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-2xl shadow-lg p-4 space-y-2.5 z-30">
                @csrf
                <input name="subject_code" value="{{ old('subject_code') }}" placeholder="Subject code (e.g. ITE 101)" required
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                <input name="subject_name" value="{{ old('subject_name') }}" placeholder="Subject name" required
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                <button type="submit"
                    class="w-full px-4 py-2 bg-[#11422d] hover:bg-[#0d3423] text-white rounded-xl text-xs font-medium">
                    Create Subject
                </button>
            </form>
        </details>
    </div>

    <!-- Search & Filter Bar -->
    <form method="GET" action="{{ route('teacher.subjects') }}"
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search subject code or name..."
            class="w-full sm:w-80 bg-white text-xs text-slate-700 px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-600 focus:outline-none shadow-xs">

        <div class="flex items-center gap-3">
            <select name="status" class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs">
                <option value="">Status: All</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit"
                class="px-4 py-2 bg-[#11422d] text-white rounded-xl text-xs font-medium">Filter</button>
        </div>
    </form>

    <!-- Subjects Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-100 bg-slate-50/50">
                        <th class="py-3.5 px-6 font-medium">Subject Code</th>
                        <th class="py-3.5 px-6 font-medium">Subject Name</th>
                        <th class="py-3.5 px-6 font-medium">Sections</th>
                        <th class="py-3.5 px-6 font-medium">Status</th>
                        <th class="py-3.5 px-6 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($subjects as $subject)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-6 font-bold text-slate-900">{{ $subject->subject_code }}</td>
                            <td class="py-3.5 px-6 text-slate-700">{{ $subject->subject_name }}</td>
                            <td class="py-3.5 px-6">
                                <a href="{{ route('teacher.sections', ['subject_id' => $subject->subject_id]) }}"
                                    class="inline-flex items-center gap-1 text-emerald-700 hover:text-emerald-900 font-medium">
                                    {{ $subject->sections_count }} {{ Str::plural('section', $subject->sections_count) }} &rarr;
                                </a>
                            </td>
                            <td class="py-3.5 px-6">
                                @if ($subject->status === 'active')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">Active</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200">Inactive</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="inline-flex items-center justify-end gap-3">
                                    <button type="button"
                                        onclick="openEditSubjectModal(this)"
                                        title="Edit subject"
                                        aria-label="Edit subject"
                                        data-id="{{ $subject->subject_id }}"
                                        data-code="{{ $subject->subject_code }}"
                                        data-name="{{ $subject->subject_name }}"
                                        class="p-1.5 text-emerald-700 hover:text-emerald-900 hover:bg-emerald-50 rounded-lg transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </button>

                                    <form method="POST" action="{{ route('teacher.subjects.toggle', $subject->subject_id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs font-medium text-slate-600 hover:text-slate-900 cursor-pointer">
                                            {{ $subject->status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 px-6 text-center text-slate-400">No subjects found yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <div>Showing {{ $subjects->firstItem() ?? 0 }}-{{ $subjects->lastItem() ?? 0 }} of {{ $subjects->total() }} subjects</div>
            <div>{{ $subjects->links() }}</div>
        </div>
    </div>

    <!-- Edit Subject Modal -->
    <div id="editSubjectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
        <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl shadow-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-slate-900">Edit Subject</h2>
                <button type="button" onclick="closeEditSubjectModal()" class="text-slate-400 hover:text-slate-700 text-lg leading-none cursor-pointer">&times;</button>
            </div>

            <form id="editSubjectForm" method="POST" action="" class="space-y-3">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-[11px] font-medium text-slate-600 mb-1">Subject Code</label>
                    <input id="edit_subject_code" name="subject_code" type="text" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-slate-600 mb-1">Subject Name</label>
                    <input id="edit_subject_name" name="subject_name" type="text" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeEditSubjectModal()"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium cursor-pointer">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 bg-[#11422d] hover:bg-[#0d3423] text-white rounded-xl text-xs font-medium cursor-pointer">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Open the edit modal and populate subject details.
        function openEditSubjectModal(button) {
            const modal = document.getElementById('editSubjectModal');
            const form = document.getElementById('editSubjectForm');

            form.action = '/teacher/subjects/' + button.dataset.id;
            document.getElementById('edit_subject_code').value = button.dataset.code || '';
            document.getElementById('edit_subject_name').value = button.dataset.name || '';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        // Close and hide the edit subject modal.
        function closeEditSubjectModal() {
            const modal = document.getElementById('editSubjectModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
@endsection