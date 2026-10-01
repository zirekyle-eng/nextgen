@extends('layouts.master')

@section('title', 'My Children AI Tutor Progress')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">My Children - AI Tutor Progress</h4>
        <a href="{{ route('my_children') }}" class="btn btn-outline-primary btn-sm">Back to My Children</a>
    </div>

    @if($warning)
        <div class="alert alert-warning">{{ $warning }}</div>
    @endif

    @forelse($childrenProgress as $item)
        @php
            $studentRecord = $item['student'];
            $student = $studentRecord->user;
            $stats = $item['stats'];
            $quizzes = $item['latest_quizzes'];
            $lessons = $item['latest_lessons'];
            $studentCollapseId = 'studentProgress' . ($student ? $student->id : $loop->index);
        @endphp
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <span>{{ $student ? $student->name : 'Unknown Student' }}</span>
                    <small class="text-white-50 ml-2">
                        {{ optional($studentRecord->my_class)->name ?: 'Class N/A' }}
                    </small>
                </div>
                <button
                    class="btn btn-sm btn-outline-light"
                    type="button"
                    data-toggle="collapse"
                    data-target="#{{ $studentCollapseId }}"
                    aria-expanded="false"
                    aria-controls="{{ $studentCollapseId }}"
                >
                    Open / Close
                </button>
            </div>
            <div id="{{ $studentCollapseId }}" class="collapse">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-2 col-6 mb-2"><strong>Points:</strong> {{ $stats['total_points'] }}</div>
                    <div class="col-md-2 col-6 mb-2"><strong>Quizzes:</strong> {{ $stats['completed_quizzes'] }}</div>
                    <div class="col-md-3 col-6 mb-2"><strong>Avg Quiz:</strong> {{ $stats['avg_quiz_percentage'] }}%</div>
                    <div class="col-md-2 col-6 mb-2"><strong>Lessons In Progress:</strong> {{ $stats['lessons_in_progress'] }}</div>
                    <div class="col-md-3 col-12 mb-2"><strong>Completed Lessons:</strong> {{ $stats['lessons_completed'] }}</div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Unit</th>
                                <th>Lesson</th>
                                <th>Status</th>
                                <th>Started</th>
                                <th>Completed</th>
                                <th>Last Activity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lessons as $lesson)
                                <tr>
                                    <td>{{ $lesson['subject'] ?: '-' }}</td>
                                    <td>{{ $lesson['unit_name'] ?: '-' }}</td>
                                    <td>{{ $lesson['lesson_name'] ?: '-' }}</td>
                                    <td>
                                        @if($lesson['status'] === 'completed')
                                            <span class="badge badge-success">Completed</span>
                                        @elseif($lesson['status'] === 'in_progress')
                                            <span class="badge badge-warning">In Progress</span>
                                        @else
                                            <span class="badge badge-secondary">Not Started</span>
                                        @endif
                                    </td>
                                    <td>{{ $lesson['started_at'] ? \Carbon\Carbon::parse($lesson['started_at'])->diffForHumans() : '-' }}</td>
                                    <td>{{ $lesson['completed_at'] ? \Carbon\Carbon::parse($lesson['completed_at'])->diffForHumans() : '-' }}</td>
                                    <td>{{ $lesson['last_activity_at'] ? \Carbon\Carbon::parse($lesson['last_activity_at'])->diffForHumans() : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">No lesson activity yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <hr>

                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Scope</th>
                                <th>Score</th>
                                <th>Percentage</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($quizzes as $quiz)
                                <tr>
                                    <td>{{ $quiz->subject }}</td>
                                    <td>{{ $quiz->file_name ?: 'Subject Quiz' }}</td>
                                    <td>{{ $quiz->score }} / {{ $quiz->total }}</td>
                                    <td>{{ $quiz->percentage }}%</td>
                                    <td>{{ optional($quiz->created_at)->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No quiz results yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info mb-0">No children linked to this parent account yet.</div>
    @endforelse
</div>
@endsection
