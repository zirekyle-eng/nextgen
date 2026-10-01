@extends('layouts.master')
@section('page_title', 'View TimeTable')

@section('content')
<style>
    .tt-page {
        max-width: 1260px;
        margin: 0 auto;
        padding: 8px 6px 14px;
    }

    .tt-hero {
        border-radius: 14px;
        background: linear-gradient(120deg, #0f2f66 0%, #1b4f9d 58%, #c32033 100%);
        color: #fff;
        padding: 16px 18px;
        margin-bottom: 12px;
        box-shadow: 0 10px 24px rgba(15, 47, 102, .2);
    }

    .tt-hero h2 {
        margin: 0;
        font-size: 1.22rem;
        font-weight: 800;
    }

    .tt-hero p {
        margin: 5px 0 0;
        font-size: .84rem;
        opacity: .93;
    }

    .tt-meta {
        margin-top: 10px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 8px;
    }

    .tt-meta-item {
        background: rgba(255,255,255,.13);
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 9px;
        padding: 8px 10px;
    }

    .tt-meta-label {
        font-size: .67rem;
        text-transform: uppercase;
        letter-spacing: .35px;
        margin-bottom: 2px;
        opacity: .9;
    }

    .tt-meta-value {
        font-size: .83rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .tt-card {
        background: #fff;
        border: 1px solid #dce4f2;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(13, 34, 70, .06);
        overflow: hidden;
    }

    .tt-card-head {
        padding: 11px 13px;
        border-bottom: 1px solid #e6edf8;
        background: #f8fbff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .tt-card-head h5 {
        margin: 0;
        color: #173867;
        font-size: .9rem;
        font-weight: 800;
    }

    .tt-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .tt-btn {
        border: 0;
        border-radius: 8px;
        padding: 7px 10px;
        font-size: .75rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
    }

    .tt-btn-print {
        background: #0f2f66;
        color: #fff;
    }

    .tt-btn-print:hover {
        color: #fff;
        background: #0b2450;
    }

    .tt-legend {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        padding: 10px 13px 0;
    }

    .tt-chip {
        font-size: .68rem;
        font-weight: 700;
        border-radius: 999px;
        padding: 4px 9px;
    }

    .tt-chip-primary {
        background: #eaf1ff;
        color: #1f3f73;
        border: 1px solid #cedcf6;
    }

    .tt-chip-secondary {
        background: #eaf7ee;
        color: #1e6c36;
        border: 1px solid #c6e8d1;
    }

    .tt-table-wrap {
        padding: 10px 13px 13px;
        overflow-x: auto;
    }

    .tt-table {
        width: 100%;
        min-width: 900px;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid #dce4f2;
        border-radius: 10px;
        overflow: hidden;
    }

    .tt-table th,
    .tt-table td {
        border-right: 1px solid #e6edf8;
        border-bottom: 1px solid #e6edf8;
        padding: 9px 8px;
        vertical-align: middle;
    }

    .tt-table th:last-child,
    .tt-table td:last-child {
        border-right: 0;
    }

    .tt-table tr:last-child td {
        border-bottom: 0;
    }

    .tt-table thead th {
        background: #f4f8ff;
        color: #365483;
        font-size: .74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .4px;
        text-align: center;
    }

    .tt-time-col {
        min-width: 120px;
        background: #fbfdff;
    }

    .tt-time-main {
        font-size: .8rem;
        color: #173867;
        font-weight: 800;
        line-height: 1.2;
    }

    .tt-time-sub {
        margin-top: 3px;
        font-size: .72rem;
        color: #6881a8;
        line-height: 1.2;
    }

    .tt-day-title {
        font-size: .78rem;
        font-weight: 800;
        color: #173867;
        line-height: 1.2;
    }

    .tt-day-date {
        margin-top: 3px;
        font-size: .67rem;
        color: #6e85ab;
    }

    .tt-empty {
        display: block;
        text-align: center;
        color: #9aaecb;
        font-size: .82rem;
        font-weight: 700;
        letter-spacing: .2px;
    }

    .tt-subject-link {
        display: block;
        width: 100%;
        text-align: left;
        border-radius: 8px;
        padding: 7px 8px;
        font-size: .76rem;
        font-weight: 700;
        line-height: 1.3;
        color: #1e3d70;
        background: #edf3ff;
        border: 1px solid #d2def4;
        text-decoration: none;
    }

    .tt-subject-link:hover {
        color: #132f59;
        background: #e4edff;
        text-decoration: none;
    }

    .tt-subject-link.general {
        background: #eaf7ee;
        border-color: #c6e8d1;
        color: #1e6c36;
    }

    .tt-subject-link .icon {
        float: right;
    }
</style>

<div class="tt-page">
    @php
        $printRoute = (Auth::user()->user_type === 'student')
            ? route('student.ttr.print', Qs::hash($ttr->id))
            : route('ttr.print', $ttr->id);
    @endphp

    <section class="tt-hero">
        <h2>{{ $ttr->name }}</h2>
        <p>Weekly timetable overview with direct live-class meeting links.</p>

        <div class="tt-meta">
            <div class="tt-meta-item">
                <div class="tt-meta-label">Class</div>
                <div class="tt-meta-value">{{ $my_class->name }}</div>
            </div>
            <div class="tt-meta-item">
                <div class="tt-meta-label">Type</div>
                <div class="tt-meta-value">{{ $ttr->exam_id ? 'Exam Timetable' : 'Class Timetable' }}</div>
            </div>
            <div class="tt-meta-item">
                <div class="tt-meta-label">Year</div>
                <div class="tt-meta-value">{{ $ttr->year }}</div>
            </div>
        </div>
    </section>

    <section class="tt-card">
        <div class="tt-card-head">
            <h5>Schedule Grid</h5>
            <div class="tt-actions">
                <a target="_blank" href="{{ $printRoute }}" class="tt-btn tt-btn-print">
                    <i class="fa fa-print"></i> Print Timetable
                </a>
            </div>
        </div>

        <div class="tt-legend">
            <span class="tt-chip tt-chip-primary">Subject Meeting</span>
            <span class="tt-chip tt-chip-secondary">General Class Meeting</span>
        </div>

        <div class="tt-table-wrap">
            <table class="tt-table">
                <thead>
                    <tr>
                        <th class="tt-time-col">Time</th>
                        @foreach($days as $day)
                            <th>
                                @if($ttr->exam_id)
                                    <div class="tt-day-title">{{ date('l', strtotime($day)) }}</div>
                                    <div class="tt-day-date">{{ date('d/m/Y', strtotime($day)) }}</div>
                                @else
                                    <div class="tt-day-title">{{ $day }}</div>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($time_slots as $tms)
                        <tr>
                            <td class="tt-time-col">
                                <div class="tt-time-main">{{ $tms->time_from }}</div>
                                <div class="tt-time-sub">{{ $tms->time_to }}</div>
                            </td>
                            @foreach($days as $day)
                                <td>
                                    @php
                                        $subject = $d_time->where('day', $day)->where('time', $tms->full)->first();
                                    @endphp

                                    @if($subject && $subject['subject'])
                                        @if($subject['tt_id'] && !is_null($subject['tt_id']) && $subject['tt_id'] != 0)
                                            <a href="{{ route('bbb.join', ['ttrId' => $ttr->id, 'ttId' => $subject['tt_id'], 'day' => $day]) }}"
                                               class="tt-subject-link"
                                               title="Join {{ $subject['subject'] }} - {{ $day }} {{ $tms->full }}">
                                                {{ $subject['subject'] }}
                                                <i class="fa fa-video-camera icon"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('bbb.join', ['ttrId' => $ttr->id, 'day' => $day]) }}"
                                               class="tt-subject-link general"
                                               title="Join {{ $ttr->name }} - {{ $day }} {{ $tms->full }}">
                                                {{ $subject['subject'] ?? $ttr->name }}
                                                <i class="fa fa-video-camera icon"></i>
                                            </a>
                                        @endif
                                    @else
                                        <span class="tt-empty">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
