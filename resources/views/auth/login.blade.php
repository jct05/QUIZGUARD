@extends('shared_layouts.guest')

@section('title', 'Login')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Welcome back</h1>
    <p class="text-xs text-slate-500 mt-1 mb-6">Log in with your DWCC email.</p>

    <form method="POST" action="/login" class="space-y-4">
        @csrf

        <div>
            <label for="dwcc_email" class="block text-xs font-bold text-slate-700 mb-1.5">Email</label>
            <input type="email" id="dwcc_email" name="dwcc_email" value="{{ old('dwcc_email') }}" required autofocus
                placeholder="name@dwcc.edu.ph"
                class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-emerald-600 focus:outline-none transition">
        </div>

        <div>
            <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">Password</label>
            <input type="password" id="password" name="password" required placeholder="e.g. LASTNAME12345"
                class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-emerald-600 focus:outline-none transition">
        </div>

        <button type="submit"
            class="w-full px-4 py-2.5 bg-[#11422d] hover:bg-[#0d3423] text-white rounded-xl text-sm font-medium transition shadow-xs">Log
            in</button>
    </form>

    <p class="text-xs text-slate-500 mt-6">
        Account not activated yet?
        <a href="/verify-otp" class="font-semibold text-emerald-700 hover:text-emerald-900">Enter your verification code</a>
    </p>
@endsection
