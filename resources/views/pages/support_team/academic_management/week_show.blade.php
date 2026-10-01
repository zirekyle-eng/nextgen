@extends('layouts.master')

@section('page_title', 'Week Workspace - CMS')
@section('full_page', 'true')
@section('content')
<style>
    select.form-control:not([size]):not([multiple]) {
    height: 3.25003rem;
}
    .ww-shell {
        max-width: 1480px;
        margin: 0 auto;
    }

    .ww-card {
        background: #fff;
        border: 1px solid #dce4ee;
        border-radius: 8px;
        box-shadow: none;
    }

    .ww-hero {
        border-left: 4px solid #1f78b5;
    }

    .ww-muted {
        color: #5a6776;
        font-size: .84rem;
        line-height: 1.45;
    }

    .ww-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        gap: 12px;
    }

    .ww-tile {
        width: 100%;
        min-height: 132px;
        border: 1px solid #dce4ee;
        border-left: 4px solid #1f78b5;
        border-radius: 8px;
        background: #fff;
        padding: 14px;
        text-align: left;
        color: #243447;
        cursor: pointer;
    }

    .ww-tile:hover {
        border-color: #1f78b5;
        box-shadow: 0 8px 18px rgba(31, 120, 181, .12);
    }

    .ww-tile-title {
        display: block;
        font-size: .96rem;
        font-weight: 800;
        line-height: 1.25;
        word-break: break-word;
    }

    .ww-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 12px;
    }

    .ww-name-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 10px;
    }

    .ww-name-tile {
        width: 100%;
        min-height: 82px;
        border: 1px solid #dce4ee;
        border-radius: 8px;
        background: #f9fbfd;
        padding: 10px;
        text-align: left;
        cursor: pointer;
    }

    .ww-file-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 10px;
    }

    .ww-file-tile {
        width: 100%;
        min-height: 92px;
        border: 1px solid #dce4ee;
        border-radius: 8px;
        background: #fff;
        padding: 10px;
        text-align: left;
        cursor: pointer;
    }

    .ww-section-title {
        font-size: .88rem;
        font-weight: 800;
        color: #243447;
        margin-bottom: .7rem;
    }

    .ww-empty {
        border: 1px dashed #cddaea;
        border-radius: 8px;
        background: #f9fcff;
        padding: .85rem;
        color: #64748b;
    }

    .ww-blocked {
        border: 1px solid #f3c4c4;
        border-radius: 8px;
        background: #fff7f7;
        color: #8a1f1f;
        padding: .85rem;
    }

    .ww-shell .form-control {
        min-height: 38px;
        font-size: .9rem;
    }

    .ww-shell .btn {
        border-radius: 6px;
    }

    body {
        background:
            radial-gradient(circle 150px at 5% 10%, rgba(30, 64, 175, .12), transparent 60%),
            radial-gradient(circle 200px at 85% 5%, rgba(179, 157, 219, .15), transparent 70%),
            radial-gradient(circle 100px at 10% 70%, rgba(217, 39, 119, .1), transparent 50%),
            linear-gradient(135deg, #e8f0f8 0%, #eff4fb 40%, #f0f6fc 70%, #e8eff7 100%);
        color: #1a2332;
    }

    .ww-shell {
        max-width: 1220px;
        padding: 22px 0;
        font-family: "Inter", "Segoe UI", Arial, sans-serif;
    }

    .ww-shell * {
        letter-spacing: 0;
    }

    .ww-card,
    .modal-content {
        border-radius: 8px;
        border: 1px solid rgba(30, 64, 175, .1);
        background: rgba(255, 255, 255, .92);
        box-shadow: 0 8px 20px rgba(30, 64, 175, .08);
    }

    .ww-hero {
        background: linear-gradient(135deg, rgba(255, 255, 255, .95), rgba(245, 250, 255, .95));
        border-left: 4px solid #1e40af;
        box-shadow: 0 12px 30px rgba(30, 64, 175, .1);
    }

    .ww-section-title,
    .ww-tile-title {
        color: #1a2332;
        font-weight: 900;
    }

    .ww-tile,
    .ww-name-tile,
    .ww-file-tile {
        border-color: rgba(30, 64, 175, .12);
        background: rgba(255, 255, 255, .9);
    }

    .ww-tile {
        border-left-color: #1e40af;
    }

    .ww-tile:hover,
    .ww-name-tile:hover,
    .ww-file-tile:hover {
        border-color: rgba(217, 39, 119, .25);
        box-shadow: 0 8px 20px rgba(30, 64, 175, .12);
    }

    .ww-shell .btn-primary {
        background: #1e40af;
        border-color: #1e40af;
    }

    .ww-shell .btn-primary:hover {
        background: #1e3a8a;
        border-color: #1e3a8a;
    }

    .am-topbar {
        background: linear-gradient(135deg, rgba(255, 255, 255, .95), rgba(245, 250, 255, .95));
        border-bottom: 1px solid rgba(30, 64, 175, .1);
        box-shadow: 0 4px 12px rgba(30, 64, 175, .08);
        padding: 14px 20px;
        margin-bottom: 8px;
        border-radius: 8px;
    }

    .am-topbar-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .am-topbar-right {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .am-user-info {
        background: rgba(30, 64, 175, .08);
        border: 1px solid rgba(30, 64, 175, .12);
        border-radius: 6px;
        padding: 8px 12px;
        font-size: .85rem;
        color: #1a2332;
        font-weight: 600;
    }

    .am-user-icon {
        width: 32px;
        height: 32px;
        background: #1e40af;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 900;
        font-size: .9rem;
    }

    .ww-wide-modal {
        width: min(96vw, 1520px);
        max-width: 96vw;
        margin: 1.25rem auto;
    }

    .ww-wide-modal .modal-content {
        max-height: calc(100vh - 2.5rem);
        overflow: hidden;
    }

    .ww-wide-modal .modal-body {
        max-height: calc(100vh - 9rem);
        overflow-y: auto;
    }

    .ww-wide-modal .ww-card {
        position: sticky;
        top: 0;
    }

    @media (max-width: 991.98px) {
        .ww-wide-modal {
            width: calc(100vw - 16px);
            max-width: calc(100vw - 16px);
            margin: .5rem auto;
        }

        .ww-wide-modal .modal-content {
            max-height: calc(100vh - 1rem);
        }

        .ww-wide-modal .modal-body {
            max-height: calc(100vh - 7.5rem);
        }

        .ww-wide-modal .ww-card {
            position: static;
        }
    }
</style>

@php
    $backUrl = route('academic.management.weekly_content', [
        'session_id' => $week->academic_session_id,
        'subject_id' => $subject->id,
    ]);
    $lessonCount = $week->units->sum(function ($unit) { return $unit->lessons->count(); });
    $unitFileCount = $week->units->sum(function ($unit) { return $unit->generalFiles->count(); });
    $lessonFileCount = $week->units->sum(function ($unit) {
        return $unit->lessons->sum(function ($lesson) { return $lesson->files->count(); });
    });
@endphp

<!-- Top Bar -->
<div class="ww-shell" style="margin-top: 0; padding-top: 0;">
    <div class="am-topbar">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="am-topbar-left">
                <div class="am-user-icon">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</div>
                <div class="am-user-info">
                    {{ Auth::user()->name ?? 'User' }}
                </div>
            </div>
            <div class="am-topbar-right">
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-primary" title="Back to Dashboard">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="ww-shell">
    <div class="ww-card ww-hero mb-3">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h4 class="mb-1 font-weight-bold text-dark">Week {{ $week->week_number }}: {{ $week->title ?: 'Untitled week' }}</h4>
                <div class="ww-muted">
                    {{ $subject->name }} | {{ optional($class)->name }} |
                    {{ optional($week->start_date)->format('M d, Y') ?: 'No start date' }} - {{ optional($week->end_date)->format('M d, Y') ?: 'No end date' }}
                </div>
            </div>
            <div class="d-flex flex-wrap mt-2 mt-md-0">
                <a href="{{ $backUrl }}" class="btn btn-light btn-sm mr-2">Back To Weeks</a>
                <button type="button" class="btn btn-outline-secondary btn-sm mr-2" data-toggle="modal" data-target="#weekEditModal">Edit Week</button>
                @if(!$week->is_blocked)
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#unitCreateModal">Add Unit</button>
                @endif
            </div>
        </div>
    </div>

    @if($week->is_blocked)
        <div class="ww-blocked mb-3">
            <strong>Holiday blocked week.</strong>
            {{ $week->block_note ?: 'Activities cannot be added or updated in this week.' }}
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-3 mb-2">
            <div class="ww-card p-3">
                <div class="font-weight-bold h4 mb-0">{{ $week->units->count() }}</div>
                <div class="ww-muted">Units</div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="ww-card p-3">
                <div class="font-weight-bold h4 mb-0">{{ $lessonCount }}</div>
                <div class="ww-muted">Lessons</div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="ww-card p-3">
                <div class="font-weight-bold h4 mb-0">{{ $unitFileCount }}</div>
                <div class="ww-muted">Unit Files</div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="ww-card p-3">
                <div class="font-weight-bold h4 mb-0">{{ $lessonFileCount }}</div>
                <div class="ww-muted">Lesson Files</div>
            </div>
        </div>
    </div>

    <div class="ww-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                <div>
                    <div class="ww-section-title mb-1">Weekly Units</div>
                    <div class="ww-muted">Open a unit to manage its lessons, files, question banks and quizzes.</div>
                </div>
                @if(!$week->is_blocked)
                    <button type="button" class="btn btn-primary btn-sm mt-2 mt-md-0" data-toggle="modal" data-target="#unitCreateModal">Add Unit</button>
                @endif
            </div>

            @if($week->units->isEmpty())
                <div class="ww-empty">No units added yet.</div>
            @else
                <div class="ww-grid">
                    @foreach($week->units as $unit)
                        <button type="button" class="ww-tile" data-toggle="modal" data-target="#unitModal{{ $unit->id }}">
                            <span class="ww-tile-title">Unit {{ $unit->unit_number }}: {{ $unit->title }}</span>
                            <span class="ww-meta">
                                <span class="badge badge-info">{{ $unit->lessons->count() }} lessons</span>
                                <span class="badge badge-primary">{{ $unit->generalFiles->count() }} unit files</span>
                                <span class="badge badge-secondary">{{ $unit->quizzes->count() }} quizzes</span>
                            </span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="weekEditModal" tabindex="-1" role="dialog" aria-labelledby="weekEditModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('academic.management.weeks.update', $week->id) }}">
                @csrf
                @method('put')
                <div class="modal-header">
                    <h5 class="modal-title" id="weekEditModalLabel">Edit Week</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Week Number</label>
                            <input type="number" name="week_number" class="form-control" value="{{ $week->week_number }}" min="1" required>
                        </div>
                        <div class="form-group col-md-8">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $week->title }}" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ optional($week->start_date)->format('Y-m-d') }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ optional($week->end_date)->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="unitCreateModal" tabindex="-1" role="dialog" aria-labelledby="unitCreateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('academic.management.units.store') }}">
                @csrf
                <input type="hidden" name="subject_week_id" value="{{ $week->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="unitCreateModalLabel">Add Unit</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Unit Number</label>
                        <input type="number" name="unit_number" class="form-control" min="1" required>
                    </div>
                    <div class="form-group mb-0">
                        <label>Unit Name</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($week->units as $unit)
    <div class="modal fade" id="unitModal{{ $unit->id }}" tabindex="-1" role="dialog" aria-labelledby="unitModalLabel{{ $unit->id }}" aria-hidden="true">
        <div class="modal-dialog ww-wide-modal" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="unitModalLabel{{ $unit->id }}">Unit {{ $unit->unit_number }}: {{ $unit->title }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xl-3 col-lg-4 mb-3">
                            <div class="ww-card p-3 h-100">
                                <div class="ww-section-title">Unit Details</div>
                                <form method="post" action="{{ route('academic.management.units.update', $unit->id) }}">
                                    @csrf
                                    @method('put')
                                    <div class="form-group">
                                        <label>Unit Number</label>
                                        <input type="number" name="unit_number" class="form-control" value="{{ $unit->unit_number }}" min="1" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Unit Name</label>
                                        <input type="text" name="title" class="form-control" value="{{ $unit->title }}" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm btn-block">Save Unit</button>
                                </form>

                                <hr>

                                <button type="button" class="btn btn-outline-primary btn-sm btn-block" data-toggle="modal" data-target="#unitFileCreateModal{{ $unit->id }}">
                                    Add Unit File
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm btn-block" data-toggle="modal" data-target="#lessonCreateModal{{ $unit->id }}">
                                    Add Lesson
                                </button>
                                <form method="post" action="{{ route('academic.management.units.destroy', $unit->id) }}" class="mt-2" onsubmit="return confirm('Delete this unit with its lessons and files?')">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-outline-danger btn-sm btn-block">Delete Unit</button>
                                </form>
                            </div>
                        </div>

                        <div class="col-xl-9 col-lg-8">
                            <div class="ww-section-title">Unit Files</div>
                            @if($unit->generalFiles->isEmpty())
                                <div class="ww-empty mb-3">No unit files yet.</div>
                            @else
                                <div class="ww-file-grid mb-3">
                                    @foreach($unit->generalFiles as $file)
                                        <button type="button" class="ww-file-tile" data-toggle="modal" data-target="#unitFileEditModal{{ $file->id }}">
                                            <span class="ww-tile-title">{{ $file->title }}</span>
                                            <span class="ww-meta">
                                                <span class="badge badge-info">{{ $unitGeneralFileTypes[$file->general_type] ?? 'File' }}</span>
                                                <span class="badge badge-primary">{{ ($file->moodle_activity_type ?? 'resource') === 'assignment' ? 'Assignment' : 'File Resource' }}</span>
                                                <span class="badge badge-secondary">{{ $moodleAudienceOptions[$file->moodle_audience ?? 'both'] ?? 'Teachers + Students' }}</span>
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="ww-section-title mb-0">Lessons</div>
                                <button type="button" class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#lessonCreateModal{{ $unit->id }}">Add Lesson</button>
                            </div>
                            @if($unit->lessons->isEmpty())
                                <div class="ww-empty">No lessons yet.</div>
                            @else
                                <div class="ww-name-grid">
                                    @foreach($unit->lessons as $lesson)
                                        <button type="button" class="ww-name-tile" data-toggle="modal" data-target="#lessonModal{{ $lesson->id }}">
                                            <strong>Lesson {{ $lesson->lesson_number }}</strong>
                                            <div class="ww-muted">{{ $lesson->title }}</div>
                                            <div class="ww-meta">
                                                <span class="badge badge-primary">{{ $lesson->files->count() }} files</span>
                                                <span class="badge badge-warning">{{ $lesson->questionBanks->count() }} banks</span>
                                                <span class="badge badge-secondary">{{ $lesson->quizzes->count() }} quizzes</span>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="unitFileCreateModal{{ $unit->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="{{ route('academic.management.files.unit_general.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="week_unit_id" value="{{ $unit->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Unit File</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>File Type</label>
                            <select name="general_type" class="form-control" required>
                                @foreach($unitGeneralFileTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Moodle Type</label>
                            <select name="moodle_activity_type" class="form-control js-activity-type" required>
                                <option value="resource">File Resource</option>
                                <option value="assignment">Assignment</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Visible To</label>
                            <select name="moodle_audience" class="form-control" required>
                                <option value="both">Teachers + Students</option>
                                <option value="teachers">Teachers Only</option>
                            </select>
                        </div>
                        <div class="js-assignment-fields" style="display: none;">
                            <div class="form-group">
                                <label>Assignment Intro</label>
                                <textarea name="activity_intro" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Available From</label>
                                    <input type="datetime-local" name="available_from" class="form-control">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Due At</label>
                                    <input type="datetime-local" name="due_at" class="form-control">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Cutoff At</label>
                                    <input type="datetime-local" name="cutoff_at" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label>File</label>
                            <input type="file" name="file" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="lessonCreateModal{{ $unit->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="{{ route('academic.management.lessons.store') }}">
                    @csrf
                    <input type="hidden" name="week_unit_id" value="{{ $unit->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Lesson</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Lesson Number</label>
                            <input type="number" name="lesson_number" class="form-control" min="1" required>
                        </div>
                        <div class="form-group mb-0">
                            <label>Lesson Name</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Lesson</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach($unit->generalFiles as $file)
        <div class="modal fade" id="unitFileEditModal{{ $file->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post" action="{{ route('academic.management.files.unit_general.update', $file->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('put')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Unit File</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>File Type</label>
                                <select name="general_type" class="form-control" required>
                                    @foreach($unitGeneralFileTypes as $value => $label)
                                        <option value="{{ $value }}" {{ $file->general_type === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" value="{{ $file->title }}" required>
                            </div>
                            <div class="form-group">
                                <label>Moodle Type</label>
                                <select name="moodle_activity_type" class="form-control js-activity-type" required>
                                    <option value="resource" {{ ($file->moodle_activity_type ?? 'resource') === 'resource' ? 'selected' : '' }}>File Resource</option>
                                    <option value="assignment" {{ ($file->moodle_activity_type ?? 'resource') === 'assignment' ? 'selected' : '' }}>Assignment</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Visible To</label>
                                <select name="moodle_audience" class="form-control" required>
                                    <option value="both" {{ ($file->moodle_audience ?? 'both') === 'both' ? 'selected' : '' }}>Teachers + Students</option>
                                    <option value="teachers" {{ ($file->moodle_audience ?? 'both') === 'teachers' ? 'selected' : '' }}>Teachers Only</option>
                                </select>
                            </div>
                            <div class="js-assignment-fields" style="display: none;">
                                <div class="form-group">
                                    <label>Assignment Intro</label>
                                    <textarea name="activity_intro" class="form-control" rows="3">{{ $file->activity_intro }}</textarea>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Available From</label>
                                        <input type="datetime-local" name="available_from" class="form-control" value="{{ optional($file->available_from)->format('Y-m-d\TH:i') }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Due At</label>
                                        <input type="datetime-local" name="due_at" class="form-control" value="{{ optional($file->due_at)->format('Y-m-d\TH:i') }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Cutoff At</label>
                                        <input type="datetime-local" name="cutoff_at" class="form-control" value="{{ optional($file->cutoff_at)->format('Y-m-d\TH:i') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Replace File</label>
                                <input type="file" name="file" class="form-control">
                            </div>
                            <div class="d-flex flex-wrap">
                                <a href="{{ asset('storage/'.$file->file_path) }}" target="_blank" class="btn btn-outline-info btn-sm mr-2 mb-2">Open File</a>
                                @if($file->moodle_view_url)
                                    <a href="{{ $file->moodle_view_url }}" target="_blank" class="btn btn-outline-primary btn-sm mb-2">Open In Moodle</a>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                    <form method="post" action="{{ route('academic.management.files.unit_general.destroy', $file->id) }}" onsubmit="return confirm('Delete this unit file?')">
                        @csrf
                        @method('delete')
                        <div class="px-3 pb-3">
                            <button type="submit" class="btn btn-outline-danger btn-block">Delete File</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    @foreach($unit->lessons as $lesson)
        <div class="modal fade" id="lessonModal{{ $lesson->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog ww-wide-modal" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Lesson {{ $lesson->lesson_number }}: {{ $lesson->title }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-4 mb-3">
                                <div class="ww-card p-3 h-100">
                                    <div class="ww-section-title">Lesson Details</div>
                                    <form method="post" action="{{ route('academic.management.lessons.update', $lesson->id) }}">
                                        @csrf
                                        @method('put')
                                        <div class="form-group">
                                            <label>Lesson Number</label>
                                            <input type="number" name="lesson_number" class="form-control" value="{{ $lesson->lesson_number }}" min="1" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Lesson Name</label>
                                            <input type="text" name="title" class="form-control" value="{{ $lesson->title }}" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm btn-block">Save Lesson</button>
                                    </form>

                                    <hr>

                                    <button type="button" class="btn btn-outline-primary btn-sm btn-block" data-toggle="modal" data-target="#lessonFileCreateModal{{ $lesson->id }}">Add Lesson File</button>
                                    <button type="button" class="btn btn-outline-warning btn-sm btn-block" data-toggle="modal" data-target="#questionBankCreateModal{{ $lesson->id }}">Add Question Bank</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block" data-toggle="modal" data-target="#lessonQuizCreateModal{{ $lesson->id }}">Add Quiz</button>
                                    <form method="post" action="{{ route('academic.management.lessons.destroy', $lesson->id) }}" class="mt-2" onsubmit="return confirm('Delete this lesson with its files and quizzes?')">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-outline-danger btn-sm btn-block">Delete Lesson</button>
                                    </form>
                                </div>
                            </div>

                            <div class="col-xl-9 col-lg-8">
                                <div class="ww-section-title">Lesson Files</div>
                                @if($lesson->files->isEmpty())
                                    <div class="ww-empty mb-3">No lesson files yet.</div>
                                @else
                                    <div class="ww-file-grid mb-3">
                                        @foreach($lesson->files as $file)
                                            <button type="button" class="ww-file-tile" data-toggle="modal" data-target="#lessonFileEditModal{{ $file->id }}">
                                                <span class="ww-tile-title">{{ $file->title }}</span>
                                            <span class="ww-meta">
                                                <span class="badge badge-info">{{ $lessonFileTypes[$file->lesson_type] ?? 'File' }}</span>
                                                <span class="badge badge-primary">{{ ($file->moodle_activity_type ?? 'resource') === 'assignment' ? 'Assignment' : 'File Resource' }}</span>
                                                <span class="badge badge-secondary">{{ $moodleAudienceOptions[$file->moodle_audience ?? 'both'] ?? 'Teachers + Students' }}</span>
                                            </span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="ww-section-title">Question Banks</div>
                                @if($lesson->questionBanks->isEmpty())
                                    <div class="ww-empty mb-3">No question banks yet.</div>
                                @else
                                    <div class="ww-file-grid mb-3">
                                        @foreach($lesson->questionBanks as $bank)
                                            <div class="ww-file-tile">
                                                <span class="ww-tile-title">{{ $bank->title }}</span>
                                                <span class="ww-meta">
                                                    <span class="badge badge-warning">{{ $bank->question_count ?: 0 }} questions</span>
                                                    <span class="badge badge-secondary">{{ ucfirst($bank->moodle_sync_status ?? 'pending') }}</span>
                                                </span>
                                                <form method="post" action="{{ route('academic.management.question_banks.lesson.destroy', $bank->id) }}" class="mt-2" onsubmit="return confirm('Delete this question bank?')">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm btn-block">Delete</button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="ww-section-title">Quizzes</div>
                                @if($lesson->quizzes->isEmpty())
                                    <div class="ww-empty">No quizzes yet.</div>
                                @else
                                    <div class="ww-file-grid">
                                        @foreach($lesson->quizzes as $quiz)
                                            <div class="ww-file-tile">
                                                <span class="ww-tile-title">{{ $quiz->title }}</span>
                                                <span class="ww-meta">
                                                    <span class="badge badge-secondary">{{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes.' min' : 'No limit' }}</span>
                                                    <span class="badge badge-info">{{ $moodleAudienceOptions[$quiz->moodle_audience ?? 'both'] ?? 'Teachers + Students' }}</span>
                                                </span>
                                                <form method="post" action="{{ route('academic.management.quizzes.destroy', $quiz->id) }}" class="mt-2" onsubmit="return confirm('Delete this quiz?')">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm btn-block">Delete</button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="lessonFileCreateModal{{ $lesson->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post" action="{{ route('academic.management.files.lesson.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="unit_lesson_id" value="{{ $lesson->id }}">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Lesson File</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>File Type</label>
                                <select name="lesson_type" class="form-control" required>
                                    @foreach($lessonFileTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Moodle Type</label>
                                <select name="moodle_activity_type" class="form-control js-activity-type" required>
                                    <option value="resource">File Resource</option>
                                    <option value="assignment">Assignment</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Visible To</label>
                                <select name="moodle_audience" class="form-control" required>
                                    <option value="both">Teachers + Students</option>
                                    <option value="teachers">Teachers Only</option>
                                </select>
                            </div>
                            <div class="js-assignment-fields" style="display: none;">
                                <div class="form-group">
                                    <label>Assignment Intro</label>
                                    <textarea name="activity_intro" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Available From</label>
                                        <input type="datetime-local" name="available_from" class="form-control">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Due At</label>
                                        <input type="datetime-local" name="due_at" class="form-control">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Cutoff At</label>
                                        <input type="datetime-local" name="cutoff_at" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <label>File</label>
                                <input type="file" name="file" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="questionBankCreateModal{{ $lesson->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post" action="{{ route('academic.management.question_banks.lesson.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="unit_lesson_id" value="{{ $lesson->id }}">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Question Bank</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Upload Type</label>
                                <select name="upload_type" class="form-control" required>
                                    <option value="questions">Questions File (CSV/TXT/PDF)</option>
                                    <option value="xml">Moodle XML</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Questions File</label>
                                <input type="file" name="questions_file" class="form-control">
                            </div>
                            <div class="form-group mb-0">
                                <label>Moodle XML File</label>
                                <input type="file" name="xml_file" class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="lessonQuizCreateModal{{ $lesson->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post" action="{{ route('academic.management.quizzes.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                        <input type="hidden" name="scope" value="lesson">
                        <input type="hidden" name="unit_lesson_id" value="{{ $lesson->id }}">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Quiz</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Time Limit (minutes)</label>
                                    <input type="number" name="time_limit_minutes" class="form-control" min="1" max="300">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Visible To</label>
                                    <select name="moodle_audience" class="form-control" required>
                                        <option value="both">Teachers + Students</option>
                                        <option value="teachers">Teachers Only</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Upload Type</label>
                                <select name="upload_type" class="form-control" required>
                                    <option value="questions">Questions File (CSV/TXT/PDF)</option>
                                    <option value="xml">Moodle XML</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Questions File</label>
                                <input type="file" name="questions_file" class="form-control">
                            </div>
                            <div class="form-group mb-0">
                                <label>Moodle XML File</label>
                                <input type="file" name="xml_file" class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Upload Quiz</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @foreach($lesson->files as $file)
            <div class="modal fade" id="lessonFileEditModal{{ $file->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form method="post" action="{{ route('academic.management.files.lesson.update', $file->id) }}" enctype="multipart/form-data">
                            @csrf
                            @method('put')
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Lesson File</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>File Type</label>
                                    <select name="lesson_type" class="form-control" required>
                                        @foreach($lessonFileTypes as $value => $label)
                                            <option value="{{ $value }}" {{ $file->lesson_type === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="title" class="form-control" value="{{ $file->title }}" required>
                                </div>
                                <div class="form-group">
                                    <label>Moodle Type</label>
                                    <select name="moodle_activity_type" class="form-control js-activity-type" required>
                                        <option value="resource" {{ ($file->moodle_activity_type ?? 'resource') === 'resource' ? 'selected' : '' }}>File Resource</option>
                                        <option value="assignment" {{ ($file->moodle_activity_type ?? 'resource') === 'assignment' ? 'selected' : '' }}>Assignment</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Visible To</label>
                                    <select name="moodle_audience" class="form-control" required>
                                        <option value="both" {{ ($file->moodle_audience ?? 'both') === 'both' ? 'selected' : '' }}>Teachers + Students</option>
                                        <option value="teachers" {{ ($file->moodle_audience ?? 'both') === 'teachers' ? 'selected' : '' }}>Teachers Only</option>
                                    </select>
                                </div>
                                <div class="js-assignment-fields" style="display: none;">
                                    <div class="form-group">
                                        <label>Assignment Intro</label>
                                        <textarea name="activity_intro" class="form-control" rows="3">{{ $file->activity_intro }}</textarea>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>Available From</label>
                                            <input type="datetime-local" name="available_from" class="form-control" value="{{ optional($file->available_from)->format('Y-m-d\TH:i') }}">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Due At</label>
                                            <input type="datetime-local" name="due_at" class="form-control" value="{{ optional($file->due_at)->format('Y-m-d\TH:i') }}">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Cutoff At</label>
                                            <input type="datetime-local" name="cutoff_at" class="form-control" value="{{ optional($file->cutoff_at)->format('Y-m-d\TH:i') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Replace File</label>
                                    <input type="file" name="file" class="form-control">
                                </div>
                                <div class="d-flex flex-wrap">
                                    <a href="{{ asset('storage/'.$file->file_path) }}" target="_blank" class="btn btn-outline-info btn-sm mr-2 mb-2">Open File</a>
                                    @if($file->moodle_view_url)
                                        <a href="{{ $file->moodle_view_url }}" target="_blank" class="btn btn-outline-primary btn-sm mb-2">Open In Moodle</a>
                                    @endif
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                        <form method="post" action="{{ route('academic.management.files.lesson.destroy', $file->id) }}" onsubmit="return confirm('Delete this lesson file?')">
                            @csrf
                            @method('delete')
                            <div class="px-3 pb-3">
                                <button type="submit" class="btn btn-outline-danger btn-block">Delete File</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach
@endforeach
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function syncAssignmentFields(select) {
            var form = select.closest('form');
            if (!form) {
                return;
            }

            var fields = form.querySelector('.js-assignment-fields');
            if (!fields) {
                return;
            }

            fields.style.display = select.value === 'assignment' ? 'block' : 'none';
        }

        document.querySelectorAll('.js-activity-type').forEach(function (select) {
            syncAssignmentFields(select);
            select.addEventListener('change', function () {
                syncAssignmentFields(select);
            });
        });
    });
</script>
@endsection
