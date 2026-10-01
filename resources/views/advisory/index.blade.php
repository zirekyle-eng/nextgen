@extends('layouts.master')

@section('content')
<style>
    :root {
        --ad-bg: #f2f6fb;
        --ad-card: #ffffff;
        --ad-line: #d9e4f0;
        --ad-text: #0f2f53;
        --ad-sub: #6a7f97;
        --ad-navy: #123d7a;
        --ad-red: #c1263e;
    }

    .ad-shell {
        background: linear-gradient(145deg, #f7fbff, var(--ad-bg));
        border: 1px solid #dce6f2;
        border-radius: 14px;
        padding: 14px;
        font-family: "Poppins", "Tahoma", sans-serif;
    }

    .ad-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }

    .ad-title {
        margin: 0;
        color: var(--ad-text);
        font-size: 1.05rem;
        font-weight: 800;
        letter-spacing: .2px;
    }

    .ad-subtitle {
        margin: 2px 0 0;
        color: var(--ad-sub);
        font-size: .78rem;
        font-weight: 500;
    }

    .ad-btn {
        background: linear-gradient(120deg, var(--ad-navy), #19529f);
        color: #fff;
        border: 1px solid #164b91;
        border-radius: 10px;
        padding: .46rem .85rem;
        font-size: .78rem;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }

    .ad-btn:hover {
        color: #fff;
        background: linear-gradient(120deg, #0f3368, #154786);
        text-decoration: none;
    }

    .ad-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .ad-card {
        background: var(--ad-card);
        border: 1px solid var(--ad-line);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(17, 52, 88, .06);
    }

    .ad-card-head {
        padding: .68rem .82rem;
        border-bottom: 1px solid var(--ad-line);
        background: linear-gradient(90deg, #fafdff, #f2f8ff);
    }

    .ad-subject {
        margin: 0 0 4px;
        color: #11365d;
        font-size: .88rem;
        font-weight: 800;
        line-height: 1.3;
    }

    .ad-meta {
        color: var(--ad-sub);
        font-size: .74rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ad-card-body {
        padding: .7rem .82rem .65rem;
    }

    .ad-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 8px;
    }

    .ad-badge {
        border-radius: 999px;
        padding: .2rem .52rem;
        font-size: .68rem;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .ad-status-open { background: #e7f3ff; color: #1d5f9a; border-color: #cfe4fb; }
    .ad-status-in_progress { background: #fff3dd; color: #8f6512; border-color: #f4dfb1; }
    .ad-status-closed { background: #e9eef4; color: #4e647b; border-color: #d4dee8; }
    .ad-status-pending { background: #fde9ed; color: #9d2238; border-color: #f4ccd6; }

    .ad-priority-low { background: #e9f6ef; color: #1f7a4a; border-color: #cfead9; }
    .ad-priority-medium { background: #edf1ff; color: #4157b3; border-color: #d4dcf8; }
    .ad-priority-high { background: #ffeaea; color: #ac2538; border-color: #f7c5cd; }
    .ad-priority-urgent { background: #c1263e; color: #fff; border-color: #c1263e; }

    .ad-preview {
        border: 1px solid #dfebf7;
        border-left: 4px solid #2b67ac;
        border-radius: 8px;
        background: #f8fbff;
        padding: .45rem .58rem;
        margin-bottom: 9px;
    }

    .ad-preview-label {
        display: block;
        color: #607992;
        font-size: .64rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        margin-bottom: 3px;
    }

    .ad-preview-text {
        margin: 0;
        font-size: .75rem;
        color: #47627d;
        line-height: 1.35;
    }

    .ad-card-foot {
        padding: .58rem .82rem;
        border-top: 1px solid var(--ad-line);
        background: #fbfdff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
    }

    .ad-time {
        color: #6f849b;
        font-size: .72rem;
        font-weight: 600;
    }

    .ad-view {
        background: linear-gradient(120deg, var(--ad-red), #a81f35);
        color: #fff;
        border: 1px solid #9d1d32;
        border-radius: 8px;
        padding: .3rem .62rem;
        font-size: .71rem;
        font-weight: 700;
        text-decoration: none;
    }

    .ad-view:hover {
        color: #fff;
        text-decoration: none;
        background: linear-gradient(120deg, #ab2036, #8f1a2d);
    }

    .ad-empty {
        background: #fff;
        border: 1px dashed #cfdceb;
        border-radius: 12px;
        text-align: center;
        padding: 2rem 1rem;
    }

    .ad-empty i {
        font-size: 2.1rem;
        color: #aab9c9;
        margin-bottom: .55rem;
    }

    .ad-empty h5 {
        margin: 0 0 .35rem;
        color: #214264;
        font-size: .98rem;
        font-weight: 800;
    }

    .ad-empty p {
        margin: 0 0 .9rem;
        font-size: .8rem;
        color: #6f8398;
    }

    .ad-pagination {
        margin-top: 12px;
        display: flex;
        justify-content: center;
    }

    @media (max-width: 991px) {
        .ad-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575px) {
        .ad-topbar {
            flex-direction: column;
            align-items: stretch;
        }

        .ad-btn {
            text-align: center;
        }
    }
</style>

<div class="container-fluid py-3">
    <div class="ad-shell">
        <div class="ad-topbar">
            <div>
                <h1 class="ad-title"><i class="fas fa-comments"></i> Parent Advisory</h1>
                <p class="ad-subtitle">Compact view of all your advisory conversations</p>
            </div>
            <a href="{{ route('advisory.create') }}" class="ad-btn">
                <i class="fas fa-plus-circle"></i> New Conversation
            </a>
        </div>

        @if($conversations->count() > 0)
            <div class="ad-grid">
                @foreach($conversations as $conversation)
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
                    <div class="ad-card">
                        <div class="ad-card-head">
                            <h5 class="ad-subject">{{ $conversation->subject }}</h5>
                            <div class="ad-meta">
                                <span><i class="fas fa-user-circle"></i> {{ $conversation->student->name ?? 'N/A' }}</span>
                                <span><i class="fas fa-user-tie"></i> {{ $conversation->advisor->name ?? 'Advisor' }}</span>
                            </div>
                        </div>

                        <div class="ad-card-body">
                            <div class="ad-badges">
                                <span class="ad-badge ad-status-{{ $status }}">{{ $statusLabel }}</span>
                                <span class="ad-badge ad-priority-{{ $priority }}">{{ $priorityLabel }}</span>
                            </div>

                            <div class="ad-preview">
                                <span class="ad-preview-label">Latest Message</span>
                                <p class="ad-preview-text">
                                    @if($conversation->latestMessage)
                                        {{ \Illuminate\Support\Str::limit((string) $conversation->latestMessage->message, 120) }}
                                    @else
                                        No messages yet.
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="ad-card-foot">
                            <span class="ad-time"><i class="fas fa-clock"></i> {{ $conversation->created_at->diffForHumans() }}</span>
                            <a href="{{ route('advisory.show', $conversation->id) }}" class="ad-view">
                                View
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="ad-pagination">
                {{ $conversations->links() }}
            </div>
        @else
            <div class="ad-empty">
                <i class="fas fa-inbox"></i>
                <h5>No Conversations Yet</h5>
                <p>Start your first advisory conversation with one click.</p>
                <a href="{{ route('advisory.create') }}" class="ad-btn">
                    <i class="fas fa-plus-circle"></i> Create Conversation
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
