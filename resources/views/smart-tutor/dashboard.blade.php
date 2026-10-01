@extends('layouts.master')

@section('title', 'Smart Tutor Dashboard')

@section('content')
<style>
    .st-header {
        background: linear-gradient(120deg, #f7fbff, #eef4ff);
        border: 1px solid #dbe8ff;
        border-radius: 14px;
        padding: 1rem 1.2rem;
        margin-bottom: 1rem;
    }

    .st-header h4 {
        margin: 0;
        color: #16325c;
        font-weight: 700;
    }

    .st-shell.card {
        border-radius: 14px;
        border: 1px solid #e6ecf5 !important;
        box-shadow: 0 10px 25px rgba(20, 40, 70, 0.06) !important;
    }

    .st-shell > .card-header {
        background: #1f4b8f !important;
        color: #fff !important;
        border-radius: 14px 14px 0 0 !important;
        font-weight: 700;
        box-shadow: none !important;
    }

    .subject-card {
        border: 1px solid #e7edf7 !important;
        border-radius: 12px !important;
        overflow: hidden;
        margin-bottom: .7rem;
    }

    .subject-card .card-header {
        background: #f8fbff !important;
        color: #0f2b4f !important;
        border-bottom: 1px solid #e7edf7 !important;
        padding: .8rem 1rem !important;
        box-shadow: none !important;
    }

    .subject-toggle {
        color: #0f2b4f !important;
        font-weight: 700 !important;
    }

    .subject-toggle:hover {
        color: #1f4b8f !important;
        text-decoration: none !important;
    }

    .file-count {
        font-size: .8rem;
        color: #4f6b90;
        background: #e8f0ff;
        border: 1px solid #d4e3ff;
        border-radius: 999px;
        padding: .2rem .55rem;
    }

    .st-table thead th {
        background: #f4f8ff;
        color: #2e4f78;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .st-table tbody td {
        vertical-align: middle;
    }

    .status-chip {
        font-size: .72rem;
        padding: .3rem .55rem;
        border-radius: 999px;
        font-weight: 700;
        display: inline-block;
    }

    .status-chip.completed {
        background: #dff6e8;
        color: #157347;
        border: 1px solid #bfe8cf;
    }

    .status-chip.progress {
        background: #fff7db;
        color: #8a6d1f;
        border: 1px solid #ffe8aa;
    }

    .status-chip.new {
        background: #ecf0f6;
        color: #5e6f86;
        border: 1px solid #d9e1ec;
    }

    .subject-progress-wrap {
        min-width: 210px;
    }

    .subject-progress-text {
        font-size: 12px;
        font-weight: 700;
        color: #0d3b75;
        text-align: right;
        margin-bottom: 4px;
    }

    .subject-progress-bar {
        width: 100%;
        height: 8px;
        background: #e8eef6;
        border-radius: 999px;
        overflow: hidden;
        border: 1px solid #d8e0eb;
    }

    .subject-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #1f78b5 0%, #0d3b75 70%, #e3202f 100%);
        border-radius: 999px;
    }
</style>

<div class="container-fluid">
    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    <div class="st-header d-flex justify-content-between align-items-center">
        <h4>{{ !empty($isParentTutorView) ? 'Child Subjects List' : 'Subjects List' }}</h4>
        <a href="{{ route('smart_tutor.app', !empty($isParentTutorView) && !empty($selectedChildId) ? ['child_id' => $selectedChildId] : []) }}" class="btn btn-primary btn-sm">Open Smart Tutor</a>
    </div>

    @if(!empty($isParentTutorView))
        <div class="card st-shell mb-3">
            <div class="card-body">
                @if($childrenForParent->isEmpty())
                    <div class="text-muted">No child is linked to this parent account yet.</div>
                @else
                    <form method="GET" action="{{ route('smart_tutor.dashboard') }}" class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="small text-muted mb-1">Choose Child</label>
                            <select class="form-control" name="child_id" onchange="this.form.submit()">
                                @foreach($childrenForParent as $childRecord)
                                    @php $childUser = $childRecord->user; @endphp
                                    @if($childUser)
                                        <option value="{{ $childUser->id }}" {{ (int)$selectedChildId === (int)$childUser->id ? 'selected' : '' }}>
                                            {{ $childUser->name }} - {{ optional($childRecord->my_class)->name ?: 'Class N/A' }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endif

    <div class="card st-shell">
        <div class="card-header">Subjects, Units and Lessons</div>
        <div class="card-body">
            @if($subjectStructure->isEmpty())
                <div class="text-muted">No subjects with units/lessons are available for this student yet.</div>
            @else
                @foreach($subjectStructure as $subject => $subjectNode)
                    @php $collapseId = 'subject_' . \Illuminate\Support\Str::slug($subject) . '_' . $loop->index; @endphp
                    @php
                        $units = collect($subjectNode['units'] ?? []);
                        $totalUnits = $units->count();
                        $totalLessons = $units->sum(function ($unit) {
                            return collect($unit['lessons'] ?? [])->count();
                        });
                        $completedLessons = $units->sum(function ($unit) {
                            return collect($unit['lessons'] ?? [])->where('status', 'completed')->count();
                        });
                        $progressPercent = $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0;
                    @endphp
                    <div class="card subject-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <button class="btn btn-link p-0 text-decoration-none subject-toggle" type="button" data-toggle="collapse" data-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                                {{ $subject }}
                            </button>
                            <div class="subject-progress-wrap">
                                <div class="subject-progress-text">{{ $progressPercent }}% ({{ $completedLessons }}/{{ $totalLessons }})</div>
                                <div class="subject-progress-bar">
                                    <div class="subject-progress-fill" style="width: {{ $progressPercent }}%;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="collapse" id="{{ $collapseId }}">
                            <div class="p-3">
                                @if($units->isEmpty())
                                    <div class="text-muted small">No units added for this subject yet.</div>
                                @else
                                    @foreach($units as $unit)
                                        <div class="border rounded p-3 mb-2 bg-white">
                                            <div class="font-weight-bold mb-2">
                                                Week {{ $unit['week_number'] }} - Unit {{ $unit['unit_number'] }}: {{ $unit['title'] }}
                                                @if(!empty($unit['week_is_blocked']))
                                                    <span class="badge badge-danger ml-2">Blocked</span>
                                                    @if(!empty($unit['week_block_note']))
                                                        <small class="text-muted ml-1">({{ $unit['week_block_note'] }})</small>
                                                    @endif
                                                @endif
                                            </div>
                                            @if(!empty($unit['week_start_date']) || !empty($unit['week_end_date']))
                                                <div class="small text-muted mb-2">
                                                    {{ $unit['week_start_date'] ?: '-' }} to {{ $unit['week_end_date'] ?: '-' }}
                                                </div>
                                            @endif
                                            @php $lessons = collect($unit['lessons'] ?? []); @endphp
                                            @if($lessons->isEmpty())
                                                <div class="text-muted small">No lessons in this unit yet.</div>
                                            @else
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-striped mb-0 st-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Lesson</th>
                                                                <th>Status</th>
                                                                <th>Timeline</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($lessons as $lesson)
                                                                @php
                                                                    $status = (string) ($lesson['status'] ?? 'not_started');
                                                                    $actionLabel = $status === 'completed' ? 'Review' : ($status === 'in_progress' ? 'Continue' : 'Start');
                                                                    $actionClass = $status === 'completed' ? 'btn-outline-success' : ($status === 'in_progress' ? 'btn-outline-warning' : 'btn-primary');
                                                                    $isWeekBlocked = !empty($unit['week_is_blocked']);
                                                                @endphp
                                                                <tr>
                                                                    <td>Lesson {{ $lesson['lesson_number'] }}: {{ $lesson['title'] }}</td>
                                                                    <td>
                                                                        @if($status === 'completed')
                                                                            <span class="status-chip completed">Completed</span>
                                                                        @elseif($status === 'in_progress')
                                                                            <span class="status-chip progress">In Progress</span>
                                                                        @else
                                                                            <span class="status-chip new">Not Started</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="small text-muted">
                                                                        Start: {{ !empty($lesson['started_at']) ? \Carbon\Carbon::parse($lesson['started_at'])->format('Y-m-d H:i') : '-' }}<br>
                                                                        End: {{ !empty($lesson['completed_at']) ? \Carbon\Carbon::parse($lesson['completed_at'])->format('Y-m-d H:i') : '-' }}
                                                                    </td>
                                                                    <td>
                                                                        @if($isWeekBlocked)
                                                                            <button class="btn btn-sm btn-outline-secondary" type="button" disabled>Blocked</button>
                                                                        @else
                                                                            <a class="btn btn-sm {{ $actionClass }}"
                                                                               href="{{ route('smart_tutor.app', array_merge(['subject' => $subject, 'unit_id' => $unit['id'], 'lesson_id' => $lesson['id']], !empty($isParentTutorView) && !empty($selectedChildId) ? ['child_id' => $selectedChildId] : [])) }}">
                                                                                {{ $actionLabel }}
                                                                            </a>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection
