<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class TeacherSettingsController extends Controller
{
    // profile page (read-only) and the change-password form
    public function index()
    {
        return view('teacher.settings', ['user' => Auth::user()]);
    }

    public function password(Request $request)
    {
        // current_password checks the typed password against the saved one
        // confirmed expects a second field called password_confirmation
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|confirmed',
        ], [
            'current_password.required' => 'Please enter your current password.',
            'password.required'         => 'Please enter a new password.',
            'password.confirmed'        => 'New password and confirmation do not match.',
        ]);

        $missing = $this->getMissingPasswordRequirements($request->password);
        if (!empty($missing)) {
            return back()->withErrors([
                'password' => 'New password is missing: ' . implode(', ', $missing) . '.',
            ]);
        }

        $user = Auth::user();

        // Verify current password unless user just completed OTP verification
        $skipCurrentCheck = session('prompt_password_change') && $request->current_password === '••••••••';
        if (!$skipCurrentCheck && !Hash::check($request->current_password, $user->password_hash)) {
            return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
        }

        User::where('user_id', $user->user_id)->update([
            'password_hash' => Hash::make($request->password),
        ]);

        // Clear verification prompt flag after saving password
        session()->forget('prompt_password_change');

        ActivityLog::record(Auth::user()->school_id_num, Auth::user()->role, 'PASSWORD_CHANGED', 'Changed own password');

        return back()->with('success', 'Password changed.');
    }

    // Validate password complexity against SQL safe character whitelist
    private function getMissingPasswordRequirements(string $password): array
    {
        $missing        = [];
        $allowedSpecial = '!@#$%^&()_+?.,';

        if (strlen($password) < 8) {
            $missing[] = 'at least 8 characters';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $missing[] = '1 capital letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $missing[] = '1 number';
        }
        if (!preg_match('/[!@#$%^&()_+?.,]/', $password)) {
            $missing[] = '1 allowed special character (' . $allowedSpecial . ')';
        }
        if ($password !== '' && !preg_match('/^[A-Za-z0-9!@#$%^&()_+?.,]+$/', $password)) {
            $missing[] = 'only SQL-safe allowed characters (no spaces, quotes, or ; - =)';
        }

        return $missing;
    }
}
