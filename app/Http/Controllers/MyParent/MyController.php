<?php

namespace App\Http\Controllers\MyParent;
use App\Http\Controllers\Controller;
use App\Models\BbbMeetingAttendance;
use App\Models\BbgMeeting;
use App\Models\Setting;
use App\Models\StudentRecord;
use App\Models\TimeTable;
use App\Models\TutorLessonProgress;
use App\Models\TutorLearningProgress;
use App\Models\TutorQuizResult;
use App\Models\UnitLesson;
use App\Repositories\StudentRepo;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class MyController extends Controller
{
    protected $student;
    public function __construct(StudentRepo $student)
    {
        $this->student = $student;
    }

    public function children()
    {
        $data['students'] = $this->student->getRecord(['my_parent_id' => Auth::user()->id])->with(['my_class', 'section'])->get();

        return view('pages.parent.children', $data);
    }

    public function aiTutorProgress()
    {
        $children = $this->student->getRecord(['my_parent_id' => Auth::user()->id])
            ->with(['my_class', 'section', 'user'])
            ->get();

        if (
            !Schema::hasTable('tutor_learning_progress') ||
            !Schema::hasTable('tutor_quiz_results') ||
            !Schema::hasTable('tutor_lesson_progress')
        ) {
            return view('pages.parent.ai_tutor_progress', [
                'childrenProgress' => collect(),
                'warning' => 'AI Tutor tables are not ready yet.',
            ]);
        }

        $childrenProgress = $children->map(function ($record) {
            $studentUser = $record->user;
            $studentUserId = $studentUser ? (int) $studentUser->id : null;

            if (!$studentUserId) {
                return [
                    'student' => $record,
                    'stats' => [
                        'total_points' => 0,
                        'completed_quizzes' => 0,
                        'avg_quiz_percentage' => 0,
                        'lessons_in_progress' => 0,
                        'lessons_completed' => 0,
                        'lessons_started' => 0,
                    ],
                    'latest_quizzes' => collect(),
                    'latest_lessons' => collect(),
                ];
            }

            $progressRows = TutorLearningProgress::where('user_id', $studentUserId)->get();
            $quizRows = TutorQuizResult::where('user_id', $studentUserId)->latest()->limit(8)->get();
            $lessonRows = TutorLessonProgress::where('user_id', $studentUserId)
                ->orderByDesc('last_activity_at')
                ->latest('id')
                ->get();

            $latestLessonRows = $lessonRows->take(8);
            $lessonMap = UnitLesson::query()
                ->with('unit.week')
                ->whereIn('id', $latestLessonRows->pluck('lesson_id')->filter()->values()->all())
                ->get()
                ->keyBy('id');

            $latestLessons = $latestLessonRows->map(function ($row) use ($lessonMap) {
                $lesson = $lessonMap->get((int) $row->lesson_id);
                $unit = optional($lesson)->unit;
                $week = optional($unit)->week;

                return [
                    'subject' => (string) $row->subject,
                    'status' => (string) $row->status,
                    'lesson_name' => $lesson
                        ? ('Lesson ' . (int) $lesson->lesson_number . ': ' . (string) $lesson->title)
                        : ('Lesson #' . (int) $row->lesson_id),
                    'unit_name' => $unit
                        ? ('Week ' . (int) optional($week)->week_number . ' - Unit ' . (int) $unit->unit_number)
                        : '-',
                    'started_at' => optional($row->started_at)->toDateTimeString(),
                    'completed_at' => optional($row->completed_at)->toDateTimeString(),
                    'last_activity_at' => optional($row->last_activity_at)->toDateTimeString(),
                ];
            });

            return [
                'student' => $record,
                'stats' => [
                    'total_points' => (int) $progressRows->sum('points'),
                    'completed_quizzes' => (int) $progressRows->sum('completed_quizzes'),
                    'avg_quiz_percentage' => (int) round(
                        TutorQuizResult::where('user_id', $studentUserId)->avg('percentage') ?? 0
                    ),
                    'lessons_in_progress' => (int) $lessonRows->where('status', 'in_progress')->count(),
                    'lessons_completed' => (int) $lessonRows->where('status', 'completed')->count(),
                    'lessons_started' => (int) $lessonRows->whereIn('status', ['in_progress', 'completed'])->count(),
                ],
                'latest_quizzes' => $quizRows,
                'latest_lessons' => $latestLessons,
            ];
        });

        return view('pages.parent.ai_tutor_progress', [
            'childrenProgress' => $childrenProgress,
            'warning' => null,
        ]);
    }

    public function attendanceReport($studentUserId)
    {
        $parentId = Auth::user()->id;
        $record = StudentRecord::where('user_id', $studentUserId)
            ->where('my_parent_id', $parentId)
            ->firstOrFail();

        $student = User::findOrFail($studentUserId);
        $parent = Auth::user();

        $selectedTerm = request()->query('term');
        $selectedSubject = request()->query('subject');

        [$from, $to, $termLabel] = $this->resolveReportRange($selectedTerm);

        $rows = BbbMeetingAttendance::where('student_user_id', $studentUserId)
            ->whereNotNull('scheduled_start_at')
            ->whereBetween('scheduled_start_at', [$from, $to])
            ->orderBy('scheduled_start_at')
            ->get();

        $meetingIds = $rows->pluck('meeting_id')->filter()->unique()->values()->all();
        $meetings = BbgMeeting::whereIn('meeting_id', $meetingIds)
            ->get(['meeting_id', 'meeting_name', 'tt_id'])
            ->keyBy('meeting_id');

        $timeTableIds = $meetings->pluck('tt_id')->filter()->unique()->values()->all();
        $timeTables = TimeTable::with('subject')
            ->whereIn('id', $timeTableIds)
            ->get()
            ->keyBy('id');

        $subjectMap = [];
        foreach ($timeTables as $timeTable) {
            if ($timeTable->subject) {
                $subjectMap[$timeTable->subject->id] = $timeTable->subject->name;
            }
        }
        asort($subjectMap, SORT_NATURAL | SORT_FLAG_CASE);

        $meetingSubjects = [];
        foreach ($meetings as $meeting) {
            $timeTable = $meeting->tt_id ? $timeTables->get($meeting->tt_id) : null;
            $meetingSubjects[$meeting->meeting_id] = $timeTable?->subject?->id;
        }

        if ($selectedSubject !== null && $selectedSubject !== '' && ctype_digit((string) $selectedSubject)) {
            $selectedSubjectId = (int) $selectedSubject;
            $rows = $rows->filter(function ($row) use ($meetingSubjects, $selectedSubjectId) {
                return (int) ($meetingSubjects[$row->meeting_id] ?? 0) === $selectedSubjectId;
            })->values();
        }

        $mapped = $rows->map(function ($row) use ($meetings, $meetingSubjects, $subjectMap) {
            $meeting = $meetings->get($row->meeting_id);
            $subjectId = $meetingSubjects[$row->meeting_id] ?? null;
            return [
                'date' => optional($row->scheduled_start_at)->format('Y-m-d'),
                'meeting' => $meeting?->meeting_name ?: $row->meeting_id,
                'subject' => $subjectId ? ($subjectMap[$subjectId] ?? '-') : '-',
                'status' => $row->status ?: 'absent',
                'late_minutes' => (int) $row->late_minutes,
                'join_at' => optional($row->join_at)->format('H:i') ?: '-',
                'left_at' => optional($row->left_at)->format('H:i') ?: '-',
                'camera_on_count' => (int) ($row->camera_on_count ?? 0),
                'first_camera_on_at' => optional($row->first_camera_on_at)->format('H:i') ?: '-',
                'last_camera_on_at' => optional($row->last_camera_on_at)->format('H:i') ?: '-',
            ];
        })->values();

        $summary = [
            'present' => $rows->where('status', 'present')->count(),
            'late' => $rows->where('status', 'late')->count(),
            'absent' => $rows->where('status', 'absent')->count(),
        ];

        return view('pages.parent.attendance_report', [
            'student' => $student,
            'parent' => $parent,
            'record' => $record,
            'from' => $from,
            'to' => $to,
            'termLabel' => $termLabel,
            'summary' => $summary,
            'rows' => $mapped,
            'subjects' => $subjectMap,
            'selectedTerm' => $selectedTerm,
            'selectedSubject' => $selectedSubject,
        ]);
    }

    private function resolveReportRange(?string $requestedTerm): array
    {
        $today = Carbon::today();
        $settings = Setting::whereIn('type', [
            'term1_start', 'term1_end',
            'term2_start', 'term2_end',
            'term3_start', 'term3_end',
        ])->pluck('description', 'type');

        $termRanges = [];
        foreach ([1, 2, 3] as $term) {
            $startKey = "term{$term}_start";
            $endKey = "term{$term}_end";
            $start = trim((string) ($settings[$startKey] ?? ''));
            $end = trim((string) ($settings[$endKey] ?? ''));
            if ($start === '' || $end === '') {
                continue;
            }

            $startDate = Carbon::parse($start)->startOfDay();
            $endDate = Carbon::parse($end)->endOfDay();
            $termRanges[(string) $term] = [$startDate, $endDate, "Term {$term}"];
        }

        if ($requestedTerm !== null && $requestedTerm !== '') {
            $requested = trim((string) $requestedTerm);
            if (isset($termRanges[$requested])) {
                return $termRanges[$requested];
            }
            if ($requested === 'last30') {
                $from = $today->copy()->subDays(30)->startOfDay();
                $to = $today->copy()->endOfDay();
                return [$from, $to, 'Last 30 days'];
            }
        }

        foreach ($termRanges as [$startDate, $endDate, $label]) {
            if ($today->between($startDate, $endDate)) {
                return [$startDate, $endDate, $label];
            }
        }

        $from = $today->copy()->subDays(30)->startOfDay();
        $to = $today->copy()->endOfDay();
        return [$from, $to, 'Last 30 days'];
    }

}
