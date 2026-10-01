@extends('layouts.master')
@section('page_title', 'Attendance Records')
@section('content')

<style>
    .attendance-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .attendance-header h2 {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
    }

    .header-actions {
        display: flex;
        gap: 10px;
    }

    .btn-action {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 2px solid white;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-action:hover {
        background: white;
        color: #667eea;
    }

    .subject-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
        border-left: 5px solid #667eea;
        transition: all 0.3s ease;
    }

    .subject-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }

    .subject-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .subject-header h4 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
    }

    .subject-badges {
        display: flex;
        gap: 10px;
    }

    .date-card {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 8px;
        margin: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .date-card:hover {
        background: #e9ecef;
        transform: translateX(-5px);
    }

    .date-info h6 {
        margin: 0;
        color: #2d3748;
        font-weight: 600;
    }

    .date-badge {
        background: #e7f5ff;
        color: #0c4a6e;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .toggle-icon {
        margin-left: 10px;
        transition: transform 0.3s ease;
    }

    .toggle-icon.collapsed {
        transform: rotate(-90deg);
    }

    .details-table {
        width: 100%;
        margin-top: 10px;
        display: none;
    }

    .details-table.show {
        display: table;
    }

    .details-table thead th {
        background: #f0f2f5;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #2d3748;
        font-size: 13px;
        border-bottom: 2px solid #dee2e6;
    }

    .details-table tbody td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        color: #4a5568;
        font-size: 14px;
    }

    .details-table tbody tr:hover {
        background: rgba(102, 126, 234, 0.05);
    }

    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-moderator {
        background: #fce7f3;
        color: #be185d;
    }

    .badge-student {
        background: #dbeafe;
        color: #0c4a6e;
    }

    .action-buttons {
        display: flex;
        gap: 5px;
    }

    .btn-sm {
        padding: 6px 10px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .btn-info {
        background: #3b82f6;
        color: white;
    }

    .btn-info:hover {
        background: #2563eb;
    }

    .btn-danger {
        background: #ef4444;
        color: white;
    }

    .btn-danger:hover {
        background: #dc2626;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #718096;
    }

    .empty-state i {
        font-size: 50px;
        margin-bottom: 20px;
        color: #cbd5e0;
    }
</style>

<div class="container-fluid">
    {{-- Header --}}
    <div class="attendance-header">
        <div>
            <h2><i class="fas fa-clipboard-check"></i> Attendance Records</h2>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">View and manage meeting attendance</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('attendance.create') }}" class="btn-action">
                <i class="fas fa-upload"></i> Upload File
            </a>
           
        </div>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Error Message --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {!! session('error') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Subject Cards --}}
    @forelse($attendancesBySubject as $subject => $subjectData)
        <div class="subject-card">
            {{-- Subject Header --}}
            <div class="subject-header">
                <div>
                    <h4><i class="fas fa-book"></i> {{ $subject }}</h4>
                </div>
                <div class="subject-badges">
                    <span class="date-badge">
                        <i class="fas fa-calendar"></i>
                        {{ count($subjectData['dates']) }} Dates
                    </span>
                    <span class="date-badge" style="background: #e0f5ff; color: #0369a1;">
                        <i class="fas fa-users"></i>
                        {{ collect($subjectData['dates'])->sum(fn($records) => count($records)) }} Students
                    </span>
                </div>
            </div>

            {{-- Subject Content --}}
            <div style="padding: 20px;">
                {{-- Date Loop --}}
                @forelse($subjectData['dates'] as $date => $records)
                    <div class="date-card" onclick="toggleTable(this)">
                        <div class="date-info">
                            <h6>
                                <i class="fas fa-calendar-alt"></i>
                                {{ \Carbon\Carbon::parse($date)->format('l, j F Y') }}
                            </h6>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span class="date-badge">
                                {{ count($records) }} Students
                            </span>
                            <i class="fas fa-chevron-down toggle-icon collapsed"></i>
                        </div>
                    </div>

                    {{-- Details Table (Hidden by default) --}}
                    <div style="overflow-x: auto; margin-bottom: 20px;">
                        <table class="details-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Talk Time</th>
                                    <th>Webcam Time</th>
                                    <th>Duration</th>
                                    <th>Messages</th>
                                    <th>Reactions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($records as $key => $attendance)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <strong>{{ $attendance->name }}</strong>
                                            @if($attendance->user)
                                                <br>
                                                <small style="color: #718096;">{{ $attendance->user->username }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $attendance->moderator ? 'badge-moderator' : 'badge-student' }}">
                                                {{ $attendance->moderator ? '👑 Moderator' : '👤 Student' }}
                                            </span>
                                        </td>
                                        <td>{{ gmdate('H:i:s', $attendance->talk_time) }}</td>
                                        <td>{{ gmdate('H:i:s', $attendance->webcam_time) }}</td>
                                        <td>{{ gmdate('H:i:s', $attendance->duration) }}</td>
                                        <td>
                                            <span class="badge" style="background: #f0fdf4; color: #166534;">
                                                {{ $attendance->messages }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: #fef3c7; color: #b45309;">
                                                {{ $attendance->reactions }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('attendance.show', $attendance->id) }}" class="btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <form method="POST" action="{{ route('attendance.destroy', $attendance->id) }}" style="display: inline;" onsubmit="return confirm('Delete this record?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @empty
                    <p style="color: #718096; text-align: center; padding: 20px;">No dates for this subject</p>
                @endforelse
            </div>
        </div>
    @empty
        {{-- Empty State --}}
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>No Attendance Records Yet</h3>
            <p>Upload attendance data to get started</p>
            <a href="{{ route('attendance.create') }}" style="color: #667eea; text-decoration: none; font-weight: 600;">
                <i class="fas fa-plus"></i> Upload First File
            </a>
        </div>
    @endforelse
</div>

<script>
    function toggleTable(element) {
        // Get the next details-table after this date-card
        const nextTable = element.nextElementSibling.querySelector('.details-table');
        const icon = element.querySelector('.toggle-icon');
        
        if (nextTable) {
            nextTable.classList.toggle('show');
            icon.classList.toggle('collapsed');
        }
    }
</script>

@endsection
