<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class OtpController extends Controller
{
    // page with the email and code fields
    public function showVerify()
    {
        return view('auth.verify_otp');
    }

    // checks the code and activates the account
    public function verify(Request $request)
    {
        $request->validate([
            'dwcc_email' => 'required|email',
            'otp'        => 'required|digits:6',
        ]);

        // only for pending accounts
        // a deactivated (inactive) user can't reactivate himself
        $user = User::where('dwcc_email', $request->dwcc_email)->where('status', 'pending')->first();

        // same vague message for every failure so nobody can test which emails exist
        $invalid = ['otp' => 'Invalid or expired code.'];

        if (!$user || !$user->otp_hash) {
            return back()->withErrors($invalid)->withInput();
        }

        // expired, or 5 wrong tries on this code request a new code is needed
        if (now()->gt($user->otp_expires_at) || $user->otp_attempts >= 5) {
            return back()->withErrors(['otp' => 'This code is no longer valid. Request a new one.'])->withInput();
        }

        // wrong code counts the try
        if (!Hash::check($request->otp, $user->otp_hash)) {
            $user->increment('otp_attempts');
            return back()->withErrors($invalid)->withInput();
        }

        // correct: activate and wipe the code so it can't be used twice
        $user->update([
            'status'         => 'active',
            'otp_hash'       => null,
            'otp_expires_at' => null,
            'otp_attempts'   => 0,
        ]);

        ActivityLog::record($user->school_id_num, $user->role, 'ACCOUNT_VERIFIED', 'Account activated with OTP');

        // Log the user in and regenerate session so protected routes work
        Auth::login($user);
        $request->session()->regenerate();

        $role = strtolower(trim((string) $user->role));
        session(['prompt_password_change' => true]);

        if ($role === 'teacher') {
            return redirect('/teacher/settings');
        }

        if ($role === 'student') {
            return redirect('/student/profile');
        }

        return redirect('/login')->with('success', 'Account activated. You can log in now.');
    }

    // sends a new code to a pending account
    public function resend(Request $request)
    {
        $request->validate(['dwcc_email' => 'required|email']);

        $user = User::where('dwcc_email', $request->dwcc_email)->where('status', 'pending')->first();

        if ($user) {
            $user->sendOtp();
        }

        // same message whether or not the email exists
        return back()->with('success', 'If the account is waiting for verification, a new code was sent.');
    }
}
