<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\QuestionBankController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\StudentQuizController;
use App\Http\Controllers\TeacherResultController;
use App\Http\Controllers\TeacherSettingsController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\AttemptController;
use App\Http\Controllers\ViolationLogController;
use App\Http\Controllers\TeacherReportController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Redirect home to login page
Route::get('/', function () {
    return redirect('/login');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/logout', [AuthController::class, 'logout']);

// OTP verification routes
Route::get('/verify-otp', [OtpController::class, 'showVerify']);
Route::post('/verify-otp', [OtpController::class, 'verify'])->middleware('throttle:5,1');
Route::post('/verify-otp/resend', [OtpController::class, 'resend'])->middleware('throttle:3,1');

// ==========================================
// Admin Routes
// ==========================================
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');
    Route::post('/admin/users', [UserController::class, 'store']);
    Route::post('/admin/users/{id}/toggle', [UserController::class, 'toggle']);
    Route::put('/admin/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::post('/admin/users/import', [UserController::class, 'import']);
    Route::get('/admin/logs', [ActivityLogController::class, 'index'])->name('admin.logs');
});

// ==========================================
// Teacher Routes
// ==========================================
Route::middleware(['auth', 'role:teacher'])->group(function () {
    // Teacher Dashboard
    Route::get('/teacher/dashboard', [DashboardController::class, 'teacher']);

    // Question bank library
    Route::get('/teacher/question-banks', [QuestionBankController::class, 'index']);
    Route::post('/teacher/question-banks', [QuestionBankController::class, 'store']);
    Route::post('/teacher/question-banks/{id}/archive', [QuestionBankController::class, 'archive']);
    Route::post('/teacher/question-banks/{id}/activate', [QuestionBankController::class, 'activate']);
    Route::get('/teacher/question-banks/{id}', [QuestionBankController::class, 'show']);

    // Quizzes
    Route::get('/teacher/quizzes', [QuizController::class, 'index']);
    Route::get('/teacher/quizzes/create', [QuizController::class, 'create']);
    Route::post('/teacher/quizzes', [QuizController::class, 'store']);
    Route::get('/teacher/quizzes/{id}/edit', [QuizController::class, 'edit']);
    Route::post('/teacher/quizzes/{id}', [QuizController::class, 'update']);
    Route::post('/teacher/quizzes/{id}/deactivate', [QuizController::class, 'deactivate']);
    Route::post('/teacher/quizzes/{id}/activate', [QuizController::class, 'activate']);

    // Teacher Subjects
    Route::get('/teacher/subjects', [SubjectController::class, 'index'])->name('teacher.subjects');
    Route::post('/teacher/subjects', [SubjectController::class, 'store'])->name('teacher.subjects.store');
    Route::put('/teacher/subjects/{id}', [SubjectController::class, 'update'])->name('teacher.subjects.update');
    Route::post('/teacher/subjects/{id}/toggle', [SubjectController::class, 'toggle'])->name('teacher.subjects.toggle');

    // Teacher Sections & Enrolled Students
    Route::get('/teacher/sections', [SectionController::class, 'index'])->name('teacher.sections');
    Route::post('/teacher/sections', [SectionController::class, 'store'])->name('teacher.sections.store');
    Route::put('/teacher/sections/{id}', [SectionController::class, 'update'])->name('teacher.sections.update');
    Route::post('/teacher/sections/{id}/lock', [SectionController::class, 'toggleLock'])->name('teacher.sections.lock');
    Route::post('/teacher/sections/{id}/toggle', [SectionController::class, 'toggleStatus'])->name('teacher.sections.toggle');

    Route::get('/teacher/sections/{id}/students', [SectionController::class, 'students'])->name('teacher.sections.students');
    Route::post('/teacher/sections/{id}/students', [SectionController::class, 'enrollStudent'])->name('teacher.sections.enroll');
    Route::delete('/teacher/sections/{sectionId}/students/{studentId}', [SectionController::class, 'removeStudent'])->name('teacher.sections.remove');

    // Quiz results and attempt details
    Route::get('/teacher/quizzes/{id}/results', [TeacherResultController::class, 'results']);
    Route::get('/teacher/attempts/{id}', [TeacherResultController::class, 'attempt']);

    // Quiz report export to teacher email
    Route::post('/teacher/quizzes/{id}/results/email', [TeacherResultController::class, 'emailReport']);

    // Teacher Reports & Email Export
    Route::get('/teacher/reports', [TeacherReportController::class, 'index'])->name('teacher.reports');
    Route::post('/teacher/reports/export', [TeacherReportController::class, 'export'])->name('teacher.reports.export');

    // Settings
    Route::get('/teacher/settings', [TeacherSettingsController::class, 'index']);
    Route::post('/teacher/settings/password', [TeacherSettingsController::class, 'password']);
});

// ==========================================
// Student Routes
// ==========================================
Route::middleware(['auth', 'role:student'])->group(function () {
    // Student Dashboard & Profile
    Route::get('/student/dashboard', [DashboardController::class, 'student'])->name('student.dashboard');
    Route::get('/student/profile', [AttemptController::class, 'profile'])->name('student.profile');

    Route::post('/student/profile/password', [AttemptController::class, 'updatePassword'])->name('student.password.update');

    // Quizzes & Reviewer Sets
    Route::get('/student/quizzes', [StudentQuizController::class, 'index'])->name('student.quizzes');
    Route::get('/student/quizzes/{id}/instructions', [AttemptController::class, 'instructions'])->name('student.instructions');
    Route::post('/student/quizzes/{id}/start', [AttemptController::class, 'start'])->name('student.quizzes.start');

    Route::get('/student/reviewer', [QuestionController::class, 'reviewerIndex'])->name('student.reviewer');
    Route::get('/student/reviewer/{id}', [QuestionController::class, 'practiceSet'])->name('student.reviewer.practice');

    // Take the quiz
    Route::get('/student/attempts/{id}', [AttemptController::class, 'show'])->name('student.exam');
    Route::post('/student/attempts/{id}/answer', [AttemptController::class, 'saveAnswer']);
    Route::post('/student/attempts/{id}/submit', [AttemptController::class, 'submit']);
    Route::get('/student/attempts/{id}/result', [AttemptController::class, 'result'])->name('student.submitted');

    // Anti-cheat
    Route::post('/student/attempts/{id}/violation', [ViolationLogController::class, 'store']);
    Route::post('/student/attempts/{id}/violation/{violationId}/return', [ViolationLogController::class, 'logReturn']);

    // Download/Review Pdf
    Route::get('/student/attempts/{id}/pdf', [AttemptController::class, 'downloadReviewPdf']);
    Route::post('/student/attempts/{id}/email-pdf', [AttemptController::class, 'emailReviewPdf']);
});
