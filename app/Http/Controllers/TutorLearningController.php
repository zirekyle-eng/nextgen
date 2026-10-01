<?php

namespace App\Http\Controllers;

use App\Models\CurriculumFile;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Models\TutorFileProgress;
use App\Models\TutorLessonProgress;
use App\Models\TutorLearningProgress;
use App\Models\TutorQuizResult;
use App\Models\UnitLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TutorLearningController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $context = $this->resolveTutorContext($request);
        $subjectStructure = $this->subjectStructureForCurrentUser($context['class_name']);
        $subjects = $this->subjectListForCurrentUser($context['class_name'])
            ->merge($subjectStructure->keys())
            ->unique()
            ->values();

        if (!$this->learningTablesReady()) {
            return view('smart-tutor.dashboard', [
                'subjects' => $subjects,
                'progressRows' => collect(),
                'stats' => [
                    'subjects_started' => 0,
                    'total_points' => 0,
                    'completed_quizzes' => 0,
                    'avg_quiz_percentage' => 0,
                    'files_started' => 0,
                    'files_completed' => 0,
                ],
                'latestProgress' => null,
                'latestQuiz' => null,
                'subjectFiles' => collect(),
                'subjectStructure' => $subjectStructure,
                'isParentTutorView' => $context['is_parent'],
                'childrenForParent' => $context['children'],
                'selectedChildId' => $context['selected_child_id'],
                'selectedChildName' => $context['selected_child_name'],
            ])->with('warning', 'Smart Tutor tables are not ready yet. Run migrations first.');
        }

        $progressRows = TutorLearningProgress::where('user_id', $user->id)
            ->orderByDesc('last_activity_at')
            ->get();

        $stats = [
            'subjects_started' => $progressRows->count(),
            'total_points' => (int) $progressRows->sum('points'),
            'completed_quizzes' => (int) $progressRows->sum('completed_quizzes'),
            'avg_quiz_percentage' => (int) round(
                TutorQuizResult::where('user_id', $user->id)->avg('percentage') ?? 0
            ),
        ];

        $latestProgress = $progressRows->first();
        $latestQuiz = TutorQuizResult::where('user_id', $user->id)->latest()->first();
        $subjectFiles = $this->subjectFilesWithStatus($user->id, $context['class_name']);
        $allLessons = $subjectStructure->flatMap(function ($subjectNode) {
            return collect($subjectNode['units'] ?? [])->flatMap(function ($unit) {
                return collect($unit['lessons'] ?? []);
            });
        });

        $stats['files_started'] = $allLessons->where('status', 'in_progress')->count();
        $stats['files_completed'] = $allLessons->where('status', 'completed')->count();

        return view('smart-tutor.dashboard', [
            'subjects' => $subjects,
            'progressRows' => $progressRows,
            'stats' => $stats,
            'latestProgress' => $latestProgress,
            'latestQuiz' => $latestQuiz,
            'subjectFiles' => $subjectFiles,
            'subjectStructure' => $subjectStructure,
            'isParentTutorView' => $context['is_parent'],
            'childrenForParent' => $context['children'],
            'selectedChildId' => $context['selected_child_id'],
            'selectedChildName' => $context['selected_child_name'],
        ]);
    }

    public function app(Request $request)
    {
        $context = $this->resolveTutorContext($request);
        $subjectStructure = $this->subjectStructureForCurrentUser($context['class_name']);
        $subjects = $this->subjectListForCurrentUser($context['class_name'])
            ->merge($subjectStructure->keys())
            ->unique()
            ->values();
        if (!$this->learningTablesReady()) {
            return redirect()->route('smart_tutor.dashboard')
                ->with('warning', 'Smart Tutor tables are not ready yet. Run migrations first.');
        }

        $latestProgress = TutorLearningProgress::where('user_id', auth()->id())
            ->orderByDesc('last_activity_at')
            ->first();

        $preselectedSubject = trim((string) $request->query('subject', '')) ?: null;
        $preselectedUnitId = $request->query('unit_id') !== null ? (int) $request->query('unit_id') : null;
        $preselectedLessonId = $request->query('lesson_id') !== null ? (int) $request->query('lesson_id') : null;
        $preselectedFileId = $request->query('file') !== null ? (int) $request->query('file') : null;

        return view('smart-tutor.app', [
            'subjects' => $subjects,
            'subjectStructure' => $subjectStructure,
            'latestProgress' => $latestProgress,
            'preselectedSubject' => $preselectedSubject,
            'preselectedUnitId' => $preselectedUnitId,
            'preselectedLessonId' => $preselectedLessonId,
            'preselectedFileId' => $preselectedFileId,
            'isParentTutorView' => $context['is_parent'],
            'childrenForParent' => $context['children'],
            'selectedChildId' => $context['selected_child_id'],
            'selectedChildName' => $context['selected_child_name'],
        ]);
    }

    public function getProgress(Request $request, string $subject)
    {
        if (!$this->learningTablesReady()) {
            return response()->json([
                'success' => false,
                'error' => 'Smart Tutor tables are not ready. Please run migrations.',
            ], 503);
        }

        $row = TutorLearningProgress::where('user_id', auth()->id())
            ->where('subject', trim($subject))
            ->first();

        if (!$row) {
            return response()->json([
                'success' => true,
                'progress' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'progress' => [
                'subject' => $row->subject,
                'last_file_id' => $row->last_file_id,
                'last_file_name' => $row->last_file_name,
                'last_file_status' => $this->resolveFileStatus(auth()->id(), $row->last_file_id),
                'last_mode' => $row->last_mode,
                'conversation_messages' => is_array($row->conversation_messages) ? $row->conversation_messages : [],
                'last_activity_at' => optional($row->last_activity_at)->toDateTimeString(),
            ],
        ]);
    }

    public function getFileSession(CurriculumFile $file)
    {
        $context = $this->resolveTutorContext(request());
        if (!$this->learningTablesReady()) {
            return response()->json([
                'success' => false,
                'error' => 'Smart Tutor tables are not ready. Please run migrations.',
            ], 503);
        }

        if (!$this->canAccessFile($file, $context['class_name'])) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have access to this file.',
            ], 403);
        }

        $row = TutorFileProgress::where('user_id', auth()->id())
            ->where('file_id', $file->id)
            ->first();

        $latestQuiz = TutorQuizResult::where('user_id', auth()->id())
            ->where('file_id', $file->id)
            ->latest()
            ->first();

        if (!$row) {
            return response()->json([
                'success' => true,
                'session' => [
                    'status' => 'not_started',
                    'last_mode' => 'normal',
                    'messages' => [],
                    'started_at' => null,
                    'completed_at' => null,
                    'last_activity_at' => null,
                    'latest_quiz_result' => $latestQuiz ? [
                        'score' => (float) $latestQuiz->score,
                        'total' => (int) $latestQuiz->total,
                        'percentage' => (int) $latestQuiz->percentage,
                        'points_awarded' => (int) $latestQuiz->points_awarded,
                        'recommendation' => (string) $latestQuiz->recommendation,
                    ] : null,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'session' => [
                'status' => $row->status ?: 'not_started',
                'last_mode' => $row->last_mode ?: 'normal',
                'messages' => is_array($row->conversation_messages) ? $row->conversation_messages : [],
                'started_at' => optional($row->started_at)->toDateTimeString(),
                'completed_at' => optional($row->completed_at)->toDateTimeString(),
                'last_activity_at' => optional($row->last_activity_at)->toDateTimeString(),
                'latest_quiz_result' => $latestQuiz ? [
                    'score' => (float) $latestQuiz->score,
                    'total' => (int) $latestQuiz->total,
                    'percentage' => (int) $latestQuiz->percentage,
                    'points_awarded' => (int) $latestQuiz->points_awarded,
                    'recommendation' => (string) $latestQuiz->recommendation,
                ] : null,
            ],
        ]);
    }

    public function getLessonSession(UnitLesson $lesson, Request $request)
    {
        $context = $this->resolveTutorContext($request);
        if (!$this->learningTablesReady()) {
            return response()->json([
                'success' => false,
                'error' => 'Smart Tutor tables are not ready. Please run migrations.',
            ], 503);
        }

        if (!$this->canAccessLesson($lesson, $context['class_name'])) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have access to this lesson.',
            ], 403);
        }

        $row = Schema::hasTable('tutor_lesson_progress')
            ? TutorLessonProgress::where('user_id', auth()->id())
                ->where('lesson_id', (int) $lesson->id)
                ->first()
            : null;

        if (!$row) {
            return response()->json([
                'success' => true,
                'session' => [
                    'status' => 'not_started',
                    'last_mode' => 'normal',
                    'messages' => [],
                    'started_at' => null,
                    'completed_at' => null,
                    'last_activity_at' => null,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'session' => [
                'status' => $row->status ?: 'not_started',
                'last_mode' => $row->last_mode ?: 'normal',
                'messages' => is_array($row->conversation_messages) ? $row->conversation_messages : [],
                'started_at' => optional($row->started_at)->toDateTimeString(),
                'completed_at' => optional($row->completed_at)->toDateTimeString(),
                'last_activity_at' => optional($row->last_activity_at)->toDateTimeString(),
            ],
        ]);
    }

    public function updateProgress(Request $request)
    {
        if (!$this->learningTablesReady()) {
            return response()->json([
                'success' => false,
                'error' => 'Smart Tutor tables are not ready. Please run migrations.',
            ], 503);
        }

        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'file_id' => 'nullable|exists:curriculum_files,id',
            'file_name' => 'nullable|string|max:255',
            'unit_id' => 'nullable|exists:week_units,id',
            'lesson_id' => 'nullable|exists:unit_lessons,id',
            'mode' => 'nullable|in:normal,quiz',
            'child_id' => 'nullable|integer',
            'message_count' => 'nullable|integer|min:0|max:5000',
            'messages' => 'nullable|array|max:120',
            'messages.*.role' => 'required_with:messages|string|in:user,assistant,bot',
            'messages.*.content' => 'required_with:messages|string|max:4000',
        ]);
        $context = $this->resolveTutorContext($request);
        if (!empty($data['file_id'])) {
            $file = CurriculumFile::find((int) $data['file_id']);
            if (!$file || !$this->canAccessFile($file, $context['class_name'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have access to this file.',
                ], 403);
            }
        }
        $lessonStatus = null;
        if (!empty($data['lesson_id'])) {
            $lesson = UnitLesson::with('unit.week.subject.my_class')->find((int) $data['lesson_id']);
            if (!$lesson || !$this->canAccessLesson($lesson, $context['class_name'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have access to this lesson.',
                ], 403);
            }
        }

        $progress = TutorLearningProgress::firstOrNew([
            'user_id' => auth()->id(),
            'subject' => trim((string) $data['subject']),
        ]);

        $isNewRow = !$progress->exists;
        $progress->last_file_id = $data['file_id'] ?? $progress->last_file_id;
        $progress->last_file_name = $data['file_name'] ?? $progress->last_file_name;
        $progress->last_mode = $data['mode'] ?? ($progress->last_mode ?: 'normal');
        $progress->last_activity_at = now();

        if (isset($data['messages']) && is_array($data['messages'])) {
            $progress->conversation_messages = $this->normalizeMessages($data['messages']);
        }

        if ($isNewRow) {
            $progress->points = max(0, (int) ($progress->points ?? 0) + 5);
        }

        $progress->save();
        $fileStatus = null;
        if (!empty($data['file_id'])) {
            $fileStatus = $this->touchFileProgress(
                auth()->id(),
                (int) $data['file_id'],
                trim((string) $data['subject']),
                'in_progress',
                $data['mode'] ?? 'normal',
                isset($data['messages']) && is_array($data['messages']) ? $this->normalizeMessages($data['messages']) : null
            );
        }
        if (!empty($data['lesson_id']) && !empty($lesson)) {
            $lessonStatus = $this->touchLessonProgress(
                auth()->id(),
                $lesson,
                'in_progress',
                $data['mode'] ?? 'normal',
                isset($data['messages']) && is_array($data['messages']) ? $this->normalizeMessages($data['messages']) : null
            );
        }

        return response()->json([
            'success' => true,
            'progress_id' => $progress->id,
            'points' => (int) $progress->points,
            'file_status' => $fileStatus,
            'lesson_status' => $lessonStatus,
        ]);
    }

    public function updateFileStatus(Request $request)
    {
        $data = $request->validate([
            'file_id' => 'required|exists:curriculum_files,id',
            'status' => 'required|in:not_started,in_progress,completed',
            'child_id' => 'nullable|integer',
        ]);
        $context = $this->resolveTutorContext($request);

        $file = CurriculumFile::findOrFail((int) $data['file_id']);
        if (!$this->canAccessFile($file, $context['class_name'])) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have access to this file.',
            ], 403);
        }
        $row = TutorFileProgress::firstOrNew([
            'user_id' => auth()->id(),
            'file_id' => (int) $file->id,
        ]);

        $row->subject = (string) $file->subject;
        $row->last_activity_at = now();
        if (!$row->started_at) {
            $row->started_at = now();
        }

        $status = (string) $data['status'];
        $row->status = $status;
        if ($status === 'completed') {
            $row->completed_at = now();
        } elseif ($status === 'in_progress') {
            $row->completed_at = null;
        } elseif ($status === 'not_started') {
            $row->started_at = null;
            $row->completed_at = null;
        }

        $row->save();

        return response()->json([
            'success' => true,
            'status' => $row->status,
            'completed_at' => optional($row->completed_at)->toDateTimeString(),
        ]);
    }

    public function updateLessonStatus(Request $request)
    {
        $data = $request->validate([
            'lesson_id' => 'required|exists:unit_lessons,id',
            'status' => 'required|in:not_started,in_progress,completed',
            'child_id' => 'nullable|integer',
        ]);

        $context = $this->resolveTutorContext($request);
        $lesson = UnitLesson::with('unit.week.subject.my_class')->findOrFail((int) $data['lesson_id']);
        if (!$this->canAccessLesson($lesson, $context['class_name'])) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have access to this lesson.',
            ], 403);
        }

        $status = $this->touchLessonProgress(
            auth()->id(),
            $lesson,
            (string) $data['status'],
            'normal',
            null,
            true
        );

        $row = TutorLessonProgress::where('user_id', auth()->id())
            ->where('lesson_id', (int) $lesson->id)
            ->first();

        return response()->json([
            'success' => true,
            'status' => $status,
            'started_at' => optional($row?->started_at)->toDateTimeString(),
            'completed_at' => optional($row?->completed_at)->toDateTimeString(),
            'last_activity_at' => optional($row?->last_activity_at)->toDateTimeString(),
        ]);
    }

    public function storeQuizResult(Request $request)
    {
        if (!$this->learningTablesReady()) {
            return response()->json([
                'success' => false,
                'error' => 'Smart Tutor tables are not ready. Please run migrations.',
            ], 503);
        }

        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'file_id' => 'nullable|exists:curriculum_files,id',
            'file_name' => 'nullable|string|max:255',
            'child_id' => 'nullable|integer',
            'score' => 'required|numeric|min:0|max:10',
            'total' => 'nullable|integer|min:1|max:20',
        ]);
        $context = $this->resolveTutorContext($request);
        if (!empty($data['file_id'])) {
            $file = CurriculumFile::find((int) $data['file_id']);
            if (!$file || !$this->canAccessFile($file, $context['class_name'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have access to this file.',
                ], 403);
            }
        }

        $total = (int) ($data['total'] ?? 10);
        $score = (float) $data['score'];
        $percentage = (int) round(($score / max(1, $total)) * 100);
        $pointsAwarded = (int) round($percentage / 5);
        $recommendation = $this->recommendationForScore($percentage, $data['subject']);

        $result = TutorQuizResult::create([
            'user_id' => auth()->id(),
            'subject' => trim((string) $data['subject']),
            'file_id' => $data['file_id'] ?? null,
            'file_name' => $data['file_name'] ?? null,
            'score' => $score,
            'total' => $total,
            'percentage' => $percentage,
            'points_awarded' => $pointsAwarded,
            'recommendation' => $recommendation,
        ]);

        $progress = TutorLearningProgress::firstOrNew([
            'user_id' => auth()->id(),
            'subject' => trim((string) $data['subject']),
        ]);

        $progress->last_file_id = $data['file_id'] ?? $progress->last_file_id;
        $progress->last_file_name = $data['file_name'] ?? $progress->last_file_name;
        $progress->last_mode = 'normal';
        $progress->last_quiz_score = $score;
        $progress->last_quiz_total = $total;
        $progress->points = max(0, (int) $progress->points) + $pointsAwarded;
        $progress->completed_quizzes = max(0, (int) $progress->completed_quizzes) + 1;
        $progress->last_activity_at = now();
        $progress->save();

        return response()->json([
            'success' => true,
            'result' => [
                'score' => $score,
                'total' => $total,
                'percentage' => $percentage,
                'points_awarded' => $pointsAwarded,
                'recommendation' => $recommendation,
            ],
            'totals' => [
                'points' => (int) $progress->points,
                'completed_quizzes' => (int) $progress->completed_quizzes,
            ],
            'result_id' => $result->id,
        ]);
    }

    private function subjectListForCurrentUser(?string $forcedClassName = null)
    {
        $query = CurriculumFile::query();
        $className = trim((string) $forcedClassName);

        if ($className !== '') {
            $query->where('year', $className);
        } elseif (auth()->check() && in_array(strtolower((string) auth()->user()->user_type), ['student', 'parent'], true)) {
            $query->whereRaw('1 = 0');
        }

        return $query->distinct()->pluck('subject')->filter()->values();
    }

    private function subjectStructureForCurrentUser(?string $forcedClassName = null)
    {
        $weekColumns = ['id', 'subject_id', 'week_number', 'title'];
        foreach (['start_date', 'end_date', 'is_blocked', 'block_note'] as $column) {
            if (Schema::hasColumn('subject_weeks', $column)) {
                $weekColumns[] = $column;
            }
        }

        $query = Subject::query()
            ->excludeActivities()
            ->with([
                'weeks:' . implode(',', $weekColumns),
                'weeks.units:id,subject_week_id,unit_number,title',
                'weeks.units.lessons:id,week_unit_id,lesson_number,title',
                'my_class:id,name',
            ])
            ->orderBy('name');

        $className = trim((string) $forcedClassName);
        if ($className !== '') {
            $query->whereHas('my_class', function ($q) use ($className) {
                $q->where('name', $className);
            });
        } elseif (auth()->check() && in_array(strtolower((string) auth()->user()->user_type), ['student', 'parent'], true)) {
            $query->whereRaw('1 = 0');
        }

        $subjects = $query->get();
        $lessonIds = $subjects->flatMap(function (Subject $subject) {
            return $subject->weeks->flatMap(function ($week) {
                return $week->units->flatMap(function ($unit) {
                    return $unit->lessons->pluck('id');
                });
            });
        })->map(function ($id) {
            return (int) $id;
        })->values();

        $statusRows = collect();
        if (Schema::hasTable('tutor_lesson_progress') && $lessonIds->isNotEmpty()) {
            $statusRows = TutorLessonProgress::query()
                ->where('user_id', auth()->id())
                ->whereIn('lesson_id', $lessonIds->all())
                ->get()
                ->keyBy('lesson_id');
        }

        return $subjects->map(function (Subject $subject) use ($statusRows) {
            $units = collect();
            foreach ($subject->weeks as $week) {
                foreach ($week->units as $unit) {
                    $units->push([
                        'id' => (int) $unit->id,
                        'week_id' => (int) $week->id,
                        'week_number' => (int) $week->week_number,
                        'week_title' => (string) ($week->title ?? ''),
                        'week_start_date' => optional($week->start_date)->toDateString(),
                        'week_end_date' => optional($week->end_date)->toDateString(),
                        'week_is_blocked' => (bool) $week->is_blocked,
                        'week_block_note' => (string) ($week->block_note ?? ''),
                        'unit_number' => (int) $unit->unit_number,
                        'title' => (string) $unit->title,
                        'lessons' => $unit->lessons->map(function ($lesson) use ($statusRows) {
                            $progress = $statusRows->get((int) $lesson->id);
                            return [
                                'id' => (int) $lesson->id,
                                'lesson_number' => (int) $lesson->lesson_number,
                                'title' => (string) $lesson->title,
                                'status' => $progress ? (string) $progress->status : 'not_started',
                                'started_at' => optional($progress?->started_at)->toDateTimeString(),
                                'completed_at' => optional($progress?->completed_at)->toDateTimeString(),
                                'last_activity_at' => optional($progress?->last_activity_at)->toDateTimeString(),
                            ];
                        })->sortBy('lesson_number')->values(),
                    ]);
                }
            }

            $units = $units->sortBy([
                ['week_number', 'asc'],
                ['unit_number', 'asc'],
            ])->values();

            return [
                'subject_id' => (int) $subject->id,
                'subject' => (string) $subject->name,
                'class_name' => (string) optional($subject->my_class)->name,
                'units' => $units,
            ];
        })->keyBy('subject');
    }

    private function getStudentClassName()
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        $studentRecord = $user->student_record()->with('my_class')->first();
        return $studentRecord && $studentRecord->my_class
            ? (string) $studentRecord->my_class->name
            : null;
    }

    private function recommendationForScore(int $percentage, string $subject)
    {
        $subject = Str::title(trim($subject));

        if ($percentage < 50) {
            return "Review the same {$subject} file for 10 minutes, then retry the quiz.";
        }

        if ($percentage < 80) {
            return "Good effort. Practice 2 more examples in {$subject}, then take a new quiz.";
        }

        return "Excellent. Move to a new file in {$subject} and level up.";
    }

    private function learningTablesReady(): bool
    {
        return Schema::hasTable('tutor_learning_progress')
            && Schema::hasTable('tutor_quiz_results')
            && Schema::hasTable('tutor_lesson_progress');
    }

    private function normalizeMessages(array $messages): array
    {
        $clean = [];
        foreach (array_slice($messages, -80) as $msg) {
            $role = (string) ($msg['role'] ?? '');
            $content = trim((string) ($msg['content'] ?? ''));
            if ($content === '' || !in_array($role, ['user', 'assistant', 'bot'], true)) {
                continue;
            }

            $clean[] = [
                'role' => $role,
                'content' => mb_substr($content, 0, 4000),
            ];
        }

        return $clean;
    }

    private function touchFileProgress(
        int $userId,
        int $fileId,
        string $subject,
        string $status = 'in_progress',
        string $lastMode = 'normal',
        ?array $messages = null
    ): string
    {
        if (!Schema::hasTable('tutor_file_progress')) {
            return 'not_started';
        }

        $row = TutorFileProgress::firstOrNew([
            'user_id' => $userId,
            'file_id' => $fileId,
        ]);

        $row->subject = $subject;
        $row->last_activity_at = now();
        $row->last_mode = $lastMode;
        if (!$row->started_at) {
            $row->started_at = now();
        }

        // Do not downgrade completed files automatically.
        if ($row->status !== 'completed') {
            $row->status = $status;
            if ($status === 'completed') {
                $row->completed_at = now();
            }
        }

        if (is_array($messages)) {
            $row->conversation_messages = $messages;
        }

        $row->save();

        return (string) $row->status;
    }

    private function touchLessonProgress(
        int $userId,
        UnitLesson $lesson,
        string $status = 'in_progress',
        string $lastMode = 'normal',
        ?array $messages = null,
        bool $forceStatus = false
    ): string
    {
        if (!Schema::hasTable('tutor_lesson_progress')) {
            return 'not_started';
        }

        $lesson->loadMissing('unit.week.subject');
        $subjectName = (string) optional(optional(optional($lesson->unit)->week)->subject)->name;
        $row = TutorLessonProgress::firstOrNew([
            'user_id' => $userId,
            'lesson_id' => (int) $lesson->id,
        ]);

        $row->unit_id = (int) $lesson->week_unit_id;
        $row->subject = $subjectName;
        $row->last_activity_at = now();
        $row->last_mode = $lastMode;

        $nextStatus = in_array($status, ['not_started', 'in_progress', 'completed'], true) ? $status : 'in_progress';
        if ($nextStatus === 'not_started') {
            $row->started_at = null;
            $row->completed_at = null;
        } else {
            if (!$row->started_at) {
                $row->started_at = now();
            }
            if ($nextStatus === 'completed') {
                $row->completed_at = now();
            } elseif ($nextStatus === 'in_progress') {
                $row->completed_at = null;
            }
        }

        if ($forceStatus || $row->status !== 'completed') {
            $row->status = $nextStatus;
        }

        if (is_array($messages)) {
            $row->conversation_messages = $messages;
        }

        $row->save();

        return (string) $row->status;
    }

    private function subjectFilesWithStatus(int $userId, ?string $className = null)
    {
        $filesQuery = CurriculumFile::query()->orderBy('subject')->orderBy('name');
        $className = trim((string) $className);
        if ($className !== '') {
            $filesQuery->where('year', $className);
        } elseif (auth()->check() && in_array(strtolower((string) auth()->user()->user_type), ['student', 'parent'], true)) {
            $filesQuery->whereRaw('1=0');
        }

        $files = $filesQuery->get(['id', 'name', 'subject', 'year']);

        $statusRows = Schema::hasTable('tutor_file_progress')
            ? TutorFileProgress::where('user_id', $userId)
                ->whereIn('file_id', $files->pluck('id')->all())
                ->get()
                ->keyBy('file_id')
            : collect();

        return $files->groupBy('subject')->map(function ($subjectFiles) use ($statusRows) {
            return $subjectFiles->map(function ($file) use ($statusRows) {
                $row = $statusRows->get($file->id);
                return [
                    'id' => (int) $file->id,
                    'name' => (string) $file->name,
                    'year' => (string) $file->year,
                    'status' => $row ? (string) $row->status : 'not_started',
                    'started_at' => optional($row?->started_at)->toDateTimeString(),
                    'completed_at' => optional($row?->completed_at)->toDateTimeString(),
                ];
            })->values();
        });
    }

    private function resolveFileStatus(int $userId, ?int $fileId): string
    {
        if (!$fileId || !Schema::hasTable('tutor_file_progress')) {
            return 'not_started';
        }

        $row = TutorFileProgress::where('user_id', $userId)
            ->where('file_id', $fileId)
            ->first();

        return $row ? (string) $row->status : 'not_started';
    }

    private function canAccessFile(CurriculumFile $file, ?string $className = null): bool
    {
        $className = trim((string) $className);
        if ($className !== '') {
            return (string) $file->year === $className;
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

    private function resolveTutorContext(Request $request): array
    {
        $user = auth()->user();
        $isParent = $user && strtolower((string) $user->user_type) === 'parent';
        $isStudent = $user && strtolower((string) $user->user_type) === 'student';

        $children = collect();
        $selectedChildId = null;
        $selectedChildName = null;
        $className = null;

        if ($isStudent) {
            $className = $this->getStudentClassName();
        }

        if ($isParent) {
            $children = StudentRecord::query()
                ->where('my_parent_id', $user->id)
                ->with(['user:id,name', 'my_class:id,name'])
                ->get()
                ->filter(function ($record) {
                    return (int) $record->user_id > 0 && $record->user;
                })
                ->values();

            $requestedChildId = (int) ($request->input('child_id', $request->query('child_id', 0)));
            $selectedRecord = $children->first(function ($record) use ($requestedChildId) {
                return (int) $record->user_id === $requestedChildId;
            });

            if (!$selectedRecord) {
                $selectedRecord = $children->first();
            }

            if ($selectedRecord) {
                $selectedChildId = (int) $selectedRecord->user_id;
                $selectedChildName = (string) optional($selectedRecord->user)->name;
                $className = (string) optional($selectedRecord->my_class)->name;
            }
        }

        return [
            'is_parent' => $isParent,
            'children' => $children,
            'selected_child_id' => $selectedChildId,
            'selected_child_name' => $selectedChildName,
            'class_name' => $className,
        ];
    }
}
