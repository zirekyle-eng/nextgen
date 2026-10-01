@extends('layouts.master')

@section('content')
@php
    $isStudent = strtolower((string) optional(auth()->user())->user_type) === 'student';
    $isTeacher = strtolower((string) optional(auth()->user())->user_type) === 'teacher';
    $schedule = json_decode((string) $material->time_table, true);
@endphp

<style>
    .ics-page {
        max-width: 1160px;
        margin: 0 auto;
        padding: 6px 4px 14px;
    }

    .ics-hero {
        border-radius: 14px;
        background: linear-gradient(120deg, #0f2f66 0%, #1b4f9d 58%, #c32033 100%);
        color: #fff;
        padding: 16px 18px;
        margin-bottom: 12px;
        box-shadow: 0 10px 24px rgba(15, 47, 102, .2);
    }

    .ics-hero-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 10px;
    }

    .ics-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #fff;
        text-decoration: none;
        font-size: .8rem;
        font-weight: 700;
        background: rgba(255,255,255,.17);
        border: 1px solid rgba(255,255,255,.28);
        border-radius: 8px;
        padding: 7px 10px;
    }

    .ics-back:hover {
        color: #fff;
        background: rgba(255,255,255,.25);
    }

    .ics-title {
        margin: 0;
        font-size: 1.25rem;
        line-height: 1.4;
        font-weight: 800;
    }

    .ics-meta {
        margin-top: 10px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 8px;
    }

    .ics-meta-item {
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 9px;
        padding: 8px 10px;
    }

    .ics-meta-label {
        font-size: .68rem;
        opacity: .9;
        text-transform: uppercase;
        letter-spacing: .4px;
        margin-bottom: 2px;
    }

    .ics-meta-value {
        font-size: .82rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .ics-grid {
        display: grid;
        grid-template-columns: 1.7fr 1fr;
        gap: 12px;
    }

    .ics-card {
        background: #fff;
        border: 1px solid #dbe4f3;
        border-radius: 12px;
        box-shadow: 0 7px 18px rgba(13, 34, 70, .06);
        overflow: hidden;
    }

    .ics-card-head {
        padding: 10px 12px;
        border-bottom: 1px solid #e7edf8;
        background: #f8fbff;
        color: #173867;
        font-size: .86rem;
        font-weight: 800;
    }

    .ics-card-body {
        padding: 12px;
    }

    .ics-overview {
        color: #445f89;
        font-size: .84rem;
        line-height: 1.55;
        margin: 0;
        white-space: pre-line;
    }

    .ics-content {
        color: #263c63;
        font-size: .9rem;
        line-height: 1.7;
    }

    .ics-content h1,
    .ics-content h2,
    .ics-content h3,
    .ics-content h4 {
        color: #173867;
        margin-top: 1rem;
        margin-bottom: .45rem;
        font-weight: 800;
    }

    .ics-content p {
        margin-bottom: .8rem;
    }

    .ics-content img {
        max-width: 100%;
        border-radius: 8px;
    }

    .ics-media-img {
        width: 100%;
        border-radius: 8px;
        border: 1px solid #d9e3f3;
    }

    .ics-media-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        height: 36px;
        border-radius: 8px;
        background: #c32033;
        color: #fff;
        text-decoration: none;
        font-size: .8rem;
        font-weight: 700;
    }

    .ics-media-link:hover {
        color: #fff;
        background: #aa1a2a;
    }

    .ics-video {
        width: 100%;
        min-height: 210px;
        border: 1px solid #d9e4f5;
        border-radius: 8px;
    }

    .ics-table-wrap {
        overflow-x: auto;
    }

    .ics-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 400px;
    }

    .ics-table th,
    .ics-table td {
        border-bottom: 1px solid #e6edf8;
        padding: 9px 8px;
        font-size: .8rem;
        color: #29436c;
        text-align: left;
    }

    .ics-table th {
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: #5a7198;
    }

    .ics-table tr:last-child td {
        border-bottom: 0;
    }

    .ics-join {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 7px;
        background: #0f2f66;
        color: #fff;
        text-decoration: none;
        padding: 5px 9px;
        font-size: .75rem;
        font-weight: 700;
    }

    .ics-join:hover {
        color: #fff;
        background: #0b2450;
    }

    .ics-empty {
        background: #fff;
        border: 1px dashed #cfdcf0;
        border-radius: 12px;
        padding: 24px 14px;
        text-align: center;
        color: #5a6f95;
    }

    @media (max-width: 980px) {
        .ics-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="ics-page">
    <section class="ics-hero">
        <div class="ics-hero-top">
            <a href="{{ route('islamic-corner.index') }}" class="ics-back">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        <h1 class="ics-title">{{ $material->title }}</h1>

        <div class="ics-meta">
            @if($material->type)
                <div class="ics-meta-item">
                    <div class="ics-meta-label">Type</div>
                    <div class="ics-meta-value">{{ ucfirst($material->type) }}</div>
                </div>
            @endif
            @if($material->subject)
                <div class="ics-meta-item">
                    <div class="ics-meta-label">Subject</div>
                    <div class="ics-meta-value">{{ $material->subject }}</div>
                </div>
            @endif
            @if($material->stage)
                <div class="ics-meta-item">
                    <div class="ics-meta-label">Stage</div>
                    <div class="ics-meta-value">{{ ucfirst($material->stage) }}</div>
                </div>
            @endif
            @if($material->class)
                <div class="ics-meta-item">
                    <div class="ics-meta-label">Class</div>
                    <div class="ics-meta-value">{{ $material->class->name }}</div>
                </div>
            @endif
            @if($material->teacher)
                <div class="ics-meta-item">
                    <div class="ics-meta-label">Teacher</div>
                    <div class="ics-meta-value">{{ $material->teacher }}</div>
                </div>
            @endif
            <div class="ics-meta-item">
                <div class="ics-meta-label">Published</div>
                <div class="ics-meta-value">{{ optional($material->published_at)->format('M d, Y') }}</div>
            </div>
        </div>
    </section>

    <section class="ics-grid">
        <div>
            @if($material->description)
                <article class="ics-card" style="margin-bottom:12px;">
                    <div class="ics-card-head">Overview</div>
                    <div class="ics-card-body">
                        <p class="ics-overview">{{ $material->description }}</p>
                    </div>
                </article>
            @endif

            <article class="ics-card">
                <div class="ics-card-head">Material Content</div>
                <div class="ics-card-body">
                    @if($material->content)
                        <div class="ics-content">{!! $material->content !!}</div>
                    @else
                        <div class="ics-empty">No content added yet.</div>
                    @endif
                </div>
            </article>
        </div>

        <div>
            @if($material->image_path)
                <article class="ics-card" style="margin-bottom:12px;">
                    <div class="ics-card-head">Image</div>
                    <div class="ics-card-body">
                        <img src="{{ asset('storage/' . $material->image_path) }}" alt="{{ $material->title }}" class="ics-media-img">
                    </div>
                </article>
            @endif

            @if($material->video_url)
                <article class="ics-card" style="margin-bottom:12px;">
                    <div class="ics-card-head">Video</div>
                    <div class="ics-card-body">
                        <iframe src="{{ $material->video_url }}" class="ics-video" allowfullscreen></iframe>
                    </div>
                </article>
            @endif

            @if($material->attachment_path)
                <article class="ics-card" style="margin-bottom:12px;">
                    <div class="ics-card-head">Attachment</div>
                    <div class="ics-card-body">
                        <a href="{{ asset('storage/' . $material->attachment_path) }}" target="_blank" class="ics-media-link">
                            <i class="fas fa-download"></i> Open Attachment
                        </a>
                    </div>
                </article>
            @endif

            <article class="ics-card">
                <div class="ics-card-head">Schedule</div>
                <div class="ics-card-body">
                    @if(is_array($schedule) && count($schedule))
                        <div class="ics-table-wrap">
                            <table class="ics-table">
                                <thead>
                                    <tr>
                                        <th>Day</th>
                                        <th>Time</th>
                                        <th>Meeting</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schedule as $item)
                                        <tr>
                                            <td>{{ $item['day'] ?? '-' }}</td>
                                            <td>{{ substr((string)($item['start_time'] ?? ''), 0, 5) }} - {{ substr((string)($item['end_time'] ?? ''), 0, 5) }}</td>
                                            <td>
                                                @if(!empty($item['day']))
                                                    <a href="{{ route('islamic-corner.join_meeting', [$material->id, $item['day']]) }}" class="ics-join">
                                                        <i class="fas fa-video"></i>
                                                        {{ $isTeacher ? 'Start' : 'Join' }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="ics-empty">No schedule available for this material.</div>
                    @endif
                </div>
            </article>
        </div>
    </section>
</div>
@endsection
