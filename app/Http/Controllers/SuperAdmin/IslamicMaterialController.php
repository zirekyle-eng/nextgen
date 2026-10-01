<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\IslamicMaterial;
use App\Models\MyClass;
use App\User;
use Illuminate\Http\Request;

class IslamicMaterialController extends Controller
{
    /**
     * Display a listing of Islamic materials
     */
    public function index(Request $request)
    {
        $query = IslamicMaterial::with('class');

        // Search
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $materials = $query->orderBy('published_at', 'desc')->paginate(15);

        return view('super-admin.islamic-materials.index', compact('materials'));
    }

    /**
     * Show the form for creating a new material
     */
    public function create()
    {
        $classes = MyClass::all();
        $teachers = User::where('user_type', 'teacher')
            ->pluck('name', 'id')
            ->all();

        return view('super-admin.islamic-materials.create', compact(
            'classes',
            'teachers'
        ));
    }

    /**
     * Store a newly created material
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'content' => 'nullable|string',
            'my_class_id' => 'nullable|exists:my_classes,id',
            'teacher' => 'nullable|string|max:255',
            'time_table' => 'nullable|string',
            'image_path' => 'nullable|image|max:2048',
            'video_url' => 'nullable|url',
            'attachment_path' => 'nullable|file|max:5120',
            'is_active' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        // Handle image upload
        if ($request->hasFile('image_path')) {
            $imagePath = $request->file('image_path')->store('islamic-materials', 'public');
            $validated['image_path'] = $imagePath;
        }

        // Handle attachment upload
        if ($request->hasFile('attachment_path')) {
            $attachmentPath = $request->file('attachment_path')->store('islamic-materials/attachments', 'public');
            $validated['attachment_path'] = $attachmentPath;
        }

        // Default published_at to now if material is active
        if ($validated['is_active'] && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        IslamicMaterial::create($validated);

        return redirect()->route('islamic-materials.index')
                       ->with('success', 'Islamic material created successfully');
    }

    /**
     * Show the form for editing a material
     */
    public function edit(IslamicMaterial $islamicMaterial)
    {
        $classes = MyClass::all();
        $teachers = User::where('user_type', 'teacher')
            ->pluck('name', 'id')
            ->all();

        return view('super-admin.islamic-materials.edit', compact(
            'islamicMaterial',
            'classes',
            'teachers'
        ));
    }

    /**
     * Update the material
     */
    public function update(Request $request, IslamicMaterial $islamicMaterial)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'content' => 'nullable|string',
            'my_class_id' => 'nullable|exists:my_classes,id',
            'teacher' => 'nullable|string|max:255',
            'time_table' => 'nullable|string',
            'image_path' => 'nullable|image|max:2048',
            'video_url' => 'nullable|url',
            'attachment_path' => 'nullable|file|max:5120',
            'is_active' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        // Handle image upload
        if ($request->hasFile('image_path')) {
            if ($islamicMaterial->image_path && \Storage::disk('public')->exists($islamicMaterial->image_path)) {
                \Storage::disk('public')->delete($islamicMaterial->image_path);
            }
            $imagePath = $request->file('image_path')->store('islamic-materials', 'public');
            $validated['image_path'] = $imagePath;
        }

        // Handle attachment upload
        if ($request->hasFile('attachment_path')) {
            if ($islamicMaterial->attachment_path && \Storage::disk('public')->exists($islamicMaterial->attachment_path)) {
                \Storage::disk('public')->delete($islamicMaterial->attachment_path);
            }
            $attachmentPath = $request->file('attachment_path')->store('islamic-materials/attachments', 'public');
            $validated['attachment_path'] = $attachmentPath;
        }

        // Default published_at to now if material is becoming active
        if ($validated['is_active'] && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $islamicMaterial->update($validated);

        return redirect()->route('islamic-materials.index')
                       ->with('success', 'Islamic material updated successfully');
    }

    /**
     * Delete a material
     */
    public function destroy(IslamicMaterial $islamicMaterial)
    {
        // Delete associated files
        if ($islamicMaterial->image_path && \Storage::disk('public')->exists($islamicMaterial->image_path)) {
            \Storage::disk('public')->delete($islamicMaterial->image_path);
        }
        if ($islamicMaterial->attachment_path && \Storage::disk('public')->exists($islamicMaterial->attachment_path)) {
            \Storage::disk('public')->delete($islamicMaterial->attachment_path);
        }

        $islamicMaterial->delete();

        return redirect()->route('islamic-materials.index')
                       ->with('success', 'Islamic material deleted successfully');
    }

    /**
     * Toggle material status
     */
    public function toggleStatus(IslamicMaterial $islamicMaterial)
    {
        $islamicMaterial->update([
            'is_active' => !$islamicMaterial->is_active,
            'published_at' => !$islamicMaterial->is_active ? now() : $islamicMaterial->published_at
        ]);

        $status = $islamicMaterial->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Islamic material {$status} successfully");
    }
}
