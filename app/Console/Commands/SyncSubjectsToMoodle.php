<?php

namespace App\Console\Commands;

use App\Models\StudentRecord;
use App\Models\Subject;
use App\Services\MoodleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SyncSubjectsToMoodle extends Command
{
    protected $signature = 'moodle:sync-subjects {--subject_id= : Sync only one subject id} {--dry-run : Preview only, no Moodle writes}';

    protected $description = 'Sync existing local subjects to Moodle courses and enrol class students';

    private MoodleService $moodle;

    public function __construct(MoodleService $moodle)
    {
        parent::__construct();
        $this->moodle = $moodle;
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $subjectId = (int) $this->option('subject_id');

        if (!$this->moodle->isConfigured()) {
            $this->error('Moodle is not configured. Check MOODLE_URL and MOODLE_TOKEN.');
            return self::FAILURE;
        }

        $subjectsQuery = Subject::excludeActivities()->with(['my_class', 'teacher'])->orderBy('id');
        if ($subjectId > 0) {
            $subjectsQuery->where('id', $subjectId);
        }

        $subjects = $subjectsQuery->get();
        if ($subjects->isEmpty()) {
            $this->warn('No subjects found for sync.');
            return self::SUCCESS;
        }

        $this->info('Subjects to sync: ' . $subjects->count() . ($dryRun ? ' (dry-run)' : ''));

        $summary = [
            'ok' => 0,
            'failed' => 0,
            'courses_created_or_found' => 0,
            'users_enrolled' => 0,
        ];

        foreach ($subjects as $subject) {
            try {
                $result = $this->syncSubject($subject, $dryRun);
                $summary['ok']++;
                $summary['courses_created_or_found'] += (int) ($result['course'] ?? 0);
                $summary['users_enrolled'] += (int) ($result['enrolled'] ?? 0);
                $this->line("[OK] Subject #{$subject->id} {$subject->name}");
            } catch (\Throwable $e) {
                $summary['failed']++;
                $this->error("[FAIL] Subject #{$subject->id} {$subject->name}: " . $e->getMessage());
                \Log::error('moodle:sync-subjects failed', [
                    'subject_id' => $subject->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info('Sync summary:');
        $this->line(' - Successful subjects: ' . $summary['ok']);
        $this->line(' - Failed subjects: ' . $summary['failed']);
        $this->line(' - Courses created/found: ' . $summary['courses_created_or_found']);
        $this->line(' - Users enrolled: ' . $summary['users_enrolled'] . ($dryRun ? ' (planned)' : ''));

        return $summary['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function syncSubject(Subject $subject, bool $dryRun = false): array
    {
        $className = (string) optional($subject->my_class)->name;
        $fullName = trim($subject->name . ($className !== '' ? " - {$className}" : ''));
        $baseShortName = trim((string) ($subject->slug ?: $subject->name));
        $shortName = strtoupper(mb_substr(Str::slug($className . '-' . $baseShortName, '_'), 0, 95));
        if ($shortName === '') {
            $shortName = 'COURSE_' . $subject->id;
        }
        $shortName .= '_' . $subject->id;

        $teacher = $subject->teacher;
        $studentRecords = StudentRecord::where('my_class_id', $subject->my_class_id)
            ->with('user:id,name,email')
            ->get();

        if ($dryRun) {
            $studentsWithEmail = $studentRecords->filter(function ($record) {
                return $record->user && !empty($record->user->email);
            })->count();

            $this->line("  - Would ensure Moodle course: {$fullName} [{$shortName}]");
            $this->line("  - Would enrol teacher: " . ($teacher && $teacher->email ? $teacher->email : 'N/A'));
            $this->line("  - Would enrol students: {$studentsWithEmail}");

            return [
                'course' => 1,
                'enrolled' => ($teacher && $teacher->email ? 1 : 0) + $studentsWithEmail,
            ];
        }

        $courseId = (int) ($subject->moodle_course_id ?? 0);
        if ($courseId <= 0) {
            $courseId = (int) $this->moodle->createOrGetCourse(
                $fullName,
                $shortName,
                (int) config('services.moodle.category_id', 1)
            );
        }

        if (!$courseId) {
            throw new \RuntimeException('Unable to create or get Moodle course');
        }

        if (Schema::hasColumn('subjects', 'moodle_course_id')) {
            $subject->moodle_course_id = $courseId;
            $subject->save();
        }

        $teacherRoleId = (int) config('services.moodle.teacher_role_id', 3);
        $studentRoleId = (int) config('services.moodle.student_role_id', 5);
        $enrolments = [];

        if ($teacher && !empty($teacher->email)) {
            [$firstName, $lastName] = $this->splitName((string) $teacher->name, 'Teacher');
            $email = trim((string) $teacher->email);

            $this->moodle->createOrUpdateUser([
                'firstname' => $firstName,
                'lastname' => $lastName,
                'email' => $email,
                'auth' => 'manual',
            ]);

            $moodleTeacher = $this->moodle->getUserByUsername($email) ?: $this->moodle->getUserByEmail($email);
            if ($moodleTeacher && !empty($moodleTeacher['id'])) {
                $enrolments[] = [
                    'roleid' => $teacherRoleId,
                    'userid' => (int) $moodleTeacher['id'],
                ];
            }
        }

        foreach ($studentRecords as $record) {
            $student = $record->user;
            if (!$student || empty($student->email)) {
                continue;
            }

            [$firstName, $lastName] = $this->splitName((string) $student->name, 'Student');
            $email = trim((string) $student->email);

            $this->moodle->createOrUpdateUser([
                'firstname' => $firstName,
                'lastname' => $lastName,
                'email' => $email,
                'auth' => 'manual',
            ]);

            $moodleStudent = $this->moodle->getUserByUsername($email) ?: $this->moodle->getUserByEmail($email);
            if ($moodleStudent && !empty($moodleStudent['id'])) {
                $enrolments[] = [
                    'roleid' => $studentRoleId,
                    'userid' => (int) $moodleStudent['id'],
                ];
            }
        }

        if (!empty($enrolments)) {
            $this->moodle->enrolUsers($courseId, $enrolments);
        }

        return [
            'course' => 1,
            'enrolled' => count($enrolments),
        ];
    }

    private function splitName(string $name, string $fallback): array
    {
        $name = trim($name);
        if ($name === '') {
            return [$fallback, $fallback];
        }

        $parts = preg_split('/\s+/', $name, 2);
        $firstName = $parts[0] ?? $fallback;
        $lastName = $parts[1] ?? $firstName;

        return [$firstName, $lastName];
    }
}
