@extends('layouts.master')
@section('page_title', 'Manage TimeTable Record')
@section('content')

    <style>
        .ttm-page {
            max-width: 1260px;
            margin: 0 auto;
            padding: 8px 6px 14px;
        }

        .ttm-hero {
            border-radius: 14px;
            background: linear-gradient(120deg, #0f2f66 0%, #1b4f9d 58%, #c32033 100%);
            color: #fff;
            padding: 16px 18px;
            margin-bottom: 12px;
            box-shadow: 0 10px 24px rgba(15, 47, 102, .2);
        }

        .ttm-hero h2 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
            line-height: 1.35;
        }

        .ttm-hero p {
            margin: 5px 0 0;
            font-size: .83rem;
            opacity: .93;
        }

        .ttm-shell {
            background: #fff;
            border: 1px solid #dce4f2;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(13, 34, 70, .06);
            overflow: hidden;
        }

        .ttm-shell-head {
            border-bottom: 1px solid #e7edf8;
            padding: 10px 12px;
            background: #f8fbff;
            color: #173867;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .ttm-shell-head h5 {
            margin: 0;
            font-size: .92rem;
            font-weight: 800;
        }

        .ttm-shell-body {
            padding: 12px;
        }

        .ttm-tabs {
            border-bottom: 1px solid #dbe5f5;
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
            margin-bottom: 10px;
            padding-bottom: .45rem;
        }

        .ttm-tabs .nav-link {
            border: 1px solid #dbe5f5;
            border-radius: 8px;
            background: #f7faff;
            color: #2f4b77;
            font-size: .8rem;
            font-weight: 700;
            padding: .5rem .8rem;
        }

        .ttm-tabs .nav-link.active {
            background: #0f2f66;
            color: #fff;
            border-color: #0f2f66;
        }

        .ttm-tabs .nav-link.external {
            background: #ffeef1;
            color: #982033;
            border-color: #ffd1d8;
        }

        .ttm-page .tab-content > .tab-pane {
            padding-top: 4px;
        }

        .ttm-page .card {
            border-radius: 10px !important;
            border: 1px solid #dce5f4 !important;
            box-shadow: 0 6px 15px rgba(13, 34, 70, .05) !important;
            margin-bottom: 10px !important;
        }

        .ttm-page .card-header.bg-danger,
        .ttm-page .card-header.bg-dark,
        .ttm-page .card-header.bg-success {
            background: #f4f8ff !important;
            color: #35517f !important;
            border-bottom: 1px solid #dce5f4 !important;
            box-shadow: none !important;
        }

        .ttm-page .card-body.collapse {
            display: block !important;
        }

        .ttm-page .form-control,
        .ttm-page .form-control-lg {
            border-radius: 8px !important;
            border-color: #ccd8ec !important;
        }

        .ttm-page .btn-primary,
        .ttm-page .btn-success {
            border-radius: 8px !important;
            font-weight: 700 !important;
        }
    </style>

    <div class="ttm-page">
        <section class="ttm-hero">
            <h2>{{ $ttr->name }} - {{ $my_class->name }} ({{ $ttr->year }})</h2>
            <p>Manage time slots, assign subjects, and keep timetable records updated.</p>
        </section>

        <section class="ttm-shell">
            <div class="ttm-shell-head">
                <h5>TimeTable Record Management</h5>
                {!! Qs::getPanelOptions() !!}
            </div>

            <div class="ttm-shell-body">
                <ul class="nav ttm-tabs">
                    <li class="nav-item"><a href="#manage-ts" class="nav-link active" data-toggle="tab">Manage Time Slots</a></li>
                    <li class="nav-item"><a href="#add-sub" class="nav-link" data-toggle="tab">Add Subject</a></li>
                    <li class="nav-item"><a href="#edit-subs" class="nav-link" data-toggle="tab">Edit Subjects</a></li>
                    <li class="nav-item"><a target="_blank" href="{{ route('ttr.show', $ttr->id) }}" class="nav-link external">View Timetable</a></li>
                </ul>

                <div class="tab-content">
                    @include('pages.support_team.timetables.time_slots.index')
                    @include('pages.support_team.timetables.subjects.add')
                    @include('pages.support_team.timetables.subjects.edit')
                </div>
            </div>
        </section>
    </div>

@endsection
