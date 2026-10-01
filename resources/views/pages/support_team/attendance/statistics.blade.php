@extends('layouts.master')
@section('page_title', 'Attendance Statistics')
@section('content')

<style>
    .stats-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 25px;
    }

    .stats-header h2 {
        margin: 0 0 10px 0;
        font-size: 28px;
        font-weight: 800;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        border-left: 5px solid #667eea;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .stat-card.blue {
        border-left-color: #3b82f6;
    }

    .stat-card.green {
        border-left-color: #10b981;
    }

    .stat-card.purple {
        border-left-color: #8b5cf6;
    }

    .stat-card.orange {
        border-left-color: #f59e0b;
    }

    .stat-label {
        color: #718096;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .stat-value {
        color: #2d3748;
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 5px;
    }

    .stat-unit {
        color: #a0aec0;
        font-size: 12px;
    }

    .table-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }

    .table-header {
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        padding: 20px;
        border-bottom: 2px solid #e2e8f0;
    }

    .table-header h3 {
        margin: 0;
        color: #2d3748;
        font-size: 18px;
        font-weight: 700;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead th {
        padding: 15px 20px;
        text-align: left;
        font-weight: 700;
        color: #2d3748;
        border-bottom: 2px solid #e2e8f0;
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    }

    tbody td {
        padding: 15px 20px;
        border-bottom: 1px solid #e2e8f0;
        color: #4a5568;
    }

    tbody tr:hover {
        background: #f7fafc;
    }

    .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 50%;
        font-weight: 700;
        font-size: 14px;
    }

    .rank-badge.first {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    }

    .rank-badge.second {
        background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
        color: #374151;
    }

    .rank-badge.third {
        background: linear-gradient(135deg, #fca5a5 0%, #dc2626 100%);
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: #e2e8f0;
        color: #2d3748;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }

    .back-button:hover {
        background: #cbd5e0;
        color: #1a202c;
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
    <a href="{{ route('attendance.index') }}" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Attendance
    </a>

    {{-- الرأس --}}
    <div class="stats-header">
        <h2>Attendance Statistics</h2>
        <p style="margin: 10px 0 0 0; opacity: 0.9;">Overview of meeting participation and engagement</p>
    </div>

    {{-- البطاقات الإحصائية --}}
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-label">Total Records</div>
            <div class="stat-value">{{ $totalAttendances }}</div>
        </div>

        <div class="stat-card green">
            <div class="stat-label">Moderators</div>
            <div class="stat-value">{{ $totalModerated }}</div>
        </div>

        <div class="stat-card purple">
            <div class="stat-label">Participants</div>
            <div class="stat-value">{{ $totalStudents }}</div>
        </div>

        <div class="stat-card orange">
            <div class="stat-label">Total Talk Time</div>
            <div class="stat-value">{{ $totalTalkTime }}</div>
            <div class="stat-unit">hours</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Total Webcam Time</div>
            <div class="stat-value">{{ $totalWebcamTime }}</div>
            <div class="stat-unit">hours</div>
        </div>
    </div>

    {{-- جدول أكثر المشاركين فعالية --}}
    @if($topParticipants->count() > 0)
    <div class="table-card">
        <div class="table-header">
            <h3><i class="fas fa-trophy mr-2"></i> Top Participants</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">Rank</th>
                    <th>Name</th>
                    <th style="text-align: center;">Sessions</th>
                    <th style="text-align: center;">Avg Talk Time</th>
                    <th style="text-align: center;">Total Talk Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topParticipants as $index => $participant)
                <tr>
                    <td>
                        <span class="rank-badge {{ $index === 0 ? 'first' : ($index === 1 ? 'second' : ($index === 2 ? 'third' : '')) }}">
                            {{ $index + 1 }}
                        </span>
                    </td>
                    <td>
                        <strong>{{ $participant->name }}</strong>
                    </td>
                    <td style="text-align: center;">
                        {{ $participant->sessions }}
                    </td>
                    <td style="text-align: center;">
                        {{ round($participant->avg_talk_time / 60, 2) }} min
                    </td>
                    <td style="text-align: center;">
                        <strong>{{ round($participant->total_talk_time / 3600, 2) }} hours</strong>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <h3>No Participation Data</h3>
        <p>Upload attendance data to view statistics</p>
    </div>
    @endif
</div>

@endsection
