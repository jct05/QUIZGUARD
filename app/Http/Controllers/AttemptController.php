<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attempt;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\ViolationLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class AttemptController extends Controller
{
    // Display quiz rules and instructions before starting
    public function instructions($quizId)
    {
        $quiz = Quiz::with('subject')->where('quiz_id', $quizId)->firstOrFail();

        return view('student.instructions', [
            'quiz' => $quiz,
        ]);
    }

    // Start a new quiz attempt or resume ongoing
    public function start(Request $request, $quizId)
    {
        $now       = now();
        $user      = Auth::user();
        $studentId = (int) ($user->user_id ?? $user->id ?? 0);
        $quiz      = Quiz::where('quiz_id', $quizId)->where('status', 'active')->firstOrFail();

        if ($quiz->open_at && $now->lt($quiz->open_at)) {
            return back()->with('error', 'This quiz is not open yet.');
        }

        if ($quiz->closed_at && $now->gt($quiz->closed_at)) {
            return back()->with('error', 'This quiz has already closed.');
        }

        // Auto enroll student into section upon taking quiz
        $pivotTable = Schema::hasTable('section_students') ? 'section_students' : 'section_student';
        if (Schema::hasTable($pivotTable) && $quiz->section_id) {
            $alreadyEnrolled = DB::table($pivotTable)
                ->where('section_id', $quiz->section_id)
                ->where('student_id', $studentId)
                ->exists();

            if (!$alreadyEnrolled) {
                DB::table($pivotTable)->insert([
                    'section_id' => $quiz->section_id,
                    'student_id' => $studentId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Resume any unfinished attempt for this quiz
        $ongoing = Attempt::where('quiz_id', $quiz->quiz_id)
            ->where('student_id', $studentId)
            ->where('status', 'in_progress')
            ->first();

        if ($ongoing) {
            return redirect('/student/attempts/' . $ongoing->attempt_id);
        }

        $usedAttempts = Attempt::where('quiz_id', $quiz->quiz_id)
            ->where('student_id', $studentId)
            ->count();

        if ($quiz->attempts_allowed && $usedAttempts >= $quiz->attempts_allowed) {
            return back()->with('error', 'You have reached the maximum allowed attempts for this quiz.');
        }

        $questions   = QuestionController::getQuizQuestions($quiz);
        $totalPoints = $questions->sum('points');

        $attempt = Attempt::create([
            'quiz_id'         => $quiz->quiz_id,
            'student_id'      => $studentId,
            'started_at'      => $now,
            'score'           => 0,
            'total_points'    => $totalPoints,
            'status'          => 'in_progress',
            'violation_count' => 0,
        ]);

        return redirect('/student/attempts/' . $attempt->attempt_id);
    }

    // Render active exam room with saved student answers
    public function show($attemptId)
    {
        $attempt = Attempt::with('quiz.subject')
            ->where('attempt_id', $attemptId)
            ->where('student_id', Auth::id())
            ->firstOrFail();

        if ($attempt->status !== 'in_progress') {
            return redirect('/student/attempts/' . $attempt->attempt_id . '/result');
        }

        $quiz = $attempt->quiz;
        $totalTimeSec = (int) (($quiz->time_limit_min ?? 30) * 60);

        // Compute exact elapsed seconds from started_at timestamp
        $startedAt = $attempt->started_at ? \Carbon\Carbon::parse($attempt->started_at) : now();
        $elapsedSec = max(0, (int) now()->diffInSeconds($startedAt));
        $remainingSec = max(0, $totalTimeSec - $elapsedSec);

        // Auto-submit if time limit already expired
        if ($quiz && $quiz->time_limit_min && $remainingSec <= 0) {
            $this->finalizeAttempt($attempt, 'auto_submitted');
            return redirect('/student/attempts/' . $attempt->attempt_id . '/result');
        }

        $questions = QuestionController::getQuizQuestions($quiz);
        $answers   = DB::table('student_answers')
            ->where('attempt_id', $attempt->attempt_id)
            ->get();

        return view('student.exam', [
            'attempt'      => $attempt,
            'quiz'         => $quiz,
            'questions'    => $questions,
            'answers'      => $answers,
            'remainingSec' => $remainingSec,
        ]);
    }

    // Validate current password and update user password hash
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|confirmed',
        ], [
            'current_password.required' => 'Please enter your current password.',
            'new_password.required'     => 'Please enter a new password.',
            'new_password.confirmed'    => 'New password and confirmation do not match.',
        ]);

        $missing = $this->getMissingPasswordRequirements($request->new_password);
        if (!empty($missing)) {
            return back()->withErrors([
                'new_password' => 'New password is missing: ' . implode(', ', $missing) . '.',
            ]);
        }

        $user = Auth::user();

        // Verify current password unless user just completed OTP verification
        $skipCurrentCheck = session('prompt_password_change') && $request->current_password === '••••••••';
        if (!$skipCurrentCheck && !Hash::check($request->current_password, $user->password_hash)) {
            return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
        }

        User::where('user_id', $user->user_id)->update([
            'password_hash' => Hash::make($request->new_password),
        ]);

        // Clear verification prompt flag after saving password
        session()->forget('prompt_password_change');

        ActivityLog::record($user->school_id_num, $user->role, 'PASSWORD_CHANGED', 'Changed own password');

        return back()->with('success', 'Your password has been updated successfully.');
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

    // Save or update student answer during active attempt
    public function saveAnswer(Request $request, $attemptId)
    {
        $request->validate([
            'question_id'    => 'required|integer',
            'student_answer' => 'nullable|string',
        ]);

        $attempt = Attempt::with('quiz')
            ->where('attempt_id', $attemptId)
            ->where('student_id', Auth::id())
            ->where('status', 'in_progress')
            ->firstOrFail();

        $question = Question::where('question_id', $request->question_id)
            ->where('bank_id', $attempt->quiz->bank_id)
            ->firstOrFail();

        $studentAnswer = trim((string) $request->student_answer);
        $isCorrect     = $this->isAnswerCorrect($question, $studentAnswer);

        $existing = DB::table('student_answers')
            ->where('attempt_id', $attempt->attempt_id)
            ->where('question_id', $question->question_id)
            ->first();

        // Block modifying prior question answers when backtracking is disabled
        if ($existing && ($attempt->quiz->disable_backtracking ?? false)) {
            return response()->json(['saved' => false, 'locked' => true], 403);
        }

        if ($existing) {
            DB::table('student_answers')
                ->where('answer_id', $existing->answer_id)
                ->update([
                    'student_answer' => $studentAnswer,
                    'is_correct'     => $isCorrect ? 1 : 0,
                    'answered_at'    => now(),
                    'updated_at'     => now(),
                ]);
        } else {
            DB::table('student_answers')->insert([
                'attempt_id'     => $attempt->attempt_id,
                'question_id'    => $question->question_id,
                'student_answer' => $studentAnswer,
                'is_correct'     => $isCorrect ? 1 : 0,
                'answered_at'    => now(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        return response()->json(['saved' => true]);
    }

    // Ensure all quiz questions are answered before manual submit
    public function submit(Request $request, $attemptId)
    {
        $attempt = Attempt::with('quiz')
            ->where('attempt_id', $attemptId)
            ->where('student_id', Auth::id())
            ->firstOrFail();

        if ($attempt->status === 'in_progress') {
            $totalQuestions = QuestionController::getQuizQuestions($attempt->quiz)->count();
            $answeredCount  = DB::table('student_answers')
                ->where('attempt_id', $attempt->attempt_id)
                ->where('student_answer', '!=', '')
                ->count();

            if ($totalQuestions > 0 && $answeredCount < $totalQuestions) {
                return redirect('/student/attempts/' . $attempt->attempt_id)
                    ->with('error', 'Please answer all ' . $totalQuestions . ' questions before submitting your quiz.');
            }

            $this->finalizeAttempt($attempt, 'submitted');
        }

        return redirect('/student/attempts/' . $attempt->attempt_id . '/result');
    }

    // Display submission summary, score, and violation audit
    public function result($attemptId)
    {
        $attempt = Attempt::with('quiz.subject')
            ->where('attempt_id', $attemptId)
            ->where('student_id', Auth::id())
            ->firstOrFail();

        $violations = ViolationLog::where('attempt_id', $attempt->attempt_id)
            ->orderBy('occurred_at')
            ->get();

        $answeredCount = DB::table('student_answers')
            ->where('attempt_id', $attempt->attempt_id)
            ->where('student_answer', '!=', '')
            ->count();

        $reviewAnswers = collect();
        if ($attempt->quiz && $attempt->quiz->allow_review) {
            $reviewAnswers = DB::table('student_answers')
                ->join('questions', 'student_answers.question_id', '=', 'questions.question_id')
                ->where('student_answers.attempt_id', $attempt->attempt_id)
                ->select('student_answers.*', 'questions.question_text', 'questions.correct_answers', 'questions.points')
                ->orderBy('student_answers.answer_id')
                ->get();
        }

        return view('student.submitted', [
            'attempt'       => $attempt,
            'quiz'          => $attempt->quiz,
            'violations'    => $violations,
            'answeredCount' => $answeredCount,
            'reviewAnswers' => $reviewAnswers,
        ]);
    }

    // Attach reviewable answers for attempts permitting student review
    public function profile()
    {
        $attempts = Attempt::with(['quiz.subject', 'violationLogs'])
            ->where('student_id', Auth::id())
            ->where('status', '!=', 'in_progress')
            ->orderByDesc('submitted_at')
            ->get();

        $reviewMap = [];
        $reviewableAttemptIds = $attempts
            ->filter(fn($a) => $a->quiz && $a->quiz->allow_review)
            ->pluck('attempt_id');

        if ($reviewableAttemptIds->isNotEmpty()) {
            $answers = DB::table('student_answers')
                ->join('questions', 'student_answers.question_id', '=', 'questions.question_id')
                ->whereIn('student_answers.attempt_id', $reviewableAttemptIds)
                ->select('student_answers.*', 'questions.question_text', 'questions.correct_answers', 'questions.points')
                ->orderBy('student_answers.answer_id')
                ->get()
                ->groupBy('attempt_id');

            foreach ($answers as $attId => $rows) {
                $reviewMap[$attId] = $rows->values()->all();
            }
        }

        return view('student.profile', [
            'attempts'  => $attempts,
            'reviewMap' => $reviewMap,
        ]);
    }

    // Regrade saved student answers and finalize attempt score
    public function finalizeAttempt(Attempt $attempt, string $status = 'submitted'): void
    {
        $savedRows = DB::table('student_answers')
            ->where('attempt_id', $attempt->attempt_id)
            ->get();

        $questions = Question::whereIn('question_id', $savedRows->pluck('question_id'))
            ->get()
            ->keyBy('question_id');

        $earnedScore = 0;
        foreach ($savedRows as $row) {
            $question = $questions->get($row->question_id);
            if (!$question) continue;

            $isCorrect = $this->isAnswerCorrect($question, trim((string) $row->student_answer));

            if ((int) $row->is_correct !== ($isCorrect ? 1 : 0)) {
                DB::table('student_answers')
                    ->where('answer_id', $row->answer_id)
                    ->update(['is_correct' => $isCorrect ? 1 : 0]);
            }

            if ($isCorrect) {
                $earnedScore += (int) ($question->points ?? 1);
            }
        }

        $totalPoints = $attempt->total_points > 0
            ? $attempt->total_points
            : QuestionController::getQuizQuestions($attempt->quiz)->sum('points');

        $attempt->update([
            'score'        => (int) $earnedScore,
            'total_points' => (int) $totalPoints,
            'submitted_at' => now(),
            'status'       => $status,
        ]);

        if ($status === 'auto_submitted' && Auth::check()) {
            ActivityLog::record(
                Auth::user()->school_id_num,
                Auth::user()->role,
                'QUIZ_AUTO_SUBMITTED',
                'Attempt ' . $attempt->attempt_id . ' auto-submitted'
            );
        }
    }

    // Match student answer against keys, indices, or values
    private function isAnswerCorrect(Question $question, string $studentAnswer): bool
    {
        if ($studentAnswer === '') {
            return false;
        }

        $rawCorrect = $question->correct_answers;
        $acceptedList = [];

        if (is_array($rawCorrect)) {
            $acceptedList = $rawCorrect;
        } else {
            $decoded = json_decode((string) $rawCorrect, true);
            $acceptedList = is_array($decoded) ? $decoded : [(string) $rawCorrect];
        }

        $options = is_array($question->options)
            ? $question->options
            : json_decode((string) ($question->options ?? ''), true);

        // Expand accepted values to include matching option keys and texts
        $expandedAccepted = [];
        foreach ($acceptedList as $item) {
            $val = trim((string) $item);
            if ($val === '') continue;
            $expandedAccepted[] = $val;

            if (is_array($options)) {
                // If correct_answers holds key (e.g. "A" or "0"), also accept its text
                if (array_key_exists($val, $options)) {
                    $expandedAccepted[] = trim((string) $options[$val]);
                } elseif (ctype_alpha($val) && strlen($val) === 1) {
                    $letterIdx = ord(strtoupper($val)) - 65;
                    if (array_key_exists($letterIdx, $options)) {
                        $expandedAccepted[] = trim((string) $options[$letterIdx]);
                    }
                }

                // If correct_answers holds text, also accept its key
                foreach ($options as $optKey => $optText) {
                    if (strcasecmp(trim((string) $optText), $val) === 0) {
                        $expandedAccepted[] = trim((string) $optKey);
                    }
                }
            }
        }

        foreach ($expandedAccepted as $accepted) {
            if (strcasecmp($accepted, $studentAnswer) === 0) {
                return true;
            }
        }

        return false;
    }

    // Download student quiz review as formatted PDF file
    public function downloadReviewPdf($attemptId)
    {
        $payload = $this->buildStudentReviewPdfPayload($attemptId);
        $pdf     = \Barryvdh\DomPDF\Facade\Pdf::loadView('student.attempt_pdf', $payload)->setPaper('a4', 'portrait');
        $slug    = preg_replace('/[^A-Za-z0-9_-]/', '_', $payload['attempt']->quiz->title ?? 'quiz_review');

        return $pdf->download($slug . '_review.pdf');
    }

    // Email student quiz review PDF when review allowed
    public function emailReviewPdf($attemptId)
    {
        $user    = Auth::user();
        $payload = $this->buildStudentReviewPdfPayload($attemptId);
        $pdfOut  = \Barryvdh\DomPDF\Facade\Pdf::loadView('student.attempt_pdf', $payload)->setPaper('a4', 'portrait')->output();
        $slug    = preg_replace('/[^A-Za-z0-9_-]/', '_', $payload['attempt']->quiz->title ?? 'quiz_review');

        \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($user, $payload, $pdfOut, $slug) {
            $message->to($user->dwcc_email)
                ->subject('QuizGuard Answer Review: ' . ($payload['attempt']->quiz->title ?? 'Quiz'))
                ->text('Attached is your PDF quiz review.')
                ->attachData($pdfOut, $slug . '_review.pdf', ['mime' => 'application/pdf']);
        });

        return back()->with('success', 'Quiz review PDF sent to ' . $user->dwcc_email . '.');
    }

    // Authorize review access and format answer labels for PDF
    private function buildStudentReviewPdfPayload($attemptId): array
    {
        $attempt = Attempt::with('quiz.subject')
            ->where('attempt_id', $attemptId)
            ->where('student_id', Auth::id())
            ->where('status', '!=', 'in_progress')
            ->firstOrFail();

        abort_if(!($attempt->quiz->allow_review ?? false), 403, 'Answer review is disabled by your teacher.');

        $reviewAnswers = DB::table('student_answers')
            ->join('questions', 'student_answers.question_id', '=', 'questions.question_id')
            ->where('student_answers.attempt_id', $attempt->attempt_id)
            ->select('student_answers.*', 'questions.question_text', 'questions.correct_answers', 'questions.options', 'questions.points')
            ->orderBy('student_answers.answer_id')
            ->get()
            ->map(function ($ans) {
                $ans->formatted_student_answer = \App\Services\TransmutationService::formatOptionLabel($ans->student_answer, $ans->options);
                $ans->formatted_correct_answer = \App\Services\TransmutationService::formatOptionLabel($ans->correct_answers, $ans->options);
                return $ans;
            });

        return [
            'attempt'       => $attempt,
            'reviewAnswers' => $reviewAnswers,
            'studentName'   => trim(Auth::user()->first_name . ' ' . Auth::user()->last_name),
            'transmuted'    => \App\Services\TransmutationService::transmute((int) $attempt->score, (int) $attempt->total_points),
        ];
    }
}
