@extends('shared_layouts.teacher')

@section('title', 'Settings')

@section('content')
    @php
        $teacher = $user ?? auth()->user();
        $isPrompted = (bool) session('prompt_password_change');
    @endphp

    <div class="space-y-6">
        <!-- Display full width verification banner above settings grid -->
        @if ($isPrompted)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3.5 text-xs text-amber-900">
                <strong class="font-extrabold block text-sm">Welcome! Please set your personal password</strong>
                <span>Your email is verified. Replace your temporary password with a custom password (minimum 8
                    characters).</span>
            </div>
        @endif

        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Settings</h1>
            <p class="text-xs text-slate-500 mt-0.5">Your profile and password.</p>
        </div>

        <!-- Render teacher profile card and password form side by side -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            <!-- Left Column: Profile (Read-Only) -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <h2 class="text-base font-extrabold text-slate-900">Profile</h2>

                <div class="space-y-3 text-xs">
                    <div>
                        <p class="text-slate-400 font-medium">Full name</p>
                        <p class="text-sm font-bold text-slate-900 mt-0.5">
                            {{ trim(($teacher->first_name ?? '') . ' ' . ($teacher->last_name ?? '')) }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 font-medium">Employee ID</p>
                        <p class="text-sm font-bold text-slate-900 mt-0.5">{{ $teacher->school_id_num ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 font-medium">DWCC email</p>
                        <p class="text-sm font-bold text-slate-900 mt-0.5">{{ $teacher->dwcc_email ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 font-medium">Department</p>
                        <p class="text-sm font-bold text-slate-900 mt-0.5">{{ $teacher->department ?? 'N/A' }}</p>
                    </div>
                </div>

                <p class="text-[11px] text-slate-400 pt-2">To change these details, ask the admin.</p>
            </div>

            <!-- Right Column: Change Password -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs space-y-5">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Change password</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Minimum 8 characters, 1 uppercase, 1 number, and 1 special
                        character required. Copy &amp; paste is disabled.</p>
                </div>

                <form id="teacherPasswordForm" action="{{ url('/teacher/settings/password') }}" method="POST" novalidate
                    class="space-y-4">
                    @csrf

                    <div>
                        <label for="teacher_current_password"
                            class="block text-xs font-semibold text-slate-700 mb-1.5">Current password</label>
                        <input type="password" id="teacher_current_password" name="current_password"
                            value="{{ $isPrompted ? '••••••••' : '' }}" {{ $isPrompted ? 'readonly' : '' }}
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-[#11422d] focus:outline-none {{ $isPrompted ? 'bg-slate-50 text-slate-500 cursor-not-allowed' : '' }}">
                        <p id="teacher_current_error"
                            class="text-[11px] text-rose-600 font-semibold mt-1 {{ $errors->has('current_password') ? '' : 'hidden' }}">
                            {{ $errors->first('current_password') }}
                        </p>
                    </div>

                    <div>
                        <label for="teacher_new_password" class="block text-xs font-semibold text-slate-700 mb-1.5">New
                            password</label>
                        <div class="relative">
                            <input type="password" id="teacher_new_password" name="password"
                                oninput="evaluateTeacherPassword()"
                                class="no-clipboard w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-[#11422d] focus:outline-none">
                            <button type="button" onclick="toggleTeacherPassword('teacher_new_password', this)"
                                class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-700"
                                title="Show/Hide password">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>

                        <!-- Render live password requirement indicators and validation errors -->
                        <div class="grid grid-cols-2 gap-1.5 mt-2 text-[11px] font-medium">
                            <span id="tch_req_len" class="text-slate-400">&bull; Min 8 characters</span>
                            <span id="tch_req_upper" class="text-slate-400">&bull; 1 capital letter (A-Z)</span>
                            <span id="tch_req_num" class="text-slate-400">&bull; 1 number (0-9)</span>
                            <span id="tch_req_sym" class="text-slate-400 col-span-2">&bull; 1 special (!@#$%^&amp;()_+?.,)</span>
                        </div>
                        <p id="teacher_new_error"
                            class="text-[11px] text-rose-600 font-semibold mt-1 {{ $errors->has('password') ? '' : 'hidden' }}">
                            {{ $errors->first('password') }}
                        </p>
                    </div>

                    <div>
                        <label for="teacher_confirm_password"
                            class="block text-xs font-semibold text-slate-700 mb-1.5">Confirm new password</label>
                        <div class="relative">
                            <input type="password" id="teacher_confirm_password" name="password_confirmation"
                                oninput="evaluateTeacherPassword()"
                                class="no-clipboard w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-[#11422d] focus:outline-none">
                            <button type="button" onclick="toggleTeacherPassword('teacher_confirm_password', this)"
                                class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-700"
                                title="Show/Hide password">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <p id="teacher_confirm_error" class="text-[11px] text-rose-600 font-semibold mt-1 hidden"></p>
                    </div>

                    <!-- Center save password button inside the card -->
                    <div class="pt-2 flex justify-center">
                        <button type="submit"
                            class="px-8 py-2.5 bg-[#11422d] hover:bg-[#0d3423] text-white text-xs font-bold rounded-xl transition shadow-xs">
                            Save password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle password input visibility between hidden and plain
        function toggleTeacherPassword(inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            btn.classList.toggle('text-[#11422d]', isHidden);
        }

        // Block copy, cut, and paste on password inputs
        document.querySelectorAll('.no-clipboard').forEach(el => {
            ['copy', 'cut', 'paste', 'drop'].forEach(evt => {
                el.addEventListener(evt, e => e.preventDefault());
            });
        });

        // Evaluate password rules using whitelisted special characters
        function evaluateTeacherPassword() {
            const pw = document.getElementById('teacher_new_password').value;
            const confirmPw = document.getElementById('teacher_confirm_password').value;
            const allowedSpecialRegex = /[!@#$%^&()_+?.,]/;
            const onlyAllowedCharsRegex = /^[A-Za-z0-9!@#$%^&()_+?.,]+$/;

            const rules = [{
                    id: 'tch_req_len',
                    ok: pw.length >= 8,
                    label: 'at least 8 characters'
                },
                {
                    id: 'tch_req_upper',
                    ok: /[A-Z]/.test(pw),
                    label: '1 capital letter'
                },
                {
                    id: 'tch_req_num',
                    ok: /[0-9]/.test(pw),
                    label: '1 number'
                },
                {
                    id: 'tch_req_sym',
                    ok: allowedSpecialRegex.test(pw),
                    label: '1 allowed special character (!@#$%^&()_+?.,)'
                },
            ];

            const missing = [];
            rules.forEach(r => {
                const el = document.getElementById(r.id);
                if (el) {
                    el.className = (r.ok ? 'text-emerald-700 font-semibold' : 'text-slate-400') + (r.id ===
                        'tch_req_sym' ? ' col-span-2' : '');
                }
                if (!r.ok) missing.push(r.label);
            });

            if (pw !== '' && !onlyAllowedCharsRegex.test(pw)) {
                missing.push('remove unsupported symbols or spaces');
            }

            const confirmErr = document.getElementById('teacher_confirm_error');
            if (confirmPw && pw !== confirmPw) {
                confirmErr.textContent = 'Passwords do not match.';
                confirmErr.classList.remove('hidden');
            } else {
                confirmErr.classList.add('hidden');
            }

            return missing;
        }

        // Validate password complexity and matching fields before submission
        document.getElementById('teacherPasswordForm')?.addEventListener('submit', function(e) {
            const current = document.getElementById('teacher_current_password').value;
            const pw = document.getElementById('teacher_new_password').value;
            const confirmPw = document.getElementById('teacher_confirm_password').value;
            const currentErr = document.getElementById('teacher_current_error');
            const newErr = document.getElementById('teacher_new_error');
            const confirmErr = document.getElementById('teacher_confirm_error');
            let hasError = false;

            if (!current.trim()) {
                currentErr.textContent = 'Please enter your current password.';
                currentErr.classList.remove('hidden');
                hasError = true;
            } else {
                currentErr.classList.add('hidden');
            }

            const missing = evaluateTeacherPassword();
            if (missing.length > 0) {
                newErr.textContent = 'Missing requirement(s): ' + missing.join(', ') + '.';
                newErr.classList.remove('hidden');
                hasError = true;
            } else {
                newErr.classList.add('hidden');
            }

            if (!confirmPw) {
                confirmErr.textContent = 'Please confirm your new password.';
                confirmErr.classList.remove('hidden');
                hasError = true;
            } else if (pw !== confirmPw) {
                confirmErr.textContent = 'Passwords do not match.';
                confirmErr.classList.remove('hidden');
                hasError = true;
            } else {
                confirmErr.classList.add('hidden');
            }

            if (hasError) e.preventDefault();
        });
    </script>
@endsection
