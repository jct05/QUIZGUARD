@extends('shared_layouts.guest')

@section('title', 'Verify account')

@section('content')
<h1 class="text-2xl font-bold text-slate-900 tracking-tight">Verify your account</h1>
<p class="text-xs text-slate-500 mt-1 mb-6">Enter the 6-digit code sent to your DWCC email. It expires in 10 minutes.</p>

<form method="POST" action="/verify-otp" class="space-y-4">
    @csrf

    <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">DWCC email</label>
        <input type="email" name="dwcc_email" value="{{ old('dwcc_email') }}" required
               class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-emerald-600 focus:outline-none transition">
    </div>

    <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Verification code</label>
        <input type="text" name="otp" required inputmode="numeric" maxlength="6" placeholder="123456"
               class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-lg text-center tracking-[0.4em] focus:border-emerald-600 focus:outline-none transition">
    </div>

    <button type="submit" class="w-full px-4 py-2.5 bg-[#11422d] hover:bg-[#0d3423] text-white rounded-xl text-sm font-medium transition shadow-xs">Verify</button>

    <button type="submit" formaction="/verify-otp/resend" formnovalidate
            class="w-full px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-medium transition">Send me a new code</button>
</form>

<p class="text-xs text-slate-500 mt-6"><a href="/login" class="font-semibold text-emerald-700 hover:text-emerald-900">&larr; Back to login</a></p>
@endsection