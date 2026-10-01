@extends('layouts.master')

@section('content')
<style>
    :root {
        --adv-bg: #f2f6fb;
        --adv-card: #ffffff;
        --adv-line: #d9e4ef;
        --adv-text: #103352;
        --adv-sub: #6a7f94;
        --adv-navy: #123d7a;
        --adv-red: #c1263e;
    }

    .adv-shell {
        background: linear-gradient(140deg, #f8fbff, var(--adv-bg));
        border: 1px solid #dce7f2;
        border-radius: 14px;
        padding: 12px;
    }

    .adv-top {
        background: linear-gradient(120deg, var(--adv-navy), #1956a1);
        color: #fff;
        border-radius: 12px;
        padding: .72rem .9rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .adv-title {
        margin: 0;
        font-size: .96rem;
        font-weight: 800;
        line-height: 1.3;
    }

    .adv-subline {
        margin: 2px 0 0;
        font-size: .72rem;
        opacity: .95;
    }

    .adv-top-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .adv-btn {
        border: 1px solid rgba(255, 255, 255, .45);
        background: rgba(255, 255, 255, .15);
        color: #fff;
        border-radius: 8px;
        padding: .33rem .62rem;
        font-size: .72rem;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }

    .adv-btn:hover { color: #fff; text-decoration: none; background: rgba(255,255,255,.25); }
    .adv-btn-danger { background: rgba(193, 38, 62, .9); border-color: rgba(255, 255, 255, .2); }
    .adv-btn-danger:hover { background: rgba(163, 24, 45, .95); }

    .adv-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 10px;
    }

    .adv-card {
        background: var(--adv-card);
        border: 1px solid var(--adv-line);
        border-radius: 12px;
        box-shadow: 0 3px 10px rgba(18, 61, 122, .06);
        overflow: hidden;
    }

    .adv-card-head {
        background: linear-gradient(90deg, #f8fcff, #f1f7ff);
        border-bottom: 1px solid var(--adv-line);
        color: var(--adv-text);
        padding: .58rem .74rem;
        font-size: .76rem;
        font-weight: 800;
    }

    .adv-card-body {
        padding: .72rem .74rem;
    }

    .adv-kv {
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
    }

    .adv-kv-item {
        border: 1px solid #e1e9f3;
        border-radius: 8px;
        background: #fbfdff;
        padding: .45rem .55rem;
    }

    .adv-kv-label {
        display: block;
        font-size: .62rem;
        text-transform: uppercase;
        color: #7a8fa4;
        font-weight: 800;
        letter-spacing: .35px;
        margin-bottom: 2px;
    }

    .adv-kv-value {
        color: #1f4568;
        font-size: .77rem;
        font-weight: 700;
        line-height: 1.3;
    }

    .adv-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: .2rem .52rem;
        font-size: .64rem;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .adv-status-open { background: #fdecef; color: #a81f35; border-color: #f6d1d8; }
    .adv-status-in_progress { background: #e7f1ff; color: #1d5d95; border-color: #cfe0f7; }
    .adv-status-closed { background: #e8edf3; color: #556b80; border-color: #d5dee8; }
    .adv-status-pending { background: #fff0dc; color: #8f6512; border-color: #f4deaf; }

    .adv-priority-low { background: #e9f6ef; color: #1f7a4a; border-color: #cfead9; }
    .adv-priority-medium { background: #eef2ff; color: #4259b3; border-color: #d4ddf8; }
    .adv-priority-high { background: #ffe8ea; color: #ad2539; border-color: #f6c3cb; }
    .adv-priority-urgent { background: #c1263e; color: #fff; border-color: #c1263e; }

    .adv-chat-wrap {
        display: grid;
        grid-template-rows: auto 1fr auto;
        min-height: 520px;
    }

    .adv-chat-head {
        padding: .58rem .74rem;
        border-bottom: 1px solid var(--adv-line);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
    }

    .adv-chat-advisor {
        font-size: .77rem;
        color: #23496c;
        font-weight: 700;
    }

    .adv-chat-status {
        font-size: .7rem;
        color: #617991;
        font-weight: 600;
    }

    .adv-messages {
        background: #f8fbff;
        padding: .72rem;
        overflow-y: auto;
        max-height: 430px;
    }

    .adv-msg-row {
        display: flex;
        margin-bottom: .62rem;
    }

    .adv-msg-row.guardian { justify-content: flex-start; }
    .adv-msg-row.advisor { justify-content: flex-end; }

    .adv-msg {
        max-width: 76%;
        border: 1px solid transparent;
        border-radius: 10px;
        padding: .5rem .62rem;
        box-shadow: 0 2px 7px rgba(16, 51, 82, .06);
    }

    .adv-msg.guardian {
        background: #eaf2ff;
        border-color: #d2e1f8;
        color: #1f476b;
    }

    .adv-msg.advisor {
        background: #fceff1;
        border-color: #f4d3d8;
        color: #7f1f30;
    }

    .adv-msg-name {
        display: block;
        font-size: .62rem;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: .3px;
        margin-bottom: 3px;
        opacity: .86;
    }

    .adv-msg-text {
        margin: 0;
        font-size: .76rem;
        line-height: 1.35;
    }

    .adv-msg-time {
        display: block;
        margin-top: 4px;
        font-size: .62rem;
        opacity: .75;
    }

    .adv-empty {
        text-align: center;
        color: #7b8ea2;
        font-size: .78rem;
        padding: 1.3rem .8rem;
    }

    .adv-input {
        border-top: 1px solid var(--adv-line);
        background: #fff;
        padding: .62rem .74rem;
    }

    .adv-input .input-group .form-control {
        border: 1px solid #cfdeed;
        border-radius: 8px;
        font-size: .78rem;
        padding: .48rem .6rem;
        height: auto;
    }

    .adv-send {
        margin-left: 6px;
        border: none;
        border-radius: 8px;
        background: linear-gradient(120deg, var(--adv-red), #a81f35);
        color: #fff;
        font-size: .74rem;
        font-weight: 700;
        padding: .45rem .72rem;
    }

    .adv-send:hover { background: linear-gradient(120deg, #ab2036, #8f1a2d); }

    .adv-closed {
        border-top: 1px solid var(--adv-line);
        background: #fff6e7;
        color: #8a5b06;
        font-size: .74rem;
        font-weight: 700;
        padding: .6rem .72rem;
    }

    .adv-list {
        max-height: 190px;
        overflow-y: auto;
    }

    .adv-grade-item,
    .adv-note-item {
        border: 1px solid #dee8f2;
        border-radius: 8px;
        background: #fbfdff;
        padding: .42rem .55rem;
        margin-bottom: 6px;
    }

    .adv-grade-item:last-child,
    .adv-note-item:last-child { margin-bottom: 0; }

    .adv-grade-title,
    .adv-note-title {
        margin: 0 0 3px;
        color: #1f476a;
        font-size: .74rem;
        font-weight: 800;
    }

    .adv-grade-meta,
    .adv-note-meta {
        color: #70879e;
        font-size: .66rem;
        margin: 0;
    }

    .adv-note-content {
        color: #4f677f;
        font-size: .72rem;
        margin: 4px 0 0;
        line-height: 1.35;
    }

    @media (max-width: 992px) {
        .adv-grid {
            grid-template-columns: 1fr;
        }

        .adv-chat-wrap {
            min-height: 460px;
        }
    }
</style>

<div class="container-fluid py-3">
    <div class="adv-shell">
        <div class="adv-top">
            <div>
                <h1 class="adv-title"><i class="fas fa-comments"></i> {{ $conversation->subject }}</h1>
                <p class="adv-subline">
                    Student: {{ $conversation->student->name ?? 'N/A' }}
                    <span class="mx-1">|</span>
                    Advisor: {{ $conversation->advisor->name ?? 'Not assigned' }}
                </p>
            </div>
            <div class="adv-top-actions">
                @if($conversation->status !== 'closed')
                    <form method="POST" action="{{ route('advisory.close', $conversation->id) }}">
                        @csrf
                        <button type="submit" class="adv-btn adv-btn-danger">Close</button>
                    </form>
                @endif
                <a href="{{ route('advisory.index') }}" class="adv-btn">Back</a>
            </div>
        </div>

        <div class="adv-grid">
            <div>
                <div class="adv-card mb-2">
                    <div class="adv-card-head"><i class="fas fa-info-circle"></i> Conversation Details</div>
                    <div class="adv-card-body">
                        @php
                            $status = (string) $conversation->status;
                            $priority = (string) $conversation->priority;
                            $statusLabel = [
                                'open' => 'Open',
                                'in_progress' => 'In Progress',
                                'closed' => 'Closed',
                                'pending' => 'Pending',
                            ][$status] ?? ucfirst(str_replace('_', ' ', $status));
                            $priorityLabel = [
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ][$priority] ?? ucfirst($priority);
                        @endphp
                        <div class="adv-kv">
                            <div class="adv-kv-item">
                                <span class="adv-kv-label">Student</span>
                                <span class="adv-kv-value">{{ $conversation->student->name ?? 'N/A' }}</span>
                            </div>
                            <div class="adv-kv-item">
                                <span class="adv-kv-label">Guardian</span>
                                <span class="adv-kv-value">{{ $conversation->parent->name ?? 'N/A' }}</span>
                            </div>
                            <div class="adv-kv-item">
                                <span class="adv-kv-label">Advisor</span>
                                <span class="adv-kv-value">{{ $conversation->advisor->name ?? 'Not Assigned' }}</span>
                            </div>
                            <div class="adv-kv-item">
                                <span class="adv-kv-label">Status</span>
                                <span class="adv-badge adv-status-{{ $status }}">{{ $statusLabel }}</span>
                            </div>
                            <div class="adv-kv-item">
                                <span class="adv-kv-label">Priority</span>
                                <span class="adv-badge adv-priority-{{ $priority }}">{{ $priorityLabel }}</span>
                            </div>
                            <div class="adv-kv-item">
                                <span class="adv-kv-label">Created</span>
                                <span class="adv-kv-value">{{ $conversation->created_at->format('Y-m-d H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($grades->count() > 0)
                    <div class="adv-card mb-2">
                        <div class="adv-card-head"><i class="fas fa-chart-bar"></i> Recent Grades</div>
                        <div class="adv-card-body adv-list">
                            @foreach($grades as $grade)
                                <div class="adv-grade-item">
                                    <p class="adv-grade-title">{{ $grade->subject->name ?? 'N/A' }} - {{ $grade->total ?? 0 }}/100</p>
                                    <p class="adv-grade-meta">{{ $grade->exam->name ?? 'Exam' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($visibleNotes->count() > 0)
                    <div class="adv-card">
                        <div class="adv-card-head"><i class="fas fa-sticky-note"></i> Advisor Notes</div>
                        <div class="adv-card-body adv-list">
                            @foreach($visibleNotes as $note)
                                <div class="adv-note-item">
                                    <p class="adv-note-title">{{ $note->title ?: 'Note' }}</p>
                                    <p class="adv-note-meta">{{ $note->advisor->name ?? 'Advisor' }} | {{ $note->created_at->diffForHumans() }}</p>
                                    <p class="adv-note-content">{{ $note->content }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="adv-card adv-chat-wrap">
                <div class="adv-chat-head">
                    <div class="adv-chat-advisor">
                        <i class="fas fa-user-tie"></i> {{ $conversation->advisor->name ?? 'Advisor' }}
                    </div>
                    <div class="adv-chat-status">
                        {{ $conversation->messages->count() }} messages
                    </div>
                </div>

                <div class="adv-messages" id="messagesContainer">
                    @forelse($conversation->messages as $message)
                        <div class="adv-msg-row {{ $message->sender_type === 'guardian' ? 'guardian' : 'advisor' }}">
                            <div class="adv-msg {{ $message->sender_type === 'guardian' ? 'guardian' : 'advisor' }}">
                                <span class="adv-msg-name">{{ $message->sender->name ?? 'User' }}</span>
                                <p class="adv-msg-text">{{ $message->message }}</p>
                                <span class="adv-msg-time">{{ $message->created_at->format('h:i A') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="adv-empty">
                            <i class="fas fa-inbox"></i><br>
                            No messages yet.
                        </div>
                    @endforelse
                </div>

                @if($conversation->status !== 'closed')
                    <div class="adv-input">
                        <form action="{{ route('advisory.sendMessage', $conversation->id) }}" method="POST">
                            @csrf
                            <div class="input-group">
                                <input type="text" class="form-control" name="message" placeholder="Type your message..." required>
                                <button type="submit" class="adv-send">
                                    <i class="fas fa-paper-plane"></i> Send
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="adv-closed">
                        <i class="fas fa-lock"></i> This conversation is closed.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('messagesContainer');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });
</script>
@endsection
