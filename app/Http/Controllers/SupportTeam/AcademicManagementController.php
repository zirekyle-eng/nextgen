<?php

namespace App\Http\Controllers\SupportTeam;

use App\Http\Controllers\Controller;
use App\Models\AcademicHoliday;
use App\Models\AcademicQuiz;
use App\Models\AcademicSession;
use App\Models\LessonFile;
use App\Models\LessonQuestionBank;
use App\Models\MoodleSyncLog;
use App\Models\MyClass;
use App\Models\Subject;
use App\Models\SubjectGeneralFile;
use App\Models\SubjectWeek;
use App\Models\UnitGeneralFile;
use App\Models\UnitLesson;
use App\Models\WeekUnit;
use App\Services\CurriculumFileTextExtractor;
use App\Services\MoodleQuizXmlBuilder;
use App\Services\MoodleService;
use App\Services\QuizQuestionCsvParser;
use App\Services\QuizQuestionPdfParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AcademicManagementController extends Controller
{
    private const HOLIDAY_BLOCK_PREFIX = 'Holiday: ';

    private const HOLIDAY_TYPES = [
        'annual' => 'Annual Holiday',
        'term' => 'Term Holiday',
    ];

    private const SUBJECT_GENERAL_FILE_TYPES = [
        'student_book' => 'Learner Book',
        'work_book' => 'Workbook',
        'teacher_guide' => 'Teacher Guide',
    ];

    private const UNIT_GENERAL_FILE_TYPES = [
        'knowledge_core' => 'Knowledge Core',
        'assessment_unit' => 'Assessment (Unit)',
    ];

    private const LESSON_FILE_TYPES = [
        'digital_resources' => 'Digital Resources',
        'learning_outcomes' => 'Learning Outcomes',
        'scheme_of_work' => 'Scheme of Work',
    ];

    private const MOODLE_FILE_ACTIVITY_TYPES = [
        'resource' => 'File Resource',
        'assignment' => 'Assignment',
    ];

    private const MOODLE_AUDIENCE_OPTIONS = [
        'both' => 'Teachers + Students',
        'students' => 'Students',
        'teachers' => 'Teachers Only',
    ];

    private MoodleService $moodle;
    private CurriculumFileTextExtractor $textExtractor;

    public function __construct(MoodleService $moodle, CurriculumFileTextExtractor $textExtractor)
    {
        $this->moodle = $moodle;
        $this->textExtractor = $textExtractor;
    }

    public function index(Request $request)
    {
        $sessions = AcademicSession::query()
            ->with('classes:id,name')
            ->orderByDesc('start_date')
            ->get();

        $holidays = AcademicHoliday::query()
            ->with('session:id,name')
            ->orderBy('starts_on')
            ->orderBy('name')
            ->get();

        $allClasses = MyClass::query()->orderBy('name')->get(['id', 'name']);

        $sessionCalendarStats = [];
        $sessionSubjects = [];
        foreach ($sessions as $session) {
            $classIds = $session->classes->pluck('id')->all();
            $subjectCount = empty($classIds)
                ? 0
                : Subject::query()->whereIn('my_class_id', $classIds)->count();

            $sessionSubjects[$session->id] = empty($classIds)
                ? collect()
                : Subject::query()
                    ->with('my_class:id,name')
                    ->whereIn('my_class_id', $classIds)
                    ->orderBy('name')
                    ->get(['id', 'name', 'my_class_id']);

            $weekQuery = SubjectWeek::query()->where('academic_session_id', $session->id);
            $sessionCalendarStats[$session->id] = [
                'subject_count' => $subjectCount,
                'week_count' => (clone $weekQuery)->distinct('week_number')->count('week_number'),
                'week_records' => (clone $weekQuery)->count(),
            ];
        }

        $syncLogs = MoodleSyncLog::query()
            ->latest()
            ->limit(25)
            ->get();

        return view('pages.support_team.academic_management.index', [
            'sessions' => $sessions,
            'allClasses' => $allClasses,
            'sessionCalendarStats' => $sessionCalendarStats,
            'sessionSubjects' => $sessionSubjects,
            'syncLogs' => $syncLogs,
            'holidays' => $holidays,
            'holidayTypes' => self::HOLIDAY_TYPES,
            'subjectGeneralFileTypes' => self::SUBJECT_GENERAL_FILE_TYPES,
            'unitGeneralFileTypes' => self::UNIT_GENERAL_FILE_TYPES,
            'lessonFileTypes' => self::LESSON_FILE_TYPES,
            'moodleFileActivityTypes' => self::MOODLE_FILE_ACTIVITY_TYPES,
            'moodleAudienceOptions' => self::MOODLE_AUDIENCE_OPTIONS,
        ]);
    }

    public function manageWeeks(Request $request, string $workspaceMode = 'materials')
    {
        $sessions = AcademicSession::query()
            ->with('classes:id,name')
            ->orderByDesc('start_date')
            ->get();

        $selectedSessionId = (int) $request->query('session_id', $sessions->first()->id ?? 0);
        $selectedSession = $sessions->firstWhere('id', $selectedSessionId);
        $sessionClassIds = $selectedSession ? $selectedSession->classes->pluck('id')->all() : [];

        $subjects = Subject::query()
            ->with('my_class:id,name')
            ->when(!empty($sessionClassIds), function ($q) use ($sessionClassIds) {
                $q->whereIn('my_class_id', $sessionClassIds);
            }, function ($q) {
                $q->whereRaw('1=0');
            })
            ->orderBy('name')
            ->get();

        $selectedSubjectId = (int) $request->query('subject_id', $subjects->first()->id ?? 0);
        $selectedSubject = $subjects->firstWhere('id', $selectedSubjectId);

        if ($selectedSubject) {
            $selectedSubject->load([
                'generalFiles' => function ($query) use ($selectedSession) {
                    if ($selectedSession) {
                        $query->where(function ($q) use ($selectedSession) {
                            $q->where('academic_session_id', $selectedSession->id)
                                ->orWhereNull('academic_session_id');
                        });
                    }
                    $query->latest();
                },
                'weeks' => function ($query) use ($selectedSession) {
                    if ($selectedSession) {
                        $query->where(function ($q) use ($selectedSession) {
                            $q->where('academic_session_id', $selectedSession->id)
                                ->orWhereNull('academic_session_id');
                        });
                    }
                    $query->orderBy('week_number');
                },
                'weeks.units.generalFiles',
                'weeks.units.quizzes',
                'weeks.units.lessons.files',
                'weeks.units.lessons.questionBanks',
                'weeks.units.lessons.quizzes',
            ]);

            $this->attachLessonQuestionBankPreviews($selectedSubject);
        }

        return view('pages.support_team.academic_management.weeks', [
            'workspaceMode' => $workspaceMode,
            'sessions' => $sessions,
            'selectedSession' => $selectedSession,
            'subjects' => $subjects,
            'selectedSubject' => $selectedSubject,
            'subjectGeneralFileTypes' => self::SUBJECT_GENERAL_FILE_TYPES,
            'unitGeneralFileTypes' => self::UNIT_GENERAL_FILE_TYPES,
            'lessonFileTypes' => self::LESSON_FILE_TYPES,
            'moodleFileActivityTypes' => self::MOODLE_FILE_ACTIVITY_TYPES,
            'moodleAudienceOptions' => self::MOODLE_AUDIENCE_OPTIONS,
        ]);
    }

    public function weeklyContent(Request $request)
    {
        return $this->manageWeeks($request, 'weekly');
    }

    public function showWeek(SubjectWeek $week)
    {
        $week->loadMissing([
            'subject.my_class',
            'units' => function ($query) {
                $query->with([
                    'lessons' => function ($q) {
                        $q->with([
                            'files',
                            'quizzes',
                            'questionBanks'
                        ])->orderBy('lesson_number');
                    },
                    'generalFiles',
                    'quizzes'
                ])->orderBy('unit_number');
            }
        ]);

        $subject = $week->subject;
        if (!$subject) {
            abort(404, 'Subject not found');
        }

        $moodleImportSummary = $this->importMoodleWeekContents($week, $subject);

        $week->load([
            'subject.my_class',
            'units' => function ($query) {
                $query->with([
                    'lessons' => function ($q) {
                        $q->with([
                            'files',
                            'quizzes',
                            'questionBanks'
                        ])->orderBy('lesson_number');
                    },
                    'generalFiles',
                    'quizzes'
                ])->orderBy('unit_number');
            }
        ]);

        return view('pages.support_team.academic_management.week_show', [
            'week' => $week,
            'subject' => $subject,
            'class' => $subject->my_class,
            'unitGeneralFileTypes' => self::UNIT_GENERAL_FILE_TYPES,
            'lessonFileTypes' => self::LESSON_FILE_TYPES,
            'moodleAudienceOptions' => self::MOODLE_AUDIENCE_OPTIONS,
            'moodleImportSummary' => $moodleImportSummary,
        ]);
    }

    public function weekMoodleContents(SubjectWeek $week)
    {
        $week->loadMissing('subject.my_class');
        $subject = $week->subject;
        if (!$subject) {
            return response()->json(['ok' => false, 'error' => 'Subject not found.'], 404);
        }

        if (!$this->moodle->isConfigured()) {
            return response()->json(['ok' => false, 'error' => 'Moodle is not configured.'], 400);
        }

        $courseId = $this->ensureMoodleCourseId($subject);
        if (!$courseId) {
            return response()->json(['ok' => false, 'error' => 'No Moodle course id for this subject.'], 400);
        }

        $result = $this->moodle->getCourseSectionModules($courseId, (int) $week->week_number);
        if (empty($result['ok'])) {
            return response()->json([
                'ok' => false,
                'error' => (string) ($result['error'] ?? 'moodle_fetch_failed'),
                'response' => $result['response'] ?? null,
            ], 400);
        }

        return response()->json([
            'ok' => true,
            'modules' => $result['modules'] ?? [],
            'section' => $result['section'] ?? (int) $week->week_number,
        ]);
    }

    public function reorderWeekMoodleContents(Request $request, SubjectWeek $week)
    {
        $data = $request->validate([
            'order' => 'required|array|min:1',
            'order.*' => 'integer|min:1',
        ]);

        $order = array_values(array_unique(array_map('intval', $data['order'])));
        if (empty($order)) {
            return response()->json(['ok' => false, 'error' => 'Order list is empty.'], 422);
        }

        $week->loadMissing('subject.my_class');
        $subject = $week->subject;
        if (!$subject) {
            return response()->json(['ok' => false, 'error' => 'Subject not found.'], 404);
        }

        if (!$this->moodle->isConfigured()) {
            return response()->json(['ok' => false, 'error' => 'Moodle is not configured.'], 400);
        }

        $courseId = $this->ensureMoodleCourseId($subject);
        if (!$courseId) {
            return response()->json(['ok' => false, 'error' => 'No Moodle course id for this subject.'], 400);
        }

        $results = [];
        $previous = 0;
        foreach ($order as $index => $cmid) {
            $after = $index === 0 ? 0 : $previous;
            $moveResult = $this->moodle->moveCourseModuleAfter((int) $cmid, (int) $after);
            $ok = !empty($moveResult['ok']);

            $results[] = [
                'cmid' => (int) $cmid,
                'after' => (int) $after,
                'ok' => $ok,
                'error' => $ok ? null : (string) ($moveResult['error'] ?? 'move_failed'),
            ];

            $previous = (int) $cmid;
        }

        $success = collect($results)->every(function (array $row) {
            return !empty($row['ok']);
        });

        $this->logSync(
            'subject_week',
            (int) $week->id,
            'moodle_reorder',
            $success ? 'success' : 'failed',
            $courseId,
            ['order' => $order, 'results' => $results],
            $success ? null : 'One or more moves failed'
        );

        return response()->json([
            'ok' => $success,
            'results' => $results,
        ]);
    }

    public function finder(Request $request)
    {
        $sessions = AcademicSession::query()
            ->with('classes:id,name')
            ->orderByDesc('start_date')
            ->get();

        $selectedSessionId = (int) $request->query('session_id', $sessions->first()->id ?? 0);
        $selectedSession = $sessions->firstWhere('id', $selectedSessionId);
        $sessionClassIds = $selectedSession ? $selectedSession->classes->pluck('id')->all() : [];

        $subjects = Subject::query()
            ->with('my_class:id,name')
            ->when(!empty($sessionClassIds), function ($q) use ($sessionClassIds) {
                $q->whereIn('my_class_id', $sessionClassIds);
            }, function ($q) {
                $q->whereRaw('1=0');
            })
            ->orderBy('name')
            ->get();

        $selectedSubjectId = (int) $request->query('subject_id', $subjects->first()->id ?? 0);
        $selectedSubject = $subjects->firstWhere('id', $selectedSubjectId);

        $finderData = $this->buildFinderData($request, $selectedSubject);

        return view('pages.support_team.academic_management.finder', array_merge([
            'sessions' => $sessions,
            'selectedSession' => $selectedSession,
            'subjects' => $subjects,
            'selectedSubject' => $selectedSubject,
        ], $finderData));
    }

    public function quizzes(Request $request)
    {
        $sessions = AcademicSession::query()
            ->with('classes:id,name')
            ->orderByDesc('start_date')
            ->get();

        $selectedSessionId = (int) $request->query('session_id', $sessions->first()->id ?? 0);
        $selectedSession = $sessions->firstWhere('id', $selectedSessionId);
        $sessionClassIds = $selectedSession ? $selectedSession->classes->pluck('id')->all() : [];

        $subjects = Subject::query()
            ->with('my_class:id,name')
            ->when(!empty($sessionClassIds), function ($q) use ($sessionClassIds) {
                $q->whereIn('my_class_id', $sessionClassIds);
            }, function ($q) {
                $q->whereRaw('1=0');
            })
            ->orderBy('name')
            ->get();

        $selectedSubjectId = (int) $request->query('subject_id', $subjects->first()->id ?? 0);
        $selectedSubject = $subjects->firstWhere('id', $selectedSubjectId);

        $units = collect();
        $lessons = collect();
        $quizzes = collect();

        if ($selectedSubject) {
            $units = WeekUnit::query()
                ->select('week_units.*')
                ->join('subject_weeks', 'subject_weeks.id', '=', 'week_units.subject_week_id')
                ->where('subject_weeks.subject_id', $selectedSubject->id)
                ->orderBy('subject_weeks.week_number')
                ->orderBy('week_units.unit_number')
                ->with('week')
                ->get();

            $lessons = UnitLesson::query()
                ->select('unit_lessons.*')
                ->join('week_units', 'week_units.id', '=', 'unit_lessons.week_unit_id')
                ->join('subject_weeks', 'subject_weeks.id', '=', 'week_units.subject_week_id')
                ->where('subject_weeks.subject_id', $selectedSubject->id)
                ->orderBy('subject_weeks.week_number')
                ->orderBy('week_units.unit_number')
                ->orderBy('unit_lessons.lesson_number')
                ->with('unit.week')
                ->get();

            $quizzes = AcademicQuiz::query()
                ->where('subject_id', $selectedSubject->id)
                ->with(['unit.week', 'lesson.unit.week'])
                ->latest()
                ->get();
        }

        return view('pages.support_team.academic_management.quizzes', [
            'sessions' => $sessions,
            'selectedSession' => $selectedSession,
            'subjects' => $subjects,
            'selectedSubject' => $selectedSubject,
            'units' => $units,
            'lessons' => $lessons,
            'quizzes' => $quizzes,
            'moodleAudienceOptions' => self::MOODLE_AUDIENCE_OPTIONS,
        ]);
    }

    public function storeSession(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120|unique:academic_sessions,name',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ]);

        $session = AcademicSession::create([
            'name' => trim((string) $data['name']),
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        $classIds = MyClass::query()->pluck('id')->all();
        if (!empty($classIds)) {
            $session->classes()->syncWithoutDetaching($classIds);
        }

        return back()->with('flash_success', 'Term created and attached to all years/classes successfully.');
    }

    public function updateSession(Request $request, AcademicSession $session)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120|unique:academic_sessions,name,' . $session->id,
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ]);

        $session->update([
            'name' => trim((string) $data['name']),
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? $session->is_active),
        ]);

        return back()->with('flash_success', 'Session updated successfully.');
    }

    public function attachClass(Request $request)
    {
        $data = $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'my_class_id' => 'required|exists:my_classes,id',
        ]);

        $session = AcademicSession::findOrFail($data['academic_session_id']);
        $session->classes()->syncWithoutDetaching([(int) $data['my_class_id']]);

        return back()->with('flash_success', 'Class attached to session.');
    }

    public function storeHoliday(Request $request)
    {
        $data = $request->validate([
            'academic_session_id' => 'nullable|exists:academic_sessions,id',
            'name' => 'required|string|max:160',
            'holiday_type' => 'required|in:' . implode(',', array_keys(self::HOLIDAY_TYPES)),
            'starts_on' => 'required|date',
            'ends_on' => 'required|date|after_or_equal:starts_on',
            'note' => 'nullable|string|max:255',
        ]);

        $holiday = AcademicHoliday::create([
            'academic_session_id' => !empty($data['academic_session_id']) ? (int) $data['academic_session_id'] : null,
            'name' => trim((string) $data['name']),
            'holiday_type' => (string) $data['holiday_type'],
            'starts_on' => $data['starts_on'],
            'ends_on' => $data['ends_on'],
            'note' => trim((string) ($data['note'] ?? '')) ?: null,
            'created_by' => auth()->id(),
        ]);

        $blockedCount = $this->applyHolidayBlocks($holiday->academic_session_id);

        return back()->with('flash_success', 'Holiday saved. ' . $blockedCount . ' week records blocked.');
    }

    public function updateHoliday(Request $request, AcademicHoliday $holiday)
    {
        $data = $request->validate([
            'academic_session_id' => 'nullable|exists:academic_sessions,id',
            'name' => 'required|string|max:160',
            'holiday_type' => 'required|in:' . implode(',', array_keys(self::HOLIDAY_TYPES)),
            'starts_on' => 'required|date',
            'ends_on' => 'required|date|after_or_equal:starts_on',
            'note' => 'nullable|string|max:255',
        ]);

        $previousSessionId = $holiday->academic_session_id;
        $holiday->update([
            'academic_session_id' => !empty($data['academic_session_id']) ? (int) $data['academic_session_id'] : null,
            'name' => trim((string) $data['name']),
            'holiday_type' => (string) $data['holiday_type'],
            'starts_on' => $data['starts_on'],
            'ends_on' => $data['ends_on'],
            'note' => trim((string) ($data['note'] ?? '')) ?: null,
        ]);

        $blockedCount = $this->applyHolidayBlocks($previousSessionId);
        if ((int) $previousSessionId !== (int) $holiday->academic_session_id) {
            $blockedCount += $this->applyHolidayBlocks($holiday->academic_session_id);
        }

        return back()->with('flash_success', 'Holiday updated. ' . $blockedCount . ' week records blocked.');
    }

    public function destroyHoliday(AcademicHoliday $holiday)
    {
        $sessionId = $holiday->academic_session_id;
        $holiday->delete();
        $blockedCount = $this->applyHolidayBlocks($sessionId);

        return back()->with('flash_success', 'Holiday deleted. ' . $blockedCount . ' week records are still blocked by holidays.');
    }

    public function generateSessionWeeks(Request $request, AcademicSession $session)
    {
        $data = $request->validate([
            'week_count' => 'required|integer|min:1|max:60',
        ]);

        $session->load('classes:id');
        $classIds = $session->classes->pluck('id')->all();

        if (empty($classIds)) {
            return back()->withErrors([
                'week_count' => 'Attach at least one year/class to this term before generating weeks.',
            ]);
        }

        $subjects = Subject::query()
            ->whereIn('my_class_id', $classIds)
            ->get(['id', 'name', 'my_class_id']);

        if ($subjects->isEmpty()) {
            return back()->withErrors([
                'week_count' => 'No subjects were found for the attached years/classes.',
            ]);
        }

        $weekCount = (int) $data['week_count'];
        $savedCount = 0;
        $attachedLegacyCount = 0;
        $existingCount = 0;

        foreach ($subjects as $subject) {
            for ($weekNumber = 1; $weekNumber <= $weekCount; $weekNumber++) {
                $weekStart = $session->start_date
                    ? $session->start_date->copy()->addWeeks($weekNumber - 1)
                    : null;
                $weekEnd = $weekStart ? $weekStart->copy()->addDays(6) : null;

                $week = SubjectWeek::query()
                    ->where('academic_session_id', $session->id)
                    ->where('subject_id', $subject->id)
                    ->where('week_number', $weekNumber)
                    ->first();

                if ($week) {
                    $existingCount++;
                    continue;
                }

                $legacyWeek = SubjectWeek::query()
                    ->whereNull('academic_session_id')
                    ->where('subject_id', $subject->id)
                    ->where('week_number', $weekNumber)
                    ->first();

                if ($legacyWeek) {
                    [$isBlocked, $blockNote] = $this->buildWeekHolidayBlock(
                        $session->id,
                        $legacyWeek->start_date ?: $weekStart,
                        $legacyWeek->end_date ?: $weekEnd,
                        (bool) $legacyWeek->is_blocked,
                        $legacyWeek->block_note
                    );

                    $legacyWeek->update([
                        'academic_session_id' => $session->id,
                        'title' => trim((string) $legacyWeek->title) !== '' ? $legacyWeek->title : 'Week ' . $weekNumber,
                        'start_date' => $legacyWeek->start_date ?: $weekStart,
                        'end_date' => $legacyWeek->end_date ?: $weekEnd,
                        'is_blocked' => $isBlocked,
                        'block_note' => $blockNote,
                    ]);
                    $attachedLegacyCount++;
                    continue;
                }

                [$isBlocked, $blockNote] = $this->buildWeekHolidayBlock($session->id, $weekStart, $weekEnd);
                SubjectWeek::create([
                    'academic_session_id' => $session->id,
                    'subject_id' => $subject->id,
                    'week_number' => $weekNumber,
                    'title' => 'Week ' . $weekNumber,
                    'start_date' => $weekStart,
                    'end_date' => $weekEnd,
                    'is_blocked' => $isBlocked,
                    'block_note' => $blockNote,
                ]);

                $savedCount++;
            }

            SubjectWeek::query()
                ->whereNull('academic_session_id')
                ->where('subject_id', $subject->id)
                ->where('week_number', '<=', $weekCount)
                ->orderBy('week_number')
                ->get()
                ->each(function (SubjectWeek $legacyWeek) use ($session, &$attachedLegacyCount) {
                    $alreadyExists = SubjectWeek::query()
                        ->where('academic_session_id', $session->id)
                        ->where('subject_id', $legacyWeek->subject_id)
                        ->where('week_number', $legacyWeek->week_number)
                        ->exists();

                    if (!$alreadyExists) {
                        $legacyWeek->update(['academic_session_id' => $session->id]);
                        $attachedLegacyCount++;
                    }
                });
        }

        $holidayBlockedCount = $this->applyHolidayBlocks($session->id);

        return back()->with(
            'flash_success',
            $savedCount . ' new week records generated, ' .
            $attachedLegacyCount . ' existing old weeks attached, and ' .
            $existingCount . ' existing term weeks kept for ' .
            $subjects->count() . ' subjects. ' .
            $holidayBlockedCount . ' week records are blocked by holidays.'
        );
    }

    public function storeWeek(Request $request)
    {
        $data = $request->validate([
            'academic_session_id' => 'nullable|exists:academic_sessions,id',
            'subject_id' => 'required|exists:subjects,id',
            'week_number' => 'required|integer|min:1|max:60',
            'title' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_blocked' => 'nullable|boolean',
            'block_note' => 'nullable|string|max:255',
        ]);

        [$isBlocked, $blockNote] = $this->buildWeekHolidayBlock(
            $data['academic_session_id'] ?? null,
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            (bool) ($data['is_blocked'] ?? false),
            trim((string) ($data['block_note'] ?? '')) ?: null
        );

        $week = SubjectWeek::firstOrCreate(
            [
                'academic_session_id' => $data['academic_session_id'] ?? null,
                'subject_id' => (int) $data['subject_id'],
                'week_number' => (int) $data['week_number'],
            ],
            [
                'title' => trim((string) ($data['title'] ?? '')),
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'is_blocked' => $isBlocked,
                'block_note' => $blockNote,
            ]
        );

        if ($week->wasRecentlyCreated === false) {
            $week->update([
                'title' => trim((string) ($data['title'] ?? '')),
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'is_blocked' => $isBlocked,
                'block_note' => $blockNote,
            ]);
        }

        return back()->with('flash_success', 'Week saved successfully.');
    }

    public function storeWeeksBulk(Request $request)
    {
        $data = $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_session_id' => 'nullable|exists:academic_sessions,id',
            'weeks' => 'required|array|min:1|max:60',
            'weeks.*.week_number' => 'required|integer|min:1|max:60',
            'weeks.*.title' => 'nullable|string|max:255',
        ]);

        $weekNumbers = collect($data['weeks'])
            ->pluck('week_number')
            ->map(static function ($weekNumber) {
                return (int) $weekNumber;
            });

        if ($weekNumbers->count() !== $weekNumbers->unique()->count()) {
            return back()->withErrors([
                'weeks' => 'Week numbers in the bulk list must be unique.',
            ]);
        }

        $savedCount = 0;
        foreach ($data['weeks'] as $weekRow) {
            [$isBlocked, $blockNote] = $this->buildWeekHolidayBlock($data['academic_session_id'] ?? null, null, null);
            $week = SubjectWeek::firstOrCreate(
                [
                    'academic_session_id' => $data['academic_session_id'] ?? null,
                    'subject_id' => (int) $data['subject_id'],
                    'week_number' => (int) $weekRow['week_number'],
                ],
                [
                    'title' => trim((string) ($weekRow['title'] ?? '')),
                    'start_date' => null,
                    'end_date' => null,
                    'is_blocked' => $isBlocked,
                    'block_note' => $blockNote,
                ]
            );

            if ($week->wasRecentlyCreated === false) {
                $week->update([
                    'title' => trim((string) ($weekRow['title'] ?? '')),
                ]);
            }

            $savedCount++;
        }

        return back()->with('flash_success', $savedCount . ' weeks saved successfully.');
    }

    public function updateWeek(Request $request, SubjectWeek $week)
    {
        $data = $request->validate([
            'week_number' => 'required|integer|min:1|max:60',
            'title' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_blocked' => 'nullable|boolean',
            'block_note' => 'nullable|string|max:255',
        ]);

        $exists = SubjectWeek::query()
            ->where('subject_id', $week->subject_id)
            ->where('academic_session_id', $week->academic_session_id)
            ->where('week_number', (int) $data['week_number'])
            ->where('id', '!=', $week->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['week_number' => 'This week number already exists for this subject.']);
        }

        [$isBlocked, $blockNote] = $this->buildWeekHolidayBlock(
            $week->academic_session_id,
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            (bool) ($data['is_blocked'] ?? false),
            trim((string) ($data['block_note'] ?? '')) ?: null
        );

        $week->update([
            'week_number' => (int) $data['week_number'],
            'title' => trim((string) ($data['title'] ?? '')),
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'is_blocked' => $isBlocked,
            'block_note' => $blockNote,
        ]);

        $week->loadMissing('subject.my_class');
        $this->syncWeekSectionName($week->subject, $week);

        return back()->with('flash_success', 'Week updated successfully.');
    }

    public function storeUnit(Request $request)
    {
        $data = $request->validate([
            'subject_week_id' => 'required|exists:subject_weeks,id',
            'unit_number' => 'required|integer|min:1|max:100',
            'title' => 'required|string|max:255',
        ]);

        $week = SubjectWeek::findOrFail((int) $data['subject_week_id']);
        if ($blockedResponse = $this->blockedWeekResponse($week)) {
            return $blockedResponse;
        }

        $unit = WeekUnit::firstOrCreate(
            [
                'subject_week_id' => (int) $data['subject_week_id'],
                'unit_number' => (int) $data['unit_number'],
            ],
            [
                'title' => trim((string) $data['title']),
            ]
        );

        if ($unit->wasRecentlyCreated === false) {
            $unit->update(['title' => trim((string) $data['title'])]);
        }

        return back()->with('flash_success', 'Unit saved successfully.');
    }

    public function updateUnit(Request $request, WeekUnit $unit)
    {
        $data = $request->validate([
            'unit_number' => 'required|integer|min:1|max:100',
            'title' => 'required|string|max:255',
        ]);

        $unit->loadMissing('week');
        if ($blockedResponse = $this->blockedWeekResponse($unit->week)) {
            return $blockedResponse;
        }

        $exists = WeekUnit::query()
            ->where('subject_week_id', $unit->subject_week_id)
            ->where('unit_number', (int) $data['unit_number'])
            ->where('id', '!=', $unit->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['unit_number' => 'This unit number already exists in this week.']);
        }

        $unit->update([
            'unit_number' => (int) $data['unit_number'],
            'title' => trim((string) $data['title']),
        ]);

        return back()->with('flash_success', 'Unit updated successfully.');
    }

    public function storeLesson(Request $request)
    {
        $data = $request->validate([
            'week_unit_id' => 'required|exists:week_units,id',
            'lesson_number' => 'required|integer|min:1|max:500',
            'title' => 'required|string|max:255',
        ]);

        $unit = WeekUnit::with('week')->findOrFail((int) $data['week_unit_id']);
        if ($blockedResponse = $this->blockedWeekResponse($unit->week)) {
            return $blockedResponse;
        }

        $lesson = UnitLesson::firstOrCreate(
            [
                'week_unit_id' => (int) $data['week_unit_id'],
                'lesson_number' => (int) $data['lesson_number'],
            ],
            [
                'title' => trim((string) $data['title']),
            ]
        );

        if ($lesson->wasRecentlyCreated === false) {
            $lesson->update(['title' => trim((string) $data['title'])]);
        }

        return back()->with('flash_success', 'Lesson saved successfully.');
    }

    public function updateLesson(Request $request, UnitLesson $lesson)
    {
        $data = $request->validate([
            'lesson_number' => 'required|integer|min:1|max:500',
            'title' => 'required|string|max:255',
        ]);

        $lesson->loadMissing('unit.week');
        if ($blockedResponse = $this->blockedWeekResponse(optional($lesson->unit)->week)) {
            return $blockedResponse;
        }

        $exists = UnitLesson::query()
            ->where('week_unit_id', $lesson->week_unit_id)
            ->where('lesson_number', (int) $data['lesson_number'])
            ->where('id', '!=', $lesson->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['lesson_number' => 'This lesson number already exists in this unit.']);
        }

        $lesson->update([
            'lesson_number' => (int) $data['lesson_number'],
            'title' => trim((string) $data['title']),
        ]);

        return back()->with('flash_success', 'Lesson updated successfully.');
    }

    public function destroyWeek(SubjectWeek $week)
    {
        $week->loadMissing('subject.my_class', 'units.generalFiles', 'units.lessons.files');
        $subject = $week->subject;
        if (!$subject) {
            return back()->withErrors(['week' => 'Cannot delete this week because subject was not found.']);
        }

        foreach ($week->units as $unit) {
            $deleteResult = $this->deleteUnitCascade($unit, $subject);
            if (empty($deleteResult['ok'])) {
                return back()->withErrors(['week' => (string) ($deleteResult['error'] ?? 'Failed to delete week from Moodle.')]);
            }
        }

        $week->delete();

        return back()->with('flash_success', 'Week deleted successfully.');
    }

    public function destroyUnit(WeekUnit $unit)
    {
        $unit->loadMissing('week.subject.my_class', 'generalFiles', 'lessons.files');
        $subject = optional($unit->week)->subject;
        if (!$subject) {
            return back()->withErrors(['unit' => 'Cannot delete this unit because subject was not found.']);
        }

        $deleteResult = $this->deleteUnitCascade($unit, $subject);
        if (empty($deleteResult['ok'])) {
            return back()->withErrors(['unit' => (string) ($deleteResult['error'] ?? 'Failed to delete unit from Moodle.')]);
        }

        return back()->with('flash_success', 'Unit deleted successfully.');
    }

    public function destroyLesson(UnitLesson $lesson)
    {
        $lesson->loadMissing('unit.week.subject.my_class', 'files', 'quizzes', 'questionBanks');
        $subject = optional(optional($lesson->unit)->week)->subject;
        if (!$subject) {
            return back()->withErrors(['lesson' => 'Cannot delete this lesson because subject was not found.']);
        }

        $deleteResult = $this->deleteLessonCascade($lesson, $subject);
        if (empty($deleteResult['ok'])) {
            return back()->withErrors(['lesson' => (string) ($deleteResult['error'] ?? 'Failed to delete lesson from Moodle.')]);
        }

        return back()->with('flash_success', 'Lesson deleted successfully.');
    }

    public function uploadSubjectGeneralFile(Request $request)
    {
        $data = $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'general_type' => $this->subjectGeneralFileTypeRule(),
            'moodle_activity_type' => $this->moodleFileActivityTypeRule(),
            'activity_intro' => 'nullable|string|max:5000',
            'available_from' => 'nullable|date',
            'due_at' => 'nullable|date|after_or_equal:available_from',
            'cutoff_at' => 'nullable|date|after_or_equal:due_at',
            'moodle_audience' => $this->moodleAudienceRule(),
            'file' => 'required|file|max:51200',
        ]);

        $session = AcademicSession::findOrFail((int) $data['academic_session_id']);
        $subject = Subject::with('my_class')->findOrFail((int) $data['subject_id']);
        $storedPath = $this->storeUploadedFile($request, "academic-management/subject_{$subject->id}/general");
        $activityType = $this->normalizeMoodleFileActivityType($data['moodle_activity_type'] ?? null);
        $availableFrom = $this->parseOptionalDateTime($data['available_from'] ?? null);
        $dueAt = $this->parseOptionalDateTime($data['due_at'] ?? null);
        $cutoffAt = $this->parseOptionalDateTime($data['cutoff_at'] ?? null);
        [$availableFrom, $dueAt, $cutoffAt] = $this->normalizeAssignmentDates($activityType, $availableFrom, $dueAt, $cutoffAt);
        if ($dateError = $this->validateActivityDatesOutsideHolidays($session->id, [$availableFrom, $dueAt, $cutoffAt])) {
            return back()->withInput()->withErrors(['available_from' => $dateError]);
        }

        $file = SubjectGeneralFile::create([
            'subject_id' => $subject->id,
            'academic_session_id' => $session->id,
            'title' => trim((string) $data['title']),
            'general_type' => (string) $data['general_type'],
            'file_path' => $storedPath,
            'original_name' => $request->file('file')->getClientOriginalName(),
            'mime_type' => $request->file('file')->getClientMimeType(),
            'size' => (int) $request->file('file')->getSize(),
            'uploaded_by' => auth()->id(),
            'moodle_activity_type' => $activityType,
            'activity_intro' => $this->normalizeOptionalText($data['activity_intro'] ?? null),
            'available_from' => $availableFrom,
            'due_at' => $dueAt,
            'cutoff_at' => $cutoffAt,
            'moodle_audience' => $this->normalizeMoodleAudience($data['moodle_audience'] ?? null),
        ]);

        $syncResult = $this->syncFileToMoodle(
            $subject,
            $file->title,
            $file->file_path,
            'subject_general_file',
            (int) $file->id,
            $this->getSessionMoodleSectionNumber($session),
            $file->moodle_activity_type,
            array_merge($this->buildMoodleFileSyncContext($file), [
                'term' => $session->name,
                'section_title' => $session->name,
            ])
        );
        $file->moodle_cmid = $this->extractCmidFromSyncResult($syncResult);
        $file->moodle_view_url = $this->extractViewUrlFromSyncResult($syncResult);
        $file->save();

        return back()->with('flash_success', 'Subject general file uploaded.');
    }

    public function uploadUnitGeneralFile(Request $request)
    {
        $data = $request->validate([
            'week_unit_id' => 'required|exists:week_units,id',
            'title' => 'required|string|max:255',
            'general_type' => $this->unitGeneralFileTypeRule(),
            'moodle_activity_type' => $this->moodleFileActivityTypeRule(),
            'activity_intro' => 'nullable|string|max:5000',
            'available_from' => 'nullable|date',
            'due_at' => 'nullable|date|after_or_equal:available_from',
            'cutoff_at' => 'nullable|date|after_or_equal:due_at',
            'moodle_audience' => $this->moodleAudienceRule(),
            'file' => 'required|file|max:51200',
        ]);

        $unit = WeekUnit::with('week.subject.my_class')->findOrFail((int) $data['week_unit_id']);
        if ($blockedResponse = $this->blockedWeekResponse($unit->week)) {
            return $blockedResponse;
        }
        $subject = $unit->week->subject;
        $storedPath = $this->storeUploadedFile($request, "academic-management/subject_{$subject->id}/week_{$unit->subject_week_id}/unit_{$unit->id}/general");
        $activityType = $this->normalizeMoodleFileActivityType($data['moodle_activity_type'] ?? null);
        $availableFrom = $this->parseOptionalDateTime($data['available_from'] ?? null);
        $dueAt = $this->parseOptionalDateTime($data['due_at'] ?? null);
        $cutoffAt = $this->parseOptionalDateTime($data['cutoff_at'] ?? null);
        [$availableFrom, $dueAt, $cutoffAt] = $this->normalizeAssignmentDates($activityType, $availableFrom, $dueAt, $cutoffAt);
        if ($dateError = $this->validateActivityDatesOutsideHolidays($unit->week->academic_session_id, [$availableFrom, $dueAt, $cutoffAt])) {
            return back()->withInput()->withErrors(['available_from' => $dateError]);
        }

        $file = UnitGeneralFile::create([
            'week_unit_id' => $unit->id,
            'title' => trim((string) $data['title']),
            'general_type' => (string) $data['general_type'],
            'file_path' => $storedPath,
            'original_name' => $request->file('file')->getClientOriginalName(),
            'mime_type' => $request->file('file')->getClientMimeType(),
            'size' => (int) $request->file('file')->getSize(),
            'uploaded_by' => auth()->id(),
            'moodle_activity_type' => $activityType,
            'activity_intro' => $this->normalizeOptionalText($data['activity_intro'] ?? null),
            'available_from' => $availableFrom,
            'due_at' => $dueAt,
            'cutoff_at' => $cutoffAt,
            'moodle_audience' => $this->normalizeMoodleAudience($data['moodle_audience'] ?? null),
        ]);

        $weekNo = (int) $unit->week->week_number;
        $this->syncWeekSectionName($subject, $unit->week);
        $this->syncUnitLabel($subject, $unit);

        $moodleTitle = trim("Week {$weekNo} - Unit {$unit->unit_number} - {$file->title}");
        $syncResult = $this->syncFileToMoodle(
            $subject,
            $moodleTitle,
            $file->file_path,
            'unit_general_file',
            (int) $file->id,
            $weekNo,
            $file->moodle_activity_type,
            $this->buildMoodleFileSyncContext($file)
        );
        $fileCmid = $this->extractCmidFromSyncResult($syncResult);
        if ($fileCmid) {
            $file->moodle_cmid = $fileCmid;
            $file->moodle_view_url = $this->extractViewUrlFromSyncResult($syncResult);
            $file->save();
            $anchorCmid = $this->findUnitAnchorCmid($unit, (int) $file->id);
            if ($anchorCmid) {
                $this->moveModuleAfter($fileCmid, $anchorCmid, 'unit_general_file', (int) $file->id, (int) $subject->moodle_course_id);
            }
        }

        return back()->with('flash_success', 'Unit general file uploaded.');
    }

    public function uploadLessonFile(Request $request)
    {
        $data = $request->validate([
            'unit_lesson_id' => 'required|exists:unit_lessons,id',
            'title' => 'required|string|max:255',
            'lesson_type' => $this->lessonFileTypeRule(),
            'moodle_activity_type' => $this->moodleFileActivityTypeRule(),
            'activity_intro' => 'nullable|string|max:5000',
            'available_from' => 'nullable|date',
            'due_at' => 'nullable|date|after_or_equal:available_from',
            'cutoff_at' => 'nullable|date|after_or_equal:due_at',
            'moodle_audience' => $this->moodleAudienceRule(),
            'file' => 'required|file|max:51200',
        ]);

        $lesson = UnitLesson::with('unit.week.subject.my_class')->findOrFail((int) $data['unit_lesson_id']);
        if ($blockedResponse = $this->blockedWeekResponse($lesson->unit->week)) {
            return $blockedResponse;
        }
        $subject = $lesson->unit->week->subject;
        $storedPath = $this->storeUploadedFile($request, "academic-management/subject_{$subject->id}/lesson_{$lesson->id}");
        $activityType = $this->normalizeMoodleFileActivityType($data['moodle_activity_type'] ?? null);
        $availableFrom = $this->parseOptionalDateTime($data['available_from'] ?? null);
        $dueAt = $this->parseOptionalDateTime($data['due_at'] ?? null);
        $cutoffAt = $this->parseOptionalDateTime($data['cutoff_at'] ?? null);
        [$availableFrom, $dueAt, $cutoffAt] = $this->normalizeAssignmentDates($activityType, $availableFrom, $dueAt, $cutoffAt);
        if ($dateError = $this->validateActivityDatesOutsideHolidays($lesson->unit->week->academic_session_id, [$availableFrom, $dueAt, $cutoffAt])) {
            return back()->withInput()->withErrors(['available_from' => $dateError]);
        }

        // Extract file content for AI tutor
        $absolutePath = Storage::disk('public')->path($storedPath);
        $extension = $request->file('file')->getClientOriginalExtension();
        $contentText = $this->textExtractor->extract($absolutePath, $extension);

        $file = LessonFile::create([
            'unit_lesson_id' => $lesson->id,
            'title' => trim((string) $data['title']),
            'lesson_type' => (string) ($data['lesson_type'] ?? 'digital_resources'),
            'file_path' => $storedPath,
            'original_name' => $request->file('file')->getClientOriginalName(),
            'mime_type' => $request->file('file')->getClientMimeType(),
            'size' => (int) $request->file('file')->getSize(),
            'content_text' => $contentText,
            'uploaded_by' => auth()->id(),
            'moodle_activity_type' => $activityType,
            'activity_intro' => $this->normalizeOptionalText($data['activity_intro'] ?? null),
            'available_from' => $availableFrom,
            'due_at' => $dueAt,
            'cutoff_at' => $cutoffAt,
            'moodle_audience' => $this->normalizeMoodleAudience($data['moodle_audience'] ?? null),
        ]);

        $weekNo = (int) $lesson->unit->week->week_number;
        $unitNo = (int) $lesson->unit->unit_number;
        $lessonNo = (int) $lesson->lesson_number;
        $this->syncWeekSectionName($subject, $lesson->unit->week);
        $this->syncUnitLabel($subject, $lesson->unit);
        $this->syncLessonLabel($subject, $lesson);

        $moodleTitle = trim("Week {$weekNo} - Unit {$unitNo} - Lesson {$lessonNo} - {$file->title}");
        $syncResult = $this->syncFileToMoodle(
            $subject,
            $moodleTitle,
            $file->file_path,
            'lesson_file',
            (int) $file->id,
            $weekNo,
            $file->moodle_activity_type,
            $this->buildMoodleFileSyncContext($file)
        );
        $fileCmid = $this->extractCmidFromSyncResult($syncResult);
        if ($fileCmid) {
            $file->moodle_cmid = $fileCmid;
            $file->moodle_view_url = $this->extractViewUrlFromSyncResult($syncResult);
            $file->save();
            $anchorCmid = $this->findLessonAnchorCmid($lesson, (int) $file->id);
            if ($anchorCmid) {
                $this->moveModuleAfter($fileCmid, $anchorCmid, 'lesson_file', (int) $file->id, (int) $subject->moodle_course_id);
            }
        }

        return back()->with('flash_success', 'Lesson file uploaded.');
    }

    public function storeQuiz(
        Request $request,
        QuizQuestionCsvParser $parser,
        MoodleQuizXmlBuilder $xmlBuilder,
        QuizQuestionPdfParser $pdfParser
    )
    {
        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'scope' => 'required|in:unit,lesson',
            'week_unit_id' => 'nullable|exists:week_units,id',
            'unit_lesson_id' => 'nullable|exists:unit_lessons,id',
            'title' => 'required|string|max:255',
            'time_limit_minutes' => 'nullable|integer|min:1|max:300',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after_or_equal:available_from',
            'moodle_audience' => $this->moodleAudienceRule(),
            'upload_type' => 'nullable|in:questions,xml',
            'questions_file' => 'nullable|file|mimes:csv,txt,pdf|max:51200',
            'xml_file' => 'nullable|file|mimes:xml|max:51200',
        ]);

        if ($data['scope'] === 'unit' && empty($data['week_unit_id'])) {
            return back()->withInput()->withErrors(['week_unit_id' => 'Please select a unit for this quiz.']);
        }
        if ($data['scope'] === 'lesson' && empty($data['unit_lesson_id'])) {
            return back()->withInput()->withErrors(['unit_lesson_id' => 'Please select a lesson for this quiz.']);
        }
        if (!empty($data['week_unit_id']) && !empty($data['unit_lesson_id'])) {
            return back()->withInput()->withErrors(['scope' => 'Select either a unit or a lesson, not both.']);
        }

        $subject = Subject::with('my_class')->findOrFail((int) $data['subject_id']);
        $unit = null;
        $lesson = null;
        $sectionNumber = 0;

        if ($data['scope'] === 'unit') {
            $unit = WeekUnit::with('week.subject.my_class')->findOrFail((int) $data['week_unit_id']);
            if ((int) $unit->week->subject_id !== (int) $subject->id) {
                return back()->withInput()->withErrors(['week_unit_id' => 'Selected unit does not belong to this subject.']);
            }
            if ($blockedResponse = $this->blockedWeekResponse($unit->week)) {
                return $blockedResponse;
            }

            $sectionNumber = (int) $unit->week->week_number;
            $this->syncWeekSectionName($subject, $unit->week);
            $this->syncUnitLabel($subject, $unit);
        }

        if ($data['scope'] === 'lesson') {
            $lesson = UnitLesson::with('unit.week.subject.my_class')->findOrFail((int) $data['unit_lesson_id']);
            if ((int) $lesson->unit->week->subject_id !== (int) $subject->id) {
                return back()->withInput()->withErrors(['unit_lesson_id' => 'Selected lesson does not belong to this subject.']);
            }
            if ($blockedResponse = $this->blockedWeekResponse($lesson->unit->week)) {
                return $blockedResponse;
            }

            $sectionNumber = (int) $lesson->unit->week->week_number;
            $this->syncWeekSectionName($subject, $lesson->unit->week);
            $this->syncUnitLabel($subject, $lesson->unit);
            $this->syncLessonLabel($subject, $lesson);
        }

        $uploadType = $data['upload_type'] ?? ($request->hasFile('xml_file') ? 'xml' : 'questions');
        $hasQuestions = $request->hasFile('questions_file');
        $hasXml = $request->hasFile('xml_file');

        if ($uploadType === 'xml' && !$hasXml) {
            return back()->withInput()->withErrors(['xml_file' => 'Please upload the Moodle XML file.']);
        }
        if ($uploadType === 'questions' && !$hasQuestions) {
            return back()->withInput()->withErrors(['questions_file' => 'Please upload the questions file.']);
        }
        if ($hasQuestions && $hasXml) {
            return back()->withInput()->withErrors(['upload_type' => 'Upload either a questions file or an XML file, not both.']);
        }

        $storedPath = null;
        $xmlPath = null;

        if ($uploadType === 'xml') {
            $xmlPath = $this->storeUploadedFile(
                $request,
                "academic-management/subject_{$subject->id}/quizzes/xml",
                'xml_file'
            );
        } else {
            $storedPath = $this->storeUploadedFile(
                $request,
                "academic-management/subject_{$subject->id}/quizzes/questions",
                'questions_file'
            );

            $absoluteQuestionsPath = Storage::disk('public')->path($storedPath);
            $extension = strtolower((string) $request->file('questions_file')->getClientOriginalExtension());
            try {
                if ($extension === 'pdf') {
                    $questions = $pdfParser->parse($absoluteQuestionsPath);
                } else {
                    $questions = $parser->parse($absoluteQuestionsPath);
                }
                $xml = $xmlBuilder->build($questions);
            } catch (\Throwable $e) {
                Storage::disk('public')->delete($storedPath);
                return back()->withInput()->withErrors(['questions_file' => $e->getMessage()]);
            }

            try {
                $xmlPath = $this->storeQuizXml($subject->id, $data['title'], $xml);
            } catch (\Throwable $e) {
                Storage::disk('public')->delete($storedPath);
                return back()->withInput()->withErrors(['questions_file' => $e->getMessage()]);
            }
        }

        $availableFrom = !empty($data['available_from']) ? Carbon::parse($data['available_from']) : null;
        $availableUntil = !empty($data['available_until']) ? Carbon::parse($data['available_until']) : null;
        $quizSessionId = $unit ? $unit->week->academic_session_id : ($lesson ? $lesson->unit->week->academic_session_id : null);
        if ($dateError = $this->validateActivityDatesOutsideHolidays($quizSessionId, [$availableFrom, $availableUntil])) {
            return back()->withInput()->withErrors(['available_from' => $dateError]);
        }

        $quiz = AcademicQuiz::create([
            'subject_id' => $subject->id,
            'week_unit_id' => $unit ? $unit->id : null,
            'unit_lesson_id' => $lesson ? $lesson->id : null,
            'title' => trim((string) $data['title']),
            'time_limit_minutes' => !empty($data['time_limit_minutes']) ? (int) $data['time_limit_minutes'] : null,
            'available_from' => $availableFrom,
            'available_until' => $availableUntil,
            'question_file_path' => $storedPath,
            'question_original_name' => $storedPath ? $request->file('questions_file')->getClientOriginalName() : null,
            'question_mime_type' => $storedPath ? $request->file('questions_file')->getClientMimeType() : null,
            'question_size' => $storedPath ? (int) $request->file('questions_file')->getSize() : null,
            'xml_file_path' => $xmlPath,
            'uploaded_by' => auth()->id(),
            'moodle_audience' => $this->normalizeMoodleAudience($data['moodle_audience'] ?? null),
        ]);

        $intro = 'Quiz uploaded from Laravel';
        if (!empty($data['time_limit_minutes'])) {
            $intro .= ' | Time limit: ' . (int) $data['time_limit_minutes'] . ' minutes';
        }

        $anchorCmid = null;
        if ($unit) {
            $anchorCmid = $this->findUnitQuizAnchorCmid($unit, (int) $quiz->id);
        } elseif ($lesson) {
            $anchorCmid = $this->findLessonQuizAnchorCmid($lesson, (int) $quiz->id);
        }

        $contextOverrides = array_merge(
            ['intro' => $intro],
            $this->buildMoodleAudienceContext($quiz->moodle_audience)
        );
        if ($availableFrom) {
            $contextOverrides['timeopen'] = $availableFrom->timestamp;
        }
        if ($availableUntil) {
            $contextOverrides['timeclose'] = $availableUntil->timestamp;
        }
        if (!empty($data['time_limit_minutes'])) {
            $contextOverrides['timelimit'] = (int) $data['time_limit_minutes'] * 60;
        }

        $syncResult = $this->syncFileToMoodle(
            $subject,
            $quiz->title,
            $quiz->xml_file_path,
            'academic_quiz',
            (int) $quiz->id,
            $sectionNumber,
            'quiz',
            $contextOverrides
        );

        $quiz->moodle_cmid = $this->extractCmidFromSyncResult($syncResult);
        $quiz->moodle_instance_id = !empty($syncResult['response']['instanceid'])
            ? (int) $syncResult['response']['instanceid']
            : null;
        $quiz->moodle_view_url = !empty($syncResult['response']['viewurl'])
            ? (string) $syncResult['response']['viewurl']
            : null;

        if (empty($quiz->moodle_cmid) && !empty($quiz->moodle_instance_id)) {
            $courseId = $this->ensureMoodleCourseId($subject);
            if ($courseId) {
                $resolvedCmid = $this->moodle->findCourseModuleIdByInstance($courseId, 'quiz', (int) $quiz->moodle_instance_id);
                if ($resolvedCmid) {
                    $quiz->moodle_cmid = $resolvedCmid;
                }
            }
        }

        $quiz->save();

        if (!empty($quiz->moodle_cmid) && $anchorCmid) {
            $this->moveModuleAfter(
                (int) $quiz->moodle_cmid,
                (int) $anchorCmid,
                'academic_quiz',
                (int) $quiz->id,
                !empty($subject->moodle_course_id) ? (int) $subject->moodle_course_id : null
            );
        }

        return back()->with('flash_success', 'Quiz uploaded successfully.');
    }

    public function destroyQuiz(AcademicQuiz $quiz)
    {
        $quiz->loadMissing('subject');
        $subject = $quiz->subject;
        if (!$subject) {
            return back()->withErrors(['quiz' => 'Cannot delete quiz because subject was not found.']);
        }

        $cmid = !empty($quiz->moodle_cmid)
            ? (int) $quiz->moodle_cmid
            : $this->getCmidFromLatestSyncLog('academic_quiz', (int) $quiz->id, 'upload');

        $deleteResult = $this->deleteMoodleModuleIfNeeded(
            $cmid,
            'academic_quiz',
            (int) $quiz->id,
            !empty($subject->moodle_course_id) ? (int) $subject->moodle_course_id : null
        );
        if (empty($deleteResult['ok'])) {
            return back()->withErrors(['quiz' => (string) ($deleteResult['error'] ?? 'Failed to delete quiz from Moodle.')]);
        }

        if (!empty($quiz->question_file_path)) {
            Storage::disk('public')->delete((string) $quiz->question_file_path);
        }
        if (!empty($quiz->xml_file_path)) {
            Storage::disk('public')->delete((string) $quiz->xml_file_path);
        }
        $quiz->delete();

        return back()->with('flash_success', 'Quiz deleted successfully.');
    }

    public function storeLessonQuestionBank(
        Request $request,
        QuizQuestionCsvParser $parser,
        MoodleQuizXmlBuilder $xmlBuilder,
        QuizQuestionPdfParser $pdfParser
    )
    {
        $data = $request->validate([
            'unit_lesson_id' => 'required|exists:unit_lessons,id',
            'title' => 'required|string|max:255',
            'upload_type' => 'nullable|in:questions,xml',
            'questions_file' => 'nullable|file|mimes:csv,txt,pdf|max:51200',
            'xml_file' => 'nullable|file|mimes:xml|max:51200',
        ]);

        $lesson = UnitLesson::with('unit.week.subject.my_class')->findOrFail((int) $data['unit_lesson_id']);
        if ($blockedResponse = $this->blockedWeekResponse($lesson->unit->week)) {
            return $blockedResponse;
        }
        $subject = $lesson->unit->week->subject;

        $uploadType = $data['upload_type'] ?? ($request->hasFile('xml_file') ? 'xml' : 'questions');
        $hasQuestions = $request->hasFile('questions_file');
        $hasXml = $request->hasFile('xml_file');

        if ($uploadType === 'xml' && !$hasXml) {
            return back()->withInput()->withErrors(['xml_file' => 'Please upload the Moodle XML file.']);
        }
        if ($uploadType === 'questions' && !$hasQuestions) {
            return back()->withInput()->withErrors(['questions_file' => 'Please upload the questions file.']);
        }
        if ($hasQuestions && $hasXml) {
            return back()->withInput()->withErrors(['upload_type' => 'Upload either a questions file or an XML file, not both.']);
        }

        $sourcePath = null;
        $sourceOriginalName = null;
        $sourceMimeType = null;
        $sourceSize = null;
        $xmlPath = null;
        $questionCount = null;

        if ($uploadType === 'xml') {
            $xmlPath = $this->storeUploadedFile(
                $request,
                "academic-management/subject_{$subject->id}/question-banks/lesson_{$lesson->id}/xml",
                'xml_file'
            );
        } else {
            $sourcePath = $this->storeUploadedFile(
                $request,
                "academic-management/subject_{$subject->id}/question-banks/lesson_{$lesson->id}/questions",
                'questions_file'
            );

            $sourceOriginalName = $request->file('questions_file')->getClientOriginalName();
            $sourceMimeType = $request->file('questions_file')->getClientMimeType();
            $sourceSize = (int) $request->file('questions_file')->getSize();

            $absoluteQuestionsPath = Storage::disk('public')->path($sourcePath);
            $extension = strtolower((string) $request->file('questions_file')->getClientOriginalExtension());

            try {
                if ($extension === 'pdf') {
                    $questions = $pdfParser->parse($absoluteQuestionsPath);
                } else {
                    $questions = $parser->parse($absoluteQuestionsPath);
                }
                $questionCount = count($questions);
                $xml = $xmlBuilder->build($questions);
            } catch (\Throwable $e) {
                Storage::disk('public')->delete($sourcePath);
                return back()->withInput()->withErrors(['questions_file' => $e->getMessage()]);
            }

            try {
                $xmlPath = $this->storeLessonQuestionBankXml($subject->id, $lesson->id, $data['title'], $xml);
            } catch (\Throwable $e) {
                Storage::disk('public')->delete($sourcePath);
                return back()->withInput()->withErrors(['questions_file' => $e->getMessage()]);
            }
        }

        $questionBank = LessonQuestionBank::create([
            'unit_lesson_id' => $lesson->id,
            'title' => trim((string) $data['title']),
            'source_file_path' => $sourcePath,
            'source_original_name' => $sourceOriginalName,
            'source_mime_type' => $sourceMimeType,
            'source_size' => $sourceSize,
            'xml_file_path' => $xmlPath,
            'question_count' => $questionCount,
            'uploaded_by' => auth()->id(),
        ]);

        $syncResult = $this->syncLessonQuestionBankToMoodle($subject, $lesson, $questionBank);
        $this->applyLessonQuestionBankSyncResult($questionBank, $syncResult);

        if (!empty($syncResult['ok'])) {
            return back()->with('flash_success', 'Lesson question bank uploaded and synced to Moodle successfully.');
        }

        return back()->with(
            'flash_warning',
            $this->buildLessonQuestionBankSyncFlashMessage($syncResult, true)
        );
    }

    public function syncLessonQuestionBank(LessonQuestionBank $questionBank)
    {
        $questionBank->loadMissing('lesson.unit.week.subject.my_class');
        $lesson = $questionBank->lesson;
        $subject = optional(optional(optional($lesson)->unit)->week)->subject;

        if (!$lesson || !$subject) {
            return back()->with('flash_danger', 'Cannot sync this question bank because lesson or subject was not found.');
        }
        if ($blockedResponse = $this->blockedWeekResponse($lesson->unit->week)) {
            return $blockedResponse;
        }

        $syncResult = $this->syncLessonQuestionBankToMoodle($subject, $lesson, $questionBank);
        $this->applyLessonQuestionBankSyncResult($questionBank, $syncResult);

        if (!empty($syncResult['ok'])) {
            return back()->with('flash_success', 'Lesson question bank synced to Moodle successfully.');
        }

        return back()->with(
            !empty($syncResult['skipped']) ? 'flash_warning' : 'flash_danger',
            $this->buildLessonQuestionBankSyncFlashMessage($syncResult, false)
        );
    }

    public function destroyLessonQuestionBank(LessonQuestionBank $questionBank)
    {
        $wasSyncedToMoodle = !empty($questionBank->moodle_category_id) || ($questionBank->moodle_sync_status ?? '') === 'success';

        if (!empty($questionBank->source_file_path)) {
            Storage::disk('public')->delete((string) $questionBank->source_file_path);
        }
        if (!empty($questionBank->xml_file_path)) {
            Storage::disk('public')->delete((string) $questionBank->xml_file_path);
        }

        $questionBank->delete();

        if ($wasSyncedToMoodle) {
            return back()->with('flash_warning', 'Lesson question bank was deleted locally. Its Moodle question category was not removed automatically yet.');
        }

        return back()->with('flash_success', 'Lesson question bank deleted successfully.');
    }

    public function updateSubjectGeneralFile(Request $request, SubjectGeneralFile $file)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'general_type' => $this->subjectGeneralFileTypeRule(),
            'moodle_activity_type' => $this->moodleFileActivityTypeRule(),
            'activity_intro' => 'nullable|string|max:5000',
            'available_from' => 'nullable|date',
            'due_at' => 'nullable|date|after_or_equal:available_from',
            'cutoff_at' => 'nullable|date|after_or_equal:due_at',
            'moodle_audience' => $this->moodleAudienceRule(),
            'file' => 'nullable|file|max:51200',
        ]);

        $subject = Subject::with('my_class')->findOrFail((int) $file->subject_id);
        $file->title = trim((string) $data['title']);
        $file->general_type = (string) $data['general_type'];
        $file->moodle_activity_type = $this->normalizeMoodleFileActivityType($data['moodle_activity_type'] ?? null);
        $file->activity_intro = $this->normalizeOptionalText($data['activity_intro'] ?? null);
        $file->available_from = $this->parseOptionalDateTime($data['available_from'] ?? null);
        $file->due_at = $this->parseOptionalDateTime($data['due_at'] ?? null);
        $file->cutoff_at = $this->parseOptionalDateTime($data['cutoff_at'] ?? null);
        [$file->available_from, $file->due_at, $file->cutoff_at] = $this->normalizeAssignmentDates(
            $file->moodle_activity_type,
            $file->available_from,
            $file->due_at,
            $file->cutoff_at
        );
        if ($dateError = $this->validateActivityDatesOutsideHolidays($file->academic_session_id, [$file->available_from, $file->due_at, $file->cutoff_at])) {
            return back()->withInput()->withErrors(['available_from' => $dateError]);
        }
        $file->moodle_audience = $this->normalizeMoodleAudience($data['moodle_audience'] ?? null);

        if ($request->hasFile('file')) {
            $oldPath = $file->file_path;
            $storedPath = $this->storeUploadedFile($request, "academic-management/subject_{$subject->id}/general");
            $file->file_path = $storedPath;
            $file->original_name = $request->file('file')->getClientOriginalName();
            $file->mime_type = $request->file('file')->getClientMimeType();
            $file->size = (int) $request->file('file')->getSize();
            if (!empty($oldPath) && $oldPath !== $storedPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $file->save();

        $syncResult = $this->syncFileToMoodle(
            $subject,
            $file->title,
            $file->file_path,
            'subject_general_file',
            (int) $file->id,
            0,
            $file->moodle_activity_type,
            $this->buildMoodleFileSyncContext($file)
        );

        $cmid = $this->extractCmidFromSyncResult($syncResult);
        if ($cmid) {
            $file->moodle_cmid = $cmid;
            $file->moodle_view_url = $this->extractViewUrlFromSyncResult($syncResult);
            $file->save();
        }

        return back()->with('flash_success', 'Subject general file updated and synced.');
    }

    public function updateUnitGeneralFile(Request $request, UnitGeneralFile $file)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'general_type' => $this->unitGeneralFileTypeRule(),
            'moodle_activity_type' => $this->moodleFileActivityTypeRule(),
            'activity_intro' => 'nullable|string|max:5000',
            'available_from' => 'nullable|date',
            'due_at' => 'nullable|date|after_or_equal:available_from',
            'cutoff_at' => 'nullable|date|after_or_equal:due_at',
            'moodle_audience' => $this->moodleAudienceRule(),
            'file' => 'nullable|file|max:51200',
        ]);

        $file->loadMissing('unit.week.subject.my_class');
        $unit = $file->unit;
        $week = $unit->week;
        if ($blockedResponse = $this->blockedWeekResponse($week)) {
            return $blockedResponse;
        }
        $subject = $week->subject;

        $file->title = trim((string) $data['title']);
        $file->general_type = (string) $data['general_type'];
        $file->moodle_activity_type = $this->normalizeMoodleFileActivityType($data['moodle_activity_type'] ?? null);
        $file->activity_intro = $this->normalizeOptionalText($data['activity_intro'] ?? null);
        $file->available_from = $this->parseOptionalDateTime($data['available_from'] ?? null);
        $file->due_at = $this->parseOptionalDateTime($data['due_at'] ?? null);
        $file->cutoff_at = $this->parseOptionalDateTime($data['cutoff_at'] ?? null);
        [$file->available_from, $file->due_at, $file->cutoff_at] = $this->normalizeAssignmentDates(
            $file->moodle_activity_type,
            $file->available_from,
            $file->due_at,
            $file->cutoff_at
        );
        if ($dateError = $this->validateActivityDatesOutsideHolidays($week->academic_session_id, [$file->available_from, $file->due_at, $file->cutoff_at])) {
            return back()->withInput()->withErrors(['available_from' => $dateError]);
        }
        $file->moodle_audience = $this->normalizeMoodleAudience($data['moodle_audience'] ?? null);
        if ($request->hasFile('file')) {
            $oldPath = $file->file_path;
            $storedPath = $this->storeUploadedFile($request, "academic-management/subject_{$subject->id}/week_{$unit->subject_week_id}/unit_{$unit->id}/general");
            $file->file_path = $storedPath;
            $file->original_name = $request->file('file')->getClientOriginalName();
            $file->mime_type = $request->file('file')->getClientMimeType();
            $file->size = (int) $request->file('file')->getSize();
            if (!empty($oldPath) && $oldPath !== $storedPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }
        $file->save();

        $weekNo = (int) $week->week_number;
        $this->syncWeekSectionName($subject, $week);
        $this->syncUnitLabel($subject, $unit);
        $moodleTitle = trim("Week {$weekNo} - Unit {$unit->unit_number} - {$file->title}");
        $syncResult = $this->syncFileToMoodle(
            $subject,
            $moodleTitle,
            $file->file_path,
            'unit_general_file',
            (int) $file->id,
            $weekNo,
            $file->moodle_activity_type,
            $this->buildMoodleFileSyncContext($file)
        );
        $fileCmid = $this->extractCmidFromSyncResult($syncResult);
        if ($fileCmid) {
            $file->moodle_cmid = $fileCmid;
            $file->moodle_view_url = $this->extractViewUrlFromSyncResult($syncResult);
            $file->save();
            $anchorCmid = $this->findUnitAnchorCmid($unit, (int) $file->id);
            if ($anchorCmid) {
                $this->moveModuleAfter($fileCmid, $anchorCmid, 'unit_general_file', (int) $file->id, (int) $subject->moodle_course_id);
            }
        }

        return back()->with('flash_success', 'Unit file updated and synced.');
    }

    public function updateLessonFile(Request $request, LessonFile $file)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'lesson_type' => $this->lessonFileTypeRule(),
            'moodle_activity_type' => $this->moodleFileActivityTypeRule(),
            'activity_intro' => 'nullable|string|max:5000',
            'available_from' => 'nullable|date',
            'due_at' => 'nullable|date|after_or_equal:available_from',
            'cutoff_at' => 'nullable|date|after_or_equal:due_at',
            'moodle_audience' => $this->moodleAudienceRule(),
            'file' => 'nullable|file|max:51200',
        ]);

        $file->loadMissing('lesson.unit.week.subject.my_class');
        $lesson = $file->lesson;
        $unit = $lesson->unit;
        $week = $unit->week;
        if ($blockedResponse = $this->blockedWeekResponse($week)) {
            return $blockedResponse;
        }
        $subject = $week->subject;

        $file->title = trim((string) $data['title']);
        $file->lesson_type = (string) ($data['lesson_type'] ?? 'digital_resources');
        $file->moodle_activity_type = $this->normalizeMoodleFileActivityType($data['moodle_activity_type'] ?? null);
        $file->activity_intro = $this->normalizeOptionalText($data['activity_intro'] ?? null);
        $file->available_from = $this->parseOptionalDateTime($data['available_from'] ?? null);
        $file->due_at = $this->parseOptionalDateTime($data['due_at'] ?? null);
        $file->cutoff_at = $this->parseOptionalDateTime($data['cutoff_at'] ?? null);
        [$file->available_from, $file->due_at, $file->cutoff_at] = $this->normalizeAssignmentDates(
            $file->moodle_activity_type,
            $file->available_from,
            $file->due_at,
            $file->cutoff_at
        );
        if ($dateError = $this->validateActivityDatesOutsideHolidays($week->academic_session_id, [$file->available_from, $file->due_at, $file->cutoff_at])) {
            return back()->withInput()->withErrors(['available_from' => $dateError]);
        }
        $file->moodle_audience = $this->normalizeMoodleAudience($data['moodle_audience'] ?? null);
        if ($request->hasFile('file')) {
            $oldPath = $file->file_path;
            $storedPath = $this->storeUploadedFile($request, "academic-management/subject_{$subject->id}/lesson_{$lesson->id}");
            $file->file_path = $storedPath;
            $file->original_name = $request->file('file')->getClientOriginalName();
            $file->mime_type = $request->file('file')->getClientMimeType();
            $file->size = (int) $request->file('file')->getSize();
            
            // Extract and save new file content
            $absolutePath = Storage::disk('public')->path($storedPath);
            $extension = $request->file('file')->getClientOriginalExtension();
            $contentText = $this->textExtractor->extract($absolutePath, $extension);
            $file->content_text = $contentText;
            
            if (!empty($oldPath) && $oldPath !== $storedPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }
        $file->save();

        $weekNo = (int) $week->week_number;
        $this->syncWeekSectionName($subject, $week);
        $this->syncUnitLabel($subject, $unit);
        $this->syncLessonLabel($subject, $lesson);
        $moodleTitle = trim("Week {$weekNo} - Unit {$unit->unit_number} - Lesson {$lesson->lesson_number} - {$file->title}");
        $syncResult = $this->syncFileToMoodle(
            $subject,
            $moodleTitle,
            $file->file_path,
            'lesson_file',
            (int) $file->id,
            $weekNo,
            $file->moodle_activity_type,
            $this->buildMoodleFileSyncContext($file)
        );
        $fileCmid = $this->extractCmidFromSyncResult($syncResult);
        if ($fileCmid) {
            $file->moodle_cmid = $fileCmid;
            $file->moodle_view_url = $this->extractViewUrlFromSyncResult($syncResult);
            $file->save();
            $anchorCmid = $this->findLessonAnchorCmid($lesson, (int) $file->id);
            if ($anchorCmid) {
                $this->moveModuleAfter($fileCmid, $anchorCmid, 'lesson_file', (int) $file->id, (int) $subject->moodle_course_id);
            }
        }

        return back()->with('flash_success', 'Lesson file updated and synced.');
    }

    public function destroySubjectGeneralFile(SubjectGeneralFile $file)
    {
        $file->loadMissing('subject');
        $subject = $file->subject;
        if (!$subject) {
            return back()->withErrors(['file' => 'Cannot delete file because subject was not found.']);
        }

        $result = $this->deleteSubjectGeneralFileRecord($file, $subject);
        if (empty($result['ok'])) {
            return back()->withErrors(['file' => (string) ($result['error'] ?? 'Failed to delete file from Moodle.')]);
        }

        return back()->with('flash_success', 'Subject file deleted successfully.');
    }

    public function destroyUnitGeneralFile(UnitGeneralFile $file)
    {
        $file->loadMissing('unit.week.subject');
        $subject = optional(optional($file->unit)->week)->subject;
        if (!$subject) {
            return back()->withErrors(['file' => 'Cannot delete file because subject was not found.']);
        }

        $result = $this->deleteUnitGeneralFileRecord($file, $subject);
        if (empty($result['ok'])) {
            return back()->withErrors(['file' => (string) ($result['error'] ?? 'Failed to delete file from Moodle.')]);
        }

        return back()->with('flash_success', 'Unit file deleted successfully.');
    }

    public function destroyLessonFile(LessonFile $file)
    {
        $file->loadMissing('lesson.unit.week.subject');
        $subject = optional(optional(optional($file->lesson)->unit)->week)->subject;
        if (!$subject) {
            return back()->withErrors(['file' => 'Cannot delete file because subject was not found.']);
        }

        $result = $this->deleteLessonFileRecord($file, $subject);
        if (empty($result['ok'])) {
            return back()->withErrors(['file' => (string) ($result['error'] ?? 'Failed to delete file from Moodle.')]);
        }

        return back()->with('flash_success', 'Lesson file deleted successfully.');
    }

    private function deleteUnitCascade(WeekUnit $unit, Subject $subject): array
    {
        $unit->loadMissing('generalFiles', 'lessons.files', 'week');

        foreach ($unit->generalFiles as $unitFile) {
            $result = $this->deleteUnitGeneralFileRecord($unitFile, $subject);
            if (empty($result['ok'])) {
                return $result;
            }
        }

        foreach ($unit->lessons as $lesson) {
            $result = $this->deleteLessonCascade($lesson, $subject);
            if (empty($result['ok'])) {
                return $result;
            }
        }

        $unitLabelCmid = $this->getCmidFromLatestSyncLog('week_unit', (int) $unit->id, 'label_sync');
        $unitLabelDelete = $this->deleteMoodleModuleIfNeeded(
            $unitLabelCmid,
            'week_unit',
            (int) $unit->id,
            !empty($subject->moodle_course_id) ? (int) $subject->moodle_course_id : null,
            'label_delete'
        );
        if (empty($unitLabelDelete['ok'])) {
            return $unitLabelDelete;
        }

        $unit->delete();

        return ['ok' => true];
    }

    private function deleteLessonCascade(UnitLesson $lesson, Subject $subject): array
    {
        $lesson->loadMissing('files', 'quizzes', 'questionBanks');

        foreach ($lesson->files as $lessonFile) {
            $result = $this->deleteLessonFileRecord($lessonFile, $subject);
            if (empty($result['ok'])) {
                return $result;
            }
        }

        foreach ($lesson->quizzes as $quiz) {
            $cmid = !empty($quiz->moodle_cmid)
                ? (int) $quiz->moodle_cmid
                : $this->getCmidFromLatestSyncLog('academic_quiz', (int) $quiz->id, 'upload');

            $deleteResult = $this->deleteMoodleModuleIfNeeded(
                $cmid,
                'academic_quiz',
                (int) $quiz->id,
                !empty($subject->moodle_course_id) ? (int) $subject->moodle_course_id : null
            );
            if (empty($deleteResult['ok'])) {
                return $deleteResult;
            }

            if (!empty($quiz->question_file_path)) {
                Storage::disk('public')->delete((string) $quiz->question_file_path);
            }
            if (!empty($quiz->xml_file_path)) {
                Storage::disk('public')->delete((string) $quiz->xml_file_path);
            }
            $quiz->delete();
        }

        foreach ($lesson->questionBanks as $questionBank) {
            if (!empty($questionBank->source_file_path)) {
                Storage::disk('public')->delete((string) $questionBank->source_file_path);
            }
            if (!empty($questionBank->xml_file_path)) {
                Storage::disk('public')->delete((string) $questionBank->xml_file_path);
            }
            $questionBank->delete();
        }

        $lessonLabelCmid = $this->getCmidFromLatestSyncLog('unit_lesson', (int) $lesson->id, 'label_sync');
        $labelDelete = $this->deleteMoodleModuleIfNeeded(
            $lessonLabelCmid,
            'unit_lesson',
            (int) $lesson->id,
            !empty($subject->moodle_course_id) ? (int) $subject->moodle_course_id : null,
            'label_delete'
        );
        if (empty($labelDelete['ok'])) {
            return $labelDelete;
        }

        $lesson->delete();

        return ['ok' => true];
    }

    private function deleteSubjectGeneralFileRecord(SubjectGeneralFile $file, Subject $subject): array
    {
        $cmid = !empty($file->moodle_cmid)
            ? (int) $file->moodle_cmid
            : $this->getCmidFromLatestSyncLog('subject_general_file', (int) $file->id, 'upload');

        $deleteResult = $this->deleteMoodleModuleIfNeeded(
            $cmid,
            'subject_general_file',
            (int) $file->id,
            !empty($subject->moodle_course_id) ? (int) $subject->moodle_course_id : null
        );
        if (empty($deleteResult['ok'])) {
            return $deleteResult;
        }

        if (!empty($file->file_path)) {
            Storage::disk('public')->delete((string) $file->file_path);
        }
        $file->delete();

        return ['ok' => true];
    }

    private function deleteUnitGeneralFileRecord(UnitGeneralFile $file, Subject $subject): array
    {
        $cmid = !empty($file->moodle_cmid)
            ? (int) $file->moodle_cmid
            : $this->getCmidFromLatestSyncLog('unit_general_file', (int) $file->id, 'upload');

        $deleteResult = $this->deleteMoodleModuleIfNeeded(
            $cmid,
            'unit_general_file',
            (int) $file->id,
            !empty($subject->moodle_course_id) ? (int) $subject->moodle_course_id : null
        );
        if (empty($deleteResult['ok'])) {
            return $deleteResult;
        }

        if (!empty($file->file_path)) {
            Storage::disk('public')->delete((string) $file->file_path);
        }
        $file->delete();

        return ['ok' => true];
    }

    private function deleteLessonFileRecord(LessonFile $file, Subject $subject): array
    {
        $cmid = !empty($file->moodle_cmid)
            ? (int) $file->moodle_cmid
            : $this->getCmidFromLatestSyncLog('lesson_file', (int) $file->id, 'upload');

        $deleteResult = $this->deleteMoodleModuleIfNeeded(
            $cmid,
            'lesson_file',
            (int) $file->id,
            !empty($subject->moodle_course_id) ? (int) $subject->moodle_course_id : null
        );
        if (empty($deleteResult['ok'])) {
            return $deleteResult;
        }

        if (!empty($file->file_path)) {
            Storage::disk('public')->delete((string) $file->file_path);
        }
        $file->delete();

        return ['ok' => true];
    }

    private function deleteMoodleModuleIfNeeded(
        ?int $cmid,
        string $entityType,
        int $entityId,
        ?int $courseId,
        string $action = 'delete'
    ): array {
        if (empty($cmid)) {
            $sourceAction = $action === 'label_delete' ? 'label_sync' : 'upload';
            if (!$this->hasSuccessfulSync($entityType, $entityId, $sourceAction)) {
                return ['ok' => true, 'skipped' => true];
            }

            $warning = 'Missing Moodle module id (cmid); skipping Moodle deletion and removing locally.';
            $this->logSync($entityType, $entityId, $action, 'skipped', $courseId, ['cmid' => null], $warning);
            return ['ok' => true, 'skipped' => true, 'warning' => $warning];
        }

        if (!$this->moodle->isConfigured()) {
            $error = 'Moodle is not configured for linked module deletion.';
            $this->logSync($entityType, $entityId, $action, 'failed', $courseId, ['cmid' => $cmid], $error);
            return ['ok' => false, 'error' => $error];
        }

        $result = $this->moodle->deleteCourseModule((int) $cmid);

        $status = !empty($result['ok']) ? 'success' : (!empty($result['skipped']) ? 'skipped' : 'failed');
        $this->logSync($entityType, $entityId, $action, $status, $courseId, $result, $result['error'] ?? null);

        if (empty($result['ok'])) {
            return ['ok' => false, 'error' => (string) ($result['error'] ?? 'module_delete_failed')];
        }

        return ['ok' => true];
    }

    private function subjectGeneralFileTypeRule(): string
    {
        return 'required|in:' . implode(',', array_keys(self::SUBJECT_GENERAL_FILE_TYPES));
    }

    private function unitGeneralFileTypeRule(): string
    {
        return 'required|in:' . implode(',', array_keys(self::UNIT_GENERAL_FILE_TYPES));
    }

    private function lessonFileTypeRule(): string
    {
        return 'required|in:' . implode(',', array_keys(self::LESSON_FILE_TYPES));
    }

    private function moodleFileActivityTypeRule(): string
    {
        return 'nullable|in:' . implode(',', array_keys(self::MOODLE_FILE_ACTIVITY_TYPES));
    }

    private function moodleAudienceRule(): string
    {
        return 'nullable|in:' . implode(',', array_keys(self::MOODLE_AUDIENCE_OPTIONS));
    }

    private function normalizeMoodleFileActivityType(?string $activityType): string
    {
        $activityType = trim((string) $activityType);

        return array_key_exists($activityType, self::MOODLE_FILE_ACTIVITY_TYPES) ? $activityType : 'resource';
    }

    private function normalizeOptionalText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function parseOptionalDateTime($value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return Carbon::parse($value);
    }

    private function normalizeAssignmentDates(string $activityType, ?Carbon $availableFrom, ?Carbon $dueAt, ?Carbon $cutoffAt): array
    {
        if ($activityType !== 'assignment') {
            return [null, null, null];
        }

        return [$availableFrom, $dueAt, $cutoffAt];
    }

    private function normalizeMoodleAudience(?string $audience): string
    {
        $audience = trim((string) $audience);

        return array_key_exists($audience, self::MOODLE_AUDIENCE_OPTIONS) ? $audience : 'both';
    }

    private function buildMoodleAudienceContext(?string $audience): array
    {
        $normalizedAudience = $this->normalizeMoodleAudience($audience);

        return [
            'moodle_audience' => $normalizedAudience,
            'visible' => $normalizedAudience === 'teachers' ? 0 : 1,
        ];
    }

    private function buildMoodleFileSyncContext(object $file): array
    {
        $context = $this->buildMoodleAudienceContext($file->moodle_audience ?? null);

        $intro = $this->normalizeOptionalText($file->activity_intro ?? null);
        if ($intro !== null) {
            $context['intro'] = $intro;
        }

        if (($file->moodle_activity_type ?? 'resource') === 'assignment') {
            if (!empty($file->available_from)) {
                $context['available_from'] = Carbon::parse($file->available_from)->timestamp;
            }
            if (!empty($file->due_at)) {
                $context['due_at'] = Carbon::parse($file->due_at)->timestamp;
            }
            if (!empty($file->cutoff_at)) {
                $context['cutoff_at'] = Carbon::parse($file->cutoff_at)->timestamp;
            }
        }

        return $context;
    }

    private function extractViewUrlFromSyncResult(array $result): ?string
    {
        if (empty($result['ok'])) {
            return null;
        }

        return $this->extractSyncPayloadString($result, [
            'response.viewurl',
            'response.url',
            'viewurl',
            'url',
        ]);
    }

    private function buildLessonQuestionBankCategoryName(UnitLesson $lesson, string $title): string
    {
        $questionBankTitle = trim((string) $title);
        if ($questionBankTitle !== '') {
            return mb_substr($questionBankTitle, 0, 255);
        }

        $lessonNumber = (int) $lesson->lesson_number;
        $lessonTitle = trim((string) $lesson->title);
        $fallbackTitle = $lessonTitle !== ''
            ? "Lesson {$lessonNumber}: {$lessonTitle}"
            : "Lesson {$lessonNumber} Question Bank";

        return mb_substr($fallbackTitle, 0, 255);
    }

    private function attachLessonQuestionBankPreviews(Subject $subject): void
    {
        foreach ($subject->weeks as $week) {
            foreach ($week->units as $unit) {
                foreach ($unit->lessons as $lesson) {
                    foreach ($lesson->questionBanks as $questionBank) {
                        $preview = $this->buildLessonQuestionBankPreview((string) ($questionBank->xml_file_path ?? ''));
                        $questionBank->preview_questions = $preview['questions'];
                        $questionBank->preview_total = $preview['total'];
                        $questionBank->preview_hidden_count = $preview['hidden_count'];
                        $questionBank->preview_error = $preview['error'];
                    }
                }
            }
        }
    }

    private function buildLessonQuestionBankPreview(string $xmlFilePath, int $limit = 20): array
    {
        $xmlFilePath = trim($xmlFilePath);
        if ($xmlFilePath === '') {
            return [
                'questions' => [],
                'total' => 0,
                'hidden_count' => 0,
                'error' => 'Question bank XML file is missing.',
            ];
        }

        $absolutePath = Storage::disk('public')->path($xmlFilePath);
        if (!is_file($absolutePath)) {
            return [
                'questions' => [],
                'total' => 0,
                'hidden_count' => 0,
                'error' => 'Question bank XML file was not found on storage.',
            ];
        }

        $previousState = libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $loaded = $doc->load($absolutePath, LIBXML_NONET | LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($previousState);

        if (!$loaded) {
            return [
                'questions' => [],
                'total' => 0,
                'hidden_count' => 0,
                'error' => 'Question bank XML could not be parsed.',
            ];
        }

        $questions = [];
        $total = 0;

        foreach ($doc->getElementsByTagName('question') as $questionNode) {
            if (!$questionNode instanceof \DOMElement) {
                continue;
            }

            $type = strtolower(trim((string) $questionNode->getAttribute('type')));
            if ($type === '' || $type === 'category') {
                continue;
            }

            $total++;
            if (count($questions) >= $limit) {
                continue;
            }

            $answers = [];
            foreach ($this->getDirectChildElements($questionNode, 'answer') as $answerNode) {
                $fraction = (float) $answerNode->getAttribute('fraction');
                $answers[] = [
                    'text' => $this->extractNestedXmlText($answerNode),
                    'is_correct' => $fraction > 0,
                ];
            }

            $questions[] = [
                'type' => $type,
                'name' => $this->extractNestedXmlText($this->getDirectChildElement($questionNode, 'name')),
                'text' => $this->extractNestedXmlText($this->getDirectChildElement($questionNode, 'questiontext')),
                'answers' => $answers,
            ];
        }

        return [
            'questions' => $questions,
            'total' => $total,
            'hidden_count' => max(0, $total - count($questions)),
            'error' => null,
        ];
    }

    private function getDirectChildElement(?\DOMElement $parent, string $tagName): ?\DOMElement
    {
        if (!$parent) {
            return null;
        }

        foreach ($parent->childNodes as $child) {
            if ($child instanceof \DOMElement && $child->tagName === $tagName) {
                return $child;
            }
        }

        return null;
    }

    private function getDirectChildElements(?\DOMElement $parent, string $tagName): array
    {
        if (!$parent) {
            return [];
        }

        $elements = [];
        foreach ($parent->childNodes as $child) {
            if ($child instanceof \DOMElement && $child->tagName === $tagName) {
                $elements[] = $child;
            }
        }

        return $elements;
    }

    private function extractNestedXmlText(?\DOMElement $element): string
    {
        if (!$element) {
            return '';
        }

        $textElement = $this->getDirectChildElement($element, 'text');
        $value = $textElement ? (string) $textElement->textContent : (string) $element->textContent;

        return $this->normalizeXmlPreviewText($value);
    }

    private function normalizeXmlPreviewText(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/<br\s*\/?>/i', "\n", $value);
        $value = preg_replace('/<\/p>/i', "\n", $value);
        $value = preg_replace('/<\/div>/i', "\n", $value);
        $value = strip_tags($value);
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = preg_replace("/[ \t]+/", ' ', $value);
        $value = preg_replace("/\n{3,}/", "\n\n", $value);

        return trim((string) $value);
    }

    private function buildLessonQuestionBankSyncFlashMessage(array $syncResult, bool $localUploadSucceeded): string
    {
        $baseMessage = $localUploadSucceeded
            ? 'Lesson question bank was saved locally, but Moodle import did not complete.'
            : 'Moodle question bank sync did not complete.';

        $error = $this->normalizeOptionalText((string) ($syncResult['error'] ?? ''));
        if ($error === null) {
            return $baseMessage;
        }

        return $baseMessage . ' ' . $error;
    }

    private function applyLessonQuestionBankSyncResult(LessonQuestionBank $questionBank, array $syncResult): void
    {
        $questionBank->moodle_sync_status = !empty($syncResult['ok'])
            ? 'success'
            : (!empty($syncResult['skipped']) ? 'skipped' : 'failed');
        $questionBank->moodle_sync_error = !empty($syncResult['ok'])
            ? null
            : $this->normalizeOptionalText((string) ($syncResult['error'] ?? 'question_bank_sync_failed'));
        $questionBank->moodle_last_synced_at = now();

        $contextId = $this->extractSyncPayloadInt($syncResult, [
            'response.contextid',
            'response.context_id',
            'contextid',
            'context_id',
        ]);
        if ($contextId !== null) {
            $questionBank->moodle_context_id = $contextId;
        }

        $categoryId = $this->extractSyncPayloadInt($syncResult, [
            'response.categoryid',
            'response.category_id',
            'response.questioncategoryid',
            'categoryid',
            'category_id',
            'questioncategoryid',
        ]);
        if ($categoryId !== null) {
            $questionBank->moodle_category_id = $categoryId;
        }

        $categoryName = $this->extractSyncPayloadString($syncResult, [
            'response.categoryname',
            'response.category_name',
            'categoryname',
            'category_name',
        ]);
        if ($categoryName !== null) {
            $questionBank->moodle_category_name = $categoryName;
        }

        $categoryUrl = $this->extractSyncPayloadString($syncResult, [
            'response.categoryurl',
            'response.category_url',
            'response.manageurl',
            'categoryurl',
            'category_url',
            'manageurl',
        ]);
        if ($categoryUrl !== null) {
            $questionBank->moodle_category_url = $categoryUrl;
        }

        $importedCount = $this->extractSyncPayloadInt($syncResult, [
            'response.importedcount',
            'response.imported_count',
            'response.questioncount',
            'importedcount',
            'imported_count',
            'questioncount',
        ]);
        if ($importedCount !== null) {
            $questionBank->moodle_imported_count = $importedCount;
            if (empty($questionBank->question_count)) {
                $questionBank->question_count = $importedCount;
            }
        }

        $questionBank->save();
    }

    private function extractSyncPayloadInt(array $payload, array $paths): ?int
    {
        foreach ($paths as $path) {
            $value = data_get($payload, $path);
            if (is_numeric($value) && (int) $value > 0) {
                return (int) $value;
            }
        }

        return null;
    }

    private function extractSyncPayloadString(array $payload, array $paths): ?string
    {
        foreach ($paths as $path) {
            $value = data_get($payload, $path);
            if (!is_scalar($value)) {
                continue;
            }

            $stringValue = trim((string) $value);
            if ($stringValue !== '') {
                return $stringValue;
            }
        }

        return null;
    }

    private function buildFinderData(Request $request, ?Subject $selectedSubject): array
    {
        $finderScopes = [
            'all' => 'All Sources',
            'subject_general' => 'Subject General',
            'unit_general' => 'Unit Files',
            'lesson' => 'Lesson Files',
        ];
        $finderTypeLabels = self::SUBJECT_GENERAL_FILE_TYPES + self::UNIT_GENERAL_FILE_TYPES + self::LESSON_FILE_TYPES;
        $finderFilters = [
            'scope' => (string) $request->query('finder_scope', 'all'),
            'week_id' => max(0, (int) $request->query('finder_week_id', 0)),
            'unit_id' => max(0, (int) $request->query('finder_unit_id', 0)),
            'type' => trim((string) $request->query('finder_type', '')),
            'query' => trim((string) $request->query('finder_q', '')),
        ];
        if (!array_key_exists($finderFilters['scope'], $finderScopes)) {
            $finderFilters['scope'] = 'all';
        }
        if ($finderFilters['type'] !== '' && !array_key_exists($finderFilters['type'], $finderTypeLabels)) {
            $finderFilters['type'] = '';
        }

        $finderSearchActive = (string) $request->query('finder_search', '') === '1';
        $finderWeeks = collect();
        $finderUnits = collect();
        $finderResults = collect();

        if ($selectedSubject) {
            $finderWeeks = SubjectWeek::query()
                ->where('subject_id', $selectedSubject->id)
                ->orderBy('week_number')
                ->get(['id', 'week_number', 'title']);

            if ($finderFilters['week_id'] > 0 && !$finderWeeks->contains('id', $finderFilters['week_id'])) {
                $finderFilters['week_id'] = 0;
            }

            $finderUnits = WeekUnit::query()
                ->whereHas('week', function ($q) use ($selectedSubject) {
                    $q->where('subject_id', $selectedSubject->id);
                })
                ->when($finderFilters['week_id'] > 0, function ($q) use ($finderFilters) {
                    $q->where('subject_week_id', $finderFilters['week_id']);
                })
                ->orderBy('subject_week_id')
                ->orderBy('unit_number')
                ->get(['id', 'subject_week_id', 'unit_number', 'title']);

            if ($finderFilters['unit_id'] > 0 && !$finderUnits->contains('id', $finderFilters['unit_id'])) {
                $finderFilters['unit_id'] = 0;
            }

            if ($finderSearchActive) {
                $maxPerSource = 250;

                $subjectRows = collect();
                if (in_array($finderFilters['scope'], ['all', 'subject_general'], true)) {
                    $subjectRows = SubjectGeneralFile::query()
                        ->where('subject_id', $selectedSubject->id)
                        ->when($finderFilters['type'] !== '', function ($q) use ($finderFilters) {
                            $q->where('general_type', $finderFilters['type']);
                        })
                        ->when($finderFilters['query'] !== '', function ($q) use ($finderFilters) {
                            $search = '%' . $finderFilters['query'] . '%';
                            $q->where(function ($inner) use ($search) {
                                $inner->where('title', 'like', $search)
                                    ->orWhere('original_name', 'like', $search);
                            });
                        })
                        ->latest('id')
                        ->limit($maxPerSource)
                        ->get(['id', 'title', 'general_type', 'file_path', 'original_name', 'size', 'created_at'])
                        ->map(function (SubjectGeneralFile $file) {
                            $typeKey = (string) ($file->general_type ?? '');
                            return [
                                'source' => 'Subject General',
                                'type_label' => self::SUBJECT_GENERAL_FILE_TYPES[$typeKey] ?? ($typeKey !== '' ? $typeKey : '-'),
                                'title' => (string) $file->title,
                                'original_name' => (string) ($file->original_name ?? ''),
                                'file_path' => (string) $file->file_path,
                                'week' => '-',
                                'unit' => '-',
                                'lesson' => '-',
                                'size_text' => $this->formatFileSize((int) ($file->size ?? 0)),
                                'created_at' => $file->created_at,
                            ];
                        });
                }

                $unitRows = collect();
                if (in_array($finderFilters['scope'], ['all', 'unit_general'], true)) {
                    $unitRows = UnitGeneralFile::query()
                        ->whereHas('unit.week', function ($q) use ($selectedSubject, $finderFilters) {
                            $q->where('subject_id', $selectedSubject->id);
                            if ($finderFilters['week_id'] > 0) {
                                $q->where('id', $finderFilters['week_id']);
                            }
                        })
                        ->when($finderFilters['unit_id'] > 0, function ($q) use ($finderFilters) {
                            $q->where('week_unit_id', $finderFilters['unit_id']);
                        })
                        ->when($finderFilters['type'] !== '', function ($q) use ($finderFilters) {
                            $q->where('general_type', $finderFilters['type']);
                        })
                        ->when($finderFilters['query'] !== '', function ($q) use ($finderFilters) {
                            $search = '%' . $finderFilters['query'] . '%';
                            $q->where(function ($inner) use ($search) {
                                $inner->where('title', 'like', $search)
                                    ->orWhere('original_name', 'like', $search);
                            });
                        })
                        ->with([
                            'unit:id,subject_week_id,unit_number,title',
                            'unit.week:id,week_number,title,subject_id',
                        ])
                        ->latest('id')
                        ->limit($maxPerSource)
                        ->get(['id', 'week_unit_id', 'title', 'general_type', 'file_path', 'original_name', 'size', 'created_at'])
                        ->map(function (UnitGeneralFile $file) {
                            $typeKey = (string) ($file->general_type ?? '');
                            $unit = $file->unit;
                            $week = optional($unit)->week;
                            return [
                                'source' => 'Unit File',
                                'type_label' => self::UNIT_GENERAL_FILE_TYPES[$typeKey] ?? ($typeKey !== '' ? $typeKey : '-'),
                                'title' => (string) $file->title,
                                'original_name' => (string) ($file->original_name ?? ''),
                                'file_path' => (string) $file->file_path,
                                'week' => $week ? ('Week ' . (int) $week->week_number) : '-',
                                'unit' => $unit ? ('Unit ' . (int) $unit->unit_number) : '-',
                                'lesson' => '-',
                                'size_text' => $this->formatFileSize((int) ($file->size ?? 0)),
                                'created_at' => $file->created_at,
                            ];
                        });
                }

                $lessonRows = collect();
                if (in_array($finderFilters['scope'], ['all', 'lesson'], true)) {
                    $lessonRows = LessonFile::query()
                        ->whereHas('lesson.unit.week', function ($q) use ($selectedSubject, $finderFilters) {
                            $q->where('subject_id', $selectedSubject->id);
                            if ($finderFilters['week_id'] > 0) {
                                $q->where('id', $finderFilters['week_id']);
                            }
                        })
                        ->when($finderFilters['unit_id'] > 0, function ($q) use ($finderFilters) {
                            $q->whereHas('lesson', function ($lessonQuery) use ($finderFilters) {
                                $lessonQuery->where('week_unit_id', $finderFilters['unit_id']);
                            });
                        })
                        ->when($finderFilters['type'] !== '', function ($q) use ($finderFilters) {
                            $q->where('lesson_type', $finderFilters['type']);
                        })
                        ->when($finderFilters['query'] !== '', function ($q) use ($finderFilters) {
                            $search = '%' . $finderFilters['query'] . '%';
                            $q->where(function ($inner) use ($search) {
                                $inner->where('title', 'like', $search)
                                    ->orWhere('original_name', 'like', $search);
                            });
                        })
                        ->with([
                            'lesson:id,week_unit_id,lesson_number,title',
                            'lesson.unit:id,subject_week_id,unit_number,title',
                            'lesson.unit.week:id,week_number,title,subject_id',
                        ])
                        ->latest('id')
                        ->limit($maxPerSource)
                        ->get(['id', 'unit_lesson_id', 'title', 'lesson_type', 'file_path', 'original_name', 'size', 'created_at'])
                        ->map(function (LessonFile $file) {
                            $typeKey = (string) ($file->lesson_type ?? '');
                            $lesson = $file->lesson;
                            $unit = optional($lesson)->unit;
                            $week = optional($unit)->week;
                            return [
                                'source' => 'Lesson File',
                                'type_label' => self::LESSON_FILE_TYPES[$typeKey] ?? ($typeKey !== '' ? $typeKey : '-'),
                                'title' => (string) $file->title,
                                'original_name' => (string) ($file->original_name ?? ''),
                                'file_path' => (string) $file->file_path,
                                'week' => $week ? ('Week ' . (int) $week->week_number) : '-',
                                'unit' => $unit ? ('Unit ' . (int) $unit->unit_number) : '-',
                                'lesson' => $lesson ? ('Lesson ' . (int) $lesson->lesson_number) : '-',
                                'size_text' => $this->formatFileSize((int) ($file->size ?? 0)),
                                'created_at' => $file->created_at,
                            ];
                        });
                }

                $finderResults = $subjectRows
                    ->concat($unitRows)
                    ->concat($lessonRows)
                    ->sortByDesc(function (array $row) {
                        $createdAt = $row['created_at'] ?? null;
                        if ($createdAt instanceof \DateTimeInterface) {
                            return $createdAt->getTimestamp();
                        }

                        return 0;
                    })
                    ->values();
            }
        }

        return [
            'finderScopes' => $finderScopes,
            'finderTypeLabels' => $finderTypeLabels,
            'finderFilters' => $finderFilters,
            'finderWeeks' => $finderWeeks,
            'finderUnits' => $finderUnits,
            'finderResults' => $finderResults,
            'finderSearchActive' => $finderSearchActive,
        ];
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = (int) floor(log($bytes, 1024));
        $pow = min($pow, count($units) - 1);
        $value = $bytes / (1024 ** $pow);

        return number_format($value, $pow === 0 ? 0 : 2) . ' ' . $units[$pow];
    }

    private function storeUploadedFile(Request $request, string $directory, string $fieldName = 'file'): string
    {
        $uploadedFile = $request->file($fieldName);
        $baseName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = Str::slug($baseName) ?: 'file';
        $ext = $uploadedFile->getClientOriginalExtension();
        $name = time() . '_' . $safeName . ($ext ? '.' . $ext : '');

        return $uploadedFile->storeAs($directory, $name, 'public');
    }

    private function storeQuizXml(int $subjectId, string $title, string $xml): string
    {
        $safeTitle = Str::slug($title) ?: 'quiz';
        $fileName = time() . '_' . $safeTitle . '.xml';
        $path = "academic-management/subject_{$subjectId}/quizzes/xml/{$fileName}";

        $stored = Storage::disk('public')->put($path, $xml);
        if (!$stored) {
            throw new \RuntimeException('Unable to store generated Moodle XML.');
        }

        return $path;
    }

    private function storeLessonQuestionBankXml(int $subjectId, int $lessonId, string $title, string $xml): string
    {
        $safeTitle = Str::slug($title) ?: 'question-bank';
        $fileName = time() . '_' . $safeTitle . '.xml';
        $path = "academic-management/subject_{$subjectId}/question-banks/lesson_{$lessonId}/xml/{$fileName}";

        $stored = Storage::disk('public')->put($path, $xml);
        if (!$stored) {
            throw new \RuntimeException('Unable to store generated question bank Moodle XML.');
        }

        return $path;
    }

    private function importMoodleWeekContents(SubjectWeek $week, Subject $subject): array
    {
        $summary = [
            'ok' => true,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'error' => null,
        ];

        if (!$this->moodle->isConfigured()) {
            $summary['ok'] = false;
            $summary['skipped'] = 1;
            $summary['error'] = 'Moodle is not configured';
            return $summary;
        }

        $courseId = $this->ensureMoodleCourseId($subject);
        if (!$courseId) {
            $summary['ok'] = false;
            $summary['skipped'] = 1;
            $summary['error'] = 'No Moodle course id for this subject';
            return $summary;
        }

        $result = $this->moodle->getCourseSectionModules($courseId, (int) $week->week_number);
        if (empty($result['ok'])) {
            $summary['ok'] = false;
            $summary['error'] = (string) ($result['error'] ?? 'moodle_fetch_failed');
            $this->logSync('subject_week', (int) $week->id, 'moodle_import', 'failed', $courseId, $result, $summary['error']);
            return $summary;
        }

        foreach (($result['modules'] ?? []) as $module) {
            $cmid = (int) ($module['cmid'] ?? 0);
            $name = trim((string) ($module['name'] ?? ''));
            $modname = strtolower(trim((string) ($module['modname'] ?? '')));

            if ($cmid <= 0 || $name === '') {
                $summary['skipped']++;
                continue;
            }

            $unitNumber = $this->extractMoodleNumber($name, 'unit') ?: 1;
            $lessonNumber = $this->extractMoodleNumber($name, 'lesson');
            $unit = $this->firstOrCreateImportedUnit($week, $unitNumber, $name);

            if ($modname === 'label') {
                if ($lessonNumber) {
                    $this->firstOrCreateImportedLesson($unit, $lessonNumber, $name);
                }
                $summary['skipped']++;
                continue;
            }

            $lesson = $lessonNumber ? $this->firstOrCreateImportedLesson($unit, $lessonNumber, $name) : null;
            $title = $this->cleanImportedMoodleTitle($name);
            $viewUrl = trim((string) ($module['url'] ?? '')) ?: null;

            if ($modname === 'quiz') {
                $quiz = AcademicQuiz::query()->where('moodle_cmid', $cmid)->first();
                $payload = [
                    'subject_id' => $subject->id,
                    'week_unit_id' => $lesson ? null : $unit->id,
                    'unit_lesson_id' => $lesson ? $lesson->id : null,
                    'title' => $title,
                    'moodle_instance_id' => !empty($module['instance']) ? (int) $module['instance'] : null,
                    'moodle_view_url' => $viewUrl,
                    'moodle_audience' => 'both',
                ];

                if ($quiz) {
                    $quiz->update($payload);
                    $summary['updated']++;
                } else {
                    $payload['moodle_cmid'] = $cmid;
                    AcademicQuiz::create($payload);
                    $summary['created']++;
                }
                continue;
            }

            $activityType = $modname === 'assign' || $modname === 'assignment' ? 'assignment' : 'resource';
            if ($lesson) {
                $file = LessonFile::query()->where('moodle_cmid', $cmid)->first();
                $payload = [
                    'unit_lesson_id' => $lesson->id,
                    'title' => $title,
                    'lesson_type' => 'digital_resources',
                    'original_name' => $name,
                    'moodle_activity_type' => $activityType,
                    'moodle_view_url' => $viewUrl,
                    'moodle_audience' => 'both',
                ];

                if ($file) {
                    $file->update($payload);
                    $summary['updated']++;
                } else {
                    $payload['moodle_cmid'] = $cmid;
                    LessonFile::create($payload);
                    $summary['created']++;
                }
                continue;
            }

            $file = UnitGeneralFile::query()->where('moodle_cmid', $cmid)->first();
            $payload = [
                'week_unit_id' => $unit->id,
                'title' => $title,
                'general_type' => 'knowledge_core',
                'original_name' => $name,
                'moodle_activity_type' => $activityType,
                'moodle_view_url' => $viewUrl,
                'moodle_audience' => 'both',
            ];

            if ($file) {
                $file->update($payload);
                $summary['updated']++;
            } else {
                $payload['moodle_cmid'] = $cmid;
                UnitGeneralFile::create($payload);
                $summary['created']++;
            }
        }

        $this->logSync('subject_week', (int) $week->id, 'moodle_import', 'success', $courseId, [
            'summary' => $summary,
            'modules' => $result['modules'] ?? [],
        ]);

        return $summary;
    }

    private function firstOrCreateImportedUnit(SubjectWeek $week, int $unitNumber, string $sourceTitle): WeekUnit
    {
        $unitNumber = max(1, $unitNumber);
        $title = $this->cleanImportedMoodleTitle($sourceTitle);

        return WeekUnit::firstOrCreate(
            [
                'subject_week_id' => $week->id,
                'unit_number' => $unitNumber,
            ],
            [
                'title' => $title !== '' ? $title : 'Unit ' . $unitNumber,
            ]
        );
    }

    private function firstOrCreateImportedLesson(WeekUnit $unit, int $lessonNumber, string $sourceTitle): UnitLesson
    {
        $lessonNumber = max(1, $lessonNumber);
        $title = $this->cleanImportedMoodleTitle($sourceTitle);

        return UnitLesson::firstOrCreate(
            [
                'week_unit_id' => $unit->id,
                'lesson_number' => $lessonNumber,
            ],
            [
                'title' => $title !== '' ? $title : 'Lesson ' . $lessonNumber,
            ]
        );
    }

    private function extractMoodleNumber(string $name, string $type): ?int
    {
        $pattern = $type === 'lesson'
            ? '/\blesson\s*(\d+)\b/i'
            : '/\bunit\s*(\d+)\b/i';

        if (preg_match($pattern, $name, $match)) {
            return max(1, (int) $match[1]);
        }

        return null;
    }

    private function cleanImportedMoodleTitle(string $name): string
    {
        $title = preg_replace('/\bweek\s*\d+\s*[-:|]?\s*/i', '', $name);
        $title = preg_replace('/\bunit\s*\d+\s*[-:|]?\s*/i', '', (string) $title);
        $title = preg_replace('/\blesson\s*\d+\s*[-:|]?\s*/i', '', (string) $title);
        $title = trim(preg_replace('/\s+/', ' ', (string) $title));

        return $title !== '' ? $title : trim($name);
    }

    private function syncLessonQuestionBankToMoodle(Subject $subject, UnitLesson $lesson, LessonQuestionBank $questionBank): array
    {
        if (!$this->moodle->isConfigured()) {
            $this->logSync('lesson_question_bank', (int) $questionBank->id, 'import', 'skipped', null, null, 'Moodle is not configured');
            return ['ok' => false, 'skipped' => true, 'error' => 'Moodle is not configured'];
        }

        $courseId = $this->ensureMoodleCourseId($subject);
        if (!$courseId) {
            $this->logSync('lesson_question_bank', (int) $questionBank->id, 'import', 'failed', null, null, 'No Moodle course id for this subject');
            return ['ok' => false, 'error' => 'No Moodle course id for this subject'];
        }

        $absolutePath = Storage::disk('public')->path((string) $questionBank->xml_file_path);
        $context = [
            'subject' => (string) $subject->name,
            'year' => (string) optional($subject->my_class)->name,
            'week' => (int) optional(optional($lesson->unit)->week)->week_number,
            'unit' => (int) optional($lesson->unit)->unit_number,
            'lesson' => (int) $lesson->lesson_number,
            'lessonname' => trim((string) $lesson->title),
            'categoryname' => $this->buildLessonQuestionBankCategoryName($lesson, (string) $questionBank->title),
        ];
        if (!empty($questionBank->question_count)) {
            $context['questioncount'] = (int) $questionBank->question_count;
        }

        $result = $this->moodle->importQuestionBankFromPath(
            $courseId,
            $absolutePath,
            (string) $questionBank->title,
            $context
        );

        $status = !empty($result['ok']) ? 'success' : (!empty($result['skipped']) ? 'skipped' : 'failed');
        $error = !empty($result['ok']) ? null : (string) ($result['error'] ?? 'question_bank_sync_failed');
        $this->logSync('lesson_question_bank', (int) $questionBank->id, 'import', $status, $courseId, $result, $error);

        return $result;
    }

    private function syncFileToMoodle(
        Subject $subject,
        string $title,
        string $filePath,
        string $entityType,
        int $entityId,
        int $sectionNumber = 0,
        string $activityType = 'resource',
        array $contextOverrides = []
    ): array
    {
        if (!$this->moodle->isConfigured()) {
            $this->logSync($entityType, $entityId, 'upload', 'skipped', null, null, 'Moodle is not configured');
            return ['ok' => false, 'skipped' => true, 'error' => 'Moodle is not configured'];
        }

        $courseId = $this->ensureMoodleCourseId($subject);
        if (!$courseId) {
            $this->logSync($entityType, $entityId, 'upload', 'failed', null, null, 'No Moodle course id for this subject');
            return ['ok' => false, 'error' => 'No Moodle course id for this subject'];
        }

        $absolutePath = Storage::disk('public')->path($filePath);
        $context = array_merge([
            'subject' => (string) $subject->name,
            'year' => (string) optional($subject->my_class)->name,
            'title' => $title,
            'section' => max(0, $sectionNumber),
        ], $contextOverrides);

        if (!empty($context['section_title'])) {
            $this->moodle->syncCourseSectionName($courseId, max(0, $sectionNumber), (string) $context['section_title']);
        }

        $result = $this->moodle->syncCourseFileFromPath($courseId, $absolutePath, $title, $activityType, $context);

        $status = !empty($result['ok']) ? 'success' : 'failed';
        $error = !empty($result['ok']) ? null : (string) ($result['error'] ?? 'sync_failed');
        $this->logSync($entityType, $entityId, 'upload', $status, $courseId, $result, $error);
        return $result;
    }

    private function getSessionMoodleSectionNumber(AcademicSession $session): int
    {
        $sessionIds = AcademicSession::query()
            ->orderBy('start_date')
            ->orderBy('id')
            ->pluck('id')
            ->values();

        $index = $sessionIds->search($session->id);

        return $index === false ? 1 : ((int) $index + 1);
    }

    private function syncWeekSectionName(Subject $subject, SubjectWeek $week): void
    {
        $courseId = $this->ensureMoodleCourseId($subject);
        if (!$courseId) {
            $this->logSync('subject_week', (int) $week->id, 'section_sync', 'failed', null, null, 'No Moodle course id');
            return;
        }

        $sectionName = $this->buildWeekSectionName($week);
        $result = $this->moodle->syncCourseSectionName($courseId, (int) $week->week_number, $sectionName);
        if (empty($result['ok']) && $this->isMissingMoodleCourseError($result)) {
            $subject->moodle_course_id = null;
            $subject->save();
            $courseId = $this->ensureMoodleCourseId($subject);
            if ($courseId) {
                $retryResult = $this->moodle->syncCourseSectionName($courseId, (int) $week->week_number, $sectionName);
                $result = array_merge($retryResult, [
                    'retried_after_missing_course' => true,
                    'first_error' => $result,
                ]);
            }
        }

        $status = !empty($result['ok']) ? 'success' : (!empty($result['skipped']) ? 'skipped' : 'failed');
        $this->logSync(
            'subject_week',
            (int) $week->id,
            'section_sync',
            $status,
            $courseId,
            $result,
            $result['error'] ?? null
        );
    }

    private function syncUnitLabel(Subject $subject, WeekUnit $unit, bool $force = false): void
    {
        if (!$force && $this->hasSuccessfulSync('week_unit', (int) $unit->id, 'label_sync')) {
            return;
        }

        $courseId = $this->ensureMoodleCourseId($subject);
        if (!$courseId) {
            $this->logSync('week_unit', (int) $unit->id, 'label_sync', 'failed', null, null, 'No Moodle course id');
            return;
        }

        $title = 'Unit ' . (int) $unit->unit_number . ': ' . trim((string) $unit->title);
        $result = $this->moodle->syncCourseSectionLabel($courseId, (int) $unit->week->week_number, $title, $title);
        $status = !empty($result['ok']) ? 'success' : (!empty($result['skipped']) ? 'skipped' : 'failed');

        $this->logSync(
            'week_unit',
            (int) $unit->id,
            'label_sync',
            $status,
            $courseId,
            $result,
            $result['error'] ?? null
        );
    }

    private function syncLessonLabel(Subject $subject, UnitLesson $lesson): void
    {
        if ($this->hasSuccessfulSync('unit_lesson', (int) $lesson->id, 'label_sync')) {
            return;
        }

        $courseId = $this->ensureMoodleCourseId($subject);
        if (!$courseId) {
            $this->logSync('unit_lesson', (int) $lesson->id, 'label_sync', 'failed', null, null, 'No Moodle course id');
            return;
        }

        $title = 'Lesson ' . (int) $lesson->lesson_number . ': ' . trim((string) $lesson->title);
        $result = $this->moodle->syncCourseSectionLabel($courseId, (int) $lesson->unit->week->week_number, $title, $title);
        $status = !empty($result['ok']) ? 'success' : (!empty($result['skipped']) ? 'skipped' : 'failed');

        $this->logSync(
            'unit_lesson',
            (int) $lesson->id,
            'label_sync',
            $status,
            $courseId,
            $result,
            $result['error'] ?? null
        );
    }

    private function buildWeekSectionName(SubjectWeek $week): string
    {
        $title = trim((string) $week->title);
        $sectionName = $title !== '' ? "Week {$week->week_number} - {$title}" : "Week {$week->week_number}";
        $holidayName = $this->extractHolidayNameFromBlockNote($week->block_note);

        if ($week->is_blocked && $holidayName !== '') {
            return $sectionName . ' - ' . $holidayName;
        }

        return $sectionName;
    }

    private function extractHolidayNameFromBlockNote(?string $note): string
    {
        $note = trim((string) $note);
        if (!$this->isHolidayBlockNote($note)) {
            return '';
        }

        $holidayName = trim(substr($note, strlen(self::HOLIDAY_BLOCK_PREFIX)));
        $datePosition = strpos($holidayName, ' (');
        if ($datePosition !== false) {
            $holidayName = trim(substr($holidayName, 0, $datePosition));
        }

        return $holidayName;
    }

    private function hasSuccessfulSync(string $entityType, int $entityId, string $action): bool
    {
        return MoodleSyncLog::query()
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('action', $action)
            ->where('status', 'success')
            ->exists();
    }

    private function extractCmidFromSyncResult(array $result): ?int
    {
        if (empty($result['ok'])) {
            return null;
        }

        return $this->extractCmidFromPayload($result);
    }

    private function findUnitAnchorCmid(WeekUnit $unit, int $currentFileId): ?int
    {
        $previousFileIds = UnitGeneralFile::query()
            ->where('week_unit_id', $unit->id)
            ->where('id', '<', $currentFileId)
            ->orderByDesc('id')
            ->pluck('id');

        foreach ($previousFileIds as $fileId) {
            $cmid = $this->getCmidFromLatestSyncLog('unit_general_file', (int) $fileId, 'upload');
            if ($cmid) {
                return $cmid;
            }
        }

        return $this->getCmidFromLatestSyncLog('week_unit', (int) $unit->id, 'label_sync');
    }

    private function findLessonAnchorCmid(UnitLesson $lesson, int $currentFileId): ?int
    {
        $previousFileIds = LessonFile::query()
            ->where('unit_lesson_id', $lesson->id)
            ->where('id', '<', $currentFileId)
            ->orderByDesc('id')
            ->pluck('id');

        foreach ($previousFileIds as $fileId) {
            $cmid = $this->getCmidFromLatestSyncLog('lesson_file', (int) $fileId, 'upload');
            if ($cmid) {
                return $cmid;
            }
        }

        return $this->getCmidFromLatestSyncLog('unit_lesson', (int) $lesson->id, 'label_sync');
    }

    private function findLessonQuizAnchorCmid(UnitLesson $lesson, int $currentQuizId): ?int
    {
        $previousQuizIds = AcademicQuiz::query()
            ->where('unit_lesson_id', $lesson->id)
            ->where('id', '<', $currentQuizId)
            ->orderByDesc('id')
            ->pluck('id');

        foreach ($previousQuizIds as $quizId) {
            $cmid = $this->getCmidFromLatestSyncLog('academic_quiz', (int) $quizId, 'upload');
            if ($cmid) {
                return $cmid;
            }
        }

        $previousFileIds = LessonFile::query()
            ->where('unit_lesson_id', $lesson->id)
            ->orderByDesc('id')
            ->pluck('id');

        foreach ($previousFileIds as $fileId) {
            $cmid = $this->getCmidFromLatestSyncLog('lesson_file', (int) $fileId, 'upload');
            if ($cmid) {
                return $cmid;
            }
        }

        return $this->getCmidFromLatestSyncLog('unit_lesson', (int) $lesson->id, 'label_sync');
    }

    private function findUnitQuizAnchorCmid(WeekUnit $unit, int $currentQuizId): ?int
    {
        $previousQuizIds = AcademicQuiz::query()
            ->where('week_unit_id', $unit->id)
            ->where('id', '<', $currentQuizId)
            ->orderByDesc('id')
            ->pluck('id');

        foreach ($previousQuizIds as $quizId) {
            $cmid = $this->getCmidFromLatestSyncLog('academic_quiz', (int) $quizId, 'upload');
            if ($cmid) {
                return $cmid;
            }
        }

        $unit->loadMissing('lessons');
        $lessonAnchor = null;
        foreach ($unit->lessons as $lesson) {
            $candidate = $this->findLessonContentAnchorCmid($lesson);
            if ($candidate) {
                $lessonAnchor = $candidate;
            }
        }
        if ($lessonAnchor) {
            return $lessonAnchor;
        }

        $previousFileIds = UnitGeneralFile::query()
            ->where('week_unit_id', $unit->id)
            ->orderByDesc('id')
            ->pluck('id');

        foreach ($previousFileIds as $fileId) {
            $cmid = $this->getCmidFromLatestSyncLog('unit_general_file', (int) $fileId, 'upload');
            if ($cmid) {
                return $cmid;
            }
        }

        return $this->getCmidFromLatestSyncLog('week_unit', (int) $unit->id, 'label_sync');
    }

    private function findLessonContentAnchorCmid(UnitLesson $lesson): ?int
    {
        $latestQuizIds = AcademicQuiz::query()
            ->where('unit_lesson_id', $lesson->id)
            ->orderByDesc('id')
            ->pluck('id');

        foreach ($latestQuizIds as $quizId) {
            $cmid = $this->getCmidFromLatestSyncLog('academic_quiz', (int) $quizId, 'upload');
            if ($cmid) {
                return $cmid;
            }
        }

        $latestFileIds = LessonFile::query()
            ->where('unit_lesson_id', $lesson->id)
            ->orderByDesc('id')
            ->pluck('id');

        foreach ($latestFileIds as $fileId) {
            $cmid = $this->getCmidFromLatestSyncLog('lesson_file', (int) $fileId, 'upload');
            if ($cmid) {
                return $cmid;
            }
        }

        return $this->getCmidFromLatestSyncLog('unit_lesson', (int) $lesson->id, 'label_sync');
    }

    private function getCmidFromLatestSyncLog(string $entityType, int $entityId, string $action): ?int
    {
        $log = MoodleSyncLog::query()
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('action', $action)
            ->where('status', 'success')
            ->latest('id')
            ->first();

        if (!$log || !$log->response_payload) {
            return null;
        }

        $payload = json_decode((string) $log->response_payload, true);
        if (!is_array($payload)) {
            return null;
        }

        return $this->extractCmidFromPayload($payload);
    }

    private function extractCmidFromPayload(array $payload): ?int
    {
        $candidates = [
            $payload['response']['cmid'] ?? null,
            $payload['response']['coursemodule'] ?? null,
            $payload['response']['coursemoduleid'] ?? null,
            $payload['response']['id'] ?? null,
            $payload['cmid'] ?? null,
            $payload['coursemodule'] ?? null,
            $payload['coursemoduleid'] ?? null,
            $payload['id'] ?? null,
        ];

        foreach ($candidates as $value) {
            if (is_numeric($value) && (int) $value > 0) {
                return (int) $value;
            }
        }

        $raw = $payload['raw_body'] ?? null;
        if (is_string($raw)) {
            if (preg_match('/"cmid"\s*:\s*(\d+)/', $raw, $match)) {
                return (int) $match[1];
            }
            if (preg_match('/"coursemodule"\s*:\s*(\d+)/', $raw, $match)) {
                return (int) $match[1];
            }
            if (preg_match('/"coursemoduleid"\s*:\s*(\d+)/', $raw, $match)) {
                return (int) $match[1];
            }
            if (preg_match('/"id"\s*:\s*(\d+)/', $raw, $match)) {
                return (int) $match[1];
            }
        }

        return null;
    }

    private function moveModuleAfter(int $cmid, int $afterCmid, string $entityType, int $entityId, ?int $courseId): void
    {
        if ($cmid <= 0 || $afterCmid <= 0 || $cmid === $afterCmid) {
            return;
        }

        $result = $this->moodle->moveCourseModuleAfter($cmid, $afterCmid);
        $status = !empty($result['ok']) ? 'success' : (!empty($result['skipped']) ? 'skipped' : 'failed');
        $this->logSync(
            $entityType,
            $entityId,
            'reorder',
            $status,
            $courseId,
            $result,
            $result['error'] ?? null
        );
    }

    private function blockedWeekResponse(?SubjectWeek $week)
    {
        if (!$week || !$week->is_blocked) {
            return null;
        }

        $message = $week->block_note
            ? 'This week is blocked: ' . $week->block_note
            : 'This week is blocked, so activities cannot be added or updated.';

        return back()->withInput()->withErrors(['holiday' => $message]);
    }

    private function validateActivityDatesOutsideHolidays(?int $sessionId, array $dates): ?string
    {
        if (!$sessionId) {
            return null;
        }

        $normalizedDates = [];
        foreach ($dates as $date) {
            if (!$date) {
                continue;
            }

            $normalizedDates[] = $date instanceof Carbon ? $date->copy()->startOfDay() : Carbon::parse($date)->startOfDay();
        }

        if (empty($normalizedDates)) {
            return null;
        }

        usort($normalizedDates, function (Carbon $a, Carbon $b) {
            return $a->timestamp <=> $b->timestamp;
        });

        $rangeStart = $normalizedDates[0];
        $rangeEnd = $normalizedDates[count($normalizedDates) - 1];
        $holiday = AcademicHoliday::query()
            ->where(function ($query) use ($sessionId) {
                $query->where('academic_session_id', $sessionId)
                    ->orWhereNull('academic_session_id');
            })
            ->whereDate('starts_on', '<=', $rangeEnd->toDateString())
            ->whereDate('ends_on', '>=', $rangeStart->toDateString())
            ->first();

        if ($holiday) {
            return 'Activity dates cannot overlap holiday: ' . $holiday->name . ' (' .
                optional($holiday->starts_on)->format('Y-m-d') . ' to ' .
                optional($holiday->ends_on)->format('Y-m-d') . ').';
        }

        return null;
    }

    private function buildWeekHolidayBlock(?int $sessionId, $startDate, $endDate, bool $manualBlocked = false, ?string $manualNote = null): array
    {
        $holiday = $this->findHolidayForRange($sessionId, $startDate, $endDate);
        if ($holiday) {
            return [true, self::HOLIDAY_BLOCK_PREFIX . $holiday->name . ' (' .
                optional($holiday->starts_on)->format('Y-m-d') . ' to ' .
                optional($holiday->ends_on)->format('Y-m-d') . ')'];
        }

        if ($manualBlocked && !$this->isHolidayBlockNote($manualNote)) {
            return [true, $manualNote];
        }

        return [false, null];
    }

    private function findHolidayForRange(?int $sessionId, $startDate, $endDate): ?AcademicHoliday
    {
        if (!$startDate || !$endDate) {
            return null;
        }

        $start = $startDate instanceof Carbon ? $startDate->copy() : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate->copy() : Carbon::parse($endDate);

        return AcademicHoliday::query()
            ->where(function ($query) use ($sessionId) {
                if ($sessionId) {
                    $query->where('academic_session_id', $sessionId)
                        ->orWhereNull('academic_session_id');
                } else {
                    $query->whereNull('academic_session_id');
                }
            })
            ->whereDate('starts_on', '<=', $end->toDateString())
            ->whereDate('ends_on', '>=', $start->toDateString())
            ->orderBy('starts_on')
            ->first();
    }

    private function applyHolidayBlocks(?int $sessionId = null): int
    {
        $count = 0;
        SubjectWeek::query()
            ->when($sessionId, function ($query) use ($sessionId) {
                $query->where('academic_session_id', $sessionId);
            })
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->chunkById(200, function ($weeks) use (&$count) {
                foreach ($weeks as $week) {
                    [$isBlocked, $blockNote] = $this->buildWeekHolidayBlock(
                        $week->academic_session_id,
                        $week->start_date,
                        $week->end_date,
                        (bool) $week->is_blocked,
                        $week->block_note
                    );

                    if ((bool) $week->is_blocked !== $isBlocked || (string) $week->block_note !== (string) $blockNote) {
                        $week->update([
                            'is_blocked' => $isBlocked,
                            'block_note' => $blockNote,
                        ]);

                        $week->loadMissing('subject.my_class');
                        if ($week->subject) {
                            $this->syncWeekSectionName($week->subject, $week);
                        }
                    }

                    if ($isBlocked && $this->isHolidayBlockNote($blockNote)) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    private function isHolidayBlockNote(?string $note): bool
    {
        return strpos((string) $note, self::HOLIDAY_BLOCK_PREFIX) === 0;
    }

    private function ensureMoodleCourseId(Subject $subject): ?int
    {
        if (!empty($subject->moodle_course_id)) {
            return (int) $subject->moodle_course_id;
        }

        $subject->loadMissing('my_class');
        $className = trim((string) optional($subject->my_class)->name);
        $fullName = trim($subject->name . ($className !== '' ? " - {$className}" : ''));
        $baseShortName = strtoupper(mb_substr(Str::slug($className . '-' . $subject->name, '_'), 0, 95));
        $shortName = ($baseShortName !== '' ? $baseShortName : 'COURSE') . '_' . $subject->id;

        $courseId = $this->moodle->createOrGetCourse($fullName, $shortName, (int) config('services.moodle.category_id', 1));
        if (!$courseId) {
            return null;
        }

        $subject->moodle_course_id = $courseId;
        $subject->save();

        return (int) $courseId;
    }

    private function isMissingMoodleCourseError(array $result): bool
    {
        $error = strtolower((string) ($result['error'] ?? ''));
        return strpos($error, 'database table course') !== false || strpos($error, 'course') !== false && strpos($error, 'not found') !== false;
    }

    private function logSync(
        string $entityType,
        int $entityId,
        string $action,
        string $status,
        ?int $moodleCourseId = null,
        ?array $payload = null,
        ?string $error = null
    ): void {
        MoodleSyncLog::create([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'status' => $status,
            'moodle_course_id' => $moodleCourseId,
            'response_payload' => $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null,
            'error_message' => $error,
        ]);
    }
}
