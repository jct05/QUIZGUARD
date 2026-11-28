<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuestionBankController extends Controller
{
    // Display teacher question banks with subject and search filters.
    public function index(Request $request)
    {
        $query = QuestionBank::with('subject')
            ->withCount([
                'questions' => fn($q) => $q->where('status', 'active'),
                'quizzes',
            ])
            ->where('teacher_id', Auth::id());

        // Filter by subject folder or dropdown.
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by active or inactive status.
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by bank title or file name.
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($w) use ($q) {
                $w->where('bank_title', 'like', "%$q%")
                    ->orWhere('source_file_name', 'like', "%$q%");
            });
        }

        $banks = $query
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('uploaded_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Only load active subjects owned by the logged-in teacher.
        $subjects = Subject::where('teacher_id', Auth::id())
            ->where('status', 'active')
            ->withCount([
                'questionBanks' => fn($q) => $q->where('status', 'active'),
                'sections'      => fn($q) => $q->where('status', 'active'),
            ])
            ->orderBy('subject_code')
            ->get();

        // Counts for the library summary tabs.
        $counts = [
            'all'      => QuestionBank::where('teacher_id', Auth::id())->count(),
            'active'   => QuestionBank::where('teacher_id', Auth::id())->where('status', 'active')->count(),
            'inactive' => QuestionBank::where('teacher_id', Auth::id())->where('status', 'inactive')->count(),
        ];

        return view('teacher.question_banks', [
            'banks'    => $banks,
            'subjects' => $subjects,
            'counts'   => $counts,
        ]);
    }

    // Show all active questions inside a specific question bank.
    public function show($id)
    {
        $bank = QuestionBank::with('subject')
            ->where('bank_id', $id)
            ->where('teacher_id', Auth::id())
            ->where('status', 'active')
            ->firstOrFail();

        $questions = Question::where('bank_id', $bank->bank_id)
            ->where('status', 'active')
            ->orderBy('question_id')
            ->get();

        return view('teacher.question_bank_details', [
            'bank'      => $bank,
            'questions' => $questions,
        ]);
    }

    // Validate uploaded file and save parsed questions to bank.
    public function store(Request $request)
    {
        $request->validate([
            'bank_title' => 'required|string|max:100',
            'subject_id' => 'required|integer',
            'file'       => 'required|file|mimes:docx,pdf,txt|max:5120',
        ]);

        // Prevent duplicate question bank titles and rapid resubmission
        $cleanTitle = trim((string) $request->bank_title);

        $duplicateTitle = QuestionBank::where('teacher_id', Auth::id())
            ->where('subject_id', $request->subject_id)
            ->whereRaw('LOWER(bank_title) = ?', [strtolower($cleanTitle)])
            ->exists();

        if ($duplicateTitle) {
            return back()->withInput()->withErrors([
                'bank_title' => 'A question bank with this title already exists under the selected subject.',
            ]);
        }

        $recentUpload = QuestionBank::where('teacher_id', Auth::id())
            ->where('uploaded_at', '>=', now()->subSeconds(5))
            ->exists();

        if ($recentUpload) {
            return back()->with('error', 'Please wait a moment before importing another question bank.');
        }

        // Verify the chosen subject belongs to this teacher.
        $subject = Subject::where('subject_id', $request->subject_id)
            ->where('teacher_id', Auth::id())
            ->where('status', 'active')
            ->firstOrFail();

        $text      = $this->extractText($request->file('file'));
        $questions = $this->readQuestions($text);

        if (count($questions) === 0) {
            return back()
                ->withErrors(['file' => 'No questions found. Check the template format (Q:, A.-D., ANSWER:).'])
                ->withInput();
        }

        if (count($questions) < 10) {
            return back()
                ->withErrors(['file' => 'A question bank must contain at least 10 questions.'])
                ->withInput();
        }

        foreach ($questions as $i => $q) {
            $number = $i + 1;

            if ($q['text'] === '' || $q['answer'] === '') {
                return back()
                    ->withErrors(['file' => "Question $number is missing its text or answer."])
                    ->withInput();
            }

            if (count($q['options']) > 0 && !isset($q['options'][strtoupper($q['answer'])])) {
                return back()
                    ->withErrors(['file' => "Question $number: the answer must be one of the choices (A-D)."])
                    ->withInput();
            }
        }

        $bank = DB::transaction(function () use ($request, $subject, $questions) {
            $bank = QuestionBank::create([
                'teacher_id'       => Auth::id(),
                'subject_id'       => $subject->subject_id,
                'bank_title'       => trim($request->bank_title),
                'source_file_name' => Str::limit($request->file('file')->getClientOriginalName(), 255, ''),
                'uploaded_at'      => now(),
                'status'           => 'active',
            ]);

            foreach ($questions as $q) {
                $isChoice = count($q['options']) > 0;

                Question::create([
                    'bank_id'         => $bank->bank_id,
                    'question_text'   => $q['text'],
                    'question_type'   => $isChoice ? 'multiple_choice' : 'identification',
                    'options'         => $isChoice ? json_encode($q['options']) : null,
                    'correct_answers' => $isChoice ? strtoupper($q['answer']) : $q['answer'],
                    'points'          => max(1, $q['points']),
                    'status'          => 'active',
                ]);
            }

            return $bank;
        });

        ActivityLog::record(
            Auth::user()->school_id_num,
            Auth::user()->role,
            'BANK_CREATED',
            'Created question bank: ' . $bank->bank_title . ' (' . count($questions) . ' questions)'
        );

        return redirect('/teacher/question-banks')->with('success', count($questions) . ' questions imported.');
    }

    // Deactivate a question bank owned by the logged-in teacher.
    public function archive($id)
    {
        $bank = QuestionBank::where('bank_id', $id)
            ->where('teacher_id', Auth::id())
            ->firstOrFail();

        $bank->update(['status' => 'inactive']);

        ActivityLog::record(
            Auth::user()->school_id_num,
            Auth::user()->role,
            'BANK_DEACTIVATED',
            'Deactivated question bank: ' . $bank->bank_title
        );

        return redirect('/teacher/question-banks')->with('success', 'Question bank deactivated.');
    }

    // Reactivate an inactive question bank owned by the teacher.
    public function activate($id)
    {
        $bank = QuestionBank::where('bank_id', $id)
            ->where('teacher_id', Auth::id())
            ->firstOrFail();

        $bank->update(['status' => 'active']);

        ActivityLog::record(
            Auth::user()->school_id_num,
            Auth::user()->role,
            'BANK_ACTIVATED',
            'Activated question bank: ' . $bank->bank_title
        );

        return redirect('/teacher/question-banks')->with('success', 'Question bank activated.');
    }

    // Extract plain text from uploaded txt, docx, or pdf.
    private function extractText($file)
    {
        $path = $file->getRealPath();
        $ext  = strtolower($file->getClientOriginalExtension());

        if ($ext === 'txt') {
            return (string) file_get_contents($path);
        }

        if ($ext === 'docx') {
            $xml = $this->extractDocxDocumentXml($path);

            if ($xml === false || $xml === '') {
                return '';
            }

            $xml = str_replace('</w:p>', "\n", $xml);
            return html_entity_decode(strip_tags($xml));
        }

        if ($ext === 'pdf') {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                return $parser->parseFile($path)->getText();
            } catch (\Throwable $e) {
                return '';
            }
        }

        return '';
    }

    // Extract word/document.xml using ZipArchive or pure-PHP ZIP fallback when ext-zip is absent.
    private function extractDocxDocumentXml(string $path): string|false
    {
        if (class_exists(\ZipArchive::class)) {
            $zip = new \ZipArchive();
            if ($zip->open($path) !== true) {
                return false;
            }
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            return $xml;
        }

        $data = @file_get_contents($path);
        if ($data === false || strlen($data) < 22) {
            return false;
        }

        // Locate End of Central Directory (PK\x05\x06) from the end of the archive
        $eocdPos = strrpos($data, "\x50\x4b\x05\x06");
        if ($eocdPos === false || $eocdPos + 22 > strlen($data)) {
            return false;
        }

        $cdSize   = unpack('V', substr($data, $eocdPos + 12, 4))[1];
        $cdOffset = unpack('V', substr($data, $eocdPos + 16, 4))[1];
        $pos      = $cdOffset;
        $cdEnd    = min(strlen($data), $cdOffset + $cdSize);

        while ($pos + 46 <= $cdEnd && substr($data, $pos, 4) === "\x50\x4b\x01\x02") {
            $method         = unpack('v', substr($data, $pos + 10, 2))[1];
            $compressedSize = unpack('V', substr($data, $pos + 20, 4))[1];
            $nameLen        = unpack('v', substr($data, $pos + 28, 2))[1];
            $extraLen       = unpack('v', substr($data, $pos + 30, 2))[1];
            $commentLen     = unpack('v', substr($data, $pos + 32, 2))[1];
            $localOffset    = unpack('V', substr($data, $pos + 42, 4))[1];
            $fileName       = substr($data, $pos + 46, $nameLen);

            if ($fileName === 'word/document.xml') {
                if ($localOffset + 30 > strlen($data) || substr($data, $localOffset, 4) !== "\x50\x4b\x03\x04") {
                    return false;
                }
                $lhNameLen  = unpack('v', substr($data, $localOffset + 26, 2))[1];
                $lhExtraLen = unpack('v', substr($data, $localOffset + 28, 2))[1];
                $dataStart  = $localOffset + 30 + $lhNameLen + $lhExtraLen;
                $compressed = substr($data, $dataStart, $compressedSize);

                if ($method === 0) {
                    return $compressed;
                }
                if ($method === 8 && function_exists('gzinflate')) {
                    return @gzinflate($compressed);
                }
                return false;
            }

            $pos += 46 + $nameLen + $extraLen + $commentLen;
        }

        return false;
    }

    // Parse extracted text into formatted questions, options, and answers.
    private function readQuestions($text)
    {
        $text      = str_replace(["\r\n", "\r"], "\n", $text);
        $questions = [];
        $current   = null;

        foreach (explode("\n", $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (stripos($line, 'Q:') === 0) {
                if ($current) {
                    $questions[] = $current;
                }
                $current = ['text' => trim(substr($line, 2)), 'options' => [], 'answer' => '', 'points' => 1];
            } elseif ($current && preg_match('/^([A-D])[.)]\s*(.+)$/i', $line, $m)) {
                $current['options'][strtoupper($m[1])] = $m[2];
            } elseif ($current && stripos($line, 'ANSWER:') === 0) {
                $current['answer'] = trim(substr($line, 7));
            } elseif ($current && stripos($line, 'POINTS:') === 0) {
                $current['points'] = (int) trim(substr($line, 7));
            } elseif ($current && $current['answer'] === '') {
                if (count($current['options']) > 0) {
                    $keys = array_keys($current['options']);
                    $last = end($keys);
                    $current['options'][$last] .= ' ' . $line;
                } else {
                    $current['text'] .= ' ' . $line;
                }
            }
        }

        if ($current) {
            $questions[] = $current;
        }

        return $questions;
    }
}
