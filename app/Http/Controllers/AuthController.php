<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // displays login page
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/' . Auth::user()->role . '/dashboard');
        }

        return view('auth.login');
    }

    // login method
    public function login(Request $request)
    {
        // tidy the email
        $request->merge(['dwcc_email' => strtolower(trim((string) $request->dwcc_email))]);

        // field checks
        $request->validate([
            'dwcc_email' => 'required|email',
            'password'   => 'required',
        ], [
            'dwcc_email.required' => 'Please enter your email.',
            'dwcc_email.email'    => 'That is not a valid email address.',
            'password.required'   => 'Please enter your password.',
        ]);

        // find the account by email
        $user = User::where('dwcc_email', $request->dwcc_email)->first();

        // no account with this email
        if (!$user) {
            ActivityLog::record(null, null, 'LOGIN_FAILED', 'Unregistered email: ' . $request->dwcc_email);

            return back()
                ->withErrors(['dwcc_email' => 'This email is not registered. Please ask the admin to create your account.'])
                ->withInput($request->only('dwcc_email'));
        }

        // account exists but the password is wrong
        if (!Hash::check($request->password, $user->password_hash)) {
            ActivityLog::record($user->school_id_num, $user->role, 'LOGIN_FAILED', 'Wrong password for ' . $user->dwcc_email);

            return back()
                ->withErrors(['password' => 'Wrong password. Please try again.'])
                ->withInput($request->only('dwcc_email'));
        }

        // password is right, but the account is not verified yet
        // go to the OTP page with the email already filled in
        if ($user->status === 'pending') {
            ActivityLog::record($user->school_id_num, $user->role, 'LOGIN_BLOCKED', 'Account not verified yet');

            return redirect('/verify-otp')
                ->withErrors(['dwcc_email' => 'Your account is not verified yet. Enter the code sent to your email, or ask for a new one.'])
                ->withInput($request->only('dwcc_email'));
        }

        // account was deactivated by the admin
        if ($user->status !== 'active') {
            ActivityLog::record($user->school_id_num, $user->role, 'LOGIN_BLOCKED', 'Account is inactive');

            return back()
                ->withErrors(['dwcc_email' => 'Your account has been deactivated. Please ask the admin to reactivate it.'])
                ->withInput($request->only('dwcc_email'));
        }

        // everything is fine
        // log the user in
        Auth::login($user);

        // makes new session id when user logs in
        $request->session()->regenerate();

        ActivityLog::record($user->school_id_num, $user->role, 'LOGIN', 'User logged in');

        // each role goes to its own dashboard
        if ($user->role === 'admin') {
            return redirect('/admin/dashboard');
        }
        if ($user->role === 'teacher') {
            return redirect('/teacher/dashboard');
        }

        return redirect('/student/dashboard');
    }

    // logout method
    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::record(Auth::user()->school_id_num, Auth::user()->role, 'LOGOUT', 'User logged out');
        }
        // logout
        Auth::logout();

        // wipes data from the user session
        $request->session()->invalidate();

        // makes new token (cross site request forgery token) for the next user
        $request->session()->regenerateToken();

        // redirect to login page
        return redirect('/login');
    }
}
