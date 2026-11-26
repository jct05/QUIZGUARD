<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attempt;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\StudentAnswer;
use App\Models\ViolationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentQuizController extends Controller
{
    public function index(Request $request)
    {
        $now       = now();
        $user      = Auth::user();
        $studentId = (int) ($user->user_id ?? $user->id ?? 0);
        $code      = trim((string) $request->query('code', ''));

        // If student searched by quiz code, direct them immediately to the quiz
        if ($code !== '') {
            $quizByCode = Quiz::where('quiz_code', $code)
                ->where('status', 'active')
                ->first();

            if ($quizByCode) {
                // If instructions exist, go there, otherwise let them view it
                return view('student.instructions', ['quiz' => $quizByCode]);
            }

            return redirect('/student/quizzes')->with('error', 'Quiz code "' . $code . '" not found or inactive.');
        }

        // Retrieve enrolled section IDs for logged in student
        $pivotTable = Schema::hasTable('section_students') ? 'section_students' : 'section_student';
        $sectionIds = Schema::hasTable($pivotTable)
            ? DB::table($pivotTable)->where('student_id', $studentId)->pluck('section_id')
            : collect();

        // Fetch available quizzes currently open for student sections
        $availableQuizzes = Quiz::with('subject')
            ->where('status', 'active')
            ->whereIn('section_id', $sectionIds)
            ->where('open_at', '<=', $now)
            ->where('closed_at', '>=', $now)
            ->orderBy('closed_at')
            ->get();

        // Fetch upcoming quizzes scheduled for student sections
        $upcomingQuizzes = Quiz::with('subject')
            ->where('status', 'active')
            ->whereIn('section_id', $sectionIds)
            ->where('open_at', '>', $now)
            ->orderBy('open_at')
            ->get();

        // Load completed and ongoing attempts for student
        $completedAttempts = Attempt::with('quiz.subject')
            ->where('student_id', $studentId)
            ->where('status', '!=', 'in_progress')
            ->orderByDesc('submitted_at')
            ->get();

        $ongoingAttempts = Attempt::where('student_id', $studentId)
            ->where('status', 'in_progress')
            ->get();

        return view('student.quizzes', [
            'quizzes'           => $availableQuizzes,
            'availableQuizzes'  => $availableQuizzes,
            'upcomingQuizzes'   => $upcomingQuizzes,
            'completedAttempts' => $completedAttempts,
            'ongoingAttempts'   => $ongoingAttempts,
        ]);
    }

    // starts a new attempt
    // or resumes an unfinished one
    public function start($id)
    {
        // the quiz must be active and belong to the student's section
        $quiz = Quiz::where('quiz_id', $id)
            ->where('status', 'active')
            ->where('section', Auth::user()->section)
            ->firstOrFail();

        // the quiz must be inside its opening/closing window times
        if (now()->lt($quiz->open_at) || now()->gt($quiz->closed_at)) {
            return back()->withErrors(['quiz' => 'This quiz is not open.']);
        }

        // unfinished attempt? continue it
        $ongoing = Attempt::where('quiz_id', $quiz->quiz_id)
            ->where('student_id', Auth::id())
            ->where('status', 'in_progress')
            ->first();

        if ($ongoing) {
            return redirect('/student/attempts/' . $ongoing->attempt_id);
        }

        // count how many attempts the student already used
        $used = Attempt::where('quiz_id', $quiz->quiz_id)
            ->where('student_id', Auth::id())
            ->count();

        if ($used >= $quiz->attempts_allowed) {
            return back()->withErrors(['quiz' => 'You have no attempts left for this quiz.']);
        }

        // pick random questions from the bank
        // limited to number_of_questions
        $questions = Question::where('bank_id', $quiz->bank_id)
            ->where('status', 'active')
            ->inRandomOrder()
            ->limit($quiz->number_of_questions)
            ->get();

        // if shuffle is off
        // show them in the bank's original order
        if (!$quiz->shuffle_questions) {
            $questions = $questions->sortBy('question_id');
        }

        // transaction = attempt and answer rows are created together, or not at all
        $attempt = DB::transaction(function () use ($quiz, $questions) {
            $attempt = Attempt::create([
                'quiz_id'         => $quiz->quiz_id,
                'student_id'      => Auth::id(),
                'started_at'      => now(),
                'score'           => 0,
                'status'          => 'in_progress',
                'violation_count' => 0,
            ]);

            //not randomizing student questions when refreshing or something happen midquiz
            foreach ($questions as $question) {
                StudentAnswer::create([
                    'attempt_id'  => $attempt->attempt_id,
                    'question_id' => $question->question_id,
                ]);
            }

            return $attempt;
        });

        ActivityLog::record(Auth::user()->school_id_num, Auth::user()->role, 'QUIZ_STARTED', 'Started quiz: ' . $quiz->title);

        return redirect('/student/attempts/' . $attempt->attempt_id);
    }

    // shows the quiz page
    public function show($id)
    {
        $attempt = $this->myAttempt($id);

        // finished go to result page
        if ($attempt->status !== 'in_progress') {
            return redirect('/student/attempts/' . $attempt->attempt_id . '/result');
        }

        // time ran out while student was away score was saved
        $deadline = $this->deadline($attempt);
        if (now()->gt($deadline)) {
            $this->finishAttempt($attempt, 'submitted');
            return redirect('/student/attempts/' . $attempt->attempt_id . '/result');
        }

        // the student questions in the order they were created at start
        $answers = StudentAnswer::with('question')
            ->where('attempt_id', $attempt->attempt_id)
            ->orderBy('answer_id')
            ->get();

        // build safe data for the page correct_answers is never sent to the browser
        $items = [];
        foreach ($answers as $a) {
            $q       = $a->question;
            $options = $q->options ? json_decode($q->options, true) : [];

            // shuffle the choices if the quiz says so
            // seeding with answer_id keeps the same order every time the page reloads
            if ($attempt->quiz->shuffle_answers && count($options) > 0) {
                $letters = array_keys($options);
                mt_srand($a->answer_id);
                shuffle($letters);
                mt_srand();   // back to normal randomness for php

                $shuffled = [];
                foreach ($letters as $letter) {
                    $shuffled[$letter] = $options[$letter];
                }
                $options = $shuffled;
            }

            $items[] = [
                'question_id' => $q->question_id,
                'text'        => $q->question_text,
                'type'        => $q->question_type,
                'options'     => $options,
                'saved'       => $a->student_answer,   // so a reload keeps what was already answered
            ];
        }

        // seconds left until the deadline
        $secondsLeft = max(0, $deadline->timestamp - now()->timestamp);

        return view('student.quiz_take', [
            'attempt'     => $attempt,
            'quiz'        => $attempt->quiz,
            'items'       => $items,
            'secondsLeft' => $secondsLeft,
        ]);
    }

    // saves answer while the student is taking the quiz
    public function saveAnswer(Request $request, $id)
    {
        $attempt = $this->myAttempt($id);

        // a finished attempt can't be changed
        if ($attempt->status !== 'in_progress') {
            return response()->json(['status' => 'closed'], 403);
        }

        // time over score it now and tell the page
        if (now()->gt($this->deadline($attempt))) {
            $this->finishAttempt($attempt, 'submitted');
            return response()->json(['status' => 'expired'], 403);
        }

        $request->validate([
            'question_id' => 'required|integer',
            'answer'      => 'nullable|string|max:255',
        ]);

        // the question must belong to attempt
        $row = StudentAnswer::where('attempt_id', $attempt->attempt_id)
            ->where('question_id', $request->question_id)
            ->firstOrFail();

        // we only store the answer here
        // grading happens at submit so is_correct never leaks
        $row->update([
            'student_answer' => $request->answer,
            'answered_at'    => now(),
        ]);

        return response()->json(['status' => 'saved']);
    }

    // student submits button or timer reaches 0 grade everything
    public function submit(Request $request, $id)
    {
        $attempt = $this->myAttempt($id);

        if ($attempt->status === 'in_progress') {

            // when submitting late gives 5s grace so that submissions still counts
            // last answer saved when auto submits
            if (now()->lte($this->deadline($attempt)->copy()->addSeconds(5))) {
                $rows = StudentAnswer::where('attempt_id', $attempt->attempt_id)->get();
                foreach ($rows as $row) {
                    $field = 'q' . $row->question_id;
                    if ($request->has($field)) {
                        $row->update([
                            'student_answer' => substr((string) $request->input($field), 0, 255),
                            'answered_at'    => now(),
                        ]);
                    }
                }
            }

            $this->finishAttempt($attempt, 'submitted');

            ActivityLog::record(Auth::user()->school_id_num, Auth::user()->role, 'QUIZ_SUBMITTED', 'Submitted quiz: ' . $attempt->quiz->title);
        }

        return redirect('/student/attempts/' . $attempt->attempt_id . '/result');
    }

    // shows the score after submitting
    public function result($id)
    {
        $attempt = $this->myAttempt($id);

        // still in progress back to quiz
        if ($attempt->status === 'in_progress') {
            return redirect('/student/attempts/' . $attempt->attempt_id);
        }

        return view('student.quiz_result', [
            'attempt' => $attempt,
            'quiz'    => $attempt->quiz,
            // passing_score is a percentage so compare with the calculated percentage
            'passed'  => $attempt->percentage >= $attempt->quiz->passing_score,
        ]);
    }

    // called by the quiz page the moment the student leaves the quiz
    public function logViolation(Request $request, $id)
    {
        $attempt = $this->myAttempt($id);

        // once attempt is no longer active blocks new violation logs
        if ($attempt->status !== 'in_progress') {
            return response()->json(['status' => 'closed'], 403);
        }

        // third_party_app = focus left the browser while the tab stayed open (chatbot, other app)
        // we only count it, we can't and not identify which app
        $request->validate([
            'violation_type' => 'required|in:tab_switch,third_party_app,window_blur,fullscreen_exit,copy_paste',
            'question_id'    => 'nullable|integer',
        ]);

        // question_id is saved only if it really belongs to this attempt
        $belongs = StudentAnswer::where('attempt_id', $attempt->attempt_id)
            ->where('question_id', $request->question_id)
            ->exists();

        // transaction = the violation row and the counter go up together or not at all
        $log = DB::transaction(function () use ($attempt, $request, $belongs) {
            $log = ViolationLog::create([
                'attempt_id'       => $attempt->attempt_id,
                'violation_type'   => $request->violation_type,
                'occurred_at'      => now(),
                'duration_seconds' => 0,   // updated when the student comes back
                'question_id'      => $belongs ? $request->question_id : null,
            ]);

            // increment 1 safely and updates $attempt->violation_count
            $attempt->increment('violation_count');

            return $log;
        });

        $max = $attempt->quiz->max_violation;

        // too many violations grade what was saved and close the attempt
        if ($attempt->violation_count >= $max) {
            $this->finishAttempt($attempt, 'auto_submitted');

            ActivityLog::record(Auth::user()->school_id_num, Auth::user()->role, 'QUIZ_AUTO_SUBMITTED', 'Auto-submitted (too many violations): ' . $attempt->quiz->title);

            return response()->json([
                'status'   => 'auto_submitted',
                'redirect' => '/student/attempts/' . $attempt->attempt_id . '/result',
            ]);
        }

        return response()->json([
            'status'       => 'logged',
            'violation_id' => $log->violation_id,
            'count'        => $attempt->violation_count,
            'max'          => $max,
        ]);
    }

    // called when student comes back: saves how many seconds they were away
    public function logReturn(Request $request, $id, $violationId)
    {
        $attempt = $this->myAttempt($id);

        $request->validate([
            'duration_seconds' => 'required|integer|min:0|max:86400',
        ]);

        // the violation must belong to this attempt
        $log = ViolationLog::where('violation_id', $violationId)
            ->where('attempt_id', $attempt->attempt_id)
            ->firstOrFail();

        $log->update(['duration_seconds' => $request->duration_seconds]);

        return response()->json(['status' => 'updated']);
    }

    // finds the attempt and makes sure it belongs to the logged-in student
    private function myAttempt($id)
    {
        return Attempt::with('quiz')
            ->where('attempt_id', $id)
            ->where('student_id', Auth::id())
            ->firstOrFail();
    }

    // the moment the attempt must end: time limit or quiz closing
    private function deadline($attempt)
    {
        $byTimeLimit = $attempt->started_at->copy()->addMinutes($attempt->quiz->time_limit_min);

        return $byTimeLimit->lt($attempt->quiz->closed_at) ? $byTimeLimit : $attempt->quiz->closed_at;
    }

    // grades every answer saves the points and closes attempt
    // $status is 'submitted' now; the violation step will use 'auto_submitted'
    private function finishAttempt($attempt, $status)
    {
        DB::transaction(function () use ($attempt, $status) {
            $earned = 0;   // points the student got
            $total  = 0;   // points the quiz was worth

            $answers = StudentAnswer::with('question')
                ->where('attempt_id', $attempt->attempt_id)
                ->get();

            foreach ($answers as $a) {
                $q = $a->question;
                $total += $q->points;

                // ignores capital letters and extra spaces
                // multiple choice compares the letter
                $correct = $a->student_answer !== null
                    && strtolower(trim($a->student_answer)) === strtolower(trim($q->correct_answers));

                $a->update(['is_correct' => $correct]);

                if ($correct) {
                    $earned += $q->points;
                }
            }

            // score = points earned
            // total_points = points possible
            // the percentage is calculated when needed
            $attempt->update([
                'score'        => $earned,
                'total_points' => $total,
                'submitted_at' => now(),
                'status'       => $status,
            ]);
        });
    }
}
