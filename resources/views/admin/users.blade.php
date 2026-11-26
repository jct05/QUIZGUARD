@extends('shared_layouts.admin')

@section('title', 'User Management')

@section('content')
    <!-- ============================================================ -->
    <!--           USER MANAGEMENT                                    -->
    <!-- ============================================================ -->

    <!-- Top Title and Action Buttons -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">User Management</h1>
            <p class="text-xs text-slate-500 mt-1">Manage student and teacher accounts.</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-3">
            <!-- Import CSV Button -->
            <details class="relative">
                <summary
                    class="list-none cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-medium transition shadow-xs">
                    Import CSV</summary>
                <form method="POST" action="/admin/users/import" enctype="multipart/form-data"
                    class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-2xl shadow-lg p-4 space-y-3 z-30">
                    @csrf
                    <p class="text-[11px] text-slate-500">First line must be: role, school_id_num, first_name, middle_name,
                        last_name, dwcc_email, password, department</p>
                    <input type="file" name="file" accept=".csv" required class="text-xs">
                    <button type="submit"
                        class="w-full px-4 py-2 bg-[#11422d] text-white rounded-xl text-xs font-medium">Import</button>
                </form>
            </details>

            <!-- Add User Button -->
            <details class="relative">
                <summary
                    class="list-none cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-[#11422d] hover:bg-[#0d3423] text-white rounded-xl text-xs font-medium transition shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add User
                </summary>
                <form method="POST" action="/admin/users"
                    class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-2xl shadow-lg p-4 space-y-2 z-30">
                    @csrf
                    <select name="role" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                        <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Student</option>
                        <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    <input name="school_id_num" value="{{ old('school_id_num') }}" placeholder="School ID number"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                    <input name="first_name" value="{{ old('first_name') }}" placeholder="First name"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                    <input name="middle_name" value="{{ old('middle_name') }}" placeholder="Middle name (optional)"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                    <input name="last_name" value="{{ old('last_name') }}" placeholder="Last name"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                    <input name="dwcc_email" value="{{ old('dwcc_email') }}" placeholder="DWCC email"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                    <input name="password" type="password" placeholder="Password (min 8)"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                    <select name="department" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white">
                        <option value="">Select Department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department }}" {{ old('department') === $department ? 'selected' : '' }}>
                                {{ $department }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit"
                        class="w-full px-4 py-2 bg-[#11422d] text-white rounded-xl text-xs font-medium">Create user</button>
                </form>
            </details>
        </div>
    </div>

    <!-- ========================================== -->
    <!--    SEARCH & FILTERS BAR                    -->
    <!-- ========================================== -->
    <form method="GET" action="{{ route('admin.users') }}"
        class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <input type="hidden" name="role" value="{{ request('role') }}">

        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name or email..."
            class="w-full lg:w-80 bg-white text-xs text-slate-700 px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-600 focus:outline-none shadow-xs">

        <div class="inline-flex p-1 bg-white border border-slate-200 rounded-xl shadow-xs text-xs font-medium">
            <a href="{{ route('admin.users') }}"
                class="px-4 py-1.5 rounded-lg {{ !request('role') ? 'bg-[#11422d] text-white font-semibold' : 'text-slate-600' }}">All
                <span class="ml-1 opacity-90">{{ $counts['all'] }}</span></a>
            <a href="{{ route('admin.users', ['role' => 'student']) }}"
                class="px-4 py-1.5 rounded-lg {{ request('role') === 'student' ? 'bg-[#11422d] text-white font-semibold' : 'text-slate-600' }}">Students
                <span class="ml-1 opacity-90">{{ $counts['students'] }}</span></a>
            <a href="{{ route('admin.users', ['role' => 'teacher']) }}"
                class="px-4 py-1.5 rounded-lg {{ request('role') === 'teacher' ? 'bg-[#11422d] text-white font-semibold' : 'text-slate-600' }}">Teachers
                <span class="ml-1 opacity-90">{{ $counts['teachers'] }}</span></a>
        </div>

        <div class="flex items-center gap-3">
            <select name="department" class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs">
                <option value="">Department: All</option>
                @foreach ($departments as $department)
                    <option value="{{ $department }}" {{ request('department') === $department ? 'selected' : '' }}>
                        {{ $department }}</option>
                @endforeach
            </select>
            <select name="status" class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs">
                <option value="">Status: All</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-[#11422d] text-white rounded-xl text-xs font-medium">Filter</button>
        </div>
    </form>

    <!-- ========================================== -->
    <!--    USERS DATA TABLE                        -->
    <!-- ========================================== -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-100 bg-slate-50/50">
                        <th class="py-3.5 px-6 font-medium">Name</th>
                        <th class="py-3.5 px-6 font-medium">Email</th>
                        <th class="py-3.5 px-6 font-medium">Role</th>
                        <th class="py-3.5 px-6 font-medium">Department</th>
                        <th class="py-3.5 px-6 font-medium">Status</th>
                        <th class="py-3.5 px-6 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-normal">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-6">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[11px] flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                    </div>
                                    <span class="font-bold text-slate-900">{{ $user->first_name }}
                                        {{ $user->last_name }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-6 text-slate-600">{{ $user->dwcc_email }}</td>
                            <td class="py-3.5 px-6">
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">{{ ucfirst($user->role) }}</span>
                            </td>
                            <td class="py-3.5 px-6 text-slate-600">{{ $user->department ?? '—' }}</td>
                            <td class="py-3.5 px-6">
                                @if ($user->status === 'active')
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">Active</span>
                                @elseif ($user->status === 'pending')
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200/60">Pending</span>
                                @else
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200">Inactive</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="inline-flex items-center justify-end gap-3">
                                    {{-- Edit icon button opens the modal with the user's current credentials --}}
                                    <button type="button" onclick="openEditModal(this)" title="Edit user"
                                        aria-label="Edit user" data-id="{{ $user->user_id }}"
                                        data-role="{{ $user->role }}" data-school-id="{{ $user->school_id_num }}"
                                        data-first-name="{{ $user->first_name }}"
                                        data-middle-name="{{ $user->middle_name }}"
                                        data-last-name="{{ $user->last_name }}" data-email="{{ $user->dwcc_email }}"
                                        data-department="{{ $user->department }}"
                                        class="p-1.5 text-emerald-700 hover:text-emerald-900 hover:bg-emerald-50 rounded-lg transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </button>

                                    {{-- pending accounts activate only through the OTP, so no toggle button for them --}}
                                    @if ($user->status === 'pending')
                                        <span class="text-slate-400">Awaiting OTP</span>
                                    @else
                                        <form method="POST" action="/admin/users/{{ $user->user_id }}/toggle"
                                            class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="text-xs font-medium text-slate-600 hover:text-slate-900 cursor-pointer">{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 px-6 text-center text-slate-400">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Pagination Footer -->
        <div
            class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <div>
                Showing
                <span
                    class="font-semibold text-slate-700">{{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }}</span>
                of
                <span class="font-semibold text-slate-700">{{ $users->total() }}</span>
                users
            </div>

            <div>
                {{ $users->links() }}
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!--    EDIT USER MODAL                         -->
    <!-- ========================================== -->
    <div id="editUserModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
        <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl shadow-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-slate-900">Edit User Credentials</h2>
                <button type="button" onclick="closeEditModal()"
                    class="text-slate-400 hover:text-slate-700 text-lg leading-none cursor-pointer">&times;</button>
            </div>

            <form id="editUserForm" method="POST" action="" class="space-y-3">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-[11px] font-medium text-slate-600 mb-1">Role</label>
                    <select id="edit_role" name="role" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white">
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-slate-600 mb-1">School ID Number</label>
                    <input id="edit_school_id_num" name="school_id_num" type="number" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">First Name</label>
                        <input id="edit_first_name" name="first_name" type="text" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">Middle Name</label>
                        <input id="edit_middle_name" name="middle_name" type="text"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">Last Name</label>
                        <input id="edit_last_name" name="last_name" type="text" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-slate-600 mb-1">DWCC Email</label>
                    <input id="edit_dwcc_email" name="dwcc_email" type="email" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-slate-600 mb-1">Department</label>
                    <select id="edit_department" name="department" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white">
                        <option value="">Select Department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department }}">{{ $department }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-slate-600 mb-1">New Password (leave blank to keep
                        current)</label>
                    <input id="edit_password" name="password" type="password" placeholder="Password (min 8)"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[#11422d] hover:bg-[#0d3423] text-white rounded-xl text-xs font-medium cursor-pointer">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Open the edit modal and populate user credentials.
        function openEditModal(button) {
            const modal = document.getElementById('editUserModal');
            const form = document.getElementById('editUserForm');

            form.action = '/admin/users/' + button.dataset.id;

            document.getElementById('edit_role').value = button.dataset.role || 'student';
            document.getElementById('edit_school_id_num').value = button.dataset.schoolId || '';
            document.getElementById('edit_first_name').value = button.dataset.firstName || '';
            document.getElementById('edit_middle_name').value = button.dataset.middleName || '';
            document.getElementById('edit_last_name').value = button.dataset.lastName || '';
            document.getElementById('edit_dwcc_email').value = button.dataset.email || '';
            document.getElementById('edit_department').value = button.dataset.department || '';
            document.getElementById('edit_password').value = '';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        // Close and hide the edit user modal.
        function closeEditModal() {
            const modal = document.getElementById('editUserModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Locate create user form excluding the edit modal
        function getCreateUserForm() {
            return Array.from(document.querySelectorAll('form')).find(form =>
                form.id !== 'editUserForm' &&
                form.querySelector('input[name="school_id_num"]') &&
                form.querySelector('input[name="last_name"]') &&
                form.querySelector('input[name="password"]')
            );
        }

        // Auto generate uppercase lastname plus ID default password
        function updateDefaultCreatePassword() {
            const createForm = getCreateUserForm();
            if (!createForm) return;

            const schoolIdInput = createForm.querySelector('input[name="school_id_num"]');
            const lastNameInput = createForm.querySelector('input[name="last_name"]');
            const passwordInput = createForm.querySelector('input[name="password"]');

            if (!schoolIdInput || !lastNameInput || !passwordInput) return;
            if (passwordInput.dataset.manualEdit === 'true') return;

            const cleanLast = lastNameInput.value.trim().replace(/\s+/g, '').toUpperCase();
            const cleanId = schoolIdInput.value.trim();

            passwordInput.value = (cleanLast || cleanId) ? `${cleanLast}${cleanId}` : '';
        }

        // Attach live input listeners to create user form
        document.addEventListener('DOMContentLoaded', function() {
            const createForm = getCreateUserForm();
            if (!createForm) return;

            const schoolIdInput = createForm.querySelector('input[name="school_id_num"]');
            const lastNameInput = createForm.querySelector('input[name="last_name"]');
            const passwordInput = createForm.querySelector('input[name="password"]');

            schoolIdInput?.addEventListener('input', updateDefaultCreatePassword);
            lastNameInput?.addEventListener('input', updateDefaultCreatePassword);

            passwordInput?.addEventListener('input', function() {
                passwordInput.dataset.manualEdit = passwordInput.value.trim() === '' ? 'false' : 'true';
            });
        });
    </script>
@endsection
