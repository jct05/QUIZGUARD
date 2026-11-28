<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Section;
use App\Models\SectionStudent;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SectionController extends Controller
{
    // Display all sections owned by the logged-in teacher.
    public function index(Request $request)
    {
        $query = Section::where('teacher_id', Auth::id())
            ->with('subject')
            ->withCount('students');

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where('section_name', 'like', "%$q%");
        }

        $sections = $query->orderBy('section_name')->paginate(15)->withQueryString();
        $subjects = Subject::where('teacher_id', Auth::id())
            ->where('status', 'active')
            ->orderBy('subject_code')
            ->get();

        return view('teacher.sections', [
            'sections' => $sections,
            'subjects' => $subjects,
        ]);
    }

    // Normalize section name into uppercase before uniqueness check and store
    public function store(Request $request)
    {
        $request->merge([
            'section_name' => strtoupper(trim((string) $request->section_name)),
        ]);

        $request->validate([
            'subject_id'   => 'required|integer|exists:subjects,subject_id',
            'section_name' => [
                'required',
                'string',
                'max:45',
                Rule::unique('sections')->where(fn($q) => $q->where('teacher_id', Auth::id())->where('subject_id', $request->subject_id)),
            ],
        ], [
            'section_name.unique' => 'A section with this name already exists for the selected subject.',
        ]);

        Section::create([
            'teacher_id'   => Auth::id(),
            'subject_id'   => $request->subject_id,
            'section_name' => $request->section_name,
            'status'       => 'active',
            'is_locked'    => 0,
        ]);

        return back()->with('success', 'Section created successfully.');
    }

    // Validate and update an existing class section.
    public function update(Request $request, $id)
    {
        $section = Section::where('section_id', $id)
            ->where('teacher_id', Auth::id())
            ->firstOrFail();

        $request->merge([
            'section_name' => strtoupper(trim((string) $request->section_name)),
        ]);

        $request->validate([
            'subject_id'   => 'required|integer',
            'section_name' => [
                'required',
                'string',
                'max:45',
                Rule::unique('sections', 'section_name')
                    ->ignore($section->section_id, 'section_id')
                    ->where(fn($q) => $q->where('teacher_id', Auth::id())->where('subject_id', $request->subject_id)),
            ],
        ], [
            'section_name.unique' => 'A section with this name already exists for the selected subject.',
        ]);

        // Ensure the selected subject belongs to this teacher.
        $subject = Subject::where('subject_id', $request->subject_id)
            ->where('teacher_id', Auth::id())
            ->firstOrFail();

        $section->update([
            'subject_id'   => $subject->subject_id,
            'section_name' => $request->section_name,
        ]);

        ActivityLog::record(
            Auth::user()->school_id_num,
            Auth::user()->role,
            'SECTION_UPDATED',
            'Updated section: ' . $section->section_name
        );

        return redirect('/teacher/sections')->with('success', 'Section updated successfully.');
    }

    // Lock or unlock enrollment for a class section.
    public function toggleLock($id)
    {
        $section = Section::where('section_id', $id)
            ->where('teacher_id', Auth::id())
            ->firstOrFail();

        $section->update([
            'is_locked' => !$section->is_locked,
        ]);

        $state = $section->is_locked ? 'LOCKED' : 'UNLOCKED';

        ActivityLog::record(
            Auth::user()->school_id_num,
            Auth::user()->role,
            'SECTION_' . $state,
            $state . ' section: ' . $section->section_name
        );

        return back()->with('success', 'Section is now ' . strtolower($state) . '.');
    }

    // Toggle a section status between active and inactive.
    public function toggleStatus($id)
    {
        $section = Section::with('subject')
            ->where('section_id', $id)
            ->where('teacher_id', Auth::id())
            ->firstOrFail();

        $newStatus = $section->status === 'active' ? 'inactive' : 'active';

        if ($newStatus === 'active' && $section->subject && $section->subject->status !== 'active') {
            return back()->with('error', 'Cannot activate section while its parent subject is inactive.');
        }

        $section->update(['status' => $newStatus]);

        return back()->with('success', 'Section is now ' . $section->status . '.');
    }

    // Show all students enrolled in this class section.
    public function students(Request $request, $id)
    {
        $section = Section::where('section_id', $id)
            ->where('teacher_id', Auth::id())
            ->with('subject')
            ->firstOrFail();

        $enrolledQuery = $section->students();

        if ($request->filled('q')) {
            $q = trim($request->q);
            $enrolledQuery->where(function ($w) use ($q) {
                $w->where('first_name', 'like', "%$q%")
                    ->orWhere('last_name', 'like', "%$q%")
                    ->orWhere('school_id_num', 'like', "%$q%")
                    ->orWhere('dwcc_email', 'like', "%$q%");
            });
        }

        $enrolledStudents = $enrolledQuery->orderBy('last_name')->paginate(20)->withQueryString();

        // Get active students not yet enrolled in this section.
        $enrolledIds = $section->sectionStudents()->pluck('student_id');
        $availableStudents = User::where('role', 'student')
            ->where('status', 'active')
            ->whereNotIn('user_id', $enrolledIds)
            ->orderBy('last_name')
            ->get();

        return view('teacher.section_students', [
            'section'           => $section,
            'enrolledStudents'  => $enrolledStudents,
            'availableStudents' => $availableStudents,
        ]);
    }

    // Enroll an active student into this class section.
    public function enrollStudent(Request $request, $id)
    {
        $section = Section::where('section_id', $id)
            ->where('teacher_id', Auth::id())
            ->firstOrFail();

        if ($section->is_locked) {
            return back()->withErrors(['section' => 'This section is locked. Unlock it first to enroll students.']);
        }

        $request->validate([
            'student_id' => 'required|integer',
        ]);

        $student = User::where('user_id', $request->student_id)
            ->where('role', 'student')
            ->where('status', 'active')
            ->firstOrFail();

        SectionStudent::firstOrCreate([
            'section_id' => $section->section_id,
            'student_id' => $student->user_id,
        ]);

        ActivityLog::record(
            Auth::user()->school_id_num,
            Auth::user()->role,
            'STUDENT_ENROLLED',
            'Enrolled student ' . $student->school_id_num . ' in section ' . $section->section_name
        );

        return back()->with('success', $student->first_name . ' ' . $student->last_name . ' added to section.');
    }

    // Remove an enrolled student from this class section.
    public function removeStudent($sectionId, $studentId)
    {
        $section = Section::where('section_id', $sectionId)
            ->where('teacher_id', Auth::id())
            ->firstOrFail();

        SectionStudent::where('section_id', $section->section_id)
            ->where('student_id', $studentId)
            ->delete();

        ActivityLog::record(
            Auth::user()->school_id_num,
            Auth::user()->role,
            'STUDENT_REMOVED',
            'Removed student ID ' . $studentId . ' from section ' . $section->section_name
        );

        return back()->with('success', 'Student removed from section.');
    }
}