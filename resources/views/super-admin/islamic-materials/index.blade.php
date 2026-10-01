@extends('layouts.master')

@section('content')
<style>
    .im-page {
        max-width: 1260px;
        margin: 0 auto;
        padding: 8px 6px 14px;
    }

    .im-hero {
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

    .im-hero h2 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 800;
    }

    .im-hero p {
        margin: 5px 0 0;
        font-size: .83rem;
        opacity: .93;
    }

    .im-add {
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

    .im-add:hover {
        color: #fff;
        background: rgba(255,255,255,.25);
    }

    .im-toolbar {
        background: #fff;
        border: 1px solid #dce4f2;
        border-radius: 12px;
        box-shadow: 0 7px 18px rgba(13, 34, 70, .06);
        padding: 11px;
        margin-bottom: 12px;
    }

    .im-toolbar .form-control,
    .im-toolbar .form-select {
        height: 46px !important;
        border: 1px solid #ccd8ec;
        border-radius: 8px;
        font-size: .9rem;
        padding: 0 12px;
    }

    .im-toolbar .btn {
        height: 38px;
        border-radius: 8px;
        font-size: .78rem;
        font-weight: 700;
    }

    .im-shell {
        background: #fff;
        border: 1px solid #dce4f2;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(13, 34, 70, .06);
        overflow: hidden;
    }

    .im-shell-head {
        border-bottom: 1px solid #e7edf8;
        padding: 10px 12px;
        background: #f8fbff;
        color: #173867;
        font-size: .88rem;
        font-weight: 800;
    }

    .im-table-wrap {
        overflow-x: auto;
    }

    .im-table {
        width: 100%;
        min-width: 1100px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .im-table thead th {
        background: #f4f8ff;
        color: #35517f;
        border-bottom: 1px solid #dce5f4;
        font-size: .74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .35px;
        padding: 10px 8px;
    }

    .im-table tbody td {
        border-bottom: 1px solid #e6edf8;
        padding: 10px 8px;
        font-size: .8rem;
        color: #2a446f;
        vertical-align: middle;
    }

    .im-title {
        margin: 0;
        color: #102f5d;
        font-weight: 800;
        font-size: .84rem;
        line-height: 1.35;
    }

    .im-sub {
        color: #6780a7;
        font-size: .72rem;
        margin-top: 1px;
    }

    .im-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 4px 8px;
        font-size: .68rem;
        font-weight: 800;
    }

    .im-active {
        background: #eaf7ee;
        color: #1f6f38;
        border: 1px solid #c6e8d1;
    }

    .im-inactive {
        background: #eef2f8;
        color: #4d638a;
        border: 1px solid #d4deec;
    }

    .im-actions {
        display: inline-flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .im-btn {
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

    .btn-edit {
        background: #e9f1ff;
        color: #1f3f73;
        border: 1px solid #cedcf6;
    }

    .btn-toggle {
        background: #fff7e8;
        color: #8d5e17;
        border: 1px solid #ffe2b5;
    }

    .btn-delete {
        background: #ffe9ed;
        color: #aa2032;
        border: 1px solid #ffc9d1;
    }

    .im-footer {
        border-top: 1px solid #e7edf8;
        background: #fbfdff;
        padding: 10px 12px;
        display: flex;
        justify-content: center;
    }

    .im-empty {
        border: 1px dashed #cfdcf0;
        border-radius: 10px;
        background: #fff;
        padding: 28px 15px;
        text-align: center;
        color: #5a6f95;
    }
</style>

<div class="im-page">
    <section class="im-hero">
        <div>
            <h2>Islamic Materials Management</h2>
            <p>Manage Islamic Corner content assigned to students and classes.</p>
        </div>
        <a href="{{ route('islamic-materials.create') }}" class="im-add">
            <i class="fas fa-plus"></i> Add New Material
        </a>
    </section>

    @include('includes.alerts')

    <section class="im-toolbar">
        <form method="GET">
            <div class="row">
                <div class="col-md-5 mb-2 mb-md-0">
                    <input type="text" name="search" class="form-control" placeholder="Search by title..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex" style="gap:6px;">
                    <button type="submit" class="btn btn-primary flex-fill">Filter</button>
                    <a href="{{ route('islamic-materials.index') }}" class="btn btn-light border flex-fill">Reset</a>
                </div>
            </div>
        </form>
    </section>

    @if($materials->count() > 0)
        <section class="im-shell">
            <div class="im-shell-head">Materials List</div>

            <div class="im-table-wrap">
                <table class="im-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Class</th>
                            <th>Teacher</th>
                            <th>Time Table</th>
                            <th>Status</th>
                            <th>Published</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materials as $material)
                            @php
                                $scheduleText = '-';
                                if ($material->time_table) {
                                    $schedule = json_decode($material->time_table, true);
                                    if (is_array($schedule) && count($schedule) > 0) {
                                        $scheduleText = collect($schedule)
                                            ->map(function ($item) {
                                                $day = $item['day'] ?? '-';
                                                $start = isset($item['start_time']) ? substr((string)$item['start_time'], 0, 5) : '-';
                                                $end = isset($item['end_time']) ? substr((string)$item['end_time'], 0, 5) : '-';
                                                return $day . ': ' . $start . '-' . $end;
                                            })->join(', ');
                                    }
                                }
                            @endphp
                            <tr>
                                <td>
                                    <p class="im-title">{{ $material->title ?? '-' }}</p>
                                    <div class="im-sub">{{ $material->stage ? ucfirst($material->stage) . ' stage' : 'All stages' }}</div>
                                </td>
                                <td>
                                    @if($material->class)
                                        <span class="im-pill im-inactive">{{ $material->class->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $material->teacher ?? '-' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($scheduleText, 52) }}</td>
                                <td>
                                    @if($material->is_active)
                                        <span class="im-pill im-active">Active</span>
                                    @else
                                        <span class="im-pill im-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $material->published_at?->format('M d, Y') ?? '-' }}</td>
                                <td>
                                    <div class="im-actions">
                                        <a href="{{ route('islamic-materials.edit', $material) }}" class="im-btn btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>

                                        <form action="{{ route('islamic-materials.toggleStatus', $material) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="im-btn btn-toggle">
                                                <i class="fas fa-{{ $material->is_active ? 'ban' : 'check' }}"></i>
                                                {{ $material->is_active ? 'Disable' : 'Enable' }}
                                            </button>
                                        </form>

                                        <form action="{{ route('islamic-materials.destroy', $material) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="im-btn btn-delete">
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

            <div class="im-footer">
                {{ $materials->links() }}
            </div>
        </section>
    @else
        <div class="im-empty">
            <h5 style="margin:0 0 5px; color:#1b3a6e;">No materials found</h5>
            <p style="margin:0;">No results match your filters. Try different criteria.</p>
        </div>
    @endif
</div>
@endsection
