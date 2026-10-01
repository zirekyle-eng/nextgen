@extends('layouts.master')
@section('page_title', 'Attendance Report')
@section('content')

<style>
    .report-shell { max-width: 1200px; margin: 0 auto; }
    .report-card { border: 1px solid #e3ebf3; border-radius: 12px; box-shadow: 0 4px 14px rgba(17, 46, 79, 0.06); }
    .report-card .card-body { padding: 1rem 1.2rem; }
    .summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
    .summary-tile { border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px; text-align: center; background: #f9fafb; }
    .summary-tile strong { display: block; font-size: 0.95rem; }
    .summary-tile span { font-size: 1.2rem; font-weight: 700; }
    .report-meta { font-size: 0.86rem; color: #4b5563; }
    .table-sm th, .table-sm td { vertical-align: middle; }
    @media (max-width: 768px) { .summary-grid { grid-template-columns: 1fr; } }
</style>

<div class="container-fluid report-shell">
    <div class="page-titles mb-4">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="text-heading">Attendance Report</h4>
            </div>
            <div class="col-sm-6 text-sm-right">
                <a href="{{ route('my_children') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-arrow-left"></i> Back to My Children
                </a>
            </div>
        </div>
    </div>

    <div class="card report-card mb-3">
        <div class="card-body">
            <form method="get" action="{{ route('my_children.attendance_report', $student->id) }}" class="mb-3">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-2">
                        <label class="small text-muted">Term</label>
                        <select name="term" class="form-control form-control-sm">
                            <option value="" {{ empty($selectedTerm) ? 'selected' : '' }}>Current term (auto)</option>
                            <option value="1" {{ (string) $selectedTerm === '1' ? 'selected' : '' }}>Term 1</option>
                            <option value="2" {{ (string) $selectedTerm === '2' ? 'selected' : '' }}>Term 2</option>
                            <option value="3" {{ (string) $selectedTerm === '3' ? 'selected' : '' }}>Term 3</option>
                            <option value="last30" {{ (string) $selectedTerm === 'last30' ? 'selected' : '' }}>Last 30 days</option>
                        </select>
                    </div>
                    <div class="col-md-5 mb-2">
                        <label class="small text-muted">Subject</label>
                        <select name="subject" class="form-control form-control-sm">
                            <option value="">All subjects</option>
                            @foreach($subjects as $subjectId => $subjectName)
                                <option value="{{ $subjectId }}" {{ (string) $selectedSubject === (string) $subjectId ? 'selected' : '' }}>
                                    {{ $subjectName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fa fa-filter"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </form>

            <div class="row mb-3">
                <div class="col-md-6">
                    <h5 class="mb-1">{{ $student->name }}</h5>
                    <div class="report-meta">
                        {{ $record->my_class->name ?? '-' }} - {{ $record->section->name ?? '-' }}
                    </div>
                </div>
                <div class="col-md-6 text-md-right report-meta">
                    <div>{{ $termLabel }}</div>
                    <div>{{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}</div>
                </div>
            </div>

            <div class="summary-grid mb-4">
                <div class="summary-tile">
                    <strong>Present</strong>
                    <span>{{ $summary['present'] }}</span>
                </div>
                <div class="summary-tile">
                    <strong>Late</strong>
                    <span>{{ $summary['late'] }}</span>
                </div>
                <div class="summary-tile">
                    <strong>Absent</strong>
                    <span>{{ $summary['absent'] }}</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Subject</th>
                            <th>Meeting</th>
                            <th>Join</th>
                            <th>Left</th>
                            <th>Cam Opens</th>
                            <th>Cam First</th>
                            <th>Cam Last</th>
                            <th>Status</th>
                            <th>Late (min)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $row['date'] }}</td>
                                <td>{{ $row['subject'] }}</td>
                                <td>{{ $row['meeting'] }}</td>
                                <td>{{ $row['join_at'] }}</td>
                                <td>{{ $row['left_at'] }}</td>
                                <td>{{ $row['camera_on_count'] }}</td>
                                <td>{{ $row['first_camera_on_at'] }}</td>
                                <td>{{ $row['last_camera_on_at'] }}</td>
                                <td>{{ ucfirst($row['status']) }}</td>
                                <td>{{ $row['late_minutes'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-muted text-center">No attendance records for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
