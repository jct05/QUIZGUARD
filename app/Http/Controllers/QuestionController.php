<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QuestionController extends Controller
{
    // Retrieve subject IDs student is currently enrolled in
    private function getEnrolledSubjectIds(int $studentId)
    {
        if ($studentId <= 0) {
            return collect();
        }

        $pivotTable = Schema::hasTable('section_students') ? 'section_students' : 'section_student';

        if (!Schema::hasTable($pivotTable) || !Schema::hasTable('sections')) {
            return collect();
        }

        return DB::table($pivotTable)
            ->join('sections', "{$pivotTable}.section_id", '=', 'sections.section_id')
            ->where("{$pivotTable}.student_id", $studentId)
            ->pluck('sections.subject_id')
            ->filter()
            ->unique();
    }

    // Display reviewer question banks matching student enrolled subjects
    public function reviewerIndex(Request $request)
    {
        $user = Auth::user();
        $studentId = (int) ($user->user_id ?? $user->id ?? 0);
        $enrolledSubjectIds = $this->getEnrolledSubjectIds($studentId);

        // Fetch banks only if enrolled, otherwise empty collection
        $reviewerSets = $enrolledSubjectIds->isNotEmpty()
            ? QuestionBank::with(['subject', 'teacher'])
            ->withCount(['questions' => fn($q) => $q->where('status', 'active')])
            ->where('status', 'active')
            ->whereIn('subject_id', $enrolledSubjectIds)
            ->orderByDesc('uploaded_at')
            ->get()
            : collect();

        return view('student.reviewer', [
            'reviewerSets' => $reviewerSets,
        ]);
    }

    // Authorize student access and load reviewer practice questions
    public function practiceSet($bankId)
    {
        $user = Auth::user();
        $studentId = (int) ($user->user_id ?? $user->id ?? 0);
        $enrolledSubjectIds = $this->getEnrolledSubjectIds($studentId);

        $bankQuery = QuestionBank::with(['subject', 'teacher'])
            ->where('bank_id', $bankId)
            ->where('status', 'active');

        // Check if student belongs to the bank subject
        if ($enrolledSubjectIds->isNotEmpty()) {
            $bankQuery->whereIn('subject_id', $enrolledSubjectIds);
        }

        $bank = $bankQuery->first();

        if (!$bank) {
            return redirect()->route('student.reviewer')
                ->with('error', 'You are not enrolled in the subject for this reviewer set.');
        }

        $questions = Question::where('bank_id', $bank->bank_id)
            ->where('status', 'active')
            ->inRandomOrder()
            ->get();

        return view('student.practice', [
            'bank'      => $bank,
            'questions' => $questions,
        ]);
    }

    // Fetch and optionally shuffle questions for quiz attempt
    public static function getQuizQuestions(?Quiz $quiz)
    {
        if (!$quiz) {
            return collect();
        }

        $query = Question::where('bank_id', $quiz->bank_id)
            ->where('status', 'active');

        if ($quiz->shuffle_questions) {
            $query->inRandomOrder();
        } else {
            $query->orderBy('question_id');
        }

        if ($quiz->number_of_questions) {
            $query->limit((int) $quiz->number_of_questions);
        }

        $questions = $query->get();

        if ($quiz->shuffle_answers) {
            $questions->transform(fn($question) => self::shuffleQuestionOptions($question));
        }

        return $questions;
    }

    // Shuffle answer options while preserving associative letter keys
    private static function shuffleQuestionOptions(Question $question): Question
    {
        $options = is_array($question->options)
            ? $question->options
            : json_decode($question->options ?? '', true);

        if (is_array($options) && count($options) > 1) {
            $keys = array_keys($options);
            shuffle($keys);
            $shuffled = [];
            foreach ($keys as $key) {
                $shuffled[$key] = $options[$key];
            }
            $question->options = is_array($question->getRawOriginal('options'))
                ? $shuffled
                : json_encode($shuffled);
        }

        return $question;
    }
}
