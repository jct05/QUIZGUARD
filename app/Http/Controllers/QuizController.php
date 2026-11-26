<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class QuizController extends Controller
{
    // Generate a unique random quiz join code.
    private function generateQuizCode()
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (Quiz::where('quiz_code', $code)->exists());

        return $code;
    }

    // Enforce SIT transmutation point totals from ten to hundred in steps of five
    private function isValidTransmutationTotal(int $totalPoints): bool
    {
        return $totalPoints >= 10 && $totalPoints <= 100 && ($totalPoints % 5 === 0);
    }

    // Display all quizzes created by the logged-in teacher.
    public function index()
    {
        $quizzes = Quiz::with(['subject', 'section'])
            ->where('teacher_id', Auth::id())
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('open_at', 'desc')
            ->get();

        return view('teacher.quizzes', ['quizzes' => $quizzes]);
    }

    // Show quiz setup page with banks, subjects, and sections.
    public function create()
    {
        $banks = QuestionBank::with([
            'subject',
            'questions' => fn($q) => $q->where('status', 'active')->orderBy('question_id'),
        ])
            ->where('teacher_id', Auth::id())
            ->where('status', 'active')
            ->orderBy('uploaded_at', 'desc')
            ->get();

        $subjects = Subject::where('teacher_id', Auth::id())
            ->where('status', 'active')
            ->orderBy('subject_code')
            ->get();

        $sections = Section::with('subject')
            ->where('teacher_id', Auth::id())
            ->where('status', 'active')
            ->orderBy('section_name')
            ->get();

        return view('teacher.quiz_setup', [
            'banks'    => $banks,
            'subjects' => $subjects,
            'sections' => $sections,
        ]);
    }

    // Validate and save a newly created teacher quiz.
    public function store(Request $request)
    {
        $request->validate([
            'creation_mode'         => 'required|in:manual,bank',
            'title'                 => 'required|string|max:100',
            'section_id'            => 'required|integer',
            'time_limit_min'        => 'required|integer|min:1',
            'time_per_question_sec' => 'nullable|integer|min:5',
            'max_violation'         => 'required|integer|min:1',
            'passing_score'         => 'required|integer|min:1',
            'attempts_allowed'      => 'required|integer|min:1',
            'open_at'               => 'required|date|after_or_equal:' . now()->subMinute()->format('Y-m-d H:i'),
            'closed_at'             => 'required|date|after:open_at',
        ], [
            'open_at.after_or_equal' => 'The opening date and time cannot be set in the past.',
            'closed_at.after'        => 'The closing date and time must be after the opening date.',
        ]);

        // Ensure the chosen section belongs to the logged-in teacher
        $section = Section::where('section_id', $request->section_id)
            ->where('teacher_id', Auth::id())
            ->where('status', 'active')
            ->firstOrFail();

        if ($request->creation_mode === 'bank') {
            $request->validate([
                'bank_id'             => 'required|exists:question_banks,bank_id',
                'number_of_questions' => 'required|integer|min:1',
            ]);

            $bank = QuestionBank::where('bank_id', $request->bank_id)
                ->where('teacher_id', Auth::id())
                ->where('status', 'active')
                ->firstOrFail();

            if ($section->subject_id !== $bank->subject_id) {
                return back()
                    ->withErrors(['section_id' => 'The selected section does not match the question bank subject.'])
                    ->withInput();
            }

            $available = Question::where('bank_id', $bank->bank_id)
                ->where('status', 'active')
                ->count();

            if ($request->number_of_questions > $available) {
                return back()
                    ->withErrors(['number_of_questions' => "This bank only has $available active questions."])
                    ->withInput();
            }

            $selectedQuestions = Question::where('bank_id', $bank->bank_id)
                ->where('status', 'active')
                ->orderByDesc('points')
                ->take($request->number_of_questions)
                ->get();

            $activeQuestionCount = (int) $request->number_of_questions;
            $totalPts = (int) $selectedQuestions->sum('points');

            // Enforce minimum ten items or points for SIT transmutation
            if ($activeQuestionCount < 10 && $totalPts < 10) {
                return back()->withInput()->withErrors([
                    'questions' => 'A quiz must have at least 10 items or 10 total points for the SIT transmutation table.',
                ]);
            }

            if (!$this->isValidTransmutationTotal($totalPts)) {
                return back()->withInput()->withErrors([
                    'questions' => "Total quiz points ({$totalPts} pts) must match the SIT Transmutation Table: 10, 15, 20, 25, ... up to 100 points.",
                ]);
            }

            if ($request->passing_score > $totalPts) {
                return back()
                    ->withErrors(['passing_score' => "Passing score ({$request->passing_score} pts) cannot exceed total quiz points ({$totalPts} pts)."])
                    ->withInput();
            }

            $quiz = Quiz::create([
                'teacher_id'            => Auth::id(),
                'bank_id'               => $bank->bank_id,
                'subject_id'            => $bank->subject_id,
                'section_id'            => $section->section_id,
                'quiz_code'             => $this->generateQuizCode(),
                'title'                 => trim($request->title),
                'time_limit_min'        => $request->time_limit_min,
                'time_per_question_sec' => $request->time_per_question_sec,
                'max_violation'         => $request->max_violation,
                'passing_score'         => $request->passing_score,
                'attempts_allowed'      => $request->attempts_allowed,
                'number_of_questions'   => $request->number_of_questions,
                'open_at'               => $request->open_at,
                'closed_at'             => $request->closed_at,
                'shuffle_questions'     => $request->boolean('shuffle_questions'),
                'shuffle_answers'       => $request->boolean('shuffle_answers'),
                'allow_review'          => $request->boolean('allow_review') ? 1 : 0,
                'disable_backtracking'  => $request->boolean('disable_backtracking') ? 1 : 0,
                'status'                => 'active',
            ]);
        } else {
            // Manual question builder mode
            $request->validate([
                'subject_id'                 => 'required|integer',
                'questions'                  => 'required|array|min:1',
                'questions.*.text'           => 'required|string',
                'questions.*.type'           => 'required|in:multiple_choice,identification',
                'questions.*.points'         => 'required|integer|min:1',
                'questions.*.options'        => 'nullable|array',
                'questions.*.correct_answer' => 'required|string|max:255',
            ]);

            $subject = Subject::where('subject_id', $request->subject_id)
                ->where('teacher_id', Auth::id())
                ->where('status', 'active')
                ->firstOrFail();

            if ($section->subject_id !== $subject->subject_id) {
                return back()
                    ->withErrors(['section_id' => 'The selected section does not match the chosen subject.'])
                    ->withInput();
            }

            $normalizedQuestions = [];
            $totalPoints = 0;

            foreach (array_values($request->questions) as $index => $q) {
                $num = $index + 1;
                $pts = max(1, (int) $q['points']);
                $totalPoints += $pts;

                if ($q['type'] === 'multiple_choice') {
                    $cleanOptions = [];
                    foreach (['A', 'B', 'C', 'D'] as $letter) {
                        $val = trim((string) ($q['options'][$letter] ?? ''));
                        if ($val !== '') {
                            $cleanOptions[$letter] = $val;
                        }
                    }

                    if (count($cleanOptions) < 2) {
                        return back()
                            ->withErrors(['questions' => "Question #$num must have at least 2 choices."])
                            ->withInput();
                    }

                    $correct = strtoupper(trim((string) $q['correct_answer']));
                    if (!isset($cleanOptions[$correct])) {
                        return back()
                            ->withErrors(['questions' => "Question #$num: select a non-empty choice as the correct answer."])
                            ->withInput();
                    }

                    $normalizedQuestions[] = [
                        'text'    => trim($q['text']),
                        'type'    => 'multiple_choice',
                        'options' => $cleanOptions,
                        'answer'  => $correct,
                        'points'  => $pts,
                    ];
                } else {
                    $normalizedQuestions[] = [
                        'text'    => trim($q['text']),
                        'type'    => 'identification',
                        'options' => null,
                        'answer'  => trim((string) $q['correct_answer']),
                        'points'  => $pts,
                    ];
                }
            }

            $activeQuestionCount = count($normalizedQuestions);

            // Enforce minimum ten items or points for SIT transmutation
            if ($activeQuestionCount < 10 && $totalPoints < 10) {
                return back()->withInput()->withErrors([
                    'questions' => 'A quiz must have at least 10 items or 10 total points for the SIT transmutation table.',
                ]);
            }

            if (!$this->isValidTransmutationTotal($totalPoints)) {
                return back()->withInput()->withErrors([
                    'questions' => "Total quiz points ({$totalPoints} pts) must match the SIT Transmutation Table: 10, 15, 20, 25, ... up to 100 points.",
                ]);
            }

            if ($request->passing_score > $totalPoints) {
                return back()
                    ->withErrors(['passing_score' => "Passing score ({$request->passing_score} pts) cannot exceed total quiz points ({$totalPoints} pts)."])
                    ->withInput();
            }

            $quiz = DB::transaction(function () use ($request, $subject, $section, $normalizedQuestions) {
                $bank = QuestionBank::create([
                    'teacher_id'       => Auth::id(),
                    'subject_id'       => $subject->subject_id,
                    'bank_title'       => Str::limit($request->title, 100, ''),
                    'source_file_name' => 'Created in Quiz Setup',
                    'uploaded_at'      => now(),
                    'status'           => 'active',
                ]);

                foreach ($normalizedQuestions as $q) {
                    Question::create([
                        'bank_id'         => $bank->bank_id,
                        'question_text'   => $q['text'],
                        'question_type'   => $q['type'],
                        'options'         => $q['options'] ? json_encode($q['options']) : null,
                        'correct_answers' => $q['answer'],
                        'points'          => $q['points'],
                        'status'          => 'active',
                    ]);
                }

                return Quiz::create([
                    'teacher_id'            => Auth::id(),
                    'bank_id'               => $bank->bank_id,
                    'subject_id'            => $subject->subject_id,
                    'section_id'            => $section->section_id,
                    'quiz_code'             => $this->generateQuizCode(),
                    'title'                 => trim($request->title),
                    'time_limit_min'        => $request->time_limit_min,
                    'time_per_question_sec' => $request->time_per_question_sec,
                    'max_violation'         => $request->max_violation,
                    'passing_score'         => $request->passing_score,
                    'attempts_allowed'      => $request->attempts_allowed,
                    'number_of_questions'   => count($normalizedQuestions),
                    'open_at'               => $request->open_at,
                    'closed_at'             => $request->closed_at,
                    'shuffle_questions'     => $request->boolean('shuffle_questions'),
                    'shuffle_answers'       => $request->boolean('shuffle_answers'),
                    'allow_review'          => $request->boolean('allow_review') ? 1 : 0,
                    'disable_backtracking'  => $request->boolean('disable_backtracking') ? 1 : 0,
                    'status'                => 'active',
                ]);
            });
        }

        ActivityLog::record(
            Auth::user()->school_id_num,
            Auth::user()->role,
            'QUIZ_CREATED',
            'Created quiz: ' . $quiz->title . ' (Code: ' . $quiz->quiz_code . ')'
        );

        return redirect('/teacher/quizzes')->with('success', 'Quiz created! Code: ' . $quiz->quiz_code);
    }

    // Load quiz, subjects, sections, and questions for editing
    public function edit($id)
    {
        $teacherId = Auth::id();

        $quiz = Quiz::with(['subject', 'section'])
            ->where('quiz_id', $id)
            ->where('teacher_id', $teacherId)
            ->firstOrFail();

        $subjects  = Subject::where('teacher_id', $teacherId)->where('status', 'active')->orderBy('subject_code')->get();
        $sections  = Section::with('subject')->where('teacher_id', $teacherId)->where('status', 'active')->orderBy('section_name')->get();
        $questions = Question::where('bank_id', $quiz->bank_id)->where('status', 'active')->orderBy('question_id')->get();

        return view('teacher.quiz_edit', [
            'quiz'      => $quiz,
            'subjects'  => $subjects,
            'sections'  => $sections,
            'questions' => $questions,
        ]);
    }

    // Validate and update quiz settings and question items
    public function update(Request $request, $id)
    {
        $teacherId = Auth::id();

        $quiz = Quiz::where('quiz_id', $id)
            ->where('teacher_id', $teacherId)
            ->firstOrFail();

        $request->validate([
            'title'                 => 'required|string|max:100',
            'subject_id'            => 'required|integer|exists:subjects,subject_id',
            'section_id'            => 'required|integer|exists:sections,section_id',
            'time_limit_min'        => 'nullable|integer|min:1',
            'time_per_question_sec' => 'nullable|integer|min:5',
            'passing_score'         => 'required|integer|min:0',
            'attempts_allowed'      => 'required|integer|min:1',
            'max_violation'         => 'required|integer|min:1',
            'open_at'               => 'required|date',
            'closed_at'             => 'required|date|after:open_at',
        ]);

        $section = Section::where('section_id', $request->section_id)
            ->where('teacher_id', $teacherId)
            ->where('status', 'active')
            ->firstOrFail();

        $subject = Subject::where('subject_id', $request->subject_id)
            ->where('teacher_id', $teacherId)
            ->where('status', 'active')
            ->firstOrFail();

        if ($section->subject_id !== $subject->subject_id) {
            return back()
                ->withErrors(['section_id' => 'The selected section does not match the chosen subject.'])
                ->withInput();
        }

        try {
            DB::transaction(function () use ($request, $quiz) {
                // Synchronize existing, updated, and newly added quiz questions
                if ($request->has('questions') && is_array($request->questions)) {
                    $keptIds = [];

                    foreach ($request->questions as $qData) {
                        $qText = trim((string) ($qData['question_text'] ?? ''));
                        if ($qText === '') {
                            continue;
                        }

                        $qType   = ($qData['question_type'] ?? 'identification') === 'multiple_choice'
                            ? 'multiple_choice'
                            : 'identification';
                        $points  = max(1, (int) ($qData['points'] ?? 1));
                        $options = null;
                        $correct = trim((string) ($qData['correct_answers'] ?? ''));

                        if ($qType === 'multiple_choice' && !empty($qData['options']) && is_array($qData['options'])) {
                            $cleanOptions = [];
                            foreach ($qData['options'] as $optKey => $optVal) {
                                $val = trim((string) $optVal);
                                if ($val !== '') {
                                    $cleanOptions[strtoupper((string) $optKey)] = $val;
                                }
                            }
                            $options = !empty($cleanOptions) ? json_encode($cleanOptions) : null;
                            $correct = strtoupper($correct);
                        }

                        if (!empty($qData['question_id'])) {
                            $existing = Question::where('question_id', $qData['question_id'])
                                ->where('bank_id', $quiz->bank_id)
                                ->first();

                            if ($existing) {
                                $existing->update([
                                    'question_text'   => $qText,
                                    'question_type'   => $qType,
                                    'options'         => $options,
                                    'correct_answers' => $correct,
                                    'points'          => $points,
                                    'status'          => 'active',
                                ]);
                                $keptIds[] = $existing->question_id;
                            }
                        } else {
                            $created = Question::create([
                                'bank_id'         => $quiz->bank_id,
                                'question_text'   => $qText,
                                'question_type'   => $qType,
                                'options'         => $options,
                                'correct_answers' => $correct,
                                'points'          => $points,
                                'status'          => 'active',
                            ]);
                            $keptIds[] = $created->question_id;
                        }
                    }

                    // Archive questions removed during editing instead of deleting history
                    Question::where('bank_id', $quiz->bank_id)
                        ->whereNotIn('question_id', $keptIds)
                        ->update(['status' => 'inactive']);
                }

                $activeQuestions = Question::where('bank_id', $quiz->bank_id)
                    ->where('status', 'active')
                    ->get();

                $activeQuestionCount = $activeQuestions->count();
                $totalPts = (int) $activeQuestions->sum('points');

                // Enforce minimum ten items or points for SIT transmutation
                if ($activeQuestionCount < 10 && $totalPts < 10) {
                    throw ValidationException::withMessages([
                        'questions' => 'A quiz must have at least 10 items or 10 total points for the SIT transmutation table.',
                    ]);
                }

                if (!$this->isValidTransmutationTotal($totalPts)) {
                    throw ValidationException::withMessages([
                        'questions' => "Total quiz points ({$totalPts} pts) must match the SIT Transmutation Table: 10, 15, 20, 25, ... up to 100 points.",
                    ]);
                }

                $quiz->update([
                    'title'                 => trim($request->title),
                    'subject_id'            => $request->subject_id,
                    'section_id'            => $request->section_id,
                    'time_limit_min'        => $request->time_limit_min ?: null,
                    'time_per_question_sec' => $request->time_per_question_sec ?: null,
                    'passing_score'         => $request->passing_score,
                    'attempts_allowed'      => $request->attempts_allowed,
                    'max_violation'         => $request->max_violation,
                    'shuffle_questions'     => $request->boolean('shuffle_questions') ? 1 : 0,
                    'shuffle_answers'       => $request->boolean('shuffle_answers') ? 1 : 0,
                    'allow_review'          => $request->boolean('allow_review') ? 1 : 0,
                    'disable_backtracking'  => $request->boolean('disable_backtracking') ? 1 : 0,
                    'number_of_questions'   => $activeQuestionCount > 0 ? $activeQuestionCount : $quiz->number_of_questions,
                    'open_at'               => $request->open_at,
                    'closed_at'             => $request->closed_at,
                ]);
            });
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        ActivityLog::record(
            Auth::user()->school_id_num,
            Auth::user()->role,
            'QUIZ_UPDATED',
            'Updated quiz: ' . $quiz->title . ' (' . $quiz->quiz_code . ')'
        );

        return redirect('/teacher/quizzes')->with('success', 'Quiz updated successfully.');
    }

    // Set a teacher quiz status to inactive
    public function deactivate($id)
    {
        $quiz = Quiz::where('quiz_id', $id)
            ->where('teacher_id', Auth::id())
            ->firstOrFail();

        $quiz->update(['status' => 'inactive']);

        ActivityLog::record(
            Auth::user()->school_id_num,
            Auth::user()->role,
            'QUIZ_DEACTIVATED',
            'Deactivated quiz: ' . $quiz->title
        );

        return redirect('/teacher/quizzes')->with('success', 'Quiz deactivated.');
    }

    // Set a teacher quiz status to active
    public function activate($id)
    {
        $quiz = Quiz::where('quiz_id', $id)
            ->where('teacher_id', Auth::id())
            ->firstOrFail();

        $quiz->update(['status' => 'active']);

        ActivityLog::record(
            Auth::user()->school_id_num,
            Auth::user()->role,
            'QUIZ_ACTIVATED',
            'Activated quiz: ' . $quiz->title
        );

        return redirect('/teacher/quizzes')->with('success', 'Quiz activated.');
    }
}
