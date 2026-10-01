@extends('layouts.master')
@section('page_title', 'Manage TimeTables')

@section('content')
<style>
    .tt-index-page {
        max-width: 1240px;
        margin: 0 auto;
        padding: 8px 6px 14px;
    }

    .tt-index-hero {
        border-radius: 14px;
        background: linear-gradient(120deg, #0f2f66 0%, #1b4f9d 58%, #c32033 100%);
        color: #fff;
        padding: 16px 18px;
        margin-bottom: 12px;
        box-shadow: 0 10px 24px rgba(15, 47, 102, .2);
    }

    .tt-index-hero h2 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 800;
    }

    .tt-index-hero p {
        margin: 5px 0 0;
        opacity: .93;
        font-size: .83rem;
    }

    .tt-shell {
        background: #fff;
        border: 1px solid #dce4f2;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(13, 34, 70, .06);
        overflow: hidden;
    }

    .tt-shell-head {
        border-bottom: 1px solid #e7edf8;
        padding: 10px 12px;
        background: #f8fbff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .tt-shell-head h5 {
        margin: 0;
        color: #173867;
        font-size: .92rem;
        font-weight: 800;
    }

    .tt-content {
        padding: 12px;
    }

    .tt-tabs {
        border-bottom: 1px solid #dbe5f5;
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        margin-bottom: 10px;
        padding-bottom: .45rem;
    }

    .tt-tabs .nav-link {
        border: 1px solid #dbe5f5;
        border-radius: 8px;
        background: #f7faff;
        color: #2f4b77;
        font-size: .8rem;
        font-weight: 700;
        padding: .5rem .8rem;
    }

    .tt-tabs .nav-link.active {
        background: #0f2f66;
        color: #fff;
        border-color: #0f2f66;
    }

    .tt-create-box {
        border: 1px solid #dce5f4;
        border-radius: 10px;
        background: #fbfdff;
        padding: 12px;
        max-width: 760px;
    }

    .tt-create-box .form-group {
        margin-bottom: .75rem;
    }

    .tt-create-box label {
        font-size: .8rem;
        font-weight: 700;
        color: #344f7b;
    }

    .tt-create-box .form-control {
        height: 38px;
        border: 1px solid #ccd8ec;
        border-radius: 8px;
    }

    .tt-create-box .btn-primary {
        border-radius: 8px;
        font-size: .8rem;
        font-weight: 700;
        padding: .5rem .95rem;
    }

    .tt-table-wrap {
        border: 1px solid #dce5f4;
        border-radius: 10px;
        overflow: hidden;
    }

    .tt-table-wrap table {
        margin-bottom: 0;
    }

    .tt-table-wrap thead th {
        background: #f4f8ff;
        color: #35517f;
        border-bottom: 1px solid #dce5f4;
        font-size: .74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .35px;
    }

    .tt-table-wrap tbody td {
        font-size: .8rem;
        color: #2a446f;
        vertical-align: middle;
    }

    .tt-mini-btn {
        border: 0;
        border-radius: 7px;
        padding: 5px 8px;
        font-size: .72rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
    }

    .tt-mini-view {
        background: #e9f1ff;
        color: #1f3f73;
        border: 1px solid #cedcf6;
    }

    .tt-empty {
        border: 1px dashed #cfdcf0;
        border-radius: 10px;
        background: #fff;
        padding: 22px 14px;
        text-align: center;
        color: #5a6f95;
    }
</style>

<div class="tt-index-page">
    <section class="tt-index-hero">
        <h2>{{ auth()->user()->user_type === 'student' ? 'Class TimeTables' : 'TimeTable Management' }}</h2>
        <p>
            @if(auth()->user()->user_type === 'student')
                Your class schedules are listed below with direct access.
            @else
                Create, browse, and manage timetable records by class.
            @endif
        </p>
    </section>

    @if(auth()->user()->user_type === 'student')
        <section class="tt-shell">
            <div class="tt-shell-head">
                <h5>Available TimeTables</h5>
                {!! Qs::getPanelOptions() !!}
            </div>
            <div class="tt-content">
                @if($tt_records->count() > 0)
                    <div class="tt-table-wrap">
                        <table class="table datatable-button-html5-columns">
                            <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Year</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($tt_records as $ttr)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $ttr->name }}</td>
                                    <td>{{ ($ttr->exam_id) ? $ttr->exam->name : 'Class TimeTable' }}</td>
                                    <td>{{ $ttr->year }}</td>
                                    <td>
                                        <a href="{{ route('ttr.show', $ttr->id) }}" class="tt-mini-btn tt-mini-view">
                                            <i class="icon-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="tt-empty">
                        <h6 style="margin:0 0 4px; color:#1b3a6e;">No timetables available</h6>
                        <p style="margin:0;">No timetable has been published for your class yet.</p>
                    </div>
                @endif
            </div>
        </section>
    @else
        <section class="tt-shell">
            <div class="tt-shell-head">
                <h5>Manage TimeTables</h5>
                {!! Qs::getPanelOptions() !!}
            </div>

            <div class="tt-content">
                <ul class="nav tt-tabs">
                    @if(Qs::userIsTeamSA())
                        <li class="nav-item">
                            <a href="#add-tt" class="nav-link active" data-toggle="tab">Create Timetable</a>
                        </li>
                    @endif

                    @foreach($my_classes as $mc)
                        <li class="nav-item">
                            <a href="#ttr{{ $mc->id }}" class="nav-link {{ !Qs::userIsTeamSA() && $loop->first ? 'active' : '' }}" data-toggle="tab">
                                {{ $mc->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content">
                    @if(Qs::userIsTeamSA())
                        <div class="tab-pane fade show active" id="add-tt">
                            <div class="tt-create-box">
                                <form class="ajax-store" method="post" action="{{ route('ttr.store') }}">
                                    @csrf

                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Name <span class="text-danger">*</span></label>
                                        <div class="col-lg-9">
                                            <input name="name" value="{{ old('name') }}" required type="text" class="form-control" placeholder="Name of timetable">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="my_class_id" class="col-lg-3 col-form-label">Class <span class="text-danger">*</span></label>
                                        <div class="col-lg-9">
                                            <select required data-placeholder="Select Class" class="form-control select" name="my_class_id" id="my_class_id">
                                                @foreach($my_classes as $mc)
                                                    <option {{ old('my_class_id') == $mc->id ? 'selected' : '' }} value="{{ $mc->id }}">{{ $mc->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="exam_id" class="col-lg-3 col-form-label">Type</label>
                                        <div class="col-lg-9">
                                            <select class="select form-control" name="exam_id" id="exam_id">
                                                <option value="">Class Timetable</option>
                                                @foreach($exams as $ex)
                                                    <option {{ old('exam_id') == $ex->id ? 'selected' : '' }} value="{{ $ex->id }}">{{ $ex->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <button id="ajax-btn" type="submit" class="btn btn-primary">
                                            Submit <i class="icon-paperplane ml-1"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    @foreach($my_classes as $mc)
                        <div class="tab-pane fade {{ !Qs::userIsTeamSA() && $loop->first ? 'show active' : '' }}" id="ttr{{ $mc->id }}">
                            <div class="tt-table-wrap">
                                <table class="table datatable-button-html5-columns">
                                    <thead>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Name</th>
                                        <th>Class</th>
                                        <th>Type</th>
                                        <th>Year</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($tt_records->where('my_class_id', $mc->id) as $ttr)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $ttr->name }}</td>
                                            <td>{{ $ttr->my_class->name }}</td>
                                            <td>{{ ($ttr->exam_id) ? $ttr->exam->name : 'Class TimeTable' }}</td>
                                            <td>{{ $ttr->year }}</td>
                                            <td class="text-center">
                                                <div class="list-icons">
                                                    <div class="dropdown">
                                                        <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                            <i class="icon-menu9"></i>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            <a href="{{ route('ttr.show', $ttr->id) }}" class="dropdown-item"><i class="icon-eye"></i> View</a>

                                                            @if(Qs::userIsTeamSA())
                                                                <a href="{{ route('ttr.manage', $ttr->id) }}" class="dropdown-item"><i class="icon-plus-circle2"></i> Manage</a>
                                                                <a href="{{ route('ttr.edit', $ttr->id) }}" class="dropdown-item"><i class="icon-pencil"></i> Edit</a>
                                                            @endif

                                                            @if(Qs::userIsSuperAdmin())
                                                                <a id="{{ $ttr->id }}" onclick="confirmDelete(this.id)" href="#" class="dropdown-item"><i class="icon-trash"></i> Delete</a>
                                                                <form method="post" id="item-delete-{{ $ttr->id }}" action="{{ route('ttr.destroy', $ttr->id) }}" class="hidden">@csrf @method('delete')</form>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
@endsection
