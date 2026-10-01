<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Announcement;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('createdBy')->orderBy('created_at', 'desc')->paginate(15);
        
        return view('pages.super_admin.announcements.index', [
            'announcements' => $announcements
        ]);
    }

    public function create()
    {
        return view('pages.super_admin.announcements.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'content' => 'required|string',
            'is_active' => 'boolean',
            'publish_date' => 'nullable|date',
            'expire_date' => 'nullable|date|after:publish_date',
        ]);

        $validated['created_by'] = Auth::id();

        Announcement::create($validated);

        return redirect()->route('announcements.index')->with('flash_success', 'Announcement created successfully');
    }

    public function edit(Announcement $announcement)
    {
        return view('pages.super_admin.announcements.edit', [
            'announcement' => $announcement
        ]);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'content' => 'required|string',
            'is_active' => 'boolean',
            'publish_date' => 'nullable|date',
            'expire_date' => 'nullable|date|after:publish_date',
        ]);

        $announcement->update($validated);

        return redirect()->route('announcements.index')->with('flash_success', 'Announcement updated successfully');
    }

    public function show(Announcement $announcement)
    {
        return view('pages.super_admin.announcements.show', [
            'announcement' => $announcement
        ]);
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return back()->with('flash_success', 'Announcement deleted successfully');
    }

    public function toggleStatus(Announcement $announcement)
    {
        $announcement->update(['is_active' => !$announcement->is_active]);

        return back()->with('flash_success', 'Announcement status updated');
    }
}
