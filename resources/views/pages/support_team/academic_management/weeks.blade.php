@extends('layouts.master')

@section('page_title', 'Add Material')
@section('full_page', 'true')

@section('content')
<style>
    .am-shell {
        max-width: 1680px;
        margin: 0 auto;
    }

    .am-shell .card {
        border-radius: 10px;
        border: 1px solid #dce3ec;
        box-shadow: none;
        overflow: hidden;
    }

    .am-hero {
        background: #ffffff;
        border-left: 4px solid #1f78b5;
    }

    .am-subtle {
        color: #5a6776;
        font-size: .84rem;
        line-height: 1.45;
    }

    .am-section-title {
        font-size: .87rem;
        color: #243447;
        margin-bottom: .75rem;
        font-weight: 700;
    }

    .am-subject-head {
        background: #f9fbfd;
        border: 1px solid #dce4ee;
        border-radius: 8px;
        padding: .85rem 1rem;
    }

    .am-week-card {
        border-left: 3px solid #1f78b5;
    }

    .am-week-card .card-header {
        background: #f8fafc;
    }

    .am-unit-card {
        border: 1px dashed #cbd8e5;
        border-radius: 8px;
        padding: .85rem;
        background: #ffffff;
    }

    .am-lesson-card {
        border: 1px solid #e2e9f2;
        border-radius: 8px;
        padding: .6rem;
        background: #ffffff;
    }

    .am-empty {
        border: 1px dashed #cddaea;
        border-radius: 8px;
        background: #f9fcff;
        padding: .8rem;
        color: #64748b;
    }

    select.form-control:not([size]):not([multiple]) {
    height: 3.25003rem;
}
    .am-shell .form-control {
        min-height: 44px;
        font-size: .9rem;
        line-height: 1.35;
    }

    .am-shell .form-control-sm {
        min-height: 38px;
        font-size: .82rem;
        line-height: 1.35;
    }

    .am-shell select.form-control,
    .am-shell select.form-control-sm {
        height: auto;
        padding-top: .62rem;
        padding-bottom: .62rem;
        line-height: 1.35;
        color: #102a43 !important;
        -webkit-text-fill-color: #102a43;
        background-position: right .75rem center;
    }

    .am-shell select.form-control option,
    .am-shell select.form-control-sm option {
        color: #102a43;
    }

    .am-shell .btn {
        border-radius: 6px;
    }

    .am-file-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 12px;
    }

    .am-file-tile {
        width: 100%;
        min-height: 122px;
        border: 1px solid #dce4ee;
        border-radius: 8px;
        background: #ffffff;
        padding: 14px;
        text-align: left;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        cursor: pointer;
        color: #243447;
    }

    .am-file-tile:hover {
        border-color: #1f78b5;
        box-shadow: 0 8px 18px rgba(31, 120, 181, .12);
    }

    .am-file-title {
        font-size: .95rem;
        font-weight: 700;
        line-height: 1.25;
        word-break: break-word;
    }

    .am-file-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 12px;
    }

    .am-week-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        gap: 12px;
    }

    .am-week-tile {
        width: 100%;
        min-height: 112px;
        border: 1px solid #dce4ee;
        border-left: 4px solid #1f78b5;
        border-radius: 8px;
        background: #fff;
        padding: 14px;
        text-align: left;
        color: #243447;
        cursor: pointer;
    }

    .am-week-tile:hover {
        border-color: #1f78b5;
        box-shadow: 0 8px 18px rgba(31, 120, 181, .12);
    }

    .am-name-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 10px;
    }

    .am-name-tile {
        border: 1px solid #dce4ee;
        border-radius: 8px;
        background: #f9fbfd;
        padding: 10px;
    }

    body {
        background:
            radial-gradient(circle 150px at 5% 10%, rgba(30, 64, 175, .12), transparent 60%),
            radial-gradient(circle 200px at 85% 5%, rgba(179, 157, 219, .15), transparent 70%),
            radial-gradient(circle 100px at 10% 70%, rgba(217, 39, 119, .1), transparent 50%),
            linear-gradient(135deg, #e8f0f8 0%, #eff4fb 40%, #f0f6fc 70%, #e8eff7 100%);
        color: #1a2332;
    }

    .am-shell {
        max-width: 1220px;
        padding: 0px 0;
        font-family: "Inter", "Segoe UI", Arial, sans-serif;
    }

    .am-shell * {
        letter-spacing: 0;
    }

    .am-shell .card {
        border-radius: 8px;
        border: 1px solid rgba(30, 64, 175, .1);
        background: rgba(255, 255, 255, .9);
        box-shadow: 0 8px 20px rgba(30, 64, 175, .08);
    }

    .am-hero {
        background: linear-gradient(135deg, rgba(255, 255, 255, .95), rgba(245, 250, 255, .95));
        border-left: 4px solid #1e40af;
        box-shadow: 0 12px 30px rgba(30, 64, 175, .1);
    }

    .am-section-title,
    .am-file-title {
        color: #1a2332;
        font-weight: 900;
    }

    .am-subject-head {
        background: rgba(255, 255, 255, .72);
        border: 1px solid rgba(30, 64, 175, .12);
        box-shadow: 0 8px 20px rgba(30, 64, 175, .06);
    }

    .am-file-tile,
    .am-week-tile,
    .am-name-tile {
        border-color: rgba(30, 64, 175, .12);
        background: rgba(255, 255, 255, .9);
    }

    .am-week-tile {
        border-left-color: #1e40af;
    }

    .am-file-tile:hover,
    .am-week-tile:hover {
        border-color: rgba(217, 39, 119, .25);
        box-shadow: 0 8px 20px rgba(30, 64, 175, .12);
    }

    .am-shell .btn-primary {
        background: #1e40af;
        border-color: #1e40af;
    }

    .am-shell .btn-primary:hover {
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
</style>

<!-- Top Bar -->
<div class="am-shell" style="margin-top: 20px; padding-top: 0;">
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

<div class="am-shell">
    <div class="row">
        <div class="col-12">
            <div class="card am-hero mb-3">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h4 class="mb-1 font-weight-bold text-dark">{{ ($workspaceMode ?? 'materials') === 'weekly' ? 'Units / Lessons / Weekly Content' : 'Add Materials' }}</h4>
                        <div class="am-subtle">
                            {{ ($workspaceMode ?? 'materials') === 'weekly'
                                ? 'Choose the term and subject, then organize weeks, units, lessons, files, question banks, and quizzes.'
                                : 'Choose the term and subject, then manage Learner Book, Workbook and Teacher Guide.' }}
                        </div>
                    </div>
                    <a href="{{ route('academic.management.index', ['session_id' => $selectedSession->id ?? null, 'subject_id' => $selectedSubject->id ?? null]) }}" class="btn btn-light btn-sm mt-2 mt-md-0">
                        Back To Academic Calendar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <form method="get" action="{{ ($workspaceMode ?? 'materials') === 'weekly' ? route('academic.management.weekly_content') : route('academic.management.weeks.manage') }}">
                        <div class="form-row">
                            <div class="col-md-6 mb-2">
                                <label class="mb-1">Term</label>
                                <select name="session_id" class="form-control" required onchange="this.form.submit()">
                                    @foreach($sessions as $session)
                                        <option value="{{ $session->id }}" {{ $selectedSession && $selectedSession->id === $session->id ? 'selected' : '' }}>
                                            {{ $session->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="mb-1">Subject</label>
                                <select name="subject_id" class="form-control" required>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ $selectedSubject && $selectedSubject->id === $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }} ({{ optional($subject->my_class)->name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Load Subject</button>
                    </form>
                </div>
            </div>

            @if(!$selectedSubject)
                <div class="am-empty">Select a session and subject to manage weeks.</div>
            @else
                <div class="am-subject-head mb-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <div class="font-weight-bold">{{ $selectedSubject->name }}</div>
                            <div class="am-subtle">Class: {{ optional($selectedSubject->my_class)->name }}</div>
                        </div>
                        <div class="d-flex flex-wrap">
                            <a class="btn btn-sm btn-outline-info mt-2 mt-md-0 mr-md-2" href="{{ route('academic.management.finder', ['session_id' => $selectedSession->id ?? null, 'subject_id' => $selectedSubject->id]) }}">
                                Open File Finder
                            </a>
                            <a class="btn btn-sm btn-outline-secondary mt-2 mt-md-0" href="{{ route('academic.management.quizzes', ['session_id' => $selectedSession->id ?? null, 'subject_id' => $selectedSubject->id]) }}">
                                Manage Quizzes
                            </a>
                        </div>
                    </div>
                </div>
                @if(($workspaceMode ?? 'materials') === 'materials')
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                            <div>
                                <div class="am-section-title mb-1">General Files For {{ optional($selectedSession)->name }}</div>
                                <div class="am-subtle">Upload Learner Book, Workbook and Teacher Guide for this subject. Files sync to the matching term section in Moodle.</div>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm mt-2 mt-md-0" data-toggle="modal" data-target="#subjectGeneralFileModal">
                                Add General File
                            </button>
                        </div>

                        @if($selectedSubject->generalFiles->isEmpty())
                            <div class="am-empty">No subject level materials uploaded yet.</div>
                        @else
                            <div class="am-file-grid">
                                @foreach($selectedSubject->generalFiles as $file)
                                    <button type="button" class="am-file-tile" data-toggle="modal" data-target="#subjectGeneralFileEditModal{{ $file->id }}">
                                        <span class="am-file-title">{{ $file->title }}</span>
                                        <span class="am-file-meta">
                                            <span class="badge badge-info">{{ $subjectGeneralFileTypes[$file->general_type ?? 'assessment_general'] ?? 'Unknown' }}</span>
                                            <span class="badge badge-primary">File Resource</span>
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                @foreach($selectedSubject->generalFiles as $file)
                    <div class="modal fade" id="subjectGeneralFileEditModal{{ $file->id }}" tabindex="-1" role="dialog" aria-labelledby="subjectGeneralFileEditModalLabel{{ $file->id }}" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <form method="post" action="{{ route('academic.management.files.subject_general.update', $file->id) }}" enctype="multipart/form-data">
                                    @csrf
                                    @method('put')
                                    <input type="hidden" name="moodle_activity_type" value="resource">

                                    <div class="modal-header">
                                        <h5 class="modal-title" id="subjectGeneralFileEditModalLabel{{ $file->id }}">Edit General File</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label class="mb-1">Material Type</label>
                                            <select name="general_type" class="form-control" required>
                                                @foreach($subjectGeneralFileTypes as $typeValue => $typeLabel)
                                                    <option value="{{ $typeValue }}" {{ ($file->general_type ?? 'assessment_general') === $typeValue ? 'selected' : '' }}>
                                                        {{ $typeLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="mb-1">Title</label>
                                            <input type="text" name="title" class="form-control" value="{{ $file->title }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="mb-1">Visible To</label>
                                            <select name="moodle_audience" class="form-control" required>
                                                <option value="both" {{ ($file->moodle_audience ?? 'both') === 'both' ? 'selected' : '' }}>Teachers + Students</option>
                                                <option value="teachers" {{ ($file->moodle_audience ?? 'both') === 'teachers' ? 'selected' : '' }}>Teachers Only</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="mb-1">Replace File</label>
                                            <input type="file" name="file" class="form-control">
                                        </div>
                                        <div class="mt-3 d-flex flex-wrap">
                                            <a href="{{ asset('storage/'.$file->file_path) }}" target="_blank" class="btn btn-outline-info btn-sm mr-2 mb-2">Open File</a>
                                            @if($file->moodle_view_url)
                                                <a href="{{ $file->moodle_view_url }}" target="_blank" class="btn btn-outline-primary btn-sm mb-2">Open In Moodle</a>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="modal-footer d-flex justify-content-between">
                                        <button class="btn btn-primary" type="submit">Save Changes</button>
                                    </div>
                                </form>
                                <form method="post" action="{{ route('academic.management.files.subject_general.destroy', $file->id) }}" onsubmit="return confirm('Delete this material from system and Moodle?')">
                                    @csrf
                                    @method('delete')
                                    <div class="px-3 pb-3">
                                        <button class="btn btn-outline-danger btn-block" type="submit">Delete Material</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="modal fade" id="subjectGeneralFileModal" tabindex="-1" role="dialog" aria-labelledby="subjectGeneralFileModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form method="post" action="{{ route('academic.management.files.subject_general.store') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="academic_session_id" value="{{ $selectedSession->id ?? '' }}">
                                <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
                                <input type="hidden" name="moodle_activity_type" value="resource">

                                <div class="modal-header">
                                    <h5 class="modal-title" id="subjectGeneralFileModalLabel">Add General File</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label class="mb-1">Material Type</label>
                                        <select name="general_type" class="form-control" required>
                                            @foreach($subjectGeneralFileTypes as $typeValue => $typeLabel)
                                                <option value="{{ $typeValue }}" {{ old('general_type', 'student_book') === $typeValue ? 'selected' : '' }}>
                                                    {{ $typeLabel }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="mb-1">Title</label>
                                        <input type="text" name="title" class="form-control" placeholder="Example: Year 3 Learner Book" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="mb-1">Visible To</label>
                                        <select name="moodle_audience" class="form-control" required>
                                            <option value="both" {{ old('moodle_audience', 'both') === 'both' ? 'selected' : '' }}>Teachers + Students</option>
                                            <option value="teachers" {{ old('moodle_audience', 'both') === 'teachers' ? 'selected' : '' }}>Teachers Only</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="mb-1">File</label>
                                        <input type="file" name="file" class="form-control" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                    <button class="btn btn-primary" type="submit">Upload</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                @if(($workspaceMode ?? 'materials') === 'weekly')
                    @if($selectedSubject->weeks->isEmpty())
                        <div class="am-empty">No weeks added yet.</div>
                    @else
                        <div class="am-week-grid mb-3">
                            @foreach($selectedSubject->weeks as $week)
                                <a href="{{ route('academic.management.weeks.show', $week->id) }}" class="am-week-tile" style="text-decoration: none; color: inherit; display: block;">
                                    <div class="font-weight-bold">Week {{ $week->week_number }}</div>
                                    <div class="am-subtle mt-1">{{ $week->title ?: 'Untitled week' }}</div>
                                    <div class="am-file-meta">
                                        <span class="badge badge-info">{{ $week->units->count() }} units</span>
                                        @if($week->is_blocked)
                                            <span class="badge badge-danger">Holiday Block</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>

                    @endif
                @endif

                @if(false)
                @forelse($selectedSubject->weeks as $week)
                    <div class="card am-week-card mb-3" id="week-{{ $week->id }}">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 font-weight-bold">Week {{ $week->week_number }}{{ $week->title ? ' - '.$week->title : '' }}</h6>
                            </div>
                            <div class="d-flex">
                                <button
                                    class="btn btn-sm btn-outline-primary"
                                    type="button"
                                    data-toggle="collapse"
                                    data-target="#weekCollapse{{ $week->id }}"
                                    aria-expanded="false"
                                    aria-controls="weekCollapse{{ $week->id }}"
                                >
                                    Open / Close
                                </button>
                                <form method="post" action="{{ route('academic.management.weeks.destroy', $week->id) }}" class="ml-2" onsubmit="return confirm('Delete this full week (units + files) from system and Moodle?')">
                                    @csrf
                                    @method('delete')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete Week</button>
                                </form>
                            </div>
                        </div>
                        <div id="weekCollapse{{ $week->id }}" class="collapse">
                            <div class="card-body">
                                <form method="post" action="{{ route('academic.management.weeks.update', $week->id) }}" class="mb-3">
                                    @csrf
                                    @method('put')
                                    <div class="form-row">
                                        <div class="col-md-2 mb-2"><input type="number" min="1" name="week_number" value="{{ $week->week_number }}" class="form-control form-control-sm" required></div>
                                        <div class="col-md-4 mb-2"><input type="text" name="title" value="{{ $week->title }}" class="form-control form-control-sm" placeholder="Week title"></div>
                                        <div class="col-md-2 mb-2"><button class="btn btn-outline-secondary btn-sm btn-block" type="submit">Update Week</button></div>
                                    </div>
                                </form>

                                <div class="am-lesson-card mb-3">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                                        <div class="font-weight-semibold">Moodle Order</div>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary js-load-moodle"
                                            data-week-id="{{ $week->id }}"
                                            data-url="{{ route('academic.management.weeks.moodle', $week->id) }}"
                                            data-sync-url="{{ route('academic.management.weeks.moodle.reorder', $week->id) }}"
                                        >
                                            Load From Moodle
                                        </button>
                                    </div>
                                    <div class="am-subtle mt-2">Reorder the list, then sync to update Moodle section order.</div>
                                    <div class="mt-2" id="moodle-list-{{ $week->id }}"></div>
                                    <div class="d-flex align-items-center mt-2">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-success js-sync-moodle"
                                            data-week-id="{{ $week->id }}"
                                            data-sync-url="{{ route('academic.management.weeks.moodle.reorder', $week->id) }}"
                                            disabled
                                        >
                                            Sync Order To Moodle
                                        </button>
                                        <span class="am-subtle ml-2" id="moodle-status-{{ $week->id }}"></span>
                                    </div>
                                </div>

                                <form method="post" action="{{ route('academic.management.units.store') }}" class="mb-3">
                                    @csrf
                                    <input type="hidden" name="subject_week_id" value="{{ $week->id }}">
                                    <div class="form-row">
                                        <div class="col-md-2 mb-2"><input type="number" min="1" name="unit_number" class="form-control" placeholder="Unit #" required></div>
                                        <div class="col-md-7 mb-2"><input type="text" name="title" class="form-control" placeholder="Unit title" required></div>
                                        <div class="col-md-3 mb-2"><button class="btn btn-outline-primary btn-block" type="submit">Save Unit</button></div>
                                    </div>
                                </form>

                                @forelse($week->units as $unit)
                                    <div class="am-unit-card mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="font-weight-semibold">Unit {{ $unit->unit_number }} - {{ $unit->title }}</div>
                                            <form method="post" action="{{ route('academic.management.units.destroy', $unit->id) }}" onsubmit="return confirm('Delete this full unit (lessons + files) from system and Moodle?')">
                                                @csrf
                                                @method('delete')
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete Unit</button>
                                            </form>
                                        </div>

                                        <form method="post" action="{{ route('academic.management.units.update', $unit->id) }}" class="mb-3">
                                            @csrf
                                            @method('put')
                                            <div class="form-row">
                                                <div class="col-md-2 mb-2"><input type="number" min="1" name="unit_number" value="{{ $unit->unit_number }}" class="form-control form-control-sm" required></div>
                                                <div class="col-md-7 mb-2"><input type="text" name="title" value="{{ $unit->title }}" class="form-control form-control-sm" required></div>
                                                <div class="col-md-3 mb-2"><button class="btn btn-outline-secondary btn-sm btn-block" type="submit">Update Unit</button></div>
                                            </div>
                                        </form>

                                        <div class="am-lesson-card mb-3">
                                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                                <div class="font-weight-semibold">Add Content</div>
                                                <select class="form-control form-control-sm w-auto js-content-switch" data-target="unit-content-{{ $unit->id }}">
                                                    <option value="file">File</option>
                                                    <option value="quiz">Quiz</option>
                                                </select>
                                            </div>
                                            <div class="mt-2" id="unit-content-{{ $unit->id }}-file">
                                                <form method="post" action="{{ route('academic.management.files.unit_general.store') }}" enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="hidden" name="week_unit_id" value="{{ $unit->id }}">
                                                    <div class="form-row">
                                                        <div class="col-md-3 mb-2"><input type="text" name="title" class="form-control" placeholder="Unit file title" required></div>
                                                        <div class="col-md-3 mb-2">
                                                            <select name="general_type" class="form-control" required>
                                                                @foreach($unitGeneralFileTypes as $typeValue => $typeLabel)
                                                                    <option value="{{ $typeValue }}">
                                                                        {{ $typeLabel }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3 mb-2">
                                                            <select name="moodle_activity_type" class="form-control" required>
                                                                @foreach($moodleFileActivityTypes as $activityValue => $activityLabel)
                                                                    <option value="{{ $activityValue }}">{{ $activityLabel }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3 mb-2">
                                                            <select name="moodle_audience" class="form-control" required>
                                                                @foreach($moodleAudienceOptions as $audienceValue => $audienceLabel)
                                                                    <option value="{{ $audienceValue }}">{{ $audienceLabel }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-row">
                                                        <div class="col-md-12 mb-2"><input type="text" name="activity_intro" class="form-control" placeholder="Assignment instructions (optional)"></div>
                                                    </div>
                                                    <div class="form-row">
                                                        <div class="col-md-3 mb-2"><input type="datetime-local" name="available_from" class="form-control"></div>
                                                        <div class="col-md-3 mb-2"><input type="datetime-local" name="due_at" class="form-control"></div>
                                                        <div class="col-md-3 mb-2"><input type="datetime-local" name="cutoff_at" class="form-control"></div>
                                                        <div class="col-md-3 mb-2"><input type="file" name="file" class="form-control" required></div>
                                                    </div>
                                                    <div class="form-row">
                                                        <div class="col-md-12 mb-2"><button class="btn btn-outline-primary btn-block" type="submit">Upload Unit File</button></div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="mt-2" id="unit-content-{{ $unit->id }}-quiz">
                                                <form method="post" action="{{ route('academic.management.quizzes.store') }}" enctype="multipart/form-data" class="js-quiz-form">
                                                    @csrf
                                                    <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
                                                    <input type="hidden" name="scope" value="unit">
                                                    <input type="hidden" name="week_unit_id" value="{{ $unit->id }}">
                                                    <div class="form-row">
                                                        <div class="col-md-3 mb-2">
                                                            <select name="upload_type" class="form-control form-control-sm js-quiz-upload-type">
                                                                <option value="questions">Questions File</option>
                                                                <option value="xml">Moodle XML</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3 mb-2">
                                                            <select name="moodle_audience" class="form-control form-control-sm" required>
                                                                @foreach($moodleAudienceOptions as $audienceValue => $audienceLabel)
                                                                    <option value="{{ $audienceValue }}">{{ $audienceLabel }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4 mb-2"><input type="text" name="title" class="form-control form-control-sm" placeholder="Quiz title" required></div>
                                                        <div class="col-md-2 mb-2"><input type="number" min="1" max="300" name="time_limit_minutes" class="form-control form-control-sm" placeholder="Minutes"></div>
                                                    </div>
                                                    <div class="form-row">
                                                        <div class="col-md-6 mb-2">
                                                            <input type="datetime-local" name="available_from" class="form-control form-control-sm" placeholder="Starts at">
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <input type="datetime-local" name="available_until" class="form-control form-control-sm" placeholder="Ends at">
                                                        </div>
                                                    </div>
                                                    <div class="form-row">
                                                        <div class="col-md-6 mb-2 js-questions-group">
                                                            <input type="file" name="questions_file" class="form-control form-control-sm">
                                                        </div>
                                                        <div class="col-md-6 mb-2 js-xml-group">
                                                            <input type="file" name="xml_file" class="form-control form-control-sm">
                                                        </div>
                                                    </div>
                                                    <div class="form-row">
                                                        <div class="col-md-12 mb-2"><button class="btn btn-outline-success btn-block" type="submit">Upload Quiz</button></div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        @if($unit->generalFiles->count())
                                            <div class="mb-3">
                                                <div class="am-subtle font-weight-semibold">Unit Files</div>
                                                @foreach($unit->generalFiles as $ugf)
                                                    <div class="am-lesson-card mb-2">
                                                        <div class="mb-2 d-flex justify-content-between align-items-center flex-wrap">
                                                            <a href="{{ asset('storage/'.$ugf->file_path) }}" target="_blank">{{ $ugf->title }}</a>
                                                            <div class="mt-1 mt-sm-0">
                                                                <span class="badge badge-info">{{ $unitGeneralFileTypes[$ugf->general_type ?? 'knowledge_core'] ?? 'Unknown' }}</span>
                                                                <span class="badge badge-primary">{{ $moodleFileActivityTypes[$ugf->moodle_activity_type ?? 'resource'] ?? 'File Resource' }}</span>
                                                                <span class="badge badge-secondary">{{ $moodleAudienceOptions[$ugf->moodle_audience ?? 'both'] ?? 'Teachers + Students' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="am-subtle mb-2">
                                                            @if($ugf->due_at)
                                                                Due: {{ $ugf->due_at->format('Y-m-d H:i') }}
                                                            @elseif($ugf->available_from)
                                                                Available: {{ $ugf->available_from->format('Y-m-d H:i') }}
                                                            @else
                                                                {{ ($ugf->moodle_activity_type ?? 'resource') === 'assignment' ? 'Assignment without due date' : 'Standard file resource' }}
                                                            @endif
                                                            @if($ugf->moodle_view_url)
                                                                | <a href="{{ $ugf->moodle_view_url }}" target="_blank">Open in Moodle</a>
                                                            @endif
                                                        </div>
                                                        <form method="post" action="{{ route('academic.management.files.unit_general.update', $ugf->id) }}" enctype="multipart/form-data">
                                                            @csrf
                                                            @method('put')
                                                            <div class="form-row">
                                                                <div class="col-md-3 mb-2"><input type="text" name="title" class="form-control form-control-sm" value="{{ $ugf->title }}" required></div>
                                                                <div class="col-md-3 mb-2">
                                                                    <select name="general_type" class="form-control form-control-sm" required>
                                                                        @foreach($unitGeneralFileTypes as $typeValue => $typeLabel)
                                                                            <option value="{{ $typeValue }}" {{ ($ugf->general_type ?? 'knowledge_core') === $typeValue ? 'selected' : '' }}>
                                                                                {{ $typeLabel }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-3 mb-2">
                                                                    <select name="moodle_activity_type" class="form-control form-control-sm" required>
                                                                        @foreach($moodleFileActivityTypes as $activityValue => $activityLabel)
                                                                            <option value="{{ $activityValue }}" {{ ($ugf->moodle_activity_type ?? 'resource') === $activityValue ? 'selected' : '' }}>
                                                                                {{ $activityLabel }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-3 mb-2">
                                                                    <select name="moodle_audience" class="form-control form-control-sm" required>
                                                                        @foreach($moodleAudienceOptions as $audienceValue => $audienceLabel)
                                                                            <option value="{{ $audienceValue }}" {{ ($ugf->moodle_audience ?? 'both') === $audienceValue ? 'selected' : '' }}>
                                                                                {{ $audienceLabel }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-row">
                                                                <div class="col-md-12 mb-2"><input type="text" name="activity_intro" class="form-control form-control-sm" value="{{ $ugf->activity_intro }}" placeholder="Assignment instructions (optional)"></div>
                                                            </div>
                                                            <div class="form-row">
                                                                <div class="col-md-3 mb-2"><input type="datetime-local" name="available_from" class="form-control form-control-sm" value="{{ optional($ugf->available_from)->format('Y-m-d\\TH:i') }}"></div>
                                                                <div class="col-md-3 mb-2"><input type="datetime-local" name="due_at" class="form-control form-control-sm" value="{{ optional($ugf->due_at)->format('Y-m-d\\TH:i') }}"></div>
                                                                <div class="col-md-3 mb-2"><input type="datetime-local" name="cutoff_at" class="form-control form-control-sm" value="{{ optional($ugf->cutoff_at)->format('Y-m-d\\TH:i') }}"></div>
                                                                <div class="col-md-3 mb-2"><input type="file" name="file" class="form-control form-control-sm"></div>
                                                            </div>
                                                            <div class="form-row">
                                                                <div class="col-md-12 mb-2"><button class="btn btn-outline-secondary btn-sm btn-block">Update File</button></div>
                                                            </div>
                                                        </form>
                                                        <form method="post" action="{{ route('academic.management.files.unit_general.destroy', $ugf->id) }}" onsubmit="return confirm('Delete this file from system and Moodle?')">
                                                            @csrf
                                                            @method('delete')
                                                            <button class="btn btn-sm btn-outline-danger">Delete File</button>
                                                        </form>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if($unit->quizzes->count())
                                            <div class="mb-3">
                                                <div class="am-subtle font-weight-semibold">Unit Quizzes</div>
                                                @foreach($unit->quizzes as $quiz)
                                                    <div class="am-lesson-card mb-2">
                                                        <div class="mb-2 d-flex justify-content-between align-items-center flex-wrap">
                                                            <div>
                                                                <div class="font-weight-semibold">{{ $quiz->title }}</div>
                                                                <div class="am-subtle">
                                                                    {{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' min' : 'No time limit' }}
                                                                    @if($quiz->available_from || $quiz->available_until)
                                                                        | {{ $quiz->available_from ? $quiz->available_from->format('Y-m-d H:i') : 'Anytime' }} → {{ $quiz->available_until ? $quiz->available_until->format('Y-m-d H:i') : 'No end' }}
                                                                    @endif
                                                                    | Audience: {{ $moodleAudienceOptions[$quiz->moodle_audience ?? 'both'] ?? 'Teachers + Students' }}
                                                                    | Moodle: {{ $quiz->moodle_cmid ? 'Synced' : 'Pending' }}
                                                                </div>
                                                            </div>
                                                            <form method="post" action="{{ route('academic.management.quizzes.destroy', $quiz->id) }}" onsubmit="return confirm('Delete this quiz from system and Moodle?')">
                                                                @csrf
                                                                @method('delete')
                                                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                                                            </form>
                                                        </div>
                                                        <div class="am-subtle">
                                                            @if($quiz->question_file_path)
                                                                <a href="{{ asset('storage/'.$quiz->question_file_path) }}" target="_blank">Questions</a>
                                                            @endif
                                                            @if($quiz->xml_file_path)
                                                                <span class="mx-1">|</span>
                                                                <a href="{{ asset('storage/'.$quiz->xml_file_path) }}" target="_blank">XML</a>
                                                            @endif
                                                            @if($quiz->moodle_view_url)
                                                                <span class="mx-1">|</span>
                                                                <a href="{{ $quiz->moodle_view_url }}" target="_blank">Open in Moodle</a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <form method="post" action="{{ route('academic.management.lessons.store') }}" class="mb-2">
                                            @csrf
                                            <input type="hidden" name="week_unit_id" value="{{ $unit->id }}">
                                            <div class="form-row">
                                                <div class="col-md-2 mb-2"><input type="number" min="1" name="lesson_number" class="form-control" placeholder="Lesson #" required></div>
                                                <div class="col-md-7 mb-2"><input type="text" name="title" class="form-control" placeholder="Lesson title" required></div>
                                                <div class="col-md-3 mb-2"><button class="btn btn-outline-dark btn-block" type="submit">Save Lesson</button></div>
                                            </div>
                                        </form>

                                        @forelse($unit->lessons as $lesson)
                                            <div class="am-lesson-card mb-2">
                                                <div class="font-weight-semibold mb-2">Lesson {{ $lesson->lesson_number }} - {{ $lesson->title }}</div>

                                                <form method="post" action="{{ route('academic.management.lessons.update', $lesson->id) }}" class="mb-2">
                                                    @csrf
                                                    @method('put')
                                                    <div class="form-row">
                                                        <div class="col-md-2 mb-2"><input type="number" min="1" name="lesson_number" value="{{ $lesson->lesson_number }}" class="form-control form-control-sm" required></div>
                                                        <div class="col-md-7 mb-2"><input type="text" name="title" value="{{ $lesson->title }}" class="form-control form-control-sm" required></div>
                                                        <div class="col-md-3 mb-2"><button class="btn btn-outline-secondary btn-sm btn-block" type="submit">Update Lesson</button></div>
                                                    </div>
                                                </form>

                                                <div class="am-lesson-card mb-2">
                                                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                                                        <div class="font-weight-semibold">Add Content</div>
                                                        <select class="form-control form-control-sm w-auto js-content-switch" data-target="lesson-content-{{ $lesson->id }}">
                                                            <option value="file">File</option>
                                                            <option value="question-bank">Question Bank</option>
                                                            <option value="quiz">Quiz</option>
                                                        </select>
                                                    </div>
                                                    <div class="mt-2" id="lesson-content-{{ $lesson->id }}-file">
                                                        <form method="post" action="{{ route('academic.management.files.lesson.store') }}" enctype="multipart/form-data">
                                                            @csrf
                                                            <input type="hidden" name="unit_lesson_id" value="{{ $lesson->id }}">
                                                            <div class="form-row">
                                                                <div class="col-md-3 mb-2"><input type="text" name="title" class="form-control" placeholder="Lesson file title" required></div>
                                                                <div class="col-md-3 mb-2">
                                                                    <select name="lesson_type" class="form-control" required>
                                                                        @foreach($lessonFileTypes as $typeValue => $typeLabel)
                                                                            <option value="{{ $typeValue }}">
                                                                                {{ $typeLabel }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-3 mb-2">
                                                                    <select name="moodle_activity_type" class="form-control" required>
                                                                        @foreach($moodleFileActivityTypes as $activityValue => $activityLabel)
                                                                            <option value="{{ $activityValue }}">{{ $activityLabel }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-3 mb-2">
                                                                    <select name="moodle_audience" class="form-control" required>
                                                                        @foreach($moodleAudienceOptions as $audienceValue => $audienceLabel)
                                                                            <option value="{{ $audienceValue }}">{{ $audienceLabel }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-row">
                                                                <div class="col-md-12 mb-2"><input type="text" name="activity_intro" class="form-control" placeholder="Assignment instructions (optional)"></div>
                                                            </div>
                                                            <div class="form-row">
                                                                <div class="col-md-3 mb-2"><input type="datetime-local" name="available_from" class="form-control"></div>
                                                                <div class="col-md-3 mb-2"><input type="datetime-local" name="due_at" class="form-control"></div>
                                                                <div class="col-md-3 mb-2"><input type="datetime-local" name="cutoff_at" class="form-control"></div>
                                                                <div class="col-md-3 mb-2"><input type="file" name="file" class="form-control" required></div>
                                                            </div>
                                                            <div class="form-row">
                                                                <div class="col-md-12 mb-2"><button class="btn btn-outline-success btn-block" type="submit">Upload Lesson File</button></div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="mt-2" id="lesson-content-{{ $lesson->id }}-question-bank">
                                                        <form method="post" action="{{ route('academic.management.question_banks.lesson.store') }}" enctype="multipart/form-data" class="js-question-bank-form">
                                                            @csrf
                                                            <input type="hidden" name="unit_lesson_id" value="{{ $lesson->id }}">
                                                            <div class="form-row">
                                                                <div class="col-md-3 mb-2">
                                                                    <select name="upload_type" class="form-control form-control-sm js-question-bank-upload-type">
                                                                        <option value="questions">Questions File</option>
                                                                        <option value="xml">Moodle XML</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-9 mb-2"><input type="text" name="title" class="form-control form-control-sm" placeholder="Question bank title" required></div>
                                                            </div>
                                                            <div class="form-row">
                                                                <div class="col-md-6 mb-2 js-question-bank-questions-group">
                                                                    <input type="file" name="questions_file" class="form-control form-control-sm">
                                                                </div>
                                                                <div class="col-md-6 mb-2 js-question-bank-xml-group">
                                                                    <input type="file" name="xml_file" class="form-control form-control-sm">
                                                                </div>
                                                            </div>
                                                            <div class="form-row">
                                                                <div class="col-md-12 mb-2"><button class="btn btn-outline-info btn-block" type="submit">Upload Question Bank</button></div>
                                                            </div>
                                                            <div class="am-subtle">This upload will try to import directly into Moodle Question Bank when the Moodle bridge supports it.</div>
                                                        </form>
                                                    </div>
                                                    <div class="mt-2" id="lesson-content-{{ $lesson->id }}-quiz">
                                                        <form method="post" action="{{ route('academic.management.quizzes.store') }}" enctype="multipart/form-data" class="js-quiz-form">
                                                            @csrf
                                                            <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
                                                            <input type="hidden" name="scope" value="lesson">
                                                            <input type="hidden" name="unit_lesson_id" value="{{ $lesson->id }}">
                                                            <div class="form-row">
                                                                <div class="col-md-3 mb-2">
                                                                    <select name="upload_type" class="form-control form-control-sm js-quiz-upload-type">
                                                                        <option value="questions">Questions File</option>
                                                                        <option value="xml">Moodle XML</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-3 mb-2">
                                                                    <select name="moodle_audience" class="form-control form-control-sm" required>
                                                                        @foreach($moodleAudienceOptions as $audienceValue => $audienceLabel)
                                                                            <option value="{{ $audienceValue }}">{{ $audienceLabel }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4 mb-2"><input type="text" name="title" class="form-control form-control-sm" placeholder="Quiz title" required></div>
                                                                <div class="col-md-2 mb-2"><input type="number" min="1" max="300" name="time_limit_minutes" class="form-control form-control-sm" placeholder="Minutes"></div>
                                                            </div>
                                                            <div class="form-row">
                                                                <div class="col-md-6 mb-2">
                                                                    <input type="datetime-local" name="available_from" class="form-control form-control-sm" placeholder="Starts at">
                                                                </div>
                                                                <div class="col-md-6 mb-2">
                                                                    <input type="datetime-local" name="available_until" class="form-control form-control-sm" placeholder="Ends at">
                                                                </div>
                                                            </div>
                                                            <div class="form-row">
                                                                <div class="col-md-6 mb-2 js-questions-group">
                                                                    <input type="file" name="questions_file" class="form-control form-control-sm">
                                                                </div>
                                                                <div class="col-md-6 mb-2 js-xml-group">
                                                                    <input type="file" name="xml_file" class="form-control form-control-sm">
                                                                </div>
                                                            </div>
                                                            <div class="form-row">
                                                                <div class="col-md-12 mb-2"><button class="btn btn-outline-success btn-block" type="submit">Upload Quiz</button></div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>

                                                @if($lesson->files->count())
                                                    @foreach($lesson->files as $lf)
                                                        <div class="am-lesson-card mb-2">
                                                            <div class="mb-2 d-flex justify-content-between align-items-center flex-wrap">
                                                                <a href="{{ asset('storage/'.$lf->file_path) }}" target="_blank">{{ $lf->title }}</a>
                                                                <div class="mt-1 mt-sm-0">
                                                                    <span class="badge badge-info">{{ $lessonFileTypes[$lf->lesson_type ?? 'digital_resources'] ?? 'Unknown' }}</span>
                                                                    <span class="badge badge-primary">{{ $moodleFileActivityTypes[$lf->moodle_activity_type ?? 'resource'] ?? 'File Resource' }}</span>
                                                                    <span class="badge badge-secondary">{{ $moodleAudienceOptions[$lf->moodle_audience ?? 'both'] ?? 'Teachers + Students' }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="am-subtle mb-2">
                                                                @if($lf->due_at)
                                                                    Due: {{ $lf->due_at->format('Y-m-d H:i') }}
                                                                @elseif($lf->available_from)
                                                                    Available: {{ $lf->available_from->format('Y-m-d H:i') }}
                                                                @else
                                                                    {{ ($lf->moodle_activity_type ?? 'resource') === 'assignment' ? 'Assignment without due date' : 'Standard file resource' }}
                                                                @endif
                                                                @if($lf->moodle_view_url)
                                                                    | <a href="{{ $lf->moodle_view_url }}" target="_blank">Open in Moodle</a>
                                                                @endif
                                                            </div>
                                                            <form method="post" action="{{ route('academic.management.files.lesson.update', $lf->id) }}" enctype="multipart/form-data">
                                                                @csrf
                                                                @method('put')
                                                                <div class="form-row">
                                                                    <div class="col-md-3 mb-2"><input type="text" name="title" class="form-control form-control-sm" value="{{ $lf->title }}" required></div>
                                                                    <div class="col-md-3 mb-2">
                                                                        <select name="lesson_type" class="form-control form-control-sm" required>
                                                                            @foreach($lessonFileTypes as $typeValue => $typeLabel)
                                                                                <option value="{{ $typeValue }}" {{ ($lf->lesson_type ?? 'digital_resources') === $typeValue ? 'selected' : '' }}>
                                                                                    {{ $typeLabel }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-3 mb-2">
                                                                        <select name="moodle_activity_type" class="form-control form-control-sm" required>
                                                                            @foreach($moodleFileActivityTypes as $activityValue => $activityLabel)
                                                                                <option value="{{ $activityValue }}" {{ ($lf->moodle_activity_type ?? 'resource') === $activityValue ? 'selected' : '' }}>
                                                                                    {{ $activityLabel }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-3 mb-2">
                                                                        <select name="moodle_audience" class="form-control form-control-sm" required>
                                                                            @foreach($moodleAudienceOptions as $audienceValue => $audienceLabel)
                                                                                <option value="{{ $audienceValue }}" {{ ($lf->moodle_audience ?? 'both') === $audienceValue ? 'selected' : '' }}>
                                                                                    {{ $audienceLabel }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-row">
                                                                    <div class="col-md-12 mb-2"><input type="text" name="activity_intro" class="form-control form-control-sm" value="{{ $lf->activity_intro }}" placeholder="Assignment instructions (optional)"></div>
                                                                </div>
                                                                <div class="form-row">
                                                                    <div class="col-md-3 mb-2"><input type="datetime-local" name="available_from" class="form-control form-control-sm" value="{{ optional($lf->available_from)->format('Y-m-d\\TH:i') }}"></div>
                                                                    <div class="col-md-3 mb-2"><input type="datetime-local" name="due_at" class="form-control form-control-sm" value="{{ optional($lf->due_at)->format('Y-m-d\\TH:i') }}"></div>
                                                                    <div class="col-md-3 mb-2"><input type="datetime-local" name="cutoff_at" class="form-control form-control-sm" value="{{ optional($lf->cutoff_at)->format('Y-m-d\\TH:i') }}"></div>
                                                                    <div class="col-md-3 mb-2"><input type="file" name="file" class="form-control form-control-sm"></div>
                                                                </div>
                                                                <div class="form-row">
                                                                    <div class="col-md-12 mb-2"><button class="btn btn-outline-secondary btn-sm btn-block">Update File</button></div>
                                                                </div>
                                                            </form>
                                                            <form method="post" action="{{ route('academic.management.files.lesson.destroy', $lf->id) }}" onsubmit="return confirm('Delete this file from system and Moodle?')">
                                                                @csrf
                                                                @method('delete')
                                                                <button class="btn btn-sm btn-outline-danger">Delete File</button>
                                                            </form>
                                                        </div>
                                                    @endforeach
                                                @endif

                                                @if($lesson->questionBanks->count())
                                                    <div class="mb-2">
                                                        <div class="am-subtle font-weight-semibold">Lesson Question Banks</div>
                                                        @foreach($lesson->questionBanks as $questionBank)
                                                            <div class="am-lesson-card mb-2">
                                                                @php($questionBankSyncStatus = $questionBank->moodle_sync_status ?? 'pending')
                                                                @php($questionBankCreatedAt = $questionBank->created_at instanceof \DateTimeInterface ? $questionBank->created_at->format('Y-m-d H:i') : (!empty($questionBank->created_at) ? \Illuminate\Support\Carbon::parse($questionBank->created_at)->format('Y-m-d H:i') : null))
                                                                @php($questionBankLastSyncedAt = $questionBank->moodle_last_synced_at instanceof \DateTimeInterface ? $questionBank->moodle_last_synced_at->format('Y-m-d H:i') : (!empty($questionBank->moodle_last_synced_at) ? \Illuminate\Support\Carbon::parse($questionBank->moodle_last_synced_at)->format('Y-m-d H:i') : null))
                                                                <div class="mb-2 d-flex justify-content-between align-items-center flex-wrap">
                                                                    <div>
                                                                        <div class="font-weight-semibold">
                                                                            {{ $questionBank->title }}
                                                                            @if($questionBankSyncStatus === 'success')
                                                                                <span class="badge badge-success">Moodle Synced</span>
                                                                            @elseif($questionBankSyncStatus === 'failed')
                                                                                <span class="badge badge-danger">Moodle Failed</span>
                                                                            @elseif($questionBankSyncStatus === 'skipped')
                                                                                <span class="badge badge-warning">Moodle Pending Setup</span>
                                                                            @else
                                                                                <span class="badge badge-secondary">Moodle Pending</span>
                                                                            @endif
                                                                        </div>
                                                                        <div class="am-subtle">
                                                                            {{ $questionBank->question_count ? $questionBank->question_count . ' questions' : 'XML bank' }}
                                                                            @if($questionBankCreatedAt)
                                                                                | {{ $questionBankCreatedAt }}
                                                                            @endif
                                                                            @if($questionBank->moodle_imported_count)
                                                                                | Imported: {{ $questionBank->moodle_imported_count }}
                                                                            @endif
                                                                            @if($questionBankLastSyncedAt)
                                                                                | Last sync: {{ $questionBankLastSyncedAt }}
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex mt-2 mt-sm-0">
                                                                        <form method="post" action="{{ route('academic.management.question_banks.lesson.sync', $questionBank->id) }}" class="mr-2">
                                                                            @csrf
                                                                            <button class="btn btn-sm btn-outline-primary">{{ $questionBankSyncStatus === 'success' ? 'Re-sync Moodle' : 'Sync To Moodle' }}</button>
                                                                        </form>
                                                                        <form method="post" action="{{ route('academic.management.question_banks.lesson.destroy', $questionBank->id) }}" onsubmit="return confirm('Delete this question bank? If it was already imported to Moodle, the Moodle category will stay there for now.')">
                                                                            @csrf
                                                                            @method('delete')
                                                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                                <div class="am-subtle">
                                                                    @if($questionBank->source_file_path)
                                                                        <a href="{{ asset('storage/'.$questionBank->source_file_path) }}" target="_blank">Source File</a>
                                                                    @endif
                                                                    @if($questionBank->xml_file_path)
                                                                        @if($questionBank->source_file_path)
                                                                            <span class="mx-1">|</span>
                                                                        @endif
                                                                        <a href="{{ asset('storage/'.$questionBank->xml_file_path) }}" target="_blank">Moodle XML</a>
                                                                    @endif
                                                                    @if($questionBank->moodle_category_url)
                                                                        <span class="mx-1">|</span>
                                                                        <a href="{{ $questionBank->moodle_category_url }}" target="_blank">Open in Moodle</a>
                                                                    @endif
                                                                </div>
                                                                <div class="am-subtle mt-1">
                                                                    @if($questionBank->moodle_category_name)
                                                                        Category: {{ $questionBank->moodle_category_name }}
                                                                    @elseif($questionBankSyncStatus === 'success')
                                                                        Category synced to Moodle.
                                                                    @endif
                                                                </div>
                                                                @if($questionBank->moodle_sync_error)
                                                                    <div class="text-danger small mt-1">{{ $questionBank->moodle_sync_error }}</div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                @if($lesson->quizzes->count())
                                                    <div class="mb-2">
                                                        <div class="am-subtle font-weight-semibold">Lesson Quizzes</div>
                                                        @foreach($lesson->quizzes as $quiz)
                                                            <div class="am-lesson-card mb-2">
                                                                <div class="mb-2 d-flex justify-content-between align-items-center flex-wrap">
                                                                    <div>
                                                                        <div class="font-weight-semibold">{{ $quiz->title }}</div>
                                                                        <div class="am-subtle">
                                                                            {{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' min' : 'No time limit' }}
                                                                            @if($quiz->available_from || $quiz->available_until)
                                                                                | {{ $quiz->available_from ? $quiz->available_from->format('Y-m-d H:i') : 'Anytime' }} → {{ $quiz->available_until ? $quiz->available_until->format('Y-m-d H:i') : 'No end' }}
                                                                            @endif
                                                                            | Audience: {{ $moodleAudienceOptions[$quiz->moodle_audience ?? 'both'] ?? 'Teachers + Students' }}
                                                                            | Moodle: {{ $quiz->moodle_cmid ? 'Synced' : 'Pending' }}
                                                                        </div>
                                                                    </div>
                                                                    <form method="post" action="{{ route('academic.management.quizzes.destroy', $quiz->id) }}" onsubmit="return confirm('Delete this quiz from system and Moodle?')">
                                                                        @csrf
                                                                        @method('delete')
                                                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                                                    </form>
                                                                </div>
                                                                <div class="am-subtle">
                                                                    @if($quiz->question_file_path)
                                                                        <a href="{{ asset('storage/'.$quiz->question_file_path) }}" target="_blank">Questions</a>
                                                                    @endif
                                                                    @if($quiz->xml_file_path)
                                                                        <span class="mx-1">|</span>
                                                                        <a href="{{ asset('storage/'.$quiz->xml_file_path) }}" target="_blank">XML</a>
                                                                    @endif
                                                                    @if($quiz->moodle_view_url)
                                                                        <span class="mx-1">|</span>
                                                                        <a href="{{ $quiz->moodle_view_url }}" target="_blank">Open in Moodle</a>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="am-empty">No lessons added yet for this unit.</div>
                                        @endforelse
                                    </div>
                                @empty
                                    <div class="am-empty">No units added for this week yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="am-empty">No weeks added yet.</div>
                @endforelse
                @endif
            @endif
        </div>
    </div>
</div>

<script>
    (function () {
        var switches = document.querySelectorAll('.js-content-switch');
        switches.forEach(function (select) {
            var target = select.getAttribute('data-target');
            var toggle = function () {
                var value = select.value;
                var fileEl = document.getElementById(target + '-file');
                var questionBankEl = document.getElementById(target + '-question-bank');
                var quizEl = document.getElementById(target + '-quiz');
                if (fileEl) {
                    fileEl.style.display = value === 'file' ? '' : 'none';
                }
                if (questionBankEl) {
                    questionBankEl.style.display = value === 'question-bank' ? '' : 'none';
                }
                if (quizEl) {
                    quizEl.style.display = value === 'quiz' ? '' : 'none';
                }
            };
            select.addEventListener('change', toggle);
            toggle();
        });

        var quizForms = document.querySelectorAll('.js-quiz-form');
        quizForms.forEach(function (form) {
            var uploadType = form.querySelector('.js-quiz-upload-type');
            var questionsGroup = form.querySelector('.js-questions-group');
            var xmlGroup = form.querySelector('.js-xml-group');
            if (!uploadType) {
                return;
            }
            var toggle = function () {
                var isXml = uploadType.value === 'xml';
                if (questionsGroup) {
                    questionsGroup.style.display = isXml ? 'none' : '';
                }
                if (xmlGroup) {
                    xmlGroup.style.display = isXml ? '' : 'none';
                }
            };
            uploadType.addEventListener('change', toggle);
            toggle();
        });

        var questionBankForms = document.querySelectorAll('.js-question-bank-form');
        questionBankForms.forEach(function (form) {
            var uploadType = form.querySelector('.js-question-bank-upload-type');
            var questionsGroup = form.querySelector('.js-question-bank-questions-group');
            var xmlGroup = form.querySelector('.js-question-bank-xml-group');
            if (!uploadType) {
                return;
            }
            var toggle = function () {
                var isXml = uploadType.value === 'xml';
                if (questionsGroup) {
                    questionsGroup.style.display = isXml ? 'none' : '';
                }
                if (xmlGroup) {
                    xmlGroup.style.display = isXml ? '' : 'none';
                }
            };
            uploadType.addEventListener('change', toggle);
            toggle();
        });

        var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

        var renderMoodleList = function (weekId, modules) {
            var listWrap = document.getElementById('moodle-list-' + weekId);
            if (!listWrap) {
                return;
            }

            listWrap.innerHTML = '';
            if (!modules || modules.length === 0) {
                listWrap.innerHTML = '<div class="am-empty">No Moodle modules found for this week.</div>';
                return;
            }

            var list = document.createElement('ul');
            list.className = 'list-group';
            modules.forEach(function (mod) {
                var item = document.createElement('li');
                item.className = 'list-group-item d-flex justify-content-between align-items-center';
                item.setAttribute('data-cmid', String(mod.cmid || 0));

                var info = document.createElement('div');
                var title = document.createElement('div');
                title.className = 'font-weight-semibold';
                title.textContent = mod.name || ('Module ' + mod.cmid);
                var meta = document.createElement('div');
                meta.className = 'am-subtle';
                var metaParts = [];
                if (mod.modname) {
                    metaParts.push(mod.modname);
                }
                metaParts.push('CMID ' + mod.cmid);
                meta.textContent = metaParts.join(' | ');
                info.appendChild(title);
                info.appendChild(meta);

                var controls = document.createElement('div');
                controls.className = 'btn-group btn-group-sm';
                var upBtn = document.createElement('button');
                upBtn.type = 'button';
                upBtn.className = 'btn btn-outline-secondary js-move-up';
                upBtn.textContent = 'Up';
                var downBtn = document.createElement('button');
                downBtn.type = 'button';
                downBtn.className = 'btn btn-outline-secondary js-move-down';
                downBtn.textContent = 'Down';
                controls.appendChild(upBtn);
                controls.appendChild(downBtn);

                item.appendChild(info);
                item.appendChild(controls);
                list.appendChild(item);
            });

            listWrap.appendChild(list);

            if (!listWrap.dataset.bound) {
                listWrap.addEventListener('click', function (event) {
                    var up = event.target.closest('.js-move-up');
                    var down = event.target.closest('.js-move-down');
                    if (!up && !down) {
                        return;
                    }
                    var item = event.target.closest('li[data-cmid]');
                    if (!item) {
                        return;
                    }
                    if (up) {
                        var prev = item.previousElementSibling;
                        if (prev) {
                            item.parentNode.insertBefore(item, prev);
                        }
                    }
                    if (down) {
                        var next = item.nextElementSibling;
                        if (next) {
                            item.parentNode.insertBefore(next, item);
                        }
                    }
                });
                listWrap.dataset.bound = '1';
            }
        };

        var loadButtons = document.querySelectorAll('.js-load-moodle');
        loadButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var weekId = btn.getAttribute('data-week-id');
                var url = btn.getAttribute('data-url');
                var syncBtn = document.querySelector('.js-sync-moodle[data-week-id="' + weekId + '"]');
                var statusEl = document.getElementById('moodle-status-' + weekId);
                if (!url) {
                    return;
                }
                if (statusEl) {
                    statusEl.textContent = 'Loading...';
                }
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (!data || !data.ok) {
                            if (statusEl) {
                                statusEl.textContent = data && data.error ? data.error : 'Failed to load Moodle list.';
                            }
                            return;
                        }
                        renderMoodleList(weekId, data.modules || []);
                        if (syncBtn) {
                            syncBtn.disabled = false;
                        }
                        if (statusEl) {
                            statusEl.textContent = 'Loaded.';
                        }
                    })
                    .catch(function () {
                        if (statusEl) {
                            statusEl.textContent = 'Failed to load Moodle list.';
                        }
                    });
            });
        });

        var syncButtons = document.querySelectorAll('.js-sync-moodle');
        syncButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var weekId = btn.getAttribute('data-week-id');
                var url = btn.getAttribute('data-sync-url');
                var listWrap = document.getElementById('moodle-list-' + weekId);
                var statusEl = document.getElementById('moodle-status-' + weekId);
                if (!url || !listWrap) {
                    return;
                }
                var items = listWrap.querySelectorAll('li[data-cmid]');
                var order = Array.prototype.map.call(items, function (item) {
                    return parseInt(item.getAttribute('data-cmid'), 10);
                }).filter(function (value) {
                    return value > 0;
                });

                if (order.length === 0) {
                    if (statusEl) {
                        statusEl.textContent = 'No modules to sync.';
                    }
                    return;
                }

                if (statusEl) {
                    statusEl.textContent = 'Syncing...';
                }

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ order: order })
                })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (!data || !data.ok) {
                            if (statusEl) {
                                statusEl.textContent = 'Sync failed. Check Moodle permissions.';
                            }
                            return;
                        }
                        if (statusEl) {
                            statusEl.textContent = 'Synced successfully.';
                        }
                    })
                    .catch(function () {
                        if (statusEl) {
                            statusEl.textContent = 'Sync failed.';
                        }
                    });
            });
        });
    })();
</script>
@endsection
