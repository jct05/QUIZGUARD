<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1e293b;
            margin: 18px;
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

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .data-table th {
            background: #11422d;
            color: #ffffff;
            text-align: left;
            padding: 6px 6px;
            font-size: 9px;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 6px 6px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
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

            <!-- Submissions Table -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>ID</th>
                        @foreach ($report['questionHeaders'] as $qHead)
                            <th style="text-align:center;">{{ $qHead }}</th>
                        @endforeach
                        <th>Score</th>
                        <th>Status</th>
                        <th>Avg / Q</th>
                        <th>Violations</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['rows'] as $row)
                        <tr>
                            <td><strong>{{ $row['student_name'] }}</strong></td>
                            <td>{{ $row['school_id_num'] }}</td>
                            @foreach ($row['q_cells'] as $qRes)
                                <td style="text-align:center;">{{ $qRes }}</td>
                            @endforeach
                            <td><strong>{{ $row['score'] }}</strong></td>
                            <td class="{{ $row['status'] === 'Passed' ? 'passed' : 'failed' }}">{{ $row['status'] }}
                            </td>
                            <td>{{ $row['avg_time'] }}</td>
                            <td>{{ $row['violations'] }}</td>
                            <td>{{ $row['submitted_at'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 7 + count($report['questionHeaders']) }}"
                                style="text-align:center; padding: 12px; color: #64748b;">
                                No submitted student attempts found for this quiz.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

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
