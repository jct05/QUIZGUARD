<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attempt;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\Subject;
use App\Services\TransmutationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class TeacherReportController extends Controller
{
    // Display filtered teacher quizzes and report export controls
    public function index(Request $request)
    {
        $teacherId = Auth::id();

        // Filter teacher quizzes by distinct uppercase section name
        $sectionNames = Section::where('teacher_id', $teacherId)
            ->pluck('section_name')
            ->map(fn($s) => strtoupper(trim((string) $s)))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $subjects  = Subject::where('teacher_id', $teacherId)->orderBy('subject_code')->get();
        $sections  = Section::with('subject')->where('teacher_id', $teacherId)->orderBy('section_name')->get();

        $hasFilter = $request->filled('subject_id')
            || $request->filled('section_name')
            || $request->filled('section_id')
            || $request->filled('from_date')
            || $request->filled('to_date');

        $quizzes       = $this->buildFilteredQuizQuery($request, $teacherId)->get();
        $quizSummaries = $this->computeQuizSummaries($quizzes);

        return view('teacher.reports', [
            'subjects'      => $subjects,
            'sections'      => $sections,
            'sectionNames'  => $sectionNames,
            'quizSummaries' => $quizSummaries,
            'hasFilter'     => $hasFilter,
            'filters'       => $request->only(['subject_id', 'section_name', 'section_id', 'from_date', 'to_date']),
        ]);
    }

    // Export filtered quiz reports as multi-page PDF and workbook
    public function export(Request $request)
    {
        $teacher   = Auth::user();
        $teacherId = $teacher->user_id ?? Auth::id();

        $hasFilter = $request->filled('subject_id')
            || $request->filled('section_name')
            || $request->filled('section_id')
            || $request->filled('from_date')
            || $request->filled('to_date');

        if (!$hasFilter) {
            return back()->with('error', 'Exporting all quizzes is disabled. Please filter by Subject, Section, or Date Range first.');
        }

        if (!$teacher->dwcc_email) {
            return back()->with('error', 'No email address is registered on your teacher account.');
        }

        $quizzes = $this->buildFilteredQuizQuery($request, $teacherId)->get();
        if ($quizzes->isEmpty()) {
            return back()->with('error', 'No quizzes match your selected filters to export.');
        }

        $quizReports = $quizzes->map(fn($quiz) => $this->prepareSingleQuizPayload($quiz, $teacher))->all();
        $pdfContent  = Pdf::loadView('teacher.reports_pdf', ['quizReports' => $quizReports])
            ->setPaper('a4', 'landscape')
            ->output();

        $quizNamesLabel = $quizzes->pluck('title')->filter()->unique()->implode(', ');
        $safeSlug       = substr(preg_replace('/[^A-Za-z0-9_-]/', '_', $quizNamesLabel ?: 'quiz_report'), 0, 50);

        // Attach CSV for single quiz, or multi-sheet XLSX for multiple quizzes
        $isBundle = count($quizReports) > 1;
        if ($isBundle) {
            $spreadsheet = $this->generateMultiSheetXlsx($quizReports);
            $sheetName   = $safeSlug . '_bundle.' . $spreadsheet['ext'];
            $sheetMime   = $spreadsheet['mime'];
            $sheetBytes  = $spreadsheet['bytes'];
        } else {
            $sheetName  = $safeSlug . '_report.csv';
            $sheetMime  = 'text/csv';
            $sheetBytes = $this->generateCsv($quizReports[0]);
        }

        try {
            Mail::send([], [], function ($message) use ($teacher, $quizNamesLabel, $pdfContent, $sheetBytes, $sheetName, $sheetMime, $safeSlug) {
                $message->to($teacher->dwcc_email)
                    ->subject('DWCC QuizGuard Report: ' . $quizNamesLabel)
                    ->text('Attached are your requested QuizGuard PDF and spreadsheet reports for: ' . $quizNamesLabel . '.')
                    ->attachData($pdfContent, $safeSlug . '_report.pdf', ['mime' => 'application/pdf'])
                    ->attachData($sheetBytes, $sheetName, ['mime' => $sheetMime]);
            });

            ActivityLog::record(
                $teacher->school_id_num,
                $teacher->role,
                'REPORT_EXPORTED',
                'Exported PDF & spreadsheet report for: ' . $quizNamesLabel
            );

            return back()->with('success', 'PDF and spreadsheet reports sent exclusively to ' . $teacher->dwcc_email . '.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to email report: ' . $e->getMessage());
        }
    }

    // Build filtered quiz query from teacher request parameters
    private function buildFilteredQuizQuery(Request $request, int $teacherId)
    {
        $query = Quiz::with(['subject', 'section'])
            ->where('teacher_id', $teacherId)
            ->orderByDesc('open_at');

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('section_name')) {
            $secName = strtoupper(trim((string) $request->section_name));
            $query->whereHas('section', fn($q) => $q->whereRaw('UPPER(section_name) = ?', [$secName]));
        } elseif ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('open_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('open_at', '<=', $request->to_date);
        }

        return $query;
    }

    // Compute attempt statistics and timing for filtered quizzes
    private function computeQuizSummaries($quizzes): array
    {
        $summaries = [];
        foreach ($quizzes as $quiz) {
            $attempts  = Attempt::where('quiz_id', $quiz->quiz_id)->where('status', '!=', 'in_progress')->get();
            $submitted = $attempts->count();
            $highest   = $submitted > 0 ? (int) $attempts->max('score') : 0;
            $lowest    = $submitted > 0 ? (int) $attempts->min('score') : 0;
            $avgScore  = $submitted > 0 ? round((float) $attempts->avg('score'), 1) : 0;
            $maxPts    = $submitted > 0 ? (int) $attempts->max('total_points') : 0;

            $avgPct = $submitted > 0
                ? round($attempts->avg(fn($a) => TransmutationService::transmute((int) $a->score, (int) $a->total_points)), 1)
                : 0;

            $passed = $attempts->filter(function ($a) {
                $transmuted = TransmutationService::transmute((int) $a->score, (int) $a->total_points);
                return $transmuted >= 75;
            })->count();

            $summaries[] = [
                'quiz'             => $quiz,
                'submitted_count'  => $submitted,
                'passed_count'     => $passed,
                'highest_score'    => $highest,
                'min_score'        => $lowest,
                'max_score'        => $highest,
                'avg_score'        => $avgScore,
                'avg_pct'          => $avgPct,
                'max_points'       => $maxPts,
                'total_violations' => (int) $attempts->sum('violation_count'),
            ];
        }
        return $summaries;
    }

    // Build individual export payload for a single quiz (best attempt per student)
    public function prepareSingleQuizPayload(Quiz $quiz, $teacher): array
    {
        $teacherName = trim(($teacher->first_name ?? '') . ' ' . ($teacher->last_name ?? ''));
        $sentAtLabel = now()->format('M d, Y g:i A');

        $subjectLabel = trim(($quiz->subject->subject_code ?? '') . ' - ' . ($quiz->subject->subject_name ?? ''), ' -');
        $sectionLabel = $quiz->section->section_name ?? 'All';
        $subjectSectionHeader = ($subjectLabel ?: 'Subject') . ' | Section ' . $sectionLabel;

        $openedLabel = $quiz->open_at ? Carbon::parse($quiz->open_at)->format('M d, Y g:i A') : 'N/A';
        $closedLabel = $quiz->closed_at ? Carbon::parse($quiz->closed_at)->format('M d, Y g:i A') : 'N/A';

        $questions = Question::where('bank_id', $quiz->bank_id)
            ->where('status', 'active')
            ->orderBy('question_id')
            ->get();

        $attempts = Attempt::with('student')
            ->where('quiz_id', $quiz->quiz_id)
            ->where('status', '!=', 'in_progress')
            ->orderByDesc('score')
            ->orderByDesc('submitted_at')
            ->get();

        $answersByAttempt = DB::table('student_answers')
            ->whereIn('attempt_id', $attempts->pluck('attempt_id'))
            ->get()
            ->groupBy('attempt_id');

        $attemptsByStudent = $attempts->groupBy('student_id');
        $rows = [];

        foreach ($attemptsByStudent as $studentId => $studentAttempts) {
            $attempt    = $studentAttempts->first();
            $triesCount = $studentAttempts->count();

            $answers      = ($answersByAttempt->get($attempt->attempt_id) ?? collect())->sortBy('answered_at');
            $answersByQId = $answers->keyBy('question_id');

            $cursor = $attempt->started_at ? Carbon::parse($attempt->started_at) : null;
            $secs   = [];
            foreach ($answers as $ans) {
                if ($cursor && $ans->answered_at) {
                    $t = Carbon::parse($ans->answered_at);
                    $secs[] = max(1, (int) $cursor->diffInSeconds($t));
                    $cursor = $t;
                }
            }
            $avgSec = !empty($secs) ? (int) round(array_sum($secs) / count($secs)) : 0;

            $qCells = [];
            foreach ($questions as $q) {
                $a = $answersByQId->get($q->question_id);
                if (!$a || trim((string) $a->student_answer) === '') {
                    $qCells[] = '-';
                } else {
                    $qCells[] = ((int) $a->is_correct === 1) ? 'Correct' : 'Wrong';
                }
            }

            $transmuted = TransmutationService::transmute((int) $attempt->score, (int) $attempt->total_points);
            $passed     = $transmuted >= 75;

            $rows[] = [
                'student_name'  => $attempt->student ? trim($attempt->student->first_name . ' ' . $attempt->student->last_name) : 'Student #' . $studentId,
                'school_id_num' => $attempt->student->school_id_num ?? 'N/A',
                'q_cells'       => $qCells,
                'score'         => $attempt->score . '/' . $attempt->total_points . ' (' . $transmuted . '%)',
                'status'        => $passed ? 'Passed' : 'Failed',
                'avg_time'      => $avgSec < 60 ? $avgSec . 's' : intdiv($avgSec, 60) . 'm ' . ($avgSec % 60) . 's',
                'violations'    => (int) ($attempt->violation_count ?? 0),
                'tries'         => $triesCount,
                'submitted_at'  => $attempt->submitted_at ? Carbon::parse($attempt->submitted_at)->format('M d, Y g:i A') : '-',
            ];
        }

        $questionHeaders = [];
        foreach ($questions as $idx => $q) {
            $questionHeaders[] = 'Q' . ($idx + 1);
        }

        return [
            'quizTitle'            => $quiz->title,
            'subjectSectionHeader' => $subjectSectionHeader,
            'openedLabel'          => $openedLabel,
            'closedLabel'          => $closedLabel,
            'sentAtLabel'          => $sentAtLabel,
            'teacherName'          => $teacherName,
            'questionHeaders'      => $questionHeaders,
            'rows'                 => $rows,
        ];
    }

    // Build 2D row matrix shared by CSV and Excel sheets
    private function payloadToMatrix(array $payload): array
    {
        $headers = array_merge(
            ['Student Name', 'Student ID'],
            $payload['questionHeaders'],
            ['Score', 'Status', 'Avg / Question', 'Violations', 'Submitted At']
        );
        $lastColPad = array_fill(0, max(1, count($headers) - 2), '');

        $matrix   = [];
        $matrix[] = array_merge(['QUIZGUARD'], $lastColPad, [$payload['subjectSectionHeader']]);
        $matrix[] = ['Quiz: ' . $payload['quizTitle']];
        $matrix[] = [];
        $matrix[] = $headers;

        foreach ($payload['rows'] as $row) {
            $matrix[] = array_merge(
                [$row['student_name'], $row['school_id_num']],
                $row['q_cells'],
                [$row['score'], $row['status'], $row['avg_time'], $row['violations'], $row['submitted_at']]
            );
        }

        $matrix[] = [];
        $matrix[] = ['Quiz Opened', $payload['openedLabel'], 'Quiz Closed', $payload['closedLabel']];
        $matrix[] = ['Report Sent', $payload['sentAtLabel'], 'Teacher', $payload['teacherName']];

        return $matrix;
    }

    // Generate CSV content with required header and footer
    public function generateCsv(array $payload): string
    {
        $handle = fopen('php://temp', 'r+');
        foreach ($this->payloadToMatrix($payload) as $line) {
            fputcsv($handle, $line);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    // Generate multi sheet Excel workbook for bundled quizzes
    private function generateMultiSheetXlsx(array $quizReports): array
    {
        $usedNames = [];
        $sheets    = [];

        foreach ($quizReports as $idx => $report) {
            $clean = preg_replace('/[\\\\\\/\\?\\*\\[\\]:]/', '', $report['quizTitle'] ?: ('Quiz ' . ($idx + 1)));
            $base  = substr(trim($clean) ?: ('Quiz_' . ($idx + 1)), 0, 27);
            $name  = $base;
            $n     = 2;
            while (in_array(strtolower($name), $usedNames, true)) {
                $name = substr($base, 0, 25) . '_' . $n++;
            }
            $usedNames[] = strtolower($name);
            $sheets[]    = ['name' => $name, 'matrix' => $this->payloadToMatrix($report)];
        }

        if (!class_exists(\ZipArchive::class)) {
            return [
                'ext'   => 'xls',
                'mime'  => 'application/vnd.ms-excel',
                'bytes' => $this->generateSpreadsheetMl($sheets),
            ];
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'qg_xlsx_');
        $zip     = new \ZipArchive();
        $zip->open($tmpFile, \ZipArchive::OVERWRITE);

        $contentTypes = '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';

        $wbSheets = '';
        $wbRels   = '';

        foreach ($sheets as $i => $sheet) {
            $idx = $i + 1;
            $contentTypes .= "<Override PartName=\"/xl/worksheets/sheet{$idx}.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml\"/>";
            $safeName = htmlspecialchars($sheet['name'], ENT_XML1, 'UTF-8');
            $wbSheets .= "<sheet name=\"{$safeName}\" sheetId=\"{$idx}\" r:id=\"rId{$idx}\"/>";
            $wbRels   .= "<Relationship Id=\"rId{$idx}\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet\" Target=\"worksheets/sheet{$idx}.xml\"/>";

            $sheetXml = '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
            foreach ($sheet['matrix'] as $rIdx => $row) {
                $rowNum = $rIdx + 1;
                $sheetXml .= "<row r=\"{$rowNum}\">";
                foreach ($row as $cIdx => $val) {
                    $colRef = $this->colLetter($cIdx) . $rowNum;
                    $esc    = htmlspecialchars((string) $val, ENT_XML1, 'UTF-8');
                    $sheetXml .= "<c r=\"{$colRef}\" t=\"inlineStr\"><is><t>{$esc}</t></is></c>";
                }
                $sheetXml .= '</row>';
            }
            $sheetXml .= '</sheetData></worksheet>';
            $zip->addFromString("xl/worksheets/sheet{$idx}.xml", $sheetXml);
        }

        $contentTypes .= '</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>' . $wbSheets . '</sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . $wbRels . '</Relationships>');
        $zip->close();

        $bytes = file_get_contents($tmpFile);
        @unlink($tmpFile);

        return [
            'ext'   => 'xlsx',
            'mime'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'bytes' => $bytes,
        ];
    }

    // Fallback multi-sheet Excel XML generator if ZipArchive is missing
    private function generateSpreadsheetMl(array $sheets): string
    {
        $xml = '<?xml version="1.0"?><Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
        foreach ($sheets as $sheet) {
            $safeName = htmlspecialchars($sheet['name'], ENT_XML1, 'UTF-8');
            $xml .= "<Worksheet ss:Name=\"{$safeName}\"><Table>";
            foreach ($sheet['matrix'] as $row) {
                $xml .= '<Row>';
                foreach ($row as $cell) {
                    $esc = htmlspecialchars((string) $cell, ENT_XML1, 'UTF-8');
                    $xml .= "<Cell><Data ss:Type=\"String\">{$esc}</Data></Cell>";
                }
                $xml .= '</Row>';
            }
            $xml .= '</Table></Worksheet>';
        }
        $xml .= '</Workbook>';
        return $xml;
    }

    // Convert zero-based column index into Excel column letters
    private function colLetter(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr(($index % 26) + 65) . $letter;
            $index  = intdiv($index, 26) - 1;
        }
        return $letter;
    }
}
