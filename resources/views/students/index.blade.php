@extends('layouts.master')

@section('content')
<style>
    .students-page {
        max-width: 1260px;
        margin: 0 auto;
        padding: 8px 6px 14px;
    }

    .students-hero {
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

    .students-hero h2 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 800;
    }

    .students-hero p {
        margin: 5px 0 0;
        font-size: .83rem;
        opacity: .93;
    }

    .students-create {
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

    .students-create:hover {
        color: #fff;
        background: rgba(255,255,255,.25);
    }

    .students-toolbar {
        background: #fff;
        border: 1px solid #dce4f2;
        border-radius: 12px;
        box-shadow: 0 7px 18px rgba(13, 34, 70, .06);
        padding: 11px;
        margin-bottom: 12px;
    }

    .students-toolbar .form-control {
        height: 38px;
        border: 1px solid #ccd8ec;
        border-radius: 8px;
        font-size: .82rem;
    }

    .students-toolbar .btn {
        height: 38px;
        border-radius: 8px;
        font-size: .78rem;
        font-weight: 700;
    }

    .students-shell {
        background: #fff;
        border: 1px solid #dce4f2;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(13, 34, 70, .06);
        overflow: hidden;
    }

    .students-shell-head {
        border-bottom: 1px solid #e7edf8;
        padding: 10px 12px;
        background: #f8fbff;
        color: #173867;
        font-size: .88rem;
        font-weight: 800;
    }

    .students-table-wrap {
        overflow-x: auto;
    }

    .students-table {
        width: 100%;
        min-width: 1080px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .students-table thead th {
        background: #f4f8ff;
        color: #35517f;
        border-bottom: 1px solid #dce5f4;
        font-size: .74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .35px;
        padding: 10px 8px;
    }

    .students-table tbody td {
        border-bottom: 1px solid #e6edf8;
        padding: 10px 8px;
        font-size: .8rem;
        color: #2a446f;
        vertical-align: middle;
    }

    .student-name {
        margin: 0;
        color: #102f5d;
        font-weight: 800;
        font-size: .84rem;
        line-height: 1.35;
    }

    .student-sub {
        color: #6780a7;
        font-size: .72rem;
        margin-top: 1px;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border-radius: 999px;
        padding: 4px 8px;
        font-size: .68rem;
        font-weight: 800;
    }

    .status-pending {
        background: #fff7e8;
        color: #8d5e17;
        border: 1px solid #ffe2b5;
    }

    .status-approved {
        background: #eaf7ee;
        color: #1f6f38;
        border: 1px solid #c6e8d1;
    }

    .status-default {
        background: #eef2f8;
        color: #4d638a;
        border: 1px solid #d4deec;
    }

    .age-pill {
        display: inline-block;
        min-width: 26px;
        text-align: center;
        border-radius: 999px;
        padding: 4px 7px;
        font-size: .68rem;
        font-weight: 800;
        color: #1d467a;
        background: #eaf1ff;
        border: 1px solid #cedcf6;
    }

    .student-actions {
        display: inline-flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .student-btn {
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

    .btn-view { background: #e9f1ff; color: #1f3f73; border: 1px solid #cedcf6; }
    .btn-edit { background: #fff6e8; color: #94621a; border: 1px solid #ffe3b3; }
    .btn-delete { background: #ffe9ed; color: #aa2032; border: 1px solid #ffc9d1; }

    .students-footer {
        border-top: 1px solid #e7edf8;
        background: #fbfdff;
        padding: 10px 12px;
        display: flex;
        justify-content: center;
    }

    .students-empty {
        border: 1px dashed #cfdcf0;
        border-radius: 10px;
        background: #fff;
        padding: 28px 15px;
        text-align: center;
        color: #5a6f95;
    }
</style>

<div class="students-page">
    <section class="students-hero">
        <div>
            <h2>Candidates Management</h2>
            <p>Manage candidate profiles, guardians, and admission status.</p>
        </div>
        <a href="{{ route('candidates.create') }}" class="students-create">
            <i class="fas fa-plus"></i> Add Candidate
        </a>
    </section>

    @include('includes.alerts')

    <section class="students-toolbar">
        <form method="GET" action="{{ route('candidates.search') }}">
            <div class="row">
                <div class="col-md-6 mb-2 mb-md-0">
                    <input type="text" name="query" class="form-control" value="{{ $query ?? '' }}" placeholder="Search by first name, last name, or stage...">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <select class="form-control" onchange="if(this.value){window.location=this.value}">
                        <option value="">Filter by status</option>
                        <option value="{{ route('candidates.filterByStatus', 'approved') }}" {{ ($status ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="{{ route('candidates.filterByStatus', 'pending') }}" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="{{ route('candidates.filterByStatus', 'rejected') }}" {{ ($status ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex" style="gap:6px;">
                    <button type="submit" class="btn btn-primary flex-fill"><i class="fas fa-search"></i> Search</button>
                    <a href="{{ route('candidates.index') }}" class="btn btn-light border flex-fill">Reset</a>
                </div>
            </div>
        </form>
    </section>

    @if($students->count() > 0)
        <section class="students-shell">
            <div class="students-shell-head">
                Candidates List
            </div>

            <div class="students-table-wrap">
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Candidate</th>
                            <th>Guardian</th>
                            <th>DOB</th>
                            <th>Age</th>
                            <th>Stage</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td>#{{ $loop->iteration }}</td>
                                <td>
                                    <p class="student-name">{{ $student->first_name }} {{ $student->last_name }}</p>
                                    <div class="student-sub">{{ $student->country ?: 'Country N/A' }}</div>
                                </td>
                                <td>
                                    <a href="{{ route('guardians.show', $student->guardian) }}">
                                        {{ $student->guardian->first_name }} {{ $student->guardian->last_name }}
                                    </a>
                                </td>
                                <td>
                                    @if($student->dob)
                                        {{ \Carbon\Carbon::parse($student->dob)->format('M d, Y') }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($student->dob)
                                        <span class="age-pill">{{ $student->age }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $student->stage ?: '-' }}</td>
                                <td>
                                    @if($student->status === 'pending')
                                        <span class="status-pill status-pending"><i class="fas fa-hourglass-half"></i> Pending</span>
                                    @elseif($student->status === 'approved')
                                        <span class="status-pill status-approved"><i class="fas fa-check-circle"></i> Approved</span>
                                    @else
                                        <span class="status-pill status-default">{{ $student->status ?: 'Unknown' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="student-actions">
                                        <a href="{{ route('candidates.show', $student) }}" class="student-btn btn-view">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="{{ route('candidates.edit', $student) }}" class="student-btn btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('candidates.destroy', $student) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="student-btn btn-delete">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="students-footer">
                {{ $students->links('pagination::bootstrap-4') }}
            </div>
        </section>
    @else
        <div class="students-empty">
            <h5 style="margin:0 0 5px; color:#1b3a6e;">No candidates found</h5>
            <p style="margin:0;">There are no candidate records yet. <a href="{{ route('candidates.create') }}">Create one now</a></p>
        </div>
    @endif
</div>
@endsection
