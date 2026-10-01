@extends('layouts.master')

@section('page_title', 'Academic Calendar')
@section('full_page', 'true')

@section('content')
<style>
    select.form-control:not([size]):not([multiple]) {
    height: 3.25003rem;
}
    .am-shell {
        max-width: 1280px;
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

    .am-stat {
        min-width: 132px;
        border-radius: 8px;
        background: #f7fbff;
        border: 1px solid #d5e4f2;
        padding: .55rem .7rem;
    }

    .am-stat-label {
        color: #586678;
        font-size: .74rem;
        line-height: 1.1;
    }

    .am-stat-value {
        color: #243447;
        font-size: 1.1rem;
        font-weight: 700;
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

    .am-table th {
        font-size: .74rem;
        text-transform: uppercase;
        color: #516274;
        background: #f7fafc;
        border-top: 0;
    }

    .am-empty {
        border: 1px dashed #cddaea;
        border-radius: 8px;
        background: #f9fcff;
        padding: .8rem;
        color: #64748b;
    }

    .am-shell .form-control {
        min-height: 38px;
        font-size: .9rem;
    }

    .am-shell .form-control-sm {
        min-height: 32px;
        font-size: .82rem;
    }

    .am-shell .btn {
        border-radius: 6px;
    }

    .am-term-card {
        border-left: 3px solid #1f78b5;
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

    .am-section-title {
        color: #1a2332;
        font-weight: 900;
    }

    .am-stat {
        background: rgba(30, 64, 175, .08);
        border-color: rgba(30, 64, 175, .2);
    }

    .am-stat-value {
        color: #1e3a8a;
        font-weight: 900;
    }

    .am-term-card {
        border-left-color: #1e40af;
    }

    .am-shell .btn-primary {
        background: #1e40af;
        border-color: #1e40af;
    }

    .am-shell .btn-primary:hover {
        background: #1e3a8a;
        border-color: #1e3a8a;
    }

    .am-subject-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 10px;
    }

    .am-subject-link {
        min-height: 64px;
        border: 1px solid rgba(30, 64, 175, .12);
        border-radius: 8px;
        background: rgba(240, 246, 252, .75);
        padding: 10px 12px;
        color: #1a2332;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .am-subject-link:hover {
        color: #0c2340;
        text-decoration: none;
        border-color: rgba(217, 39, 119, .25);
        box-shadow: 0 8px 18px rgba(30, 64, 175, .12);
    }

    .am-subject-name {
        font-size: .9rem;
        font-weight: 900;
        line-height: 1.2;
    }

    .am-subject-class {
        margin-top: 4px;
        color: #717f94;
        font-size: .75rem;
        font-weight: 800;
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

    .am-holiday-row {
        border: 1px solid rgba(30, 64, 175, .12);
        border-radius: 8px;
        background: rgba(249, 252, 255, .86);
        padding: 10px;
    }

    .am-holiday-row + .am-holiday-row {
        margin-top: 10px;
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
    <div class="card am-hero mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h4 class="mb-1 font-weight-bold text-dark">Academic Calendar</h4>
                <div class="am-subtle">Create the academic year terms, attach years/classes, then set how many weeks each term contains.</div>
            </div>
            <div class="d-flex flex-wrap mt-2 mt-md-0">
                <div class="am-stat mr-2 mb-2">
                    <div class="am-stat-label">Terms</div>
                    <div class="am-stat-value">{{ $sessions->count() }}</div>
                </div>
                <div class="am-stat mr-2 mb-2">
                    <div class="am-stat-label">Years / Classes</div>
                    <div class="am-stat-value">{{ $allClasses->count() }}</div>
                </div>
                <div class="am-stat mb-2">
                    <div class="am-stat-label">Calendar Records</div>
                    <div class="am-stat-value">{{ collect($sessionCalendarStats)->sum('week_records') }}</div>
                </div>
                <div class="am-stat ml-2 mb-2">
                    <div class="am-stat-label">Holidays</div>
                    <div class="am-stat-value">{{ $holidays->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="am-section-title">Create Term</div>
                    <form action="{{ route('academic.management.sessions.store') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label class="mb-1">Term Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Term 1" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label class="mb-1">Start Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="form-group col-6">
                                <label class="mb-1">End Date</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>
                        </div>
                        <button class="btn btn-primary btn-sm" type="submit">Create Term</button>
                    </form>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="am-section-title">Year / Class Attachment</div>
                    <div class="am-subtle">
                        New terms are attached to all years/classes automatically. Create the term, then set how many weeks it has from the term card.
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="am-section-title">Holiday Blocks</div>
                    <form action="{{ route('academic.management.holidays.store') }}" method="post" class="mb-3">
                        @csrf
                        <div class="form-group">
                            <label class="mb-1">Holiday Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Winter Break" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label class="mb-1">Type</label>
                                <select name="holiday_type" class="form-control" required>
                                    @foreach($holidayTypes as $typeValue => $typeLabel)
                                        <option value="{{ $typeValue }}">{{ $typeLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-6">
                                <label class="mb-1">Term</label>
                                <select name="academic_session_id" class="form-control">
                                    <option value="">All Terms</option>
                                    @foreach($sessions as $sessionOption)
                                        <option value="{{ $sessionOption->id }}">{{ $sessionOption->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label class="mb-1">Start Date</label>
                                <input type="date" name="starts_on" class="form-control" required>
                            </div>
                            <div class="form-group col-6">
                                <label class="mb-1">End Date</label>
                                <input type="date" name="ends_on" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="mb-1">Note</label>
                            <input type="text" name="note" class="form-control" placeholder="Optional note">
                        </div>
                        <button class="btn btn-primary btn-sm" type="submit">Add Holiday</button>
                    </form>

                    @if($holidays->isEmpty())
                        <div class="am-empty">No holiday blocks added yet.</div>
                    @else
                        @foreach($holidays as $holiday)
                            <div class="am-holiday-row">
                                <form action="{{ route('academic.management.holidays.update', $holiday->id) }}" method="post">
                                    @csrf
                                    @method('put')
                                    <div class="form-group mb-2">
                                        <input type="text" name="name" value="{{ $holiday->name }}" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-6 mb-2">
                                            <select name="holiday_type" class="form-control form-control-sm" required>
                                                @foreach($holidayTypes as $typeValue => $typeLabel)
                                                    <option value="{{ $typeValue }}" {{ $holiday->holiday_type === $typeValue ? 'selected' : '' }}>{{ $typeLabel }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-6 mb-2">
                                            <select name="academic_session_id" class="form-control form-control-sm">
                                                <option value="">All Terms</option>
                                                @foreach($sessions as $sessionOption)
                                                    <option value="{{ $sessionOption->id }}" {{ (int) $holiday->academic_session_id === (int) $sessionOption->id ? 'selected' : '' }}>{{ $sessionOption->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-6 mb-2">
                                            <input type="date" name="starts_on" value="{{ optional($holiday->starts_on)->format('Y-m-d') }}" class="form-control form-control-sm" required>
                                        </div>
                                        <div class="form-group col-6 mb-2">
                                            <input type="date" name="ends_on" value="{{ optional($holiday->ends_on)->format('Y-m-d') }}" class="form-control form-control-sm" required>
                                        </div>
                                    </div>
                                    <div class="form-group mb-2">
                                        <input type="text" name="note" value="{{ $holiday->note }}" class="form-control form-control-sm" placeholder="Optional note">
                                    </div>
                                    <button class="btn btn-outline-secondary btn-sm" type="submit">Save</button>
                                </form>
                                <form action="{{ route('academic.management.holidays.destroy', $holiday->id) }}" method="post" class="mt-2" onsubmit="return confirm('Delete this holiday block?')">
                                    @csrf
                                    @method('delete')
                                    <button class="btn btn-outline-danger btn-sm" type="submit">Delete</button>
                                </form>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="am-section-title">Terms / Academic Year</div>

                    @forelse($sessions as $session)
                        @php
                            $stats = $sessionCalendarStats[$session->id] ?? ['subject_count' => 0, 'week_count' => 0, 'week_records' => 0];
                            $subjectsForSession = $sessionSubjects[$session->id] ?? collect();
                        @endphp
                        <div class="card am-term-card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                                    <div>
                                        <h5 class="font-weight-bold mb-1">{{ $session->name }}</h5>
                                        <div class="am-subtle">
                                            {{ optional($session->start_date)->format('Y-m-d') ?: 'No start date' }}
                                            @if($session->end_date)
                                                to {{ $session->end_date->format('Y-m-d') }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap mt-2 mt-md-0">
                                        <span class="badge badge-primary mr-1 mb-1">{{ $session->classes->count() }} years/classes</span>
                                        <span class="badge badge-info mr-1 mb-1">{{ $stats['subject_count'] }} subjects</span>
                                        <span class="badge badge-success mb-1">{{ $stats['week_count'] }} weeks</span>
                                    </div>
                                </div>

                                <form method="post" action="{{ route('academic.management.sessions.update', $session->id) }}" class="mb-3">
                                    @csrf
                                    @method('put')
                                    <div class="form-row">
                                        <div class="col-md-4 mb-2">
                                            <input type="text" name="name" value="{{ $session->name }}" class="form-control form-control-sm" required>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <input type="date" name="start_date" value="{{ optional($session->start_date)->format('Y-m-d') }}" class="form-control form-control-sm" required>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <input type="date" name="end_date" value="{{ optional($session->end_date)->format('Y-m-d') }}" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-md-2 mb-2">
                                            <button class="btn btn-outline-secondary btn-sm btn-block" type="submit">Save</button>
                                        </div>
                                    </div>
                                </form>

                                <div class="mb-3">
                                    <div class="am-subtle mb-1">Attached years/classes</div>
                                    @if($session->classes->isEmpty())
                                        <div class="am-empty">No years/classes attached yet.</div>
                                    @else
                                        @foreach($session->classes as $class)
                                            <span class="badge badge-light border mr-1 mb-1">{{ $class->name }}</span>
                                        @endforeach
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <div class="am-subtle mb-2">Subjects</div>
                                    @if($subjectsForSession->isEmpty())
                                        <div class="am-empty">No subjects found for the attached years/classes.</div>
                                    @else
                                        <div class="am-subject-grid">
                                            @foreach($subjectsForSession as $subject)
                                                <a class="am-subject-link" href="{{ route('academic.management.weekly_content', ['session_id' => $session->id, 'subject_id' => $subject->id]) }}">
                                                    <span class="am-subject-name">{{ $subject->name }}</span>
                                                    <span class="am-subject-class">{{ optional($subject->my_class)->name }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <form method="post" action="{{ route('academic.management.sessions.weeks.generate', $session->id) }}">
                                    @csrf
                                    <div class="form-row align-items-end">
                                        <div class="col-md-8 mb-2">
                                            <label class="mb-1">How many weeks in this term?</label>
                                            <input type="number" min="1" max="60" name="week_count" class="form-control" value="{{ max(1, (int) $stats['week_count']) }}" required>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <button class="btn btn-primary btn-block" type="submit">Generate Calendar Weeks</button>
                                        </div>
                                    </div>
                                    <div class="am-subtle">
                                        This creates the same week calendar for every subject inside the attached years/classes.
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="am-empty">No terms created yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
