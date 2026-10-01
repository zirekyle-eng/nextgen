@extends('layouts.master')
@section('page_title', 'Attendance Details')
@section('content')

<style>
    .detail-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .detail-header h2 {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .detail-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border-top: 4px solid #667eea;
    }

    .detail-item {
        margin-bottom: 20px;
    }

    .detail-item:last-child {
        margin-bottom: 0;
    }

    .detail-label {
        color: #718096;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
        display: block;
    }

    .detail-value {
        color: #2d3748;
        font-size: 18px;
        font-weight: 700;
    }

    .badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-moderator {
        background: #dbeafe;
        color: #0c4a6e;
    }

    .badge-student {
        background: #dcfce7;
        color: #15803d;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .stat-box {
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        padding: 20px;
        border-radius: 10px;
        border-left: 4px solid #667eea;
    }

    .stat-box.active {
        border-left-color: #10b981;
    }

    .stat-box.message {
        border-left-color: #3b82f6;
    }

    .stat-box.reaction {
        border-left-color: #f59e0b;
    }

    .stat-number {
        font-size: 24px;
        font-weight: 800;
        color: #2d3748;
        margin-bottom: 3px;
    }

    .stat-name {
        font-size: 12px;
        color: #718096;
        font-weight: 600;
        text-transform: uppercase;
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
    }

    .actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-danger {
        background: #ef4444;
        color: white;
    }

    .btn-danger:hover {
        background: #dc2626;
    }
</style>

<div class="container-fluid">
    <a href="{{ route('attendance.index') }}" class="back-button">
        <i class="fas fa-arrow-left"></i> Back
    </a>

    {{-- الرأس --}}
    <div class="detail-header">
        <div>
            <h2>{{ $attendance->name }}</h2>
            <p style="margin: 8px 0 0 0; opacity: 0.9;">
                <span class="badge {{ $attendance->moderator ? 'badge-moderator' : 'badge-student' }}">
                    {{ $attendance->moderator ? 'Moderator' : 'Student' }}
                </span>
            </p>
        </div>
    </div>

    {{-- المعلومات الأساسية --}}
    <div class="detail-grid">
        <div class="detail-card">
            <div class="detail-item">
                <span class="detail-label">Subject</span>
                <div class="detail-value">{{ $attendance->subject_name }}</div>
            </div>
            <div class="detail-item">
                <span class="detail-label">Date</span>
                <div class="detail-value">{{ $attendance->attendance_date->format('d M Y') }}</div>
            </div>
            <div class="detail-item">
                <span class="detail-label">System User</span>
                <div class="detail-value">
                    @if($attendance->user)
                        <a href="#" style="color: #667eea; text-decoration: none;">
                            {{ $attendance->user->name }}
                        </a>
                    @else
                        <span style="color: #a0aec0;">Not linked</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="detail-card">
            <div class="detail-item">
                <span class="detail-label">Join Time</span>
                <div class="detail-value">
                    @if($attendance->join_time)
                        {{ $attendance->join_time->format('H:i:s') }}
                    @else
                        <span style="color: #a0aec0;">-</span>
                    @endif
                </div>
            </div>
            <div class="detail-item">
                <span class="detail-label">Left Time</span>
                <div class="detail-value">
                    @if($attendance->left_time)
                        {{ $attendance->left_time->format('H:i:s') }}
                    @else
                        <span style="color: #a0aec0;">-</span>
                    @endif
                </div>
            </div>
            <div class="detail-item">
                <span class="detail-label">Total Duration</span>
                <div class="detail-value">{{ $attendance->getDurationInMinutes() }} min</div>
            </div>
        </div>
    </div>

    {{-- الإحصائيات --}}
    <div class="detail-card" style="margin-bottom: 30px;">
        <h3 style="margin: 0 0 20px 0; color: #2d3748; font-size: 18px; font-weight: 700;">
            Engagement Metrics
        </h3>
        <div class="stats-grid">
            <div class="stat-box active">
                <div class="stat-number">{{ $attendance->activity_score }}</div>
                <div class="stat-name">Activity Score</div>
            </div>

            <div class="stat-box">
                <div class="stat-number">{{ $attendance->getTalkTimeInMinutes() }}</div>
                <div class="stat-name">Talk Time (min)</div>
            </div>

            <div class="stat-box">
                <div class="stat-number">{{ $attendance->getWebcamTimeInMinutes() }}</div>
                <div class="stat-name">Webcam Time (min)</div>
            </div>

            <div class="stat-box message">
                <div class="stat-number">{{ $attendance->messages }}</div>
                <div class="stat-name">Messages</div>
            </div>

            <div class="stat-box reaction">
                <div class="stat-number">{{ $attendance->reactions }}</div>
                <div class="stat-name">Reactions</div>
            </div>

            <div class="stat-box">
                <div class="stat-number">{{ $attendance->poll_votes }}</div>
                <div class="stat-name">Poll Votes</div>
            </div>

            <div class="stat-box">
                <div class="stat-number">{{ $attendance->raise_hands }}</div>
                <div class="stat-name">Raise Hands</div>
            </div>
        </div>
    </div>

    {{-- الإجراءات --}}
    <div class="actions">
        <form method="POST" action="{{ route('attendance.destroy', $attendance->id) }}" style="display: inline;" onsubmit="return confirm('Delete this record?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Delete Record
            </button>
        </form>
    </div>
</div>

@endsection
