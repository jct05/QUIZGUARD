<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Subject;
use App\Models\Section;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    // Display all subjects owned by the logged-in teacher.
    public function index(Request $request)
    {
        $query = Subject::where('teacher_id', Auth::id())
            ->withCount('sections');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($w) use ($q) {
                $w->where('subject_code', 'like', "%$q%")
                  ->orWhere('subject_name', 'like', "%$q%");
            });
        }

        $subjects = $query->orderBy('subject_code')->paginate(15)->withQueryString();

        return view('teacher.subjects', ['subjects' => $subjects]);
    }

    // Validate and create a new teacher subject.
    public function store(Request $request)
    {
        $request->validate([
            'subject_code' => 'required|string|max:45',
            'subject_name' => 'required|string|max:100',
        ]);

        $subject = Subject::create([
            'teacher_id'   => Auth::id(),
            'subject_code' => strtoupper(trim($request->subject_code)),
            'subject_name' => trim($request->subject_name),
            'status'       => 'active',
        ]);

        ActivityLog::record(
            Auth::user()->school_id_num,
            Auth::user()->role,
            'SUBJECT_CREATED',
            'Created subject: ' . $subject->subject_code . ' - ' . $subject->subject_name
        );

        return redirect('/teacher/subjects')->with('success', 'Subject created successfully.');
    }

    // Validate and update an existing teacher subject.
    public function update(Request $request, $id)
    {
        $subject = Subject::where('subject_id', $id)
            ->where('teacher_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'subject_code' => 'required|string|max:45',
            'subject_name' => 'required|string|max:100',
        ]);

        $subject->update([
            'subject_code' => strtoupper(trim($request->subject_code)),
            'subject_name' => trim($request->subject_name),
        ]);

        ActivityLog::record(
            Auth::user()->school_id_num,
            Auth::user()->role,
            'SUBJECT_UPDATED',
            'Updated subject: ' . $subject->subject_code
        );

        return redirect('/teacher/subjects')->with('success', 'Subject updated successfully.');
    }

    // Toggle a subject status between active and inactive.
    public function toggle($id)
    {
        $subject = Subject::where('subject_id', $id)
            ->where('teacher_id', Auth::id())
            ->firstOrFail();

        $newStatus = $subject->status === 'active' ? 'inactive' : 'active';

        DB::transaction(function () use ($subject, $newStatus) {
            $subject->update(['status' => $newStatus]);

            Section::where('subject_id', $subject->subject_id)
                ->where('teacher_id', Auth::id())
                ->update(['status' => $newStatus]);
        });

        ActivityLog::record(
            Auth::user()->school_id_num,
            Auth::user()->role,
            'SUBJECT_' . strtoupper($newStatus),
            'Set subject ' . $subject->subject_code . ' and its sections to ' . $newStatus
        );

        return back()->with('success', 'Subject and all its sections are now ' . $newStatus . '.');
    }
}