<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Return the list of available school departments.
    private function getDepartments()
    {
        return [
            'SIT',
            'SOA',
            'SOE',
            'SBHTM',
        ];
    }

    // Build default uppercase lastname plus school ID password
    private function generateDefaultPassword(string $lastName, string $schoolIdNum): string
    {
        $cleanLast = strtoupper(preg_replace('/\s+/', '', trim($lastName)));
        $cleanId   = trim($schoolIdNum);

        return $cleanLast . $cleanId;
    }

    // Display paginated users list with optional filters.
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role'))       $query->where('role', $request->role);
        if ($request->filled('department')) $query->where('department', $request->department);
        if ($request->filled('status'))     $query->where('status', $request->status);

        // Search by first name, last name, or email.
        if ($request->filled('q')) {
            foreach (preg_split('/\s+/', trim($request->q)) as $word) {
                $query->where(function ($w) use ($word) {
                    $w->where('first_name', 'like', "%$word%")
                        ->orWhere('last_name', 'like', "%$word%")
                        ->orWhere('dwcc_email', 'like', "%$word%");
                });
            }
        }

        $users = $query->orderBy('last_name')->paginate(20)->withQueryString();

        // Count users for the All, Students, and Teachers tabs.
        $counts = [
            'all'      => User::count(),
            'students' => User::where('role', 'student')->count(),
            'teachers' => User::where('role', 'teacher')->count(),
        ];

        $departments = $this->getDepartments();

        return view('admin.users', [
            'users'       => $users,
            'counts'      => $counts,
            'departments' => $departments,
        ]);
    }

    // Validate and create a new user account.
    public function store(Request $request)
    {
        // Normalize role and uppercase department before validation
        $request->merge([
            'role'       => strtolower(trim((string) $request->role)),
            'department' => strtoupper(trim((string) $request->department)),
        ]);

        // Enforce minimum five digit school ID validation
        $request->validate([
            'role'          => 'required|in:student,teacher,admin',
            'school_id_num' => 'required|integer|min:10000|unique:users,school_id_num',
            'first_name'    => 'required|max:45',
            'middle_name'   => 'nullable|max:45',
            'last_name'     => 'required|max:45',
            'dwcc_email'    => 'required|email|max:45|unique:users,dwcc_email',
            'password'      => 'nullable|min:8',
            'department'    => ['required', 'string', 'max:45', Rule::in($this->getDepartments())],
        ], [
            'school_id_num.min' => 'School ID number must be at least 5 digits.',
        ]);

        // Generate default password when input field is left blank
        $rawPassword = $request->filled('password')
            ? $request->password
            : $this->generateDefaultPassword($request->last_name, (string) $request->school_id_num);

        $user = User::create([
            'role'          => $request->role,
            'school_id_num' => $request->school_id_num,
            'first_name'    => $request->first_name,
            'middle_name'   => $request->middle_name,
            'last_name'     => $request->last_name,
            'dwcc_email'    => $request->dwcc_email,
            'password_hash' => Hash::make($rawPassword),
            'department'    => $request->department,
            'status'        => 'pending',
        ]);

        ActivityLog::record(
            Auth::user()->school_id_num,
            Auth::user()->role,
            'USER_CREATED',
            'Created ' . $user->role . ' account: ' . $user->school_id_num
        );

        try {
            $user->sendOtp();
        } catch (\Exception $e) {
            return redirect('/admin/users')->with('success', 'User created, but the email could not be sent.');
        }

        return redirect('/admin/users')->with('success', 'User created. A verification code was sent to their email.');
    }

    // Validate and update an existing user account.
    public function update(Request $request, $id)
    {
        $user = User::where('user_id', $id)->firstOrFail();
        $oldDept = $user->department;

        // Normalize role and uppercase department before validation
        $request->merge([
            'role'       => strtolower(trim((string) $request->role)),
            'department' => strtoupper(trim((string) $request->department)),
        ]);

        // Enforce minimum five digit school ID validation
        $request->validate([
            'role'          => 'required|in:student,teacher,admin',
            'school_id_num' => ['required', 'integer', 'min:10000', Rule::unique('users', 'school_id_num')->ignore($user->user_id, 'user_id')],
            'first_name'    => 'required|max:45',
            'middle_name'   => 'nullable|max:45',
            'last_name'     => 'required|max:45',
            'dwcc_email'    => ['required', 'email', 'max:45', Rule::unique('users', 'dwcc_email')->ignore($user->user_id, 'user_id')],
            'department'    => ['required', 'string', 'max:45', Rule::in($this->getDepartments())],
            'password'      => 'nullable|min:8',
        ], [
            'school_id_num.min' => 'School ID number must be at least 5 digits.',
        ]);

        // Prevent the logged-in admin from demoting their own role.
        if ($user->user_id == Auth::id() && $request->role !== 'admin') {
            return back()
                ->withErrors(['role' => 'You cannot change your own role or demote yourself.'])
                ->withInput();
        }

        $updateData = [
            'role'          => $request->role,
            'school_id_num' => $request->school_id_num,
            'first_name'    => $request->first_name,
            'middle_name'   => $request->middle_name,
            'last_name'     => $request->last_name,
            'dwcc_email'    => $request->dwcc_email,
            'department'    => $request->department,
        ];

        // Only update the password if a new one was entered.
        if ($request->filled('password')) {
            $updateData['password_hash'] = Hash::make($request->password);
        }

        $user->update($updateData);

        if ($user->role === 'teacher' && $oldDept !== $request->department) {
            ActivityLog::record(
                Auth::user()->school_id_num,
                Auth::user()->role,
                'DEPARTMENT_CHANGED',
                "Changed teacher {$user->school_id_num} department from {$oldDept} to {$request->department}"
            );
        }

        ActivityLog::record(
            Auth::user()->school_id_num,
            Auth::user()->role,
            'USER_UPDATED',
            'Updated user account: ' . $user->school_id_num
        );

        return redirect('/admin/users')->with('success', 'User credentials updated successfully.');
    }

    // Toggle a user account between active and inactive.
    public function toggle($id)
    {
        $user = User::where('user_id', $id)->firstOrFail();

        if ($user->status === 'pending') {
            return back()->withErrors(['user' => 'This account must be activated with its OTP.']);
        }

        if ($user->user_id == Auth::id()) {
            return back()->withErrors(['user' => 'You cannot deactivate your own account.']);
        }

        // Prevent deactivating teacher with active classes or sessions
        if ($user->role === 'teacher' && $user->status === 'active') {
            $hasActiveClasses = Subject::where('teacher_id', $user->user_id)->where('status', 'active')->exists()
                || Section::where('teacher_id', $user->user_id)->where('status', 'active')->exists()
                || Quiz::where('teacher_id', $user->user_id)->where('status', 'active')->exists();

            $hasLiveSession = Schema::hasTable('sessions')
                && DB::table('sessions')
                ->where('user_id', $user->user_id)
                ->where('last_activity', '>=', now()->subMinutes(15)->timestamp)
                ->exists();

            if ($hasActiveClasses || $hasLiveSession) {
                return back()->withErrors([
                    'user' => 'Cannot deactivate this teacher while they are currently active or handling active subjects, sections, or quizzes.',
                ]);
            }
        }

        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);

        ActivityLog::record(
            Auth::user()->school_id_num,
            Auth::user()->role,
            'USER_' . strtoupper($user->status),
            'Set user ' . $user->school_id_num . ' to ' . $user->status
        );

        return redirect('/admin/users')->with('success', 'User is now ' . $user->status . '.');
    }

    // Import multiple user accounts from an uploaded CSV.
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);

        set_time_limit(300);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($handle);

        if (!$header) {
            return back()->withErrors(['file' => 'The file is empty.']);
        }

        // Remove invisible Excel BOM characters and clean header column names.
        $header[0] = ltrim($header[0], "\xEF\xBB\xBF");
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        $departments = $this->getDepartments();
        $created = 0;
        $errors  = [];
        $line    = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $line++;

            if (count(array_filter($row, fn($v) => trim($v) !== '')) === 0) {
                continue;
            }

            if (count($row) !== count($header)) {
                $errors[] = "Line $line: wrong number of columns.";
                continue;
            }

            $data = array_combine($header, array_map('trim', $row));

            // Normalize role and uppercase department before CSV validation
            $data['role']       = strtolower($data['role'] ?? '');
            $data['department'] = strtoupper($data['department'] ?? '');

            // Enforce minimum five digit school ID validation
            $validator = Validator::make($data, [
                'role'          => 'required|in:student,teacher,admin',
                'school_id_num' => 'required|integer|min:10000|unique:users,school_id_num',
                'first_name'    => 'required|max:45',
                'middle_name'   => 'nullable|max:45',
                'last_name'     => 'required|max:45',
                'dwcc_email'    => 'required|email|max:45|unique:users,dwcc_email',
                'password'      => 'nullable|min:8',
                'department'    => ['required', 'string', 'max:45', Rule::in($departments)],
            ], [
                'school_id_num.min' => 'School ID number must be at least 5 digits.',
                'department.in'     => 'The department "' . $data['department'] . '" is invalid. Allowed: ' . implode(', ', $departments) . '.',
            ]);

            if ($validator->fails()) {
                $errors[] = "Line $line: " . $validator->errors()->first();
                continue;
            }

            // Generate default password when CSV cell is empty
            $rawPassword = !empty($data['password'])
                ? $data['password']
                : $this->generateDefaultPassword($data['last_name'], (string) $data['school_id_num']);

            $user = User::create([
                'role'          => $data['role'],
                'school_id_num' => $data['school_id_num'],
                'first_name'    => $data['first_name'],
                'middle_name'   => !empty($data['middle_name']) ? $data['middle_name'] : null,
                'last_name'     => $data['last_name'],
                'dwcc_email'    => $data['dwcc_email'],
                'password_hash' => Hash::make($rawPassword),
                'department'    => $data['department'],
                'status'        => 'pending',
            ]);

            try {
                $user->sendOtp();
            } catch (\Exception $e) {
                $errors[] = "Line $line: user created, but the email could not be sent.";
            }

            $created++;
        }

        fclose($handle);

        ActivityLog::record(
            Auth::user()->school_id_num,
            Auth::user()->role,
            'USERS_IMPORTED',
            "Imported $created users from CSV"
        );

        return redirect('/admin/users')
            ->with('success', "$created users imported.")
            ->withErrors(array_slice($errors, 0, 10));
    }
}
