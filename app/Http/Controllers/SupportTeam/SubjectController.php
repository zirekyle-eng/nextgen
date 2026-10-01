<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Requests\Subject\SubjectCreate;
use App\Http\Requests\Subject\SubjectUpdate;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\User;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;
use App\Services\MoodleService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SubjectController extends Controller
{
    protected $my_class, $user, $moodle;

    public function __construct(MyClassRepo $my_class, UserRepo $user, MoodleService $moodle)
    {
        $this->middleware('teamSA', ['except' => ['destroy',] ]);
        $this->middleware('super_admin', ['only' => ['destroy',] ]);

        $this->my_class = $my_class;
        $this->user = $user;
        $this->moodle = $moodle;
    }

    public function index()
    {
        $d['my_classes'] = $this->my_class->all();
        $d['teachers'] = $this->user->getUserByType('teacher');
        $d['subjects'] = $this->my_class->getAllSubjects();

        return view('pages.support_team.subjects.index', $d);
    }

    public function store(SubjectCreate $req)
    {
        $data = $req->all();
        $subject = $this->my_class->createSubject($data);

        $this->syncSubjectToMoodle($subject);

        return Qs::jsonStoreOk();
    }

    public function edit($id)
    {
        $d['s'] = $sub = $this->my_class->findSubject($id);
        $d['my_classes'] = $this->my_class->all();
        $d['teachers'] = $this->user->getUserByType('teacher');

        return is_null($sub) ? Qs::goWithDanger('subjects.index') : view('pages.support_team.subjects.edit', $d);
    }

    public function update(SubjectUpdate $req, $id)
    {
        $data = $req->all();
        $this->my_class->updateSubject($id, $data);

        $subject = $this->my_class->findSubject($id);
        if ($subject && !$subject->is_activity) {
            $this->syncSubjectToMoodle($subject);
        } elseif ($subject && $subject->is_activity && Schema::hasColumn('subjects', 'moodle_course_id') && !is_null($subject->moodle_course_id)) {
            $subject->moodle_course_id = null;
            $subject->save();
        }

        return Qs::jsonUpdateOk();
    }

    public function destroy($id)
    {
        $this->my_class->deleteSubject($id);
        return back()->with('flash_success', __('msg.del_ok'));
    }

    private function syncSubjectToMoodle(Subject $subject): void
    {
        if ($subject->is_activity) {
            return;
        }

        if (!$this->moodle->isConfigured()) {
            \Log::warning('Moodle sync skipped on subject create: Moodle is not configured', ['subject_id' => $subject->id]);
            return;
        }

        try {
            $subject->loadMissing(['my_class', 'teacher']);

            $className = (string) optional($subject->my_class)->name;
            $fullName = trim($subject->name . ($className !== '' ? " - {$className}" : ''));
            $baseShortName = trim($subject->slug ?: $subject->name);
            $shortName = strtoupper(mb_substr(Str::slug($className . '-' . $baseShortName, '_'), 0, 95));
            if ($shortName === '') {
                $shortName = 'COURSE_' . $subject->id;
            }
            $shortName .= '_' . $subject->id;

            $courseId = $this->moodle->createOrGetCourse($fullName, $shortName, (int) config('services.moodle.category_id', 1));
            if (!$courseId) {
                \Log::error('Moodle course create/get failed', ['subject_id' => $subject->id, 'shortname' => $shortName]);
                return;
            }

            if (Schema::hasColumn('subjects', 'moodle_course_id')) {
                $subject->moodle_course_id = $courseId;
                $subject->save();
            }

            $teacherRoleId = (int) config('services.moodle.teacher_role_id', 3);
            $studentRoleId = (int) config('services.moodle.student_role_id', 5);
            $enrolments = [];

            $teacher = $subject->teacher;
            if ($teacher && !empty($teacher->email)) {
                $this->moodle->createOrUpdateUser([
                    'firstname' => trim((string) $teacher->name) !== '' ? explode(' ', trim((string) $teacher->name))[0] : 'Teacher',
                    'lastname' => trim((string) $teacher->name) !== '' ? (trim(str_replace(explode(' ', trim((string) $teacher->name))[0], '', trim((string) $teacher->name))) ?: 'Teacher') : 'Teacher',
                    'email' => trim((string) $teacher->email),
                    'auth' => 'manual',
                ]);
                $moodleTeacher = $this->moodle->getUserByUsername(trim((string) $teacher->email))
                    ?: $this->moodle->getUserByEmail(trim((string) $teacher->email));

                if ($moodleTeacher && !empty($moodleTeacher['id'])) {
                    $enrolments[] = [
                        'roleid' => $teacherRoleId,
                        'userid' => (int) $moodleTeacher['id'],
                    ];
                }
            }

            $studentRecords = StudentRecord::where('my_class_id', $subject->my_class_id)
                ->with('user:id,name,email')
                ->get();

            foreach ($studentRecords as $record) {
                $student = $record->user;
                if (!$student || empty($student->email)) {
                    continue;
                }

                $studentName = trim((string) $student->name);
                $nameParts = preg_split('/\s+/', $studentName, 2);
                $firstName = $nameParts[0] ?? 'Student';
                $lastName = $nameParts[1] ?? 'Student';

                $this->moodle->createOrUpdateUser([
                    'firstname' => $firstName,
                    'lastname' => $lastName,
                    'email' => trim((string) $student->email),
                    'auth' => 'manual',
                ]);

                $moodleStudent = $this->moodle->getUserByUsername(trim((string) $student->email))
                    ?: $this->moodle->getUserByEmail(trim((string) $student->email));

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
        } catch (\Throwable $e) {
            \Log::error('Moodle sync failed on subject create', [
                'subject_id' => $subject->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
