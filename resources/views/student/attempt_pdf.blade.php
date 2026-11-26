<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 20px;
        }

        .header {
            border-bottom: 2px solid #11422d;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }

        .brand {
            font-size: 18px;
            font-weight: 800;
            color: #11422d;
        }

        .meta {
            font-size: 11px;
            color: #475569;
            margin-top: 4px;
        }

        .q-box {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .correct {
            color: #166534;
            font-weight: bold;
        }

        .wrong {
            color: #be123c;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="brand">DWCC QUIZGUARD — QUIZ REVIEW</div>
        <div class="meta">
            <strong>Quiz:</strong> {{ $attempt->quiz->title }} &nbsp;|&nbsp;
            <strong>Student:</strong> {{ $studentName }} &nbsp;|&nbsp;
            <strong>Score:</strong> {{ $attempt->score }}/{{ $attempt->total_points }} ({{ $transmuted }}% -
            {{ $transmuted >= 75 ? 'PASSED' : 'FAILED' }})
        </div>
    </div>

    @foreach ($reviewAnswers as $idx => $ans)
        <div class="q-box">
            <div>
                <strong>Q{{ $idx + 1 }}. {{ $ans->question_text }}</strong>
                <span class="{{ $ans->is_correct ? 'correct' : 'wrong' }}" style="float:right;">
                    {{ $ans->is_correct ? 'CORRECT' : 'WRONG' }}
                </span>
            </div>
            <div style="margin-top:6px;">
                Your answer: <strong>{{ $ans->formatted_student_answer }}</strong>
                @if (!$ans->is_correct)
                    &nbsp;|&nbsp; Correct answer: <strong class="correct">{{ $ans->formatted_correct_answer }}</strong>
                @endif
            </div>
        </div>
    @endforeach
</body>

</html>
