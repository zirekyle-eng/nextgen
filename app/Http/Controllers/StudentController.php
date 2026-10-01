<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\MyClass;
use App\Models\Section;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of all students
     */
    public function index()
    {
        $students = Student::with('guardian')->paginate(15);
        return view('students.index', compact('students'));
    }

    /**
     * Show the form for creating a new student
     */
    public function create()
    {
        $guardians = Guardian::orderBy('first_name')->get();
        return view('students.create', compact('guardians'));
    }

    /**
     * Store a newly created student in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'guardian_id' => 'required|exists:guardians,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dob' => 'nullable|date',
            'country' => 'nullable|string|max:100',
            'stage' => 'nullable|string|max:100',
            'preferred_start_date' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:50',
            'religion_status' => 'nullable|string|in:Muslim,Non-Muslim',
        ]);

        Student::create($validated);

        return redirect()->route('students.index')
            ->with('success', 'تم إضافة الطالب بنجاح');
    }

    /**
     * Display the specified student
     */
    public function show(Student $student)
    {
        $classes = MyClass::all();
        return view('students.show', compact('student', 'classes'));
    }

    /**
     * Show the form for editing the specified student
     */
    public function edit(Student $student)
    {
        $guardians = Guardian::orderBy('first_name')->get();
        return view('students.edit', compact('student', 'guardians'));
    }

    /**
     * Update the specified student
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'guardian_id' => 'required|exists:guardians,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'gender' => 'nullable|string',
            'address' => 'nullable|string',
            'dob' => 'nullable|date',
            'country' => 'nullable|string|max:100',
            'stage' => 'nullable|string|max:100',
            'preferred_start_date' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:50',
            'religion_status' => 'nullable|string|in:Muslim,Non-Muslim',
        ]);

        $student->update($validated);

        return redirect()->route('students.show', $student)
            ->with('success', 'تم تحديث بيانات الطالب بنجاح');
    }

    /**
     * Remove the specified student
     */
    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'تم حذف الطالب بنجاح');
    }

    /**
     * Get students by guardian
     */
    public function byGuardian(Guardian $guardian)
    {
        $students = $guardian->students()->paginate(15);
        return view('students.by-guardian', compact('guardian', 'students'));
    }

    /**
     * Filter students by status
     */
    public function filterByStatus($status)
    {
        $students = Student::byStatus($status)->with('guardian')->paginate(15);
        return view('students.index', compact('students', 'status'));
    }

    /**
     * Search students
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $students = Student::where('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->with('guardian')
            ->paginate(15);

        return view('students.index', compact('students', 'query'));
    }

    /**
     * Change student status
     */
    public function changeStatus(Request $request, Student $student)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:Pending,Approved,Rejected',
        ]);

        $student->update(['status' => $validated['status']]);

        return redirect()->route('candidates.show', $student)
            ->with('success', "تم تحديث الحالة إلى {$validated['status']}");
    }

    /**
     * Approve student with class and section
     */
    public function approveWithClass(Request $request, Student $student)
    {
        $validated = $request->validate([
            'my_class_id' => 'required|exists:my_classes,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        // Store class and section info in session BEFORE updating status
        session([
            'approval_class_id' => $validated['my_class_id'],
            'approval_section_id' => $validated['section_id'],
        ]);

        // Update student status (this triggers the observer)
        $student->update(['status' => 'Approved']);

        return redirect()->route('candidates.show', $student)
            ->with('success', 'تم الموافقة على الطالب وإضافته للفصل بنجاح!');
    }

    /**
     * Get sections for a specific class (API endpoint)
     */
    public function getSections($classId)
    {
        $sections = Section::where('my_class_id', $classId)
            ->where('active', 1)
            ->select('id', 'name')
            ->get();

        return response()->json(['sections' => $sections]);
    }
}

