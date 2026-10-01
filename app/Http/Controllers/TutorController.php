<?php
namespace App\Http\Controllers;

use App\Models\CurriculumFile;
use App\Models\MyClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TutorController extends Controller
{
    public function index()
    {
        if (auth()->check() && strtolower((string) auth()->user()->user_type) === 'teacher') {
            return redirect()->route('tutor.upload');
        }

        $isStudent = auth()->check() && strtolower((string) auth()->user()->user_type) === 'student';
        $studentClassName = $isStudent ? $this->getStudentClassName() : null;

        $query = CurriculumFile::query()->latest();

        if ($isStudent) {
            if ($studentClassName) {
                $query->where('year', $studentClassName);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $files = $query->get()->map(function($file) use ($isStudent) {
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

        $years = $isStudent
            ? collect([$studentClassName])->filter()->values()
            : CurriculumFile::distinct()->pluck('year')->filter()->values();
        $subjects = CurriculumFile::distinct()->pluck('subject')->filter()->values();
        
        return view('tutor.index', compact('files', 'years', 'subjects'));
    }
    
    public function upload()
    {
        if (!$this->canManageTutorFiles()) {
            abort(403, 'Only teacher/admin can upload tutor files.');
        }

        $classes = MyClass::orderBy('name', 'asc')->get(['id', 'name']);
        $subjectsQuery = Subject::query()->excludeActivities()->with('my_class:id,name');
        if (auth()->check() && strtolower((string) auth()->user()->user_type) === 'teacher') {
            $teacherSubjectsQuery = Subject::query()
                ->excludeActivities()
                ->with('my_class:id,name')
                ->where('teacher_id', auth()->id());
            $subjectsQuery = $teacherSubjectsQuery->exists() ? $teacherSubjectsQuery : $subjectsQuery;
        }

        $subjectsByClass = [];
        foreach ($subjectsQuery->get(['id', 'name', 'my_class_id']) as $subject) {
            $className = trim((string) optional($subject->my_class)->name);
            $subjectName = trim((string) $subject->name);
            if ($className === '' || $subjectName === '') {
                continue;
            }

            if (!isset($subjectsByClass[$className])) {
                $subjectsByClass[$className] = [];
            }

            if (!in_array($subjectName, $subjectsByClass[$className], true)) {
                $subjectsByClass[$className][] = $subjectName;
            }
        }

        foreach ($subjectsByClass as $className => $names) {
            sort($names, SORT_NATURAL | SORT_FLAG_CASE);
            $subjectsByClass[$className] = array_values($names);
        }

        return view('tutor.upload', compact('classes', 'subjectsByClass'));
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

    private function getAvailableKeyStagesFromClasses()
    {
        $stages = MyClass::with('class_type')->get()
            ->map(function ($class) {
                return $this->mapClassToKeyStage(
                    (string) $class->name,
                    (string) optional($class->class_type)->code
                );
            })
            ->filter()
            ->unique()
            ->sortBy(function ($stage) {
                return (int) Str::after($stage, 'Key Stage ');
            })
            ->values();

        return $stages->isEmpty()
            ? collect(['Key Stage 1', 'Key Stage 2', 'Key Stage 3', 'Key Stage 4', 'Key Stage 5'])
            : $stages;
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
}
