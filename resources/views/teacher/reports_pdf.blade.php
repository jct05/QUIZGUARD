<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5px;
            color: #1e293b;
            margin: 16px;
        }

        .quiz-page {
            width: 100%;
        }

        .page-break {
            page-break-after: always;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #11422d;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .brand {
            font-size: 18px;
            font-weight: 800;
            color: #11422d;
            letter-spacing: 1px;
        }

        .scope {
            text-align: right;
            font-size: 11px;
            font-weight: 700;
            color: #334155;
        }

        .quiz-title {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            margin: 6px 0 14px;
        }

        .section-label {
            font-size: 10px;
            font-weight: 700;
            color: #11422d;
            text-transform: uppercase;
            margin: 12px 0 4px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
        }

        .data-table th {
            background: #11422d;
            color: #ffffff;
            text-align: left;
            padding: 5px 6px;
            font-size: 8.5px;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 6px 6px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 8.5px;
            word-wrap: break-word;
        }

        .passed {
            color: #166534;
            font-weight: bold;
        }

        .failed {
            color: #be123c;
            font-weight: bold;
        }

        .footer {
            margin-top: 24px;
            padding-top: 8px;
            border-top: 1px solid #cbd5e1;
            font-size: 9px;
            color: #475569;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            padding: 2px 0;
        }
    </style>
</head>

<body>
    @foreach ($quizReports as $report)
        @php
            // Chunk question breakdown matrix so wide quizzes never overflow
            $questionChunks = !empty($report['questionHeaders'])
                ? array_chunk($report['questionHeaders'], 15, true)
                : []; // No questions = no breakdown
        @endphp

        <div class="quiz-page {{ !$loop->last ? 'page-break' : '' }}">
            <!-- Top Header: QUIZGUARD left, Subject & Section right at same height -->
            <table class="header-table">
                <tr>
                    <td class="brand" style="vertical-align: middle;">QUIZGUARD</td>
                    <td class="scope" style="vertical-align: middle;">{{ $report['subjectSectionHeader'] }}</td>
                </tr>
            </table>

            <!-- Quiz Name directly below header -->
            <div class="quiz-title">Quiz: {{ $report['quizTitle'] }}</div>

            <!-- Student Results Summary -->
            <div class="section-label">Student Results Summary</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:22%;">Student</th>
                        <th style="width:12%;">Student ID</th>
                        <th style="width:14%;">Score</th>
                        <th style="width:10%;">Status</th>
                        <th style="width:10%;">Avg / Q</th>
                        <th style="width:9%;">Violations</th>
                        <th style="width:7%;">Tries</th>
                        <th style="width:16%;">Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['rows'] as $row)
                        <tr>
                            <td><strong>{{ $row['student_name'] }}</strong></td>
                            <td>{{ $row['school_id_num'] }}</td>
                            <td><strong>{{ $row['score'] }}</strong></td>
                            <td class="{{ $row['status'] === 'Passed' ? 'passed' : 'failed' }}">{{ $row['status'] }}
                            </td>
                            <td>{{ $row['avg_time'] }}</td>
                            <td>{{ $row['violations'] }}</td>
                            <td>{{ $row['tries'] ?? 1 }}</td>
                            <td>{{ $row['submitted_at'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding: 10px; color: #64748b;">
                                No submitted student attempts found for this quiz.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if (!empty($report['rows']) && !empty($questionChunks))
                @foreach ($questionChunks as $chunkIdx => $chunkHeaders)
                    @php
                        $keys = array_keys($chunkHeaders);
                        $firstQ = reset($chunkHeaders);
                        $lastQ = end($chunkHeaders);
                    @endphp
                    <div class="section-label">Per-Question Breakdown ({{ $firstQ }} &ndash;
                        {{ $lastQ }})</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:18%;">Student</th>
                                @foreach ($chunkHeaders as $qHead)
                                    <th style="text-align:center;">{{ $qHead }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($report['rows'] as $row)
                                <tr>
                                    <td><strong>{{ $row['student_name'] }}</strong></td>
                                    @foreach ($keys as $qIndex)
                                        @php
                                            $res = $row['q_cells'][$qIndex] ?? '-';
                                            $short = $res === 'Correct' ? 'C' : ($res === 'Wrong' ? 'W' : '-');
                                        @endphp
                                        <td style="text-align:center;"
                                            class="{{ $res === 'Correct' ? 'passed' : ($res === 'Wrong' ? 'failed' : '') }}">
                                            {{ $short }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
                <div style="font-size:8px; color:#64748b; margin-top:2px;">Legend: C = Correct, W = Wrong, - =
                    Unanswered</div>
            @endif

            <!-- Report Footer: Open/Close dates, Sent timestamp, and Teacher Name -->
            <div class="footer">
                <table class="footer-table">
                    <tr>
                        <td><strong>Quiz Opened:</strong> {{ $report['openedLabel'] }}</td>
                        <td style="text-align: right;"><strong>Quiz Closed:</strong> {{ $report['closedLabel'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Report Sent:</strong> {{ $report['sentAtLabel'] }}</td>
                        <td style="text-align: right;"><strong>Prepared By:</strong> {{ $report['teacherName'] }}</td>
                    </tr>
                </table>
            </div>
        </div>
    @endforeach
</body>

</html>
