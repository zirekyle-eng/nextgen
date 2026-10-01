@extends('layouts.master')

@section('content')
<style>
    .advm-page {
        max-width: 1260px;
        margin: 0 auto;
        padding: 8px 6px 14px;
    }

    .advm-hero {
        border-radius: 14px;
        background: linear-gradient(120deg, #0f2f66 0%, #1b4f9d 58%, #c32033 100%);
        color: #fff;
        padding: 16px 18px;
        margin-bottom: 12px;
        box-shadow: 0 10px 24px rgba(15, 47, 102, .2);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .advm-hero h2 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 800;
    }

    .advm-hero p {
        margin: 5px 0 0;
        font-size: .83rem;
        opacity: .93;
    }

    .advm-create {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 8px;
        padding: 8px 11px;
        font-size: .78rem;
        font-weight: 700;
        text-decoration: none;
        color: #fff;
        border: 1px solid rgba(255,255,255,.35);
        background: rgba(255,255,255,.16);
    }

    .advm-create:hover {
        color: #fff;
        background: rgba(255,255,255,.25);
    }

    .advm-shell {
        background: #fff;
        border: 1px solid #dce4f2;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(13, 34, 70, .06);
        overflow: hidden;
    }

    .advm-shell-head {
        border-bottom: 1px solid #e7edf8;
        padding: 10px 12px;
        background: #f8fbff;
        color: #173867;
        font-size: .88rem;
        font-weight: 800;
    }

    .advm-table-wrap {
        overflow-x: auto;
    }

    .advm-table {
        width: 100%;
        min-width: 1120px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .advm-table thead th {
        background: #f4f8ff;
        color: #35517f;
        border-bottom: 1px solid #dce5f4;
        font-size: .74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .35px;
        padding: 10px 8px;
    }

    .advm-table tbody td {
        border-bottom: 1px solid #e6edf8;
        padding: 10px 8px;
        font-size: .8rem;
        color: #2a446f;
        vertical-align: middle;
    }

    .advm-subject {
        margin: 0;
        color: #102f5d;
        font-weight: 800;
        font-size: .84rem;
        line-height: 1.35;
    }

    .advm-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border-radius: 999px;
        padding: 4px 8px;
        font-size: .68rem;
        font-weight: 800;
    }

    .st-open { background: #ffecef; color: #a62132; border: 1px solid #ffcdd5; }
    .st-progress { background: #eaf1ff; color: #1d467a; border: 1px solid #cedcf6; }
    .st-closed { background: #eaf7ee; color: #1f6f38; border: 1px solid #c6e8d1; }
    .st-pending { background: #fff7e8; color: #8d5e17; border: 1px solid #ffe2b5; }
    .st-default { background: #eef2f8; color: #4d638a; border: 1px solid #d4deec; }

    .pr-low { background: #eaf1ff; color: #1d467a; border: 1px solid #cedcf6; }
    .pr-medium { background: #fff7e8; color: #8d5e17; border: 1px solid #ffe2b5; }
    .pr-high { background: #ffecef; color: #a62132; border: 1px solid #ffcdd5; }
    .pr-urgent { background: #f0f2f6; color: #2a364a; border: 1px solid #d6dde9; }

    .advm-actions {
        display: inline-flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .advm-btn {
        border: 0;
        border-radius: 7px;
        padding: 5px 8px;
        font-size: .72rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        cursor: pointer;
    }

    .btn-view {
        background: #e9f1ff;
        color: #1f3f73;
        border: 1px solid #cedcf6;
    }

    .btn-assign {
        background: #eaf7ee;
        color: #1f6f38;
        border: 1px solid #c6e8d1;
    }

    .advm-muted {
        font-size: .72rem;
        color: #6a81a7;
    }

    .advm-footer {
        border-top: 1px solid #e7edf8;
        background: #fbfdff;
        padding: 10px 12px;
        display: flex;
        justify-content: center;
    }

    .advm-empty {
        border: 1px dashed #cfdcf0;
        border-radius: 10px;
        background: #fff;
        padding: 28px 15px;
        text-align: center;
        color: #5a6f95;
    }
</style>

<div class="advm-page">
    <section class="advm-hero">
        <div>
            <h2>Advisory Conversations</h2>
            <p>Track, assign, and follow up all advisory conversations in one place.</p>
        </div>
        <a href="{{ route('advisory.management.create') }}" class="advm-create">
            <i class="fas fa-plus-circle"></i> Start New Conversation
        </a>
    </section>

    @include('includes.alerts')

    @if($conversations->count() > 0)
        <section class="advm-shell">
            <div class="advm-shell-head">Conversations List</div>

            <div class="advm-table-wrap">
                <table class="advm-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Student</th>
                            <th>Guardian</th>
                            <th>Advisor</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Last Update</th>
                            <th>Action</th>
                            @if($isAdmin)
                                <th>Assign</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($conversations as $conversation)
                            @php
                                $statusClass = 'st-default';
                                $statusLabel = ucfirst((string)$conversation->status);
                                if ($conversation->status === 'open') { $statusClass = 'st-open'; $statusLabel = 'Open'; }
                                elseif ($conversation->status === 'in_progress') { $statusClass = 'st-progress'; $statusLabel = 'In Progress'; }
                                elseif ($conversation->status === 'closed') { $statusClass = 'st-closed'; $statusLabel = 'Closed'; }
                                elseif ($conversation->status === 'pending') { $statusClass = 'st-pending'; $statusLabel = 'Pending'; }

                                $priorityClass = 'pr-medium';
                                $priorityLabel = ucfirst((string)$conversation->priority);
                                if ($conversation->priority === 'low') { $priorityClass = 'pr-low'; $priorityLabel = 'Low'; }
                                elseif ($conversation->priority === 'medium') { $priorityClass = 'pr-medium'; $priorityLabel = 'Medium'; }
                                elseif ($conversation->priority === 'high') { $priorityClass = 'pr-high'; $priorityLabel = 'High'; }
                                elseif ($conversation->priority === 'urgent') { $priorityClass = 'pr-urgent'; $priorityLabel = 'Urgent'; }
                            @endphp
                            <tr>
                                <td>
                                    <p class="advm-subject">{{ $conversation->subject }}</p>
                                </td>
                                <td>{{ $conversation->student->name ?? 'N/A' }}</td>
                                <td>{{ $conversation->guardian->name ?? 'N/A' }}</td>
                                <td>
                                    @if($conversation->advisor)
                                        <strong style="color:#1d467a;">{{ $conversation->advisor->name }}</strong>
                                    @else
                                        <span class="advm-muted">Not Assigned</span>
                                    @endif
                                </td>
                                <td><span class="advm-pill {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                <td><span class="advm-pill {{ $priorityClass }}">{{ $priorityLabel }}</span></td>
                                <td><span class="advm-muted">{{ $conversation->updated_at->diffForHumans() }}</span></td>
                                <td>
                                    <a href="{{ route('advisory.management.show', $conversation->id) }}" class="advm-btn btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                                @if($isAdmin)
                                    <td>
                                        @if(!$conversation->advisor)
                                            <button class="advm-btn btn-assign openAssignModal" type="button" data-conversation-id="{{ $conversation->id }}">
                                                <i class="fas fa-user-check"></i> Assign
                                            </button>
                                        @else
                                            <span class="advm-muted" style="color:#1f6f38;font-weight:700;">Assigned</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="advm-footer">
                {{ $conversations->links() }}
            </div>
        </section>

        @foreach($conversations as $conversation)
            @if($isAdmin && !$conversation->advisor)
                <div class="modal fade" id="assignModal{{ $conversation->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content border-0" style="box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
                            <div class="modal-header" style="background: linear-gradient(120deg, #0f2f66 0%, #c32033 100%); color: white; border: none;">
                                <h5 class="modal-title"><i class="fas fa-user-check"></i> Assign Advisor</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('advisory.management.assignByAdmin', $conversation->id) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <p class="mb-2"><strong>Conversation:</strong> {{ $conversation->subject }}</p>
                                    <p class="mb-3"><strong>Student:</strong> {{ $conversation->student->name ?? 'N/A' }}</p>
                                    <div class="mb-0">
                                        <label class="form-label fw-bold">Select Advisor <span class="text-danger">*</span></label>
                                        <select class="form-select" name="advisor_id" required>
                                            <option value="">-- Choose Advisor --</option>
                                            @forelse($advisors as $advisor)
                                                <option value="{{ $advisor->id }}">{{ $advisor->name }}</option>
                                            @empty
                                                <option value="" disabled>No advisors available</option>
                                            @endforelse
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light border-top-0">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Assign</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @else
        <div class="advm-empty">
            <h5 style="margin:0 0 5px; color:#1b3a6e;">No Conversations</h5>
            <p style="margin:0;">No advisory conversations have been started yet.</p>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.openAssignModal');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            const conversationId = this.getAttribute('data-conversation-id');
            const modal = document.getElementById('assignModal' + conversationId);
            if (modal && window.bootstrap && bootstrap.Modal) {
                new bootstrap.Modal(modal).show();
            }
        });
    });
});
</script>
@endsection
