@extends('shared_layouts.admin')

@section('title', 'System Logs')

@section('content')
    <!-- Top Title -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">System Logs</h1>
        <p class="text-xs text-slate-500 mt-1">Audit trail of actions across QuizGuard.</p>
    </div>

    <!-- 1. SEARCH & FILTERS BAR -->
    <form id="logs-filter-form" method="GET" action="{{ route('admin.logs') }}"
        class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <!-- Search Input -->
        <div class="relative w-full lg:w-80">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input type="text" name="q" value="{{ request('q') }}"
                placeholder="Search name, school ID or details..."
                class="w-full bg-white text-xs text-slate-700 pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-600 focus:outline-none transition shadow-xs placeholder-slate-400">
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap items-center gap-3">
            <label class="inline-flex items-center gap-1.5 bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-500 shadow-xs focus-within:border-emerald-600 transition">
                <span class="font-medium text-slate-400">From:</span>
                <input type="date" id="filter-from" name="from" value="{{ request('from') }}"
                    max="{{ request('to') ?: '' }}" title="From date"
                    class="bg-transparent text-xs text-slate-700 focus:outline-none">
            </label>
            <label class="inline-flex items-center gap-1.5 bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-500 shadow-xs focus-within:border-emerald-600 transition">
                <span class="font-medium text-slate-400">To:</span>
                <input type="date" id="filter-to" name="to" value="{{ request('to') }}"
                    min="{{ request('from') ?: '' }}" title="To date"
                    class="bg-transparent text-xs text-slate-700 focus:outline-none">
            </label>

            <select name="role" onchange="this.form.submit()"
                class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 shadow-xs">
                <option value="">Role: All</option>
                @foreach (['student', 'teacher', 'admin'] as $role)
                    <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>
                        {{ ucfirst($role) }}</option>
                @endforeach
            </select>

            <select name="action" onchange="this.form.submit()"
                class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 shadow-xs">
                <option value="">Action: All</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                        {{ ucfirst(strtolower(str_replace('_', ' ', $action))) }}</option>
                @endforeach
            </select>

            <button type="submit"
                class="px-4 py-2 bg-[#11422d] hover:bg-[#0d3423] text-white rounded-xl text-xs font-medium transition shadow-xs">Filter</button>
            <a href="{{ route('admin.logs') }}" class="text-xs text-slate-500 hover:text-slate-800">Reset</a>
        </div>
    </form>

    <!-- 2. AUDIT LOGS DATA TABLE -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-100 bg-slate-50/50">
                        <th class="py-3.5 px-6 font-medium">Timestamp</th>
                        <th class="py-3.5 px-6 font-medium">User</th>
                        <th class="py-3.5 px-6 font-medium">Role</th>
                        <th class="py-3.5 px-6 font-medium">Action</th>
                        <th class="py-3.5 px-6 font-medium">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-normal">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-6 text-slate-500 whitespace-nowrap">
                                {{ $log->created_at->format('M d, g:i A') }}</td>
                            <td class="py-3.5 px-6">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] flex items-center justify-center shrink-0">
                                        @if ($log->user)
                                            {{ strtoupper(substr($log->user->first_name, 0, 1) . substr($log->user->last_name, 0, 1)) }}
                                        @else
                                            ?
                                        @endif
                                    </div>
                                    @if ($log->user)
                                        <span class="font-bold text-slate-900">{{ $log->user->first_name }}
                                            {{ $log->user->last_name }}</span>
                                    @else
                                        {{-- failed logins with an unknown email have no user --}}
                                        <span class="text-slate-400">Unknown</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-6">
                                @if ($log->role === 'student')
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">Student</span>
                                @elseif ($log->role === 'teacher')
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200/60">Teacher</span>
                                @elseif ($log->role === 'admin')
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">Admin</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-slate-700 font-medium">
                                {{ ucfirst(strtolower(str_replace('_', ' ', $log->action))) }}</td>
                            <td class="py-3.5 px-6 text-slate-600">{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 px-6 text-center text-slate-400">No logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Pagination Footer -->
<div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
    <div>
        Showing
        <span class="font-semibold text-slate-700">{{ $logs->firstItem() ?? 0 }}-{{ $logs->lastItem() ?? 0 }}</span>
        of
        <span class="font-semibold text-slate-700">{{ $logs->total() }}</span>
        logs
    </div>

    <div class="inline-flex items-center gap-2">
        @if ($logs->onFirstPage())
            <span class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-300">Previous</span>
        @else
            <a href="{{ $logs->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition">Previous</a>
        @endif

        <span class="text-slate-500">Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}</span>

        @if ($logs->hasMorePages())
            <a href="{{ $logs->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-[#11422d] text-white hover:bg-[#0d3423] transition">Next</a>
        @else
            <span class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-300">Next</span>
        @endif
    </div>
</div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('logs-filter-form');
            const fromInput = document.getElementById('filter-from');
            const toInput = document.getElementById('filter-to');

            if (!form || !fromInput || !toInput) return;

            fromInput.addEventListener('change', function () {
                toInput.min = fromInput.value || '';
                if (toInput.value && fromInput.value && fromInput.value > toInput.value) {
                    toInput.value = fromInput.value;
                }
                form.submit();
            });

            toInput.addEventListener('change', function () {
                fromInput.max = toInput.value || '';
                if (fromInput.value && toInput.value && toInput.value < fromInput.value) {
                    fromInput.value = toInput.value;
                }
                form.submit();
            });
        })();
    </script>
@endsection
