@extends('shared_layouts.admin')

@section('title', 'Dashboard')

@section('content')
<!-- Top Title and Actions Row -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">System Health</h1>
            @if ($dbOk)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    All systems operational
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Database not responding
                </span>
            @endif
        </div>
        <p class="text-xs text-slate-500 mt-1">Live status of QuizGuard. Last checked {{ $checkedAt }}.</p>
    </div>

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-medium transition shadow-xs">Refresh</a>
        <a href="{{ route('admin.logs') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#11422d] hover:bg-[#0d3423] text-white rounded-xl text-xs font-medium transition shadow-xs">View logs</a>
    </div>
</div>

<!-- 1. LIVE METRICS (4 cards) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-5">
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
        <p class="text-2xl font-bold text-slate-900 tracking-tight">{{ $activeSessions }}</p>
        <p class="text-xs text-slate-500 mt-0.5 font-medium">Active quiz sessions</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Students taking quizzes now</p>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
        <p class="text-2xl font-bold text-slate-900 tracking-tight">{{ $failedLogins }}</p>
        <p class="text-xs text-slate-500 mt-0.5 font-medium">Failed logins (24h)</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Wrong email or password</p>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
        <p class="text-2xl font-bold text-slate-900 tracking-tight">{{ $violations24 }}</p>
        <p class="text-xs text-slate-500 mt-0.5 font-medium">Violations (24h)</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Of {{ $violations }} recorded in total</p>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
        <p class="text-2xl font-bold text-slate-900 tracking-tight">{{ $dbMs }} ms</p>
        <p class="text-xs text-slate-500 mt-0.5 font-medium">Database query time</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Measured when this page loaded</p>
    </div>
</div>

<!-- 2. TOTALS -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-5 mb-8">
    @foreach ([['Students', $students], ['Teachers', $teachers], ['Subjects', $subjects], ['Quizzes', $quizzes], ['Quiz attempts', $attempts]] as [$label, $value])
        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs">
            <p class="text-xl font-bold text-slate-900 tracking-tight">{{ $value }}</p>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">{{ $label }}</p>
        </div>
    @endforeach
</div>

<!-- 3. CHART + STATUS -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    <!-- Chart -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-xs p-6">
        <h2 class="text-base font-bold text-slate-900">Security Events Over Time</h2>
        <p class="text-xs text-slate-500 mb-6">Failed logins and violations, last 24 hours ({{ $chartTotal }} total)</p>

        <div class="flex items-end gap-3 h-40">
            @foreach ($chart as $bar)
                <div class="flex-1 flex flex-col items-center justify-end h-full">
                    <span class="text-[10px] text-slate-500 mb-1">{{ $bar['events'] }}</span>
                    <div class="w-full rounded-t-md {{ $bar['events'] >= 3 ? 'bg-amber-300' : 'bg-emerald-200' }}" style="height: {{ $bar['height'] }}%"></div>
                </div>
            @endforeach
        </div>
        <div class="flex gap-3 mt-2 border-t border-slate-100 pt-2">
            @foreach ($chart as $bar)
                <span class="flex-1 text-center text-[10px] text-slate-400">{{ $bar['label'] }}</span>
            @endforeach
        </div>
    </div>

    <!-- Service status -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-6">
        <h2 class="text-base font-bold text-slate-900">Service Status</h2>
        <p class="text-xs text-slate-500 mb-4">Current state of each component</p>

        <div class="divide-y divide-slate-100">
            <div class="flex items-center justify-between py-3">
                <div>
                    <p class="text-sm font-semibold text-slate-900">Web Application</p>
                    <p class="text-xs text-slate-400">Responding to requests</p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">Operational</span>
            </div>
            <div class="flex items-center justify-between py-3">
                <div>
                    <p class="text-sm font-semibold text-slate-900">Database (MySQL)</p>
                    <p class="text-xs text-slate-400">{{ $dbMs }} ms query time</p>
                </div>
                @if ($dbOk)
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">Operational</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-rose-50 text-rose-700 border border-rose-200/60">Down</span>
                @endif
            </div>
            <div class="flex items-center justify-between py-3">
                <div>
                    <p class="text-sm font-semibold text-slate-900">Email (OTP codes)</p>
                    <p class="text-xs text-slate-400">Mailer: {{ $mailer }}</p>
                </div>
                @if ($mailer === 'log')
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200/60">Testing (log only)</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">Configured</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- 4. RECENT SECURITY EVENTS -->
<div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
    <div class="p-6 flex items-center justify-between">
        <div>
            <h2 class="text-base font-bold text-slate-900">Recent Security Events</h2>
            <p class="text-xs text-slate-500">Latest failed logins and quizzes auto-submitted for violations</p>
        </div>
        <a href="{{ route('admin.logs') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900">View all logs &rarr;</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="text-slate-400 border-y border-slate-100 bg-slate-50/50">
                    <th class="py-3 px-6 font-medium">Time</th>
                    <th class="py-3 px-6 font-medium">Level</th>
                    <th class="py-3 px-6 font-medium">User</th>
                    <th class="py-3 px-6 font-medium">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse ($recent as $log)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3.5 px-6 text-slate-500 whitespace-nowrap">{{ $log->created_at->format('M d, g:i A') }}</td>
                        <td class="py-3.5 px-6">
                            @if ($log->action === 'LOGIN_FAILED')
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200/60">Failed login</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-rose-50 text-rose-700 border border-rose-200/60">Auto-submitted</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-6 font-bold text-slate-900">
                            @if ($log->user) {{ $log->user->first_name }} {{ $log->user->last_name }} @else <span class="text-slate-400 font-normal">Unknown</span> @endif
                        </td>
                        <td class="py-3.5 px-6 text-slate-600">{{ $log->description }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-8 px-6 text-center text-slate-400">No security events yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection