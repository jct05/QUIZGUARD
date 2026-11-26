<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\ViolationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ViolationLogController extends Controller
{
    // Lock attempt row to prevent duplicate concurrent violations
    public function store(Request $request, $attemptId)
    {
        $request->validate([
            'violation_type' => 'required|string|max:45',
            'question_id'    => 'nullable|integer',
        ]);

        return DB::transaction(function () use ($request, $attemptId) {
            $attempt = Attempt::with('quiz')
                ->where('attempt_id', $attemptId)
                ->where('student_id', Auth::id())
                ->lockForUpdate()
                ->firstOrFail();

            $maxViolations = (int) ($attempt->quiz->max_violation ?? 2);

            if ($attempt->status !== 'in_progress') {
                return response()->json([
                    'violation_count' => $attempt->violation_count,
                    'max_violation'   => $maxViolations,
                    'auto_submitted'  => true,
                ]);
            }

            // Prevent duplicate burst logs within 1 second
            $recentLog = ViolationLog::where('attempt_id', $attempt->attempt_id)
                ->where('occurred_at', '>=', now()->subSecond())
                ->first();

            if ($recentLog) {
                return response()->json([
                    'violation_id'    => $recentLog->violation_id,
                    'violation_count' => $attempt->violation_count,
                    'max_violation'   => $maxViolations,
                    'auto_submitted'  => false,
                    'deduplicated'    => true,
                ]);
            }

            $violation = ViolationLog::create([
                'attempt_id'       => $attempt->attempt_id,
                'violation_type'   => $request->violation_type,
                'occurred_at'      => now(),
                'duration_seconds' => 0,
                'question_id'      => $request->question_id,
            ]);

            $attempt->increment('violation_count');
            $attempt->refresh();

            $autoSubmitted = false;

            // Auto submit only after exceeding allowed violation limit
            if ($attempt->violation_count > $maxViolations) {
                $this->autoSubmitAttempt($attempt);
                $autoSubmitted = true;
            }

            return response()->json([
                'violation_id'    => $violation->violation_id,
                'violation_count' => $attempt->violation_count,
                'max_violation'   => $maxViolations,
                'auto_submitted'  => $autoSubmitted,
            ]);
        });
    }

    // Update violation duration when student returns to quiz
    public function logReturn(Request $request, $attemptId, $violationId)
    {
        $attempt = Attempt::where('attempt_id', $attemptId)
            ->where('student_id', Auth::id())
            ->firstOrFail();

        $violation = ViolationLog::where('violation_id', $violationId)
            ->where('attempt_id', $attempt->attempt_id)
            ->firstOrFail();

        $durationSeconds = max(0, (int) abs(\Carbon\Carbon::parse($violation->occurred_at)->diffInSeconds(now())));

        $violation->update([
            'duration_seconds' => $durationSeconds,
        ]);

        return response()->json([
            'updated'          => true,
            'duration_seconds' => $durationSeconds,
        ]);
    }

    // Grade and lock attempt after reaching violation limit
    private function autoSubmitAttempt(Attempt $attempt): void
    {
        app(AttemptController::class)->finalizeAttempt($attempt, 'auto_submitted');
    }
}
