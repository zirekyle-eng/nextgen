<?php
namespace App\Http\Controllers;

use App\Models\CurriculumFile;
use App\Models\MyClass;
use App\Models\StudentRecord;
use App\Services\CurriculumFileTextExtractor;
use App\Services\MoodleService;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    private CurriculumFileTextExtractor $textExtractor;
    private MoodleService $moodle;

    public function __construct(CurriculumFileTextExtractor $textExtractor, MoodleService $moodle)
    {
        $this->textExtractor = $textExtractor;
        $this->moodle = $moodle;
    }

    public function apiIndex(Request $request)
    {
        $query = CurriculumFile::query();
        $isStudent = auth()->check() && strtolower((string) auth()->user()->user_type) === 'student';
        $isParent = auth()->check() && strtolower((string) auth()->user()->user_type) === 'parent';
        $studentClassName = $isStudent ? $this->getStudentClassName() : null;
        $parentChildClassName = null;

        if ($isParent) {
            $requestedChildId = (int) $request->query('child_id', 0);
            $childRecord = StudentRecord::query()
                ->where('my_parent_id', auth()->id())
                ->when($requestedChildId > 0, function ($q) use ($requestedChildId) {
                    $q->where('user_id', $requestedChildId);
                })
                ->with('my_class:id,name')
                ->first();

            if ($childRecord && $childRecord->my_class) {
                $parentChildClassName = (string) $childRecord->my_class->name;
            }
        }

        if ($isStudent) {
            if ($studentClassName) {
                $query->where('year', $studentClassName);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($isParent) {
            if ($parentChildClassName) {
                $query->where('year', $parentChildClassName);
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        
        if ($request->year && $request->year !== 'all') {
            $query->where('year', trim((string) $request->year));
        }
        
        if ($request->subject && $request->subject !== 'all') {
            $query->where('subject', $request->subject);
        }
        
        $files = $query->latest()->get()->map(function($file) use ($isStudent) {
            return [
                'id' => $file->id,
                'name' => $file->name,
                'path' => $file->path,
                'url' => asset('storage/' . $file->path),
                'ext' => $file->extension,
                'size' => $this->formatBytes($file->size),
                'year' => $file->year,
                'subject' => $file->subject,
            ];
        });
        
        return response()->json([
            'success' => true,
            'files' => $files
        ]);
    }
    
    public function store(Request $request)
    {
        if (!$this->canManageTutorFiles()) {
            abort(403, 'Only teacher/admin can upload tutor files.');
        }

        $request->validate([
            'files' => 'required',
            'files.*' => 'file|mimes:pdf,docx,xlsx,pptx,txt|max:51200',
            'year' => 'required|string',
            'subject' => 'required|string',
            'activity_type' => 'nullable|in:resource,assignment,quiz',
            'activity_title' => 'nullable|string|max:255',
            'activity_intro' => 'nullable|string|max:5000',
            'available_from' => 'nullable|date',
            'due_at' => 'nullable|date',
            'cutoff_at' => 'nullable|date',
        ]);

        if ((string) $request->input('activity_type', 'resource') === 'assignment') {
            $request->validate([
                'activity_title' => ['required', 'string', 'max:255'],
                'available_from' => ['required', 'date'],
                'due_at' => ['required', 'date', 'after_or_equal:available_from'],
                'cutoff_at' => ['nullable', 'date', 'after_or_equal:due_at'],
            ]);
        }

        if (!$this->isAllowedSubjectForYear((string) $request->year, (string) $request->subject)) {
            return back()
                ->withInput()
                ->withErrors(['subject' => 'Selected subject is not assigned for this class.']);
        }

        $activityType = $this->normalizeActivityType((string) $request->input('activity_type', 'resource'));
        $activityOptions = [
            'title' => trim((string) $request->input('activity_title', '')),
            'intro' => trim((string) $request->input('activity_intro', '')),
            'available_from' => $this->toTimestamp($request->input('available_from')),
            'due_at' => $this->toTimestamp($request->input('due_at')),
            'cutoff_at' => $this->toTimestamp($request->input('cutoff_at')),
        ];
        $uploadedCount = 0;

        if ($request->hasFile('files')) {
            $files = $request->file('files');
            
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                if ($file->isValid()) {
                    $this->saveFile($file, $request->year, $request->subject, $activityType, $activityOptions);
                    $uploadedCount++;
                }
            }
        }

        return redirect()->route('tutor.index')
            ->with('success', "$uploadedCount files uploaded successfully");
    }
    
    private function saveFile($file, $year, $subject, string $activityType = 'resource', array $activityOptions = [])
    {
        $originalName = $file->getClientOriginalName();
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $originalName);
        
        $path = $file->storeAs(
            "uploads/{$year}/{$subject}", 
            $fileName, 
            'public'
        );

        $absolutePath = Storage::disk('public')->path($path);
        $contentText = $this->textExtractor->extract($absolutePath, $file->getClientOriginalExtension());

        $record = CurriculumFile::create([
            'name' => $originalName,
            'original_name' => $originalName,
            'path' => $path,
            'year' => $year,
            'subject' => $subject,
            'extension' => $file->getClientOriginalExtension(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'content_text' => $contentText,
            'user_id' => auth()->id() ?: 1
        ]);

        $this->syncFileToMoodle($record, $absolutePath, $activityType, $activityOptions);

        return $record;
    }
    
    public function destroy(CurriculumFile $curriculumFile)
    {
        if (!$this->canManageTutorFiles()) {
            abort(403, 'Only teacher/admin can delete tutor files.');
        }

        Storage::disk('public')->delete($curriculumFile->path);
        $curriculumFile->delete();

        return redirect()->route('tutor.index')
            ->with('success', 'File deleted successfully');
    }
    
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    private function getStudentKeyStage(): ?string
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        $studentRecord = $user->student_record()->with('my_class.class_type')->first();
        if (!$studentRecord || !$studentRecord->my_class) {
            return null;
        }

        return $this->mapClassToKeyStage(
            (string) $studentRecord->my_class->name,
            (string) optional($studentRecord->my_class->class_type)->code
        );
    }

    private function getStudentClassName(): ?string
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

    private function mapYearToKeyStage(?string $year): ?string
    {
        $year = strtolower(trim((string) $year));
        if ($year === '') {
            return null;
        }

        if (Str::contains($year, ['creche', 'pre nursery', 'nursery'])) {
            return 'Key Stage 1';
        }
        if (Str::contains($year, ['primary'])) {
            return 'Key Stage 2';
        }
        if (Str::contains($year, ['jss', 'junior secondary'])) {
            return 'Key Stage 3';
        }
        if (Str::contains($year, ['sss', 'senior secondary'])) {
            return Str::contains($year, ['3']) ? 'Key Stage 5' : 'Key Stage 4';
        }

        if (in_array($year, ['year1', 'year 1', 'year2', 'year 2', 'ks1', 'key stage 1'], true)) {
            return 'Key Stage 1';
        }
        if (in_array($year, ['year3', 'year 3', 'year4', 'year 4', 'year5', 'year 5', 'year6', 'year 6', 'ks2', 'key stage 2'], true)) {
            return 'Key Stage 2';
        }
        if (in_array($year, ['year7', 'year 7', 'year8', 'year 8', 'year9', 'year 9', 'ks3', 'key stage 3', 'jss 1', 'jss 2', 'jss 3'], true)) {
            return 'Key Stage 3';
        }
        if (in_array($year, ['year10', 'year 10', 'year11', 'year 11', 'igcse', 'gcse', 'ks4', 'key stage 4', 'sss 1', 'sss 2'], true)) {
            return 'Key Stage 4';
        }
        if (in_array($year, ['year12', 'year 12', 'year13', 'year 13', 'a-level', 'as-level', 'ks5', 'key stage 5', 'sss 3'], true)) {
            return 'Key Stage 5';
        }

        return null;
    }

    private function yearsForKeyStage(string $keyStage): array
    {
        $baseValues = match (strtolower(trim($keyStage))) {
            'key stage 1', 'ks1' => ['key stage 1', 'Key Stage 1', 'year1', 'year2', 'year 1', 'year 2'],
            'key stage 2', 'ks2' => ['key stage 2', 'Key Stage 2', 'year3', 'year4', 'year5', 'year6', 'year 3', 'year 4', 'year 5', 'year 6'],
            'key stage 3', 'ks3' => ['key stage 3', 'Key Stage 3', 'year7', 'year8', 'year9', 'year 7', 'year 8', 'year 9'],
            'key stage 4', 'ks4' => ['key stage 4', 'Key Stage 4', 'year10', 'year11', 'year 10', 'year 11', 'igcse', 'gcse'],
            'key stage 5', 'ks5' => ['key stage 5', 'Key Stage 5', 'year12', 'year13', 'year 12', 'year 13', 'a-level', 'as-level'],
            default => [],
        };

        $classNames = MyClass::with('class_type')->get()
            ->filter(function ($class) use ($keyStage) {
                return strtolower((string) $this->mapClassToKeyStage($class->name, optional($class->class_type)->code)) === strtolower(trim($keyStage));
            })
            ->pluck('name')
            ->toArray();

        return array_values(array_unique(array_merge($baseValues, $classNames)));
    }

    private function mapClassToKeyStage(?string $className, ?string $classTypeCode): ?string
    {
        $className = strtolower(trim((string) $className));
        $classTypeCode = strtoupper(trim((string) $classTypeCode));

        if (in_array($classTypeCode, ['C', 'PN', 'N'], true)) {
            return 'Key Stage 1';
        }
        if ($classTypeCode === 'P') {
            return 'Key Stage 2';
        }
        if ($classTypeCode === 'J') {
            return 'Key Stage 3';
        }
        if ($classTypeCode === 'S') {
            return Str::contains($className, ['sss 3', 'ss3', 'year 13', 'a-level']) ? 'Key Stage 5' : 'Key Stage 4';
        }

        return $this->mapYearToKeyStage($className);
    }

    private function canManageTutorFiles(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return in_array(strtolower((string) auth()->user()->user_type), ['teacher', 'admin', 'super_admin'], true);
    }

    private function syncFileToMoodle(
        CurriculumFile $file,
        string $absolutePath,
        string $activityType = 'resource',
        array $activityOptions = []
    ): void
    {
        try {
            if (!$this->moodle->isConfigured()) {
                return;
            }

            $courseId = $this->resolveMoodleCourseId((string) $file->year, (string) $file->subject);
            if (!$courseId) {
                \Log::warning('Moodle file sync skipped: no mapped Moodle course', [
                    'file_id' => $file->id,
                    'year' => $file->year,
                    'subject' => $file->subject,
                ]);
                return;
            }

            $result = $this->moodle->syncCourseFileFromPath(
                $courseId,
                $absolutePath,
                (string) $file->name,
                $activityType,
                array_merge([
                    'year' => (string) $file->year,
                    'subject' => (string) $file->subject,
                ], $activityOptions)
            );

            if (empty($result['ok'])) {
                \Log::warning('Moodle file sync failed', [
                    'file_id' => $file->id,
                    'course_id' => $courseId,
                    'activity_type' => $activityType,
                    'error' => $result['error'] ?? 'unknown',
                    'response' => $result['response'] ?? null,
                ]);
                return;
            }

            \Log::info('Moodle file sync successful', [
                'file_id' => $file->id,
                'course_id' => $courseId,
                'activity_type' => $activityType,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Moodle file sync exception', [
                'file_id' => $file->id,
                'activity_type' => $activityType,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function normalizeActivityType(string $activityType): string
    {
        $activityType = strtolower(trim($activityType));
        if (!in_array($activityType, ['resource', 'assignment', 'quiz'], true)) {
            return 'resource';
        }

        return $activityType;
    }

    private function toTimestamp($value): ?int
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $ts = strtotime($value);
        if ($ts === false) {
            return null;
        }

        return $ts;
    }

    private function resolveMoodleCourseId(string $year, string $subject): ?int
    {
        $year = trim($year);
        $subject = trim($subject);
        if ($year === '' || $subject === '') {
            return null;
        }

        $subjectRow = Subject::query()
            ->whereRaw('TRIM(LOWER(name)) = ?', [mb_strtolower($subject)])
            ->whereHas('my_class', function ($q) use ($year) {
                $q->whereRaw('TRIM(LOWER(name)) = ?', [mb_strtolower($year)]);
            })
            ->first();

        if (!$subjectRow || empty($subjectRow->moodle_course_id)) {
            return null;
        }

        return (int) $subjectRow->moodle_course_id;
    }

    private function isAllowedSubjectForYear(string $year, string $subject): bool
    {
        $year = trim($year);
        $subject = trim($subject);
        if ($year === '' || $subject === '') {
            return false;
        }

        $query = Subject::query()
            ->whereRaw('TRIM(LOWER(name)) = ?', [mb_strtolower($subject)])
            ->whereHas('my_class', function ($q) use ($year) {
                $q->whereRaw('TRIM(LOWER(name)) = ?', [mb_strtolower($year)]);
            });

        if (auth()->check() && strtolower((string) auth()->user()->user_type) === 'teacher') {
            $teacherScoped = (clone $query)->where('teacher_id', auth()->id());
            if ($teacherScoped->exists()) {
                return true;
            }
        }

        return $query->exists();
    }
}
