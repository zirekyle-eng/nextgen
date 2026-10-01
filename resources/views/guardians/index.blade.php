@extends('layouts.master')

@section('content')
<style>
    .guardians-page {
        max-width: 1260px;
        margin: 0 auto;
        padding: 8px 6px 14px;
    }

    .guardians-hero {
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

    .guardians-hero h2 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 800;
    }

    .guardians-hero p {
        margin: 5px 0 0;
        font-size: .83rem;
        opacity: .93;
    }

    .guardians-create {
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

    .guardians-create:hover {
        color: #fff;
        background: rgba(255,255,255,.25);
    }

    .guardians-toolbar {
        background: #fff;
        border: 1px solid #dce4f2;
        border-radius: 12px;
        box-shadow: 0 7px 18px rgba(13, 34, 70, .06);
        padding: 11px;
        margin-bottom: 12px;
    }

    .guardians-toolbar .form-control,
    .guardians-toolbar .form-select {
        height: 38px;
        border: 1px solid #ccd8ec;
        border-radius: 8px;
        font-size: .82rem;
    }

    .guardians-toolbar .btn {
        height: 38px;
        border-radius: 8px;
        font-size: .78rem;
        font-weight: 700;
    }

    .guardians-shell {
        background: #fff;
        border: 1px solid #dce4f2;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(13, 34, 70, .06);
        overflow: hidden;
    }

    .guardians-shell-head {
        border-bottom: 1px solid #e7edf8;
        padding: 10px 12px;
        background: #f8fbff;
        color: #173867;
        font-size: .88rem;
        font-weight: 800;
    }

    .guardians-table-wrap {
        overflow-x: auto;
    }

    .guardians-table {
        width: 100%;
        min-width: 1020px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .guardians-table thead th {
        background: #f4f8ff;
        color: #35517f;
        border-bottom: 1px solid #dce5f4;
        font-size: .74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .35px;
        padding: 10px 8px;
    }

    .guardians-table tbody td {
        border-bottom: 1px solid #e6edf8;
        padding: 10px 8px;
        font-size: .8rem;
        color: #2a446f;
        vertical-align: middle;
    }

    .guardian-name {
        margin: 0;
        color: #102f5d;
        font-weight: 800;
        font-size: .84rem;
        line-height: 1.35;
    }

    .guardian-sub {
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

    .status-rejected {
        background: #ffecef;
        color: #a62132;
        border: 1px solid #ffcdd5;
    }

    .status-default {
        background: #eef2f8;
        color: #4d638a;
        border: 1px solid #d4deec;
    }

    .children-pill {
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

    .guardian-actions {
        display: inline-flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .guardian-btn {
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

    .guardians-footer {
        border-top: 1px solid #e7edf8;
        background: #fbfdff;
        padding: 10px 12px;
        display: flex;
        justify-content: center;
    }

    .guardians-empty {
        border: 1px dashed #cfdcf0;
        border-radius: 10px;
        background: #fff;
        padding: 28px 15px;
        text-align: center;
        color: #5a6f95;
    }
</style>

<div class="guardians-page">
    <section class="guardians-hero">
        <div>
            <h2>Guardians Management</h2>
            <p>Manage guardian profiles, status, and student connections.</p>
        </div>
        <a href="{{ route('guardians.create') }}" class="guardians-create">
            <i class="fas fa-plus"></i> Add Guardian
        </a>
    </section>

    @include('includes.alerts')

    <section class="guardians-toolbar">
        <form method="GET" action="{{ route('guardians.search') }}">
            <div class="row">
                <div class="col-md-6 mb-2 mb-md-0">
                    <input type="text" name="query" class="form-control" value="{{ $query ?? '' }}" placeholder="Search by first name, last name, or email...">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <select class="form-control" onchange="if(this.value){window.location=this.value}">
                        <option value="">Filter by status</option>
                        <option value="{{ route('guardians.filterByStatus', 'approved') }}" {{ ($status ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="{{ route('guardians.filterByStatus', 'pending') }}" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="{{ route('guardians.filterByStatus', 'rejected') }}" {{ ($status ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex" style="gap:6px;">
                    <button type="submit" class="btn btn-primary flex-fill"><i class="fas fa-search"></i> Search</button>
                    <a href="{{ route('guardians.index') }}" class="btn btn-light border flex-fill">Reset</a>
                </div>
            </div>
        </form>
    </section>

    @if($guardians->count() > 0)
        <section class="guardians-shell">
            <div class="guardians-shell-head">
                Guardians List
            </div>

            <div class="guardians-table-wrap">
                <table class="guardians-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Guardian</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Country</th>
                            <th>Status</th>
                            <th>Children</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($guardians as $guardian)
                            <tr>
                                <td>#{{ $loop->iteration }}</td>
                                <td>
                                    <p class="guardian-name">{{ $guardian->first_name }} {{ $guardian->last_name }}</p>
                                    <div class="guardian-sub">{{ $guardian->role ? ucfirst($guardian->role) : 'Guardian' }}</div>
                                </td>
                                <td>
                                    <a href="mailto:{{ $guardian->email }}">{{ $guardian->email }}</a>
                                </td>
                                <td>
                                    @if($guardian->phone)
                                        {{ $guardian->phone_prefix }}{{ $guardian->phone }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $guardian->country ?: '-' }}</td>
                                <td>
                                    @if($guardian->status === 'pending')
                                        <span class="status-pill status-pending"><i class="fas fa-hourglass-half"></i> Pending</span>
                                    @elseif($guardian->status === 'approved')
                                        <span class="status-pill status-approved"><i class="fas fa-check-circle"></i> Approved</span>
                                    @elseif($guardian->status === 'rejected')
                                        <span class="status-pill status-rejected"><i class="fas fa-times-circle"></i> Rejected</span>
                                    @else
                                        <span class="status-pill status-default">{{ $guardian->status ?: 'Unknown' }}</span>
                                    @endif
                                </td>
                                <td><span class="children-pill">{{ $guardian->students->count() }}</span></td>
                                <td>
                                    <div class="guardian-actions">
                                        <a href="{{ route('guardians.show', $guardian) }}" class="guardian-btn btn-view">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="{{ route('guardians.edit', $guardian) }}" class="guardian-btn btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('guardians.destroy', $guardian) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="guardian-btn btn-delete">
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

            <div class="guardians-footer">
                {{ $guardians->links('pagination::bootstrap-4') }}
            </div>
        </section>
    @else
        <div class="guardians-empty">
            <h5 style="margin:0 0 5px; color:#1b3a6e;">No guardians found</h5>
            <p style="margin:0;">There are no guardian records yet. <a href="{{ route('guardians.create') }}">Create one now</a></p>
        </div>
    @endif
</div>
@endsection
