<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Http\Request;

class GuardianController extends Controller
{
    /**
     * Display a listing of all guardians
     */
    public function index()
    {
        $guardians = Guardian::paginate(15);
        return view('guardians.index', compact('guardians'));
    }

    /**
     * Show the form for creating a new guardian
     */
    public function create()
    {
        return view('guardians.create');
    }

    /**
     * Store a newly created guardian in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:50',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:guardians,email',
            'role' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'phone_prefix' => 'nullable|string|max:10',
            'postal_code' => 'nullable|string|max:20',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'keep_updated' => 'nullable|boolean',
            'status' => 'nullable|string|max:50',
        ]);

        Guardian::create($validated);

        return redirect()->route('guardians.index')
            ->with('success', 'تم إضافة الولي بنجاح');
    }

    /**
     * Display the specified guardian
     */
    public function show(Guardian $guardian)
    {
        $students = $guardian->students()->paginate(10);
        return view('guardians.show', compact('guardian', 'students'));
    }

    /**
     * Show the form for editing the specified guardian
     */
    public function edit(Guardian $guardian)
    {
        return view('guardians.edit', compact('guardian'));
    }

    /**
     * Update the specified guardian
     */
    public function update(Request $request, Guardian $guardian)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:50',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:guardians,email,' . $guardian->id,
            'role' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'phone_prefix' => 'nullable|string|max:10',
            'postal_code' => 'nullable|string|max:20',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'keep_updated' => 'nullable|boolean',
            'status' => 'nullable|string|max:50',
        ]);

        $guardian->update($validated);

        return redirect()->route('guardians.show', $guardian)
            ->with('success', 'تم تحديث بيانات الولي بنجاح');
    }

    /**
     * Remove the specified guardian
     */
    public function destroy(Guardian $guardian)
    {
        $guardian->delete();

        return redirect()->route('guardians.index')
            ->with('success', 'تم حذف الولي بنجاح');
    }

    /**
     * Filter guardians by status
     */
    public function filterByStatus($status)
    {
        $guardians = Guardian::byStatus($status)->paginate(15);
        return view('guardians.index', compact('guardians', 'status'));
    }

    /**
     * Search guardians
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $guardians = Guardian::where('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->paginate(15);

        return view('guardians.index', compact('guardians', 'query'));
    }
}
