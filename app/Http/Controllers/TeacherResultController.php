<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attempt;
use App\Models\Question;
use App\Models\Quiz;
use App\Services\TransmutationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class TeacherResultController extends Controller
{
    // Display quiz results, per question accuracy, and student matrix
    public function results($quizId)
    {
        $quiz = Quiz::with(['subject', 'section'])
            ->where('quiz_id', $quizId)
            ->where('teacher_id', Auth::id())
            ->firstOrFail();

        $reportData = $this->buildQuizReportData($quiz);

        $viewName = view()->exists('teacher.quiz_results')
            ? 'teacher.quiz_results'
            : (view()->exists('teacher.results') ? 'teacher.results' : 'teacher.quizzes.results');

        return view($viewName, $reportData);
    }

    // Show individual student attempt details
    public function attempt($attemptId)
    {
        $attempt = Attempt::with(['quiz.subject', 'student', 'violationLogs'])
            ->where('attempt_id', $attemptId)
            ->whereHas('quiz', fn($q) => $q->where('teacher_id', Auth::id()))
            ->firstOrFail();

        $answers = DB::table('student_answers')
            ->join('questions', 'student_answers.question_id', '=', 'questions.question_id')
            ->where('student_answers.attempt_id', $attempt->attempt_id)
            ->select(
                'student_answers.*',
                'questions.question_text',
                'questions.correct_answers',
                'questions.options',
                'questions.points'
            )
            ->orderBy('student_answers.answered_at')
            ->get()
            ->map(function ($ans) {
                $ans->formatted_student_answer = TransmutationService::formatOptionLabel(
                    $ans->student_answer ?? null,
                    $ans->options ?? null
                );
                $ans->formatted_correct_answer = TransmutationService::formatOptionLabel(
                    $ans->correct_answers ?? null,
                    $ans->options ?? null
                );
                return $ans;
            });

               $viewName = collect(['teacher.attempt_detail', 'teacher.quiz_attempt', 'teacher.attempt'])
            ->first(fn($v) => view()->exists($v)) ?? 'teacher.attempt';

        $viewName = view()->exists('teacher.attempt')
            ? 'teacher.attempt'
            : 'teacher.results_attempt';

        return view($viewName, [
            'attempt'    => $attempt,
            'answers'    => $answers
        ]);
    }

    // Delegate single quiz email export to shared report template
    public function emailReport(Request $request, $quizId)
    {
        $teacher = Auth::user();
        $quiz    = Quiz::with(['subject', 'section'])
            ->where('quiz_id', $quizId)
            ->where('teacher_id', Auth::id())
            ->firstOrFail();

        if (!$teacher->dwcc_email) {
            return back()->with('error', 'No email address is registered on your teacher account.');
        }

        $reportController = app(TeacherReportController::class);
        $payload          = $reportController->prepareSingleQuizPayload($quiz, $teacher);
        $csvContent       = $reportController->generateCsv($payload);
        $pdfContent       = Pdf::loadView('teacher.reports_pdf', ['quizReports' => [$payload]])
            ->setPaper('a4', 'landscape')
            ->output();

        $safeSlug = preg_replace('/[^A-Za-z0-9_-]/', '_', $quiz->title ?: 'quiz_report');

        try {
            Mail::send([], [], function ($message) use ($teacher, $quiz, $pdfContent, $csvContent, $safeSlug) {
                $message->to($teacher->dwcc_email)
                    ->subject('DWCC QuizGuard Report: ' . $quiz->title)
                    ->text('Attached are your QuizGuard PDF and CSV reports for "' . $quiz->title . '".')
                    ->attachData($pdfContent, $safeSlug . '_report.pdf', ['mime' => 'application/pdf'])
                    ->attachData($csvContent, $safeSlug . '_report.csv', ['mime' => 'text/csv']);
            });

            ActivityLog::record(
                $teacher->school_id_num,
                $teacher->role,
                'REPORT_EXPORTED',
                'Exported PDF & CSV report for quiz: ' . $quiz->title
            );

            return back()->with('success', 'PDF and CSV report sent to ' . $teacher->dwcc_email . '.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to send report email: ' . $e->getMessage());
        }
    }

    // Compute per question accuracy fractions and distinct student rows
    private function buildQuizReportData(Quiz $quiz): array
    {
        $attempts = Attempt::with('student')
            ->where('quiz_id', $quiz->quiz_id)
            ->where('status', '!=', 'in_progress')
            ->orderByDesc('score')
            ->orderByDesc('submitted_at')
            ->get();

        $questions = Question::where('bank_id', $quiz->bank_id)
            ->where('status', 'active')
            ->orderBy('question_id')
            ->get();

        $answersByAttempt = DB::table('student_answers')
            ->whereIn('attempt_id', $attempts->pluck('attempt_id'))
            ->get()
            ->groupBy('attempt_id');

        // Group attempts by student and pick highest scoring try
        $attemptsByStudent = $attempts->groupBy('student_id');
        $questionCorrects  = array_fill_keys($questions->pluck('question_id')->all(), 0);
        $questionDurations = [];
        $studentRows       = [];

        foreach ($attemptsByStudent as $studentId => $studentAttempts) {
            $bestAttempt = $studentAttempts->first();
            $triesCount  = $studentAttempts->count();

            // Build individual try details for student history modal
            $chronologicalTries = $studentAttempts->sortBy('submitted_at')->values();
            $allTriesData       = [];

            foreach ($chronologicalTries as $idx => $try) {
                $tryParsed = $this->formatAttemptSummary($try, $quiz, $questions, $answersByAttempt);
                $tryParsed['try_number'] = $idx + 1;
                $tryParsed['is_best']    = $try->attempt_id === $bestAttempt->attempt_id;
                $tryParsed['status']     = $try->status;
                $allTriesData[]          = $tryParsed;
            }

            $bestSummary = $this->formatAttemptSummary($bestAttempt, $quiz, $questions, $answersByAttempt);

            foreach ($bestSummary['question_results'] as $qId => $status) {
                if ($status === 'correct') {
                    $questionCorrects[$qId] = ($questionCorrects[$qId] ?? 0) + 1;
                }
            }

            foreach ($bestSummary['question_durations'] as $qId => $sec) {
                $questionDurations[$qId][] = $sec;
            }

            $studentName = $bestAttempt->student
                ? trim($bestAttempt->student->first_name . ' ' . $bestAttempt->student->last_name)
                : 'Student #' . $studentId;

            $studentRows[] = [
                'student_id'       => $studentId,
                'attempt_id'       => $bestAttempt->attempt_id,
                'student_name'     => $studentName,
                'school_id_num'    => $bestAttempt->student->school_id_num ?? 'N/A',
                'score'            => $bestSummary['score'],
                'total_points'     => $bestSummary['total_points'],
                'percentage'       => $bestSummary['percentage'],
                'transmuted'       => $bestSummary['transmuted'],
                'passed'           => $bestSummary['passed'],
                'avg_time_label'   => $bestSummary['avg_time_label'],
                'avg_time_sec'     => $bestSummary['avg_time_sec'],
                'violation_count'  => $bestSummary['violation_count'],
                'tries'            => $triesCount,
                'submitted_label'  => $bestSummary['submitted_label'],
                'question_results' => $bestSummary['question_results'],
                'all_tries'        => $allTriesData,
            ];
        }

        // Order distinct student rows by most recent submission
        usort($studentRows, fn($a, $b) => $b['attempt_id'] <=> $a['attempt_id']);

        $submittedCount = count($studentRows);
        $questionStats  = [];
        $qNum           = 1;

        foreach ($questions as $question) {
            $qId          = $question->question_id;
            $correctCount = $questionCorrects[$qId] ?? 0;
            $list         = $questionDurations[$qId] ?? [];
            $avgQ         = !empty($list) ? (int) round(array_sum($list) / count($list)) : 0;

            $questionStats[] = [
                'question_id'    => $qId,
                'label'          => 'Q' . $qNum,
                'question_text'  => $question->question_text,
                'points'         => (int) ($question->points ?? 1),
                'correct_count'  => $correctCount,
                'total_attempts' => $submittedCount,
                'fraction'       => $correctCount . '/' . $submittedCount,
                'accuracy_pct'   => $submittedCount > 0 ? round(($correctCount / $submittedCount) * 100) : 0,
                'avg_label'      => $this->formatDuration($avgQ),
            ];
            $qNum++;
        }

        $highestScore    = $submittedCount > 0 ? max(array_column($studentRows, 'score')) : 0;
        $maxPoints       = $submittedCount > 0
            ? max(array_column($studentRows, 'total_points'))
            : (int) $questions->sum('points');
        $averagePct      = $submittedCount > 0 ? round(array_sum(array_column($studentRows, 'percentage')) / $submittedCount) : 0;
        $totalViolations = (int) $attempts->sum('violation_count');
        $passedCount     = count(array_filter($studentRows, fn($r) => $r['passed']));
        $passingRate     = $submittedCount > 0 ? round(($passedCount / $submittedCount) * 100) : 0;
        $overallAvgQSec  = $submittedCount > 0 ? (int) round(array_sum(array_column($studentRows, 'avg_time_sec')) / $submittedCount) : 0;
        $boxPlotStats    = $this->computeBoxPlotStats(array_column($studentRows, 'score'));

        return [
            'quiz'             => $quiz,
            'questions'        => $questions,
            'studentRows'      => $studentRows,
            'questionStats'    => $questionStats,
            'submittedCount'   => $submittedCount,
            'highestScore'     => $highestScore,
            'maxPoints'        => $maxPoints,
            'averagePct'       => $averagePct,
            'totalViolations'  => $totalViolations,
            'passedCount'      => $passedCount,
            'passingRate'      => $passingRate,
            'overallAvgQLabel' => $this->formatDuration($overallAvgQSec),
            'maxViolations'    => (int) ($quiz->max_violation ?? 3),
            'boxPlotStats'     => $boxPlotStats,
        ];
    }

    // Compute 5-number summary, IQR, fences, and outliers for score box plot
    private function computeBoxPlotStats(array $scores): array
    {
        $n = count($scores);
        if ($n === 0) {
            return [
                'count'    => 0,
                'min'      => null,
                'q1'       => null,
                'median'   => null,
                'q3'       => null,
                'max'      => null,
                'mean'     => null,
                'iqr'      => null,
                'raw_min'  => null,
                'raw_max'  => null,
                'outliers' => [],
                'scores'   => [],
            ];
        }

        $sorted = array_map('floatval', $scores);
        sort($sorted, SORT_NUMERIC);

        $percentile = function (array $arr, float $p): float {
            $count = count($arr);
            if ($count === 1) {
                return $arr[0];
            }
            $pos   = ($count - 1) * $p;
            $lower = (int) floor($pos);
            $upper = (int) ceil($pos);
            $frac  = $pos - $lower;
            return $arr[$lower] + ($arr[$upper] - $arr[$lower]) * $frac;
        };

        $q1     = round($percentile($sorted, 0.25), 2);
        $median = round($percentile($sorted, 0.50), 2);
        $q3     = round($percentile($sorted, 0.75), 2);
        $iqr    = round($q3 - $q1, 2);
        $mean   = round(array_sum($sorted) / $n, 2);

        $lowerFence = $q1 - 1.5 * $iqr;
        $upperFence = $q3 + 1.5 * $iqr;

        $inliers  = [];
        $outliers = [];
        foreach ($sorted as $val) {
            if ($val < $lowerFence || $val > $upperFence) {
                $outliers[] = $val;
            } else {
                $inliers[] = $val;
            }
        }

        $whiskerMin = !empty($inliers) ? min($inliers) : $sorted[0];
        $whiskerMax = !empty($inliers) ? max($inliers) : $sorted[$n - 1];

        return [
            'count'    => $n,
            'min'      => round($whiskerMin, 2),
            'q1'       => $q1,
            'median'   => $median,
            'q3'       => $q3,
            'max'      => round($whiskerMax, 2),
            'mean'     => $mean,
            'iqr'      => $iqr,
            'raw_min'  => round($sorted[0], 2),
            'raw_max'  => round($sorted[$n - 1], 2),
            'outliers' => $outliers,
            'scores'   => $sorted,
        ];
    }

    // Extract score, timing, and per question status for an attempt
    private function formatAttemptSummary(Attempt $attempt, Quiz $quiz, $questions, $answersByAttempt): array
    {
        $answers      = ($answersByAttempt->get($attempt->attempt_id) ?? collect())->sortBy('answered_at');
        $answersByQId = $answers->keyBy('question_id');

        $cursor            = $attempt->started_at ? Carbon::parse($attempt->started_at) : null;
        $attemptDurations  = [];
        $questionDurations = [];

        foreach ($answers as $ans) {
            if ($cursor && $ans->answered_at) {
                $ansTime = Carbon::parse($ans->answered_at);
                $sec     = max(1, (int) $cursor->diffInSeconds($ansTime));
                $attemptDurations[] = $sec;
                $questionDurations[$ans->question_id] = $sec;
                $cursor = $ansTime;
            }
        }

        if (empty($attemptDurations) && $attempt->started_at && $attempt->submitted_at) {
            $totalElapsed = max(1, (int) Carbon::parse($attempt->started_at)->diffInSeconds(Carbon::parse($attempt->submitted_at)));
            $avgSec       = (int) round($totalElapsed / max(1, $questions->count()));
        } else {
            $avgSec = !empty($attemptDurations) ? (int) round(array_sum($attemptDurations) / count($attemptDurations)) : 0;
        }

        $questionResults = [];
        foreach ($questions as $q) {
            $ansRow = $answersByQId->get($q->question_id);
            if (!$ansRow || trim((string) $ansRow->student_answer) === '') {
                $questionResults[$q->question_id] = 'unanswered';
            } elseif ((int) $ansRow->is_correct === 1) {
                $questionResults[$q->question_id] = 'correct';
            } else {
                $questionResults[$q->question_id] = 'incorrect';
            }
        }

        $score       = (int) ($attempt->score ?? 0);
        $totalPoints = (int) ($attempt->total_points ?? 0);
        $transmuted  = TransmutationService::transmute($score, $totalPoints);
        $passed      = $transmuted >= 75;

        return [
            'attempt_id'         => $attempt->attempt_id,
            'score'              => $score,
            'total_points'       => $totalPoints,
            'percentage'         => $transmuted,
            'transmuted'         => $transmuted,
            'passed'             => $passed,
            'avg_time_sec'       => $avgSec,
            'avg_time_label'     => $this->formatDuration($avgSec),
            'violation_count'    => (int) ($attempt->violation_count ?? 0),
            'submitted_label'    => $attempt->submitted_at ? Carbon::parse($attempt->submitted_at)->format('M d, g:i A') : '-',
            'question_results'   => $questionResults,
            'question_durations' => $questionDurations,
        ];
    }

    // Format seconds into readable minute and second string
    private function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) return '0s';
        if ($seconds < 60) return $seconds . 's';
        return intdiv($seconds, 60) . 'm ' . ($seconds % 60) . 's';
    }
}
