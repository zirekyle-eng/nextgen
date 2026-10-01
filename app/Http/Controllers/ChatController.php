<?php
namespace App\Http\Controllers;

use App\Models\CurriculumFile;
use App\Models\LessonFile;
use App\Models\StudentRecord;
use App\Models\UnitLesson;
use App\Services\CurriculumFileTextExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    private CurriculumFileTextExtractor $textExtractor;
    private bool $hasActiveFileContent = false;

    public function __construct(CurriculumFileTextExtractor $textExtractor)
    {
        $this->textExtractor = $textExtractor;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'lesson_id' => 'nullable|exists:unit_lessons,id',
            'file_id' => 'nullable|exists:curriculum_files,id',
            'subject' => 'nullable|string|max:255',
            'child_id' => 'nullable|integer',
            'history' => 'nullable|array',
            'mode' => 'nullable|in:normal,quiz',
            'quiz_question' => 'nullable|integer|min:0|max:10'
        ]);
        
        try {
            $mode = (string) $request->input('mode', 'normal');
            $subject = trim((string) $request->input('subject', ''));
            $quizQuestion = (int) $request->input('quiz_question', 0);
            $systemPrompt = $this->buildSystemPrompt(
                $request->input('lesson_id') ? (int) $request->input('lesson_id') : null,
                $request->file_id,
                $request->message,
                $mode,
                $subject !== '' ? $subject : null,
                $quizQuestion,
                $request->input('child_id')
            );
            
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt]
            ];
            
            if ($request->history) {
                foreach (array_slice($request->history, -5) as $msg) {
                    $messages[] = $msg;
                }
            }
            
            $messages[] = ['role' => 'user', 'content' => $request->message];
            
            $response = $this->requestChatCompletion($messages);
            
            if ($response->successful()) {
                $data = $response->json();
                $reply = $this->extractReplyContent($data);

                if (
                    $this->hasActiveFileContent &&
                    $this->looksLikeNoFileAccessReply($reply)
                ) {
                    $messages[0]['content'] .= "\nIMPORTANT: The file text is already provided above. Do not claim you cannot access files. Answer using that text.";
                    $retryResponse = $this->requestChatCompletion($messages);
                    if ($retryResponse->successful()) {
                        $retryData = $retryResponse->json();
                        $reply = $this->extractReplyContent($retryData) ?: $reply;
                    }
                }

                if (trim((string) $reply) === '') {
                    $reply = $this->buildLocalFallbackReply(
                        (string) $request->input('message'),
                        $request->input('lesson_id') ? (int) $request->input('lesson_id') : null,
                        $request->file_id ? (int) $request->file_id : null,
                        $subject !== '' ? $subject : null,
                        $mode,
                        $request->input('child_id')
                    ) ?? ($mode === 'quiz'
                        ? 'Question 1: Explain the main idea of this lesson in your own words.'
                        : 'Nextgen AI Tutor did not send a clear answer this time. Please try again.');
                }
                
                return response()->json([
                    'success' => true,
                    'reply' => $reply
                ]);
            }

            $fallbackReply = $this->buildLocalFallbackReply(
                (string) $request->input('message'),
                $request->input('lesson_id') ? (int) $request->input('lesson_id') : null,
                $request->file_id ? (int) $request->file_id : null,
                $subject !== '' ? $subject : null,
                $mode,
                $request->input('child_id')
            );

            if ($fallbackReply !== null) {
                return response()->json([
                    'success' => true,
                    'reply' => $fallbackReply,
                    'fallback' => true
                ]);
            }

            Log::warning('AI chat provider returned an error', [
                'status' => $response->status(),
                'body' => mb_substr((string) $response->body(), 0, 1000),
            ]);

            if ($response->status() === 429) {
                $retryAfter = (int) $response->header('Retry-After', 0);
                return response()->json([
                    'success' => false,
                    'error' => 'Rate limit reached. Please wait and retry.',
                    'retry_after' => $retryAfter > 0 ? $retryAfter : null
                ], 429, $retryAfter > 0 ? ['Retry-After' => (string) $retryAfter] : []);
            }
            
            return response()->json([
                'success' => false,
                'error' => 'API Error: ' . $response->status()
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Chat Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    private function buildSystemPrompt(?int $lessonId = null, $fileId = null, string $question = '', string $mode = 'normal', ?string $subject = null, int $quizQuestion = 0, $childId = null)
    {
        $this->hasActiveFileContent = false;
        $targetStudent = $this->resolveTargetStudent((int) $childId);

        if ($mode === 'quiz') {
            $prompt = "You are an interactive quiz tutor for a school management system. ";
            $prompt .= "Ask one question at a time and wait for the student answer. ";
            $prompt .= "The quiz must be exactly 10 questions in total. ";
            $prompt .= "After each answer: grade it (Correct/Partially correct/Incorrect), give a short explanation, then ask the next question if applicable.\n\n";
            $prompt .= "Current quiz progress:\n";
            $prompt .= "- Current question number: {$quizQuestion}\n";
            $prompt .= "- Maximum questions: 10\n\n";
            $prompt .= "Rules:\n";
            $prompt .= "1) If user message is '__start_quiz__', ask Question 1 only.\n";
            $prompt .= "2) If current question number is 1 to 9, evaluate the answer then ask exactly one next question.\n";
            $prompt .= "3) If current question number is 10, evaluate the final answer, provide a final score out of 10 with short feedback, and DO NOT ask another question.\n\n";
        } else {
            $prompt = "You are a helpful AI tutor for a school management system. ";
            $prompt .= "Answer questions in a clear, educational way. ";
            $prompt .= "Use simple language and provide examples when helpful.\n\n";
        }

        $prompt .= "Tone and student-safety style:\n";
        $prompt .= "- Write in a kind, warm, encouraging way suitable for school students.\n";
        $prompt .= "- Use simple age-appropriate words and short clear paragraphs.\n";
        $prompt .= "- Be patient and supportive if the student is confused or gives a wrong answer.\n";
        $prompt .= "- Do not be harsh, sarcastic, frightening, or overly formal.\n";
        $prompt .= "- Prefer friendly explanations with small steps and easy examples.\n\n";

        if (auth()->check()) {
            $studentName = $targetStudent['name'] ?: (auth()->user()->name ?? 'Student');
            $studentClass = $targetStudent['class_name'] ?: (optional(optional(auth()->user())->student_record)->my_class->name ?? 'Unknown class');
            $prompt .= "Current student profile:\n";
            $prompt .= "- Name: {$studentName}\n";
            $prompt .= "- Class: {$studentClass}\n\n";
        }
        
        if ($lessonId) {
            $lesson = UnitLesson::with(['unit.week.subject.my_class', 'files'])->find($lessonId);
            if ($lesson) {
                if (!empty($targetStudent['class_name']) && !$this->canAccessLesson($lesson, $targetStudent['class_name'])) {
                    return $prompt . "The selected lesson is outside the student's class. Ask the user to pick the correct lesson.\n\n";
                }

                $lessonLabel = 'Lesson ' . (int) $lesson->lesson_number . ': ' . trim((string) $lesson->title);
                $subjectName = (string) optional(optional(optional($lesson->unit)->week)->subject)->name;

                $prompt .= "The student is currently studying: {$lessonLabel}\n";
                if ($subjectName !== '') {
                    $prompt .= "- Subject: {$subjectName}\n";
                }
                $prompt .= "Use the uploaded lesson file(s) for this lesson as the primary source.\n\n";

                $contextLimit = $mode === 'quiz' ? 12000 : 28000;
                $lessonFiles = $lesson->files ?: collect();
                if ($lessonFiles->isNotEmpty()) {
                    $this->hasActiveFileContent = true;
                    $prompt .= $this->buildLessonContextBlock($lessonFiles, $question, $contextLimit);
                    $prompt .= "Important: You already have the lesson file text above. Never say you cannot view or access the lesson files.\n";
                    if ($mode === 'quiz') {
                        $prompt .= "Generate questions strictly from the lesson file content above.\n\n";
                    } else {
                        $prompt .= "If the answer is not in the lesson file content, say that clearly.\n\n";
                    }
                } else {
                    $prompt .= "No uploaded lesson files are available for this lesson. Answer based on the lesson and subject context.\n\n";
                }
            }
        } elseif ($subject) {
            $files = $this->filesForSubject($subject, (int) $childId);
            if ($files->isNotEmpty()) {
                $this->hasActiveFileContent = true;
                $prompt .= "The student selected subject: {$subject}\n";
                $prompt .= "Use all available curriculum files in this subject as the primary source.\n\n";
                $contextLimit = $mode === 'quiz' ? 12000 : 28000;
                $prompt .= $this->buildSubjectContextBlock($files, $question, $contextLimit);
                $prompt .= "Important: You already have curriculum text above. Never say you cannot access files.\n";
                if ($mode === 'quiz') {
                    $prompt .= "Generate questions strictly from the curriculum text above.\n\n";
                } else {
                    $prompt .= "If the answer is not in the curriculum text, say that clearly.\n\n";
                }
            }
        } elseif ($fileId) {
            $file = CurriculumFile::find($fileId);
            if ($file) {
                if (!empty($targetStudent['class_name']) && (string) $file->year !== (string) $targetStudent['class_name']) {
                    return $prompt . "The selected file is outside the student's class. Ask the user to pick a correct file.\n\n";
                }
                $prompt .= "The student is currently studying:\n";
                $prompt .= "- File: {$file->name}\n";
                $prompt .= "- Year: {$file->year}\n";
                $prompt .= "- Subject: {$file->subject}\n\n";

                $content = $this->getOrExtractContent($file);
                if ($content !== '') {
                    $this->hasActiveFileContent = true;
                    $excerpt = $this->buildFullFileContent($content, 22000);
                    $prompt .= "Use this uploaded file content as the primary source:\n";
                    $prompt .= "--- FILE CONTENT START ---\n";
                    $prompt .= $excerpt . "\n";
                    $prompt .= "--- FILE CONTENT END ---\n\n";
                    $prompt .= "Important: You already have the file text above. Never say you cannot view or access the file.\n";
                    if ($mode === 'quiz') {
                        $prompt .= "Generate questions strictly from the file content above.\n\n";
                    } else {
                        $prompt .= "If the answer is not in the file content, say that clearly.\n\n";
                    }
                } else {
                    if ($mode === 'quiz') {
                        $prompt .= "File content is unavailable. Ask the student to choose another file.\n\n";
                    } else {
                        $prompt .= "File content is unavailable. Rely on general tutoring only and mention that.\n\n";
                    }
                }
            }
        }
        
        return $prompt;
    }
    
    private function getOrExtractContent(CurriculumFile $file): string
    {
        $content = trim((string) $file->content_text);
        if ($content !== '') {
            return $content;
        }

        if (!Storage::disk('public')->exists($file->path)) {
            return '';
        }

        $absolutePath = Storage::disk('public')->path($file->path);
        $extracted = $this->textExtractor->extract($absolutePath, $file->extension);

        if ($extracted !== '') {
            $file->content_text = $extracted;
            $file->save();
        }

        return $extracted;
    }

    private function getOrExtractLessonFileContent(LessonFile $file): string
    {
        $content = trim((string) $file->content_text);
        if ($content !== '') {
            return $content;
        }

        $absolutePath = $this->resolvePublicFilePath((string) $file->file_path);
        if ($absolutePath === null) {
            return '';
        }

        $extension = pathinfo($absolutePath, PATHINFO_EXTENSION);
        $extracted = $this->textExtractor->extract($absolutePath, $extension);

        if ($extracted !== '') {
            $file->content_text = $extracted;
            $file->save();
        }

        return $extracted;
    }

    private function buildLessonContextBlock($files, string $question, int $maxChars = 28000): string
    {
        $remaining = $maxChars;
        $block = "---- UPLOADED LESSON FILES CONTENT START ----\n";

        foreach ($files as $file) {
            if ($remaining <= 300) {
                break;
            }

            $content = $this->getOrExtractLessonFileContent($file);
            if (trim($content) === '') {
                continue;
            }

            $fileText = $this->buildFullFileContent($content, $remaining);
            if (trim($fileText) === '') {
                continue;
            }

            $header = "[Uploaded Lesson File: {$file->title} | Original: {$file->original_name} | Size: {$file->size} bytes]\n";
            $segment = $header . $fileText . "\n\n";
            $segmentLen = mb_strlen($segment);
            if ($segmentLen > $remaining) {
                $segment = mb_substr($segment, 0, $remaining);
                $segmentLen = mb_strlen($segment);
            }

            $block .= $segment;
            $remaining -= $segmentLen;
        }

        $block .= "---- UPLOADED LESSON FILES CONTENT END ----\n\n";
        return $block;
    }

    private function resolvePublicFilePath(string $relativePath): ?string
    {
        $relativePath = trim(str_replace('\\', '/', trim($relativePath)));
        if ($relativePath === '') {
            return null;
        }

        $candidates = [$relativePath];
        if (stripos($relativePath, 'public/') === 0) {
            $candidates[] = substr($relativePath, 7);
        }
        if (stripos($relativePath, 'storage/') === 0) {
            $candidates[] = substr($relativePath, 8);
        }

        foreach ($candidates as $candidate) {
            $candidate = ltrim($candidate, '/');
            if ($candidate === '') {
                continue;
            }

            if (Storage::disk('public')->exists($candidate)) {
                return Storage::disk('public')->path($candidate);
            }

            $publicStoragePath = public_path('storage/' . $candidate);
            if (is_file($publicStoragePath)) {
                return $publicStoragePath;
            }

            $storageAppPublicPath = storage_path('app/public/' . $candidate);
            if (is_file($storageAppPublicPath)) {
                return $storageAppPublicPath;
            }

            $publicPath = public_path($candidate);
            if (is_file($publicPath)) {
                return $publicPath;
            }
        }

        return null;
    }

    private function requestChatCompletion(array $messages)
    {
        if (trim((string) env('GEMINI_API_KEY', '')) !== '') {
            return $this->requestGeminiCompletion($messages);
        }

        $models = $this->resolveModels();
        $lastResponse = null;
        $lastException = null;

        foreach ($models as $model) {
            try {
                $response = Http::timeout(55)->withHeaders([
                    'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => request()->getHost(),
                    'X-Title' => 'School Management System'
                ])->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => $model,
                    'messages' => $messages,
                    'max_tokens' => 900,
                    'temperature' => 0.4
                ]);

                if ($response->status() === 429 || $response->status() >= 500) {
                    $lastResponse = $response;
                    continue;
                }

                return $response;
            } catch (\Throwable $e) {
                $msg = $e->getMessage();
                if (stripos($msg, 'Could not resolve host') !== false) {
                    throw new \RuntimeException('AI service DNS/network issue. Please check internet/DNS and try again.');
                }
                $lastException = $e;
            }
        }

        if ($lastResponse) {
            return $lastResponse;
        }

        if ($lastException) {
            throw $lastException;
        }

        throw new \RuntimeException('No AI model configured.');
    }

    private function requestGeminiCompletion(array $messages)
    {
        $endpoint = rtrim((string) env('GEMINI_API_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $model = trim((string) env('GEMINI_MODEL', 'gemini-2.5-flash'));
        $apiKey = trim((string) env('GEMINI_API_KEY', ''));
        $timeout = (int) env('GEMINI_API_TIMEOUT', 120);
        $maxTokens = (int) env('GEMINI_MAX_OUTPUT_TOKENS', 900);
        $temperature = (float) env('GEMINI_TEMPERATURE', 0.4);

        if ($apiKey === '' || $model === '') {
            throw new \RuntimeException('Gemini API is not configured.');
        }

        $systemText = '';
        $contents = [];

        foreach ($messages as $message) {
            $role = (string) ($message['role'] ?? 'user');
            $content = trim((string) ($message['content'] ?? ''));
            if ($content === '') {
                continue;
            }

            if ($role === 'system') {
                $systemText .= ($systemText === '' ? '' : "\n\n") . $content;
                continue;
            }

            $contents[] = [
                'role' => $role === 'assistant' ? 'model' : 'user',
                'parts' => [
                    ['text' => $content],
                ],
            ];
        }

        if (empty($contents)) {
            $contents[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => 'Hello'],
                ],
            ];
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'maxOutputTokens' => $maxTokens > 0 ? $maxTokens : 900,
                'temperature' => $temperature,
                'thinkingConfig' => [
                    'thinkingBudget' => 0,
                ],
            ],
        ];

        if ($systemText !== '') {
            $payload['systemInstruction'] = [
                'parts' => [
                    ['text' => $systemText],
                ],
            ];
        }

        return Http::timeout($timeout > 0 ? $timeout : 120)
            ->post($endpoint . '/models/' . rawurlencode($model) . ':generateContent?key=' . urlencode($apiKey), $payload);
    }

    private function buildRelevantExcerpt(string $content, string $question, int $maxLength = 4500): string
    {
        $content = trim($content);
        if ($content === '') {
            return '';
        }

        if (mb_strlen($content) <= $maxLength) {
            return $content;
        }

        $question = strtolower(trim($question));
        $tokens = preg_split('/\W+/u', $question, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $tokens = array_values(array_filter($tokens, function ($token) {
            return mb_strlen($token) >= 4;
        }));

        if (empty($tokens)) {
            return mb_substr($content, 0, $maxLength);
        }

        $lower = mb_strtolower($content);
        foreach ($tokens as $token) {
            $pos = mb_strpos($lower, mb_strtolower($token));
            if ($pos !== false) {
                $start = max(0, $pos - (int) floor($maxLength / 3));
                return mb_substr($content, $start, $maxLength);
            }
        }

        return mb_substr($content, 0, $maxLength);
    }

    private function buildFullFileContent(string $content, int $maxLength): string
    {
        $content = trim($content);
        if ($content === '' || $maxLength <= 0) {
            return '';
        }

        if (mb_strlen($content) <= $maxLength) {
            return $content;
        }

        return mb_substr($content, 0, max(0, $maxLength - 120))
            . "\n\n[Content truncated because the uploaded file is larger than the AI context limit.]";
    }

    private function looksLikeNoFileAccessReply(string $reply): bool
    {
        $reply = strtolower($reply);
        $patterns = [
            "can't view files",
            "cannot view files",
            "can't access files",
            "cannot access files",
            "i don't have access to",
            "i can’t view files directly",
            "since i can’t view files directly",
        ];

        foreach ($patterns as $pattern) {
            if (strpos($reply, strtolower($pattern)) !== false) {
                return true;
            }
        }

        return false;
    }

    private function resolveModels(): array
    {
        $primary = trim((string) env('OPENROUTER_MODEL', 'openrouter/free'));
        $fallback = trim((string) env('OPENROUTER_FALLBACK_MODEL', 'openrouter/free'));

        $models = array_values(array_filter([$primary, $fallback]));
        $models = array_values(array_unique($models));

        return empty($models) ? ['openrouter/free'] : $models;
    }

    private function buildLocalFallbackReply(string $message, ?int $lessonId, ?int $fileId, ?string $subject = null, string $mode = 'normal', $childId = null): ?string
    {
        if ($lessonId) {
            $lesson = UnitLesson::with('files')->find($lessonId);
            if ($lesson && $lesson->files->isNotEmpty()) {
                $combined = $this->buildLocalLessonExcerpt($lesson->files, $message, 2200);
                if (trim($combined) !== '') {
                    if ($mode === 'quiz') {
                        return $this->buildLocalQuizQuestion($combined);
                    }

                    $msg = mb_strtolower(trim($message));
                    if ($msg === '' || str_contains($msg, 'summary') || str_contains($msg, 'what is in this file')) {
                        return "Nextgen AI Tutor is busy right now, but no worries. Here is a quick summary from your selected lesson files:\n\n" . $combined;
                    }

                    return "Nextgen AI Tutor is busy right now, so I found this helpful part from your selected lesson files:\n\n" . $combined;
                }
            }
        }

        if ($subject) {
            $files = $this->filesForSubject($subject, (int) $childId);
            if ($files->isEmpty()) {
                return null;
            }

            $combined = $this->buildLocalSubjectExcerpt($files, $message, 2200);
            if (trim($combined) === '') {
                return null;
            }

            if ($mode === 'quiz') {
                return $this->buildLocalQuizQuestion($combined);
            }

            $msg = mb_strtolower(trim($message));
            if ($msg === '' || str_contains($msg, 'summary') || str_contains($msg, 'what is in this file')) {
                return "Nextgen AI Tutor is busy right now, but no worries. Here is a quick summary from your selected subject files:\n\n" . $combined;
            }

            return "Nextgen AI Tutor is busy right now, so I found this helpful part from your selected subject files:\n\n" . $combined;
        }

        if (!$fileId) {
            return null;
        }

        $file = CurriculumFile::find($fileId);
        if (!$file) {
            return null;
        }

        $content = $this->getOrExtractContent($file);
        if (trim($content) === '') {
            return null;
        }

        if ($mode === 'quiz') {
            return $this->buildLocalQuizQuestion($content);
        }

        $msg = mb_strtolower(trim($message));
        if ($msg === '' || str_contains($msg, 'summary') || str_contains($msg, 'what is in this file')) {
            $excerpt = $this->buildRelevantExcerpt($content, '', 1200);
            return "Nextgen AI Tutor is busy right now, but no worries. Here is a quick summary from your file:\n\n" . $excerpt;
        }

        $excerpt = $this->buildRelevantExcerpt($content, $message, 1200);
        return "Nextgen AI Tutor is busy right now, so I found this helpful part from your file:\n\n" . $excerpt;
    }

    private function buildLocalQuizQuestion(string $content): string
    {
        $excerpt = $this->buildRelevantExcerpt($content, '', 800);
        $lines = preg_split('/\R+/', $excerpt) ?: [];
        $line = '';
        foreach ($lines as $candidate) {
            $candidate = trim($candidate);
            if (mb_strlen($candidate) >= 30) {
                $line = $candidate;
                break;
            }
        }

        if ($line === '') {
            $line = trim(mb_substr($excerpt, 0, 180));
        }

        return "Nextgen AI Tutor is busy right now, so I will continue with a simple practice question.\n\n"
            . "Question: Based on this statement, explain the main idea in your own words:\n"
            . "\"{$line}\"";
    }

    private function filesForSubject(string $subject, int $childId = 0)
    {
        $query = CurriculumFile::query()->where('subject', $subject)->latest();

        if (auth()->check() && strtolower((string) auth()->user()->user_type) === 'student') {
            $studentClass = optional(optional(auth()->user())->student_record)->my_class->name;
            if ($studentClass) {
                $query->where('year', $studentClass);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif (auth()->check() && strtolower((string) auth()->user()->user_type) === 'parent') {
            $targetStudent = $this->resolveTargetStudent($childId);
            if (!empty($targetStudent['class_name'])) {
                $query->where('year', $targetStudent['class_name']);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query->get();
    }

    private function resolveTargetStudent(int $childId = 0): array
    {
        $default = [
            'name' => null,
            'class_name' => null,
        ];

        if (!auth()->check()) {
            return $default;
        }

        $user = auth()->user();
        $type = strtolower((string) $user->user_type);

        if ($type === 'student') {
            return [
                'name' => (string) $user->name,
                'class_name' => (string) (optional(optional($user)->student_record)->my_class->name ?? ''),
            ];
        }

        if ($type === 'parent') {
            $record = StudentRecord::query()
                ->where('my_parent_id', $user->id)
                ->when($childId > 0, function ($q) use ($childId) {
                    $q->where('user_id', $childId);
                })
                ->with(['user:id,name', 'my_class:id,name'])
                ->first();

            if (!$record) {
                return $default;
            }

            return [
                'name' => (string) optional($record->user)->name,
                'class_name' => (string) optional($record->my_class)->name,
            ];
        }

        return $default;
    }

    private function canAccessLesson(UnitLesson $lesson, ?string $className = null): bool
    {
        $lesson->loadMissing('unit.week.subject.my_class');
        if ((bool) optional(optional($lesson->unit)->week)->is_blocked) {
            return false;
        }

        $subjectClassName = (string) optional(optional(optional(optional($lesson->unit)->week)->subject)->my_class)->name;
        $className = trim((string) $className);

        if ($className !== '') {
            return $subjectClassName !== '' && $subjectClassName === $className;
        }

        $isStudent = auth()->check() && strtolower((string) auth()->user()->user_type) === 'student';
        if ($isStudent) {
            return false;
        }

        $isParent = auth()->check() && strtolower((string) auth()->user()->user_type) === 'parent';
        if ($isParent) {
            return false;
        }

        return true;
    }

    private function buildSubjectContextBlock($files, string $question, int $maxChars = 28000): string
    {
        $remaining = $maxChars;
        $block = "---- SUBJECT FILES CONTEXT START ----\n";

        foreach ($files as $file) {
            if ($remaining <= 300) {
                break;
            }

            $content = $this->getOrExtractContent($file);
            if (trim($content) === '') {
                continue;
            }

            $snippet = $this->buildRelevantExcerpt($content, $question, min(1800, $remaining));
            if (trim($snippet) === '') {
                continue;
            }

            $header = "[File: {$file->name} | Year: {$file->year} | Subject: {$file->subject}]\n";
            $segment = $header . $snippet . "\n\n";
            $segmentLen = mb_strlen($segment);
            if ($segmentLen > $remaining) {
                $segment = mb_substr($segment, 0, $remaining);
                $segmentLen = mb_strlen($segment);
            }

            $block .= $segment;
            $remaining -= $segmentLen;
        }

        $block .= "---- SUBJECT FILES CONTEXT END ----\n\n";
        return $block;
    }

    private function buildLocalSubjectExcerpt($files, string $question, int $maxChars = 2200): string
    {
        $remaining = $maxChars;
        $parts = [];

        foreach ($files as $file) {
            if ($remaining <= 180) {
                break;
            }

            $content = $this->getOrExtractContent($file);
            if (trim($content) === '') {
                continue;
            }

            $snippet = $this->buildRelevantExcerpt($content, $question, min(700, $remaining));
            if (trim($snippet) === '') {
                continue;
            }

            $piece = "[{$file->name}]\n{$snippet}";
            $piece = mb_substr($piece, 0, $remaining);
            $parts[] = $piece;
            $remaining -= mb_strlen($piece) + 2;
        }

        return implode("\n\n", $parts);
    }

    private function buildLocalLessonExcerpt($files, string $question, int $maxChars = 2200): string
    {
        $remaining = $maxChars;
        $parts = [];

        foreach ($files as $file) {
            if ($remaining <= 180) {
                break;
            }

            $content = $this->getOrExtractLessonFileContent($file);
            if (trim($content) === '') {
                continue;
            }

            $snippet = $this->buildRelevantExcerpt($content, $question, min(700, $remaining));
            if (trim($snippet) === '') {
                continue;
            }

            $piece = "[Lesson File: {$file->title}]\n{$snippet}";
            $piece = mb_substr($piece, 0, $remaining);
            $parts[] = $piece;
            $remaining -= mb_strlen($piece) + 2;
        }

        return implode("\n\n", $parts);
    }

    private function extractReplyContent(array $data): string
    {
        $geminiContent = data_get($data, 'candidates.0.content.parts');
        if (is_array($geminiContent)) {
            $parts = [];
            foreach ($geminiContent as $item) {
                if (is_array($item) && isset($item['text']) && is_string($item['text'])) {
                    $parts[] = $item['text'];
                }
            }

            if (!empty($parts)) {
                return trim(implode("\n", $parts));
            }
        }

        $content = data_get($data, 'choices.0.message.content', '');

        if (is_string($content)) {
            return trim($content);
        }

        if (is_array($content)) {
            $parts = [];
            foreach ($content as $item) {
                if (is_string($item)) {
                    $parts[] = $item;
                    continue;
                }

                if (is_array($item) && isset($item['text']) && is_string($item['text'])) {
                    $parts[] = $item['text'];
                }
            }

            return trim(implode("\n", $parts));
        }

        return '';
    }
}
