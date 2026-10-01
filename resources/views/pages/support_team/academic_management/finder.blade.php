@extends('layouts.master')

@section('page_title', 'Academic File Finder')
@section('full_page', 'true')

@section('content')
<style>
    .finder-shell .card {
        border-radius: 12px;
        border: 1px solid #e6eaf0;
        box-shadow: 0 4px 14px rgba(8, 36, 74, 0.06);
    }

    .finder-head {
        background: linear-gradient(120deg, #0d3b75 0%, #1f78b5 100%);
        color: #fff;
    }

    .finder-subtle {
        color: #5f6b7a;
        font-size: .86rem;
    }

    .finder-empty {
        border: 1px dashed #cddaea;
        border-radius: 8px;
        background: #f9fcff;
        padding: .8rem;
        color: #64748b;
    }

    body {
        background:
            radial-gradient(circle 150px at 5% 10%, rgba(30, 64, 175, .12), transparent 60%),
            radial-gradient(circle 200px at 85% 5%, rgba(179, 157, 219, .15), transparent 70%),
            radial-gradient(circle 100px at 10% 70%, rgba(217, 39, 119, .1), transparent 50%),
            linear-gradient(135deg, #e8f0f8 0%, #eff4fb 40%, #f0f6fc 70%, #e8eff7 100%);
        color: #1a2332;
    }

    .finder-shell {
        max-width: 1220px;
        margin: 0 auto;
        padding: 0px 0;
        font-family: "Inter", "Segoe UI", Arial, sans-serif;
    }

    .finder-shell * {
        letter-spacing: 0;
    }

    .finder-shell .card {
        border-radius: 8px;
        border: 1px solid rgba(30, 64, 175, .1);
        background: rgba(255, 255, 255, .9);
        box-shadow: 0 8px 20px rgba(30, 64, 175, .08);
    }

    .finder-head {
        background: linear-gradient(135deg, rgba(255, 255, 255, .95), rgba(245, 250, 255, .95));
        color: #1a2332;
        border-left: 4px solid #1e40af;
        box-shadow: 0 12px 30px rgba(30, 64, 175, .1);
    }

    .finder-head .text-white-50 {
        color: #717f94 !important;
    }

    .finder-shell .btn-primary {
        background: #1e40af;
        border-color: #1e40af;
    }

    .finder-shell .btn-primary:hover {
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
        max-width: 1220px;
        margin-left: auto;
        margin-right: auto;
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

<div class="finder-shell">
    <div class="row">
        <div class="col-12">
            <div class="card finder-head mb-3">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h4 class="mb-1 font-weight-bold">Academic File Finder</h4>
                        <div class="text-white-50">Search files by subject, week, unit, and file type.</div>
                    </div>
                    <a href="{{ route('academic.management.index', ['session_id' => $selectedSession->id ?? null, 'subject_id' => $selectedSubject->id ?? null]) }}" class="btn btn-light btn-sm mt-2 mt-md-0">
                        Back To Workspace
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="get" action="{{ route('academic.management.finder') }}">
                        <input type="hidden" name="finder_search" value="1">

                        <div class="form-row">
                            <div class="col-md-6 mb-2">
                                <label class="mb-1">Session</label>
                                <select name="session_id" class="form-control" required>
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

                        @if($selectedSubject)
                            <div class="form-row">
                                <div class="col-md-3 mb-2">
                                    <label class="mb-1">Source</label>
                                    <select name="finder_scope" class="form-control form-control-sm">
                                        @foreach($finderScopes as $scopeValue => $scopeLabel)
                                            <option value="{{ $scopeValue }}" {{ ($finderFilters['scope'] ?? 'all') === $scopeValue ? 'selected' : '' }}>
                                                {{ $scopeLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="mb-1">Week</label>
                                    <select name="finder_week_id" class="form-control form-control-sm">
                                        <option value="0">All Weeks</option>
                                        @foreach($finderWeeks as $finderWeek)
                                            <option value="{{ $finderWeek->id }}" {{ (int) ($finderFilters['week_id'] ?? 0) === (int) $finderWeek->id ? 'selected' : '' }}>
                                                Week {{ $finderWeek->week_number }}{{ $finderWeek->title ? ' - '.$finderWeek->title : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="mb-1">Unit</label>
                                    <select name="finder_unit_id" class="form-control form-control-sm">
                                        <option value="0">All Units</option>
                                        @foreach($finderUnits as $finderUnit)
                                            <option value="{{ $finderUnit->id }}" {{ (int) ($finderFilters['unit_id'] ?? 0) === (int) $finderUnit->id ? 'selected' : '' }}>
                                                Unit {{ $finderUnit->unit_number }}{{ $finderUnit->title ? ' - '.$finderUnit->title : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="mb-1">Type</label>
                                    <select name="finder_type" class="form-control form-control-sm">
                                        <option value="">All Types</option>
                                        @foreach($finderTypeLabels as $typeValue => $typeLabel)
                                            <option value="{{ $typeValue }}" {{ ($finderFilters['type'] ?? '') === $typeValue ? 'selected' : '' }}>
                                                {{ $typeLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-9 mb-2">
                                    <label class="mb-1">Title / File Name</label>
                                    <input type="text" name="finder_q" class="form-control form-control-sm" value="{{ $finderFilters['query'] ?? '' }}" placeholder="Search text...">
                                </div>
                                <div class="col-md-3 mb-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-sm btn-block">Search Files</button>
                                </div>
                            </div>
                        @else
                            <div class="finder-empty mt-2">No subjects found in this session. Select another session.</div>
                        @endif
                    </form>

                    @if($selectedSubject && $finderSearchActive)
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="finder-subtle">Found {{ $finderResults->count() }} file(s).</div>
                            <a href="{{ route('academic.management.finder', ['session_id' => $selectedSession->id ?? null, 'subject_id' => $selectedSubject->id]) }}" class="btn btn-sm btn-outline-secondary">
                                Clear Filters
                            </a>
                        </div>

                        @if($finderResults->isEmpty())
                            <div class="finder-empty">No files matched your filters.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                    <tr>
                                        <th>Source / Type</th>
                                        <th>File</th>
                                        <th>Location</th>
                                        <th>Size</th>
                                        <th>Uploaded</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($finderResults as $finderRow)
                                        <tr>
                                            <td>
                                                <div class="font-weight-semibold">{{ $finderRow['source'] }}</div>
                                                <div class="finder-subtle">{{ $finderRow['type_label'] }}</div>
                                            </td>
                                            <td>
                                                <a href="{{ asset('storage/'.$finderRow['file_path']) }}" target="_blank">{{ $finderRow['title'] }}</a>
                                                @if(!empty($finderRow['original_name']))
                                                    <div class="finder-subtle">{{ $finderRow['original_name'] }}</div>
                                                @endif
                                            </td>
                                            <td class="finder-subtle">
                                                {{ $finderRow['week'] }} | {{ $finderRow['unit'] }} | {{ $finderRow['lesson'] }}
                                            </td>
                                            <td>{{ $finderRow['size_text'] }}</td>
                                            <td>{{ optional($finderRow['created_at'])->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
