@extends('layouts.master')

@section('content')
<style>
    .advs-page {
        max-width: 1260px;
        margin: 0 auto;
        padding: 8px 6px 14px;
    }

    .advs-hero {
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

    .advs-hero h2 {
        margin: 0;
        font-size: 1.16rem;
        font-weight: 800;
        line-height: 1.35;
    }

    .advs-hero p {
        margin: 5px 0 0;
        font-size: .82rem;
        opacity: .93;
    }

    .advs-back {
        border: 1px solid rgba(255,255,255,.35);
        border-radius: 8px;
        padding: 7px 10px;
        font-size: .78rem;
        font-weight: 700;
        color: #fff;
        text-decoration: none;
        background: rgba(255,255,255,.16);
        display: inline-flex;
        gap: 5px;
        align-items: center;
    }

    .advs-back:hover {
        color: #fff;
        background: rgba(255,255,255,.25);
    }

    .advs-grid {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 12px;
    }

    .advs-card {
        background: #fff;
        border: 1px solid #dce4f2;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(13, 34, 70, .06);
        overflow: hidden;
    }

    .advs-card-head {
        border-bottom: 1px solid #e7edf8;
        background: #f8fbff;
        padding: 10px 12px;
        color: #173867;
        font-size: .88rem;
        font-weight: 800;
    }

    .advs-card-body {
        padding: 12px;
    }

    .advs-kv {
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
    }

    .advs-kv-item {
        border: 1px solid #e3eaf7;
        border-radius: 9px;
        background: #fbfdff;
        padding: 8px 10px;
    }

    .advs-kv-label {
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        color: #667ea4;
        font-weight: 700;
        margin-bottom: 2px;
    }

    .advs-kv-value {
        font-size: .82rem;
        color: #1f3a64;
        font-weight: 700;
        line-height: 1.35;
        word-break: break-word;
    }

    .advs-pill {
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

    .pr-low { background: #eaf1ff; color: #1d467a; border: 1px solid #cedcf6; }
    .pr-medium { background: #fff7e8; color: #8d5e17; border: 1px solid #ffe2b5; }
    .pr-high { background: #ffecef; color: #a62132; border: 1px solid #ffcdd5; }
    .pr-urgent { background: #f0f2f6; color: #2a364a; border: 1px solid #d6dde9; }

    .advs-form-row {
        display: flex;
        gap: 6px;
    }

    .advs-form-row .form-select,
    .advs-form-row .form-control {
        border-radius: 8px !important;
        border-color: #ccd8ec !important;
        font-size: .8rem;
    }

    .advs-form-row .btn {
        border-radius: 8px !important;
        font-size: .75rem !important;
        font-weight: 700 !important;
    }

    .advs-chat-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .advs-chat-head .advisor {
        font-size: .8rem;
        color: #35517f;
        font-weight: 700;
    }

    .advs-messages {
        height: 470px;
        overflow-y: auto;
        border: 1px solid #dce5f4;
        border-radius: 10px;
        background: #f9fbff;
        padding: 10px;
    }

    .advs-msg {
        display: flex;
        margin-bottom: 8px;
    }

    .advs-msg.from-parent {
        justify-content: flex-start;
    }

    .advs-msg.from-advisor {
        justify-content: flex-end;
    }

    .advs-bubble {
        max-width: 74%;
        border-radius: 10px;
        padding: 7px 9px;
        font-size: .8rem;
        line-height: 1.45;
        word-wrap: break-word;
        border: 1px solid #d9e4f5;
        background: #fff;
        color: #1f3a64;
    }

    .advs-bubble.from-advisor {
        background: #eaf7ee;
        border-color: #c6e8d1;
        color: #1f6f38;
    }

    .advs-bubble .name {
        font-size: .69rem;
        font-weight: 800;
        opacity: .85;
        margin-bottom: 2px;
        text-transform: uppercase;
    }

    .advs-bubble .time {
        font-size: .67rem;
        opacity: .7;
        margin-top: 2px;
    }

    .advs-compose {
        margin-top: 8px;
    }

    .advs-compose form {
        display: flex;
        gap: 6px;
    }

    .advs-compose input {
        flex: 1;
        border: 1px solid #ccd8ec;
        border-radius: 8px;
        padding: .6rem .7rem;
        font-size: .82rem;
    }

    .advs-compose button {
        border: 0;
        border-radius: 8px;
        padding: .6rem .85rem;
        font-size: .78rem;
        font-weight: 700;
        color: #fff;
        background: #0f2f66;
    }

    .advs-locked {
        margin-top: 8px;
        background: #fff7e8;
        border: 1px solid #ffe2b5;
        color: #8d5e17;
        border-radius: 8px;
        padding: 9px 10px;
        font-size: .78rem;
        font-weight: 700;
        text-align: center;
    }

    .advs-note {
        background: #f4f8ff;
        border: 1px solid #dce5f4;
        border-left: 3px solid #0f2f66;
        border-radius: 8px;
        padding: 8px 10px;
        margin-bottom: 8px;
    }

    .advs-note-title {
        font-size: .82rem;
        font-weight: 800;
        color: #173867;
        margin: 0 0 2px;
    }

    .advs-note-meta {
        font-size: .69rem;
        color: #6a81a7;
        margin-bottom: 4px;
    }

    .advs-note-content {
        font-size: .8rem;
        color: #1f3a64;
        margin: 0;
        line-height: 1.45;
    }

    @media (max-width: 1024px) {
        .advs-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@php
    $statusClass = 'st-pending';
    if ($conversation->status === 'open') { $statusClass = 'st-open'; }
    elseif ($conversation->status === 'in_progress') { $statusClass = 'st-progress'; }
    elseif ($conversation->status === 'closed') { $statusClass = 'st-closed'; }
    elseif ($conversation->status === 'pending') { $statusClass = 'st-pending'; }

    $priorityClass = 'pr-medium';
    if ($conversation->priority === 'low') { $priorityClass = 'pr-low'; }
    elseif ($conversation->priority === 'medium') { $priorityClass = 'pr-medium'; }
    elseif ($conversation->priority === 'high') { $priorityClass = 'pr-high'; }
    elseif ($conversation->priority === 'urgent') { $priorityClass = 'pr-urgent'; }
@endphp

<div class="advs-page">
    <section class="advs-hero">
        <div>
            <h2>{{ $conversation->subject }}</h2>
            <p>Conversation thread, status management, and advisor notes.</p>
        </div>
        <a href="{{ route('advisory.management.index') }}" class="advs-back">
            <i class="fas fa-arrow-left"></i> Back to Conversations
        </a>
    </section>

    @include('includes.alerts')

    <section class="advs-grid">
        <aside>
            <article class="advs-card">
                <div class="advs-card-head">Conversation Details</div>
                <div class="advs-card-body">
                    <div class="advs-kv">
                        <div class="advs-kv-item">
                            <div class="advs-kv-label">Student</div>
                            <div class="advs-kv-value">{{ $conversation->student->name ?? 'N/A' }}</div>
                        </div>
                        <div class="advs-kv-item">
                            <div class="advs-kv-label">Guardian</div>
                            <div class="advs-kv-value">{{ $conversation->guardian->name ?? 'N/A' }}</div>
                        </div>
                        <div class="advs-kv-item">
                            <div class="advs-kv-label">Advisor</div>
                            <div class="advs-kv-value">
                                @if($conversation->advisor)
                                    {{ $conversation->advisor->name }}
                                @else
                                    <span style="color:#a55c12;">Not Assigned</span>
                                @endif
                            </div>
                        </div>
                        <div class="advs-kv-item">
                            <div class="advs-kv-label">Status</div>
                            <div class="advs-kv-value">
                                <span class="advs-pill {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $conversation->status)) }}</span>
                            </div>
                        </div>
                        <div class="advs-kv-item">
                            <div class="advs-kv-label">Priority</div>
                            <div class="advs-kv-value">
                                <span class="advs-pill {{ $priorityClass }}">{{ ucfirst($conversation->priority) }}</span>
                            </div>
                        </div>
                        <div class="advs-kv-item">
                            <div class="advs-kv-label">Created</div>
                            <div class="advs-kv-value">{{ $conversation->created_at->format('M d, Y H:i') }}</div>
                        </div>
                    </div>

                    <form action="{{ route('advisory.management.updateStatus', $conversation->id) }}" method="POST" style="margin-top:10px;">
                        @csrf
                        <div class="advs-kv-label" style="margin-bottom:5px;">Update Status</div>
                        <div class="advs-form-row">
                            <select class="form-select" name="status" required>
                                <option value="open" {{ $conversation->status === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="in_progress" {{ $conversation->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="pending" {{ $conversation->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="closed" {{ $conversation->status === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                            <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i></button>
                        </div>
                    </form>

                    @if(!$conversation->advisor || $conversation->advisor->id !== Auth::id())
                        <form action="{{ route('advisory.management.assign', $conversation->id) }}" method="POST" style="margin-top:8px;">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block" style="border-radius:8px;font-size:.78rem;font-weight:700;">
                                <i class="fas fa-user-check mr-1"></i> Assign to Me
                            </button>
                        </form>
                    @endif
                </div>
            </article>
        </aside>

        <main>
            <article class="advs-card">
                <div class="advs-card-head">Conversation Chat</div>
                <div class="advs-card-body">
                    <div class="advs-chat-head">
                        <div class="advisor">
                            Advisor:
                            @if($conversation->advisor)
                                {{ $conversation->advisor->name }}
                            @else
                                Not Assigned
                            @endif
                        </div>
                    </div>

                    <div id="messagesContainer" class="advs-messages">
                        @forelse($conversation->messages as $message)
                            @php $isParent = $message->sender_type === 'guardian'; @endphp
                            <div class="advs-msg {{ $isParent ? 'from-parent' : 'from-advisor' }}" data-message-id="{{ $message->id }}">
                                <div class="advs-bubble {{ $isParent ? '' : 'from-advisor' }}">
                                    <div class="name">{{ $message->sender->name ?? 'User' }}</div>
                                    <div>{{ $message->message }}</div>
                                    <div class="time">{{ $message->created_at->format('h:i A') }}</div>
                                </div>
                            </div>
                        @empty
                            <div style="text-align:center;padding:20px 10px;color:#6a81a7;">
                                <i class="fas fa-inbox" style="font-size:1.6rem;opacity:.5;display:block;margin-bottom:6px;"></i>
                                No messages yet.
                            </div>
                        @endforelse
                    </div>

                    @if($conversation->status !== 'closed')
                        <div class="advs-compose">
                            <form action="{{ route('advisory.management.sendMessage', $conversation->id) }}" method="POST">
                                @csrf
                                <input type="text" name="message" placeholder="Type your message..." required>
                                <button type="submit"><i class="fas fa-paper-plane"></i> Send</button>
                            </form>
                        </div>
                    @else
                        <div class="advs-locked"><i class="fas fa-lock"></i> This conversation is closed.</div>
                    @endif
                </div>
            </article>

            @if($notes->count() > 0)
                <article class="advs-card">
                    <div class="advs-card-head">Notes & Observations</div>
                    <div class="advs-card-body">
                        @foreach($notes as $note)
                            <div class="advs-note">
                                @if($note->title)
                                    <p class="advs-note-title">{{ $note->title }}</p>
                                @endif
                                <div class="advs-note-meta">
                                    {{ $note->advisor->name ?? 'Advisor' }} - {{ $note->created_at->diffForHumans() }}
                                </div>
                                <p class="advs-note-content">{{ $note->content }}</p>
                                <div style="margin-top:5px;">
                                    <span class="advs-pill pr-low" style="font-size:.65rem;">
                                        @php
                                            $typeLabels = [
                                                'observation' => 'Observation',
                                                'recommendation' => 'Recommendation',
                                                'warning' => 'Warning',
                                                'praise' => 'Praise',
                                                'general' => 'General'
                                            ];
                                        @endphp
                                        {{ $typeLabels[$note->note_type] ?? $note->note_type }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            @endif
        </main>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const messagesContainer = document.getElementById('messagesContainer');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});
</script>
@endsection
