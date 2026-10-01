@extends('layouts.master')
@section('page_title', 'Manage Announcements')

@section('content')
<style>
    .ann-page {
        max-width: 1240px;
        margin: 0 auto;
        padding: 8px 6px 14px;
    }

    .ann-hero {
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

    .ann-hero h2 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 800;
    }

    .ann-hero p {
        margin: 5px 0 0;
        opacity: .93;
        font-size: .83rem;
    }

    .ann-add {
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

    .ann-add:hover {
        color: #fff;
        background: rgba(255,255,255,.25);
    }

    .ann-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
        gap: 12px;
    }

    .ann-card {
        background: #fff;
        border: 1px solid #dce4f2;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(13, 34, 70, .06);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .ann-card-head {
        border-bottom: 1px solid #e7edf8;
        background: #f8fbff;
        padding: 10px 12px;
    }

    .ann-title {
        margin: 0;
        color: #102f5d;
        font-size: .93rem;
        line-height: 1.35;
        font-weight: 800;
    }

    .ann-meta {
        margin-top: 4px;
        color: #6c83a8;
        font-size: .72rem;
    }

    .ann-body {
        padding: 11px 12px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        flex: 1;
    }

    .ann-desc {
        margin: 0;
        color: #405d88;
        font-size: .8rem;
        line-height: 1.5;
        white-space: pre-line;
    }

    .ann-line {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
    }

    .ann-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border-radius: 999px;
        padding: 4px 8px;
        font-size: .68rem;
        font-weight: 800;
    }

    .ann-active {
        background: #eaf7ee;
        color: #1f6f38;
        border: 1px solid #c6e8d1;
    }

    .ann-inactive {
        background: #eef2f8;
        color: #4d638a;
        border: 1px solid #d4deec;
    }

    .ann-date {
        font-size: .7rem;
        color: #6c83a8;
    }

    .ann-actions {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .ann-btn {
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

    .ann-pagination {
        margin-top: 14px;
        display: flex;
        justify-content: center;
    }

    .ann-empty {
        border: 1px dashed #cfdcf0;
        border-radius: 10px;
        background: #fff;
        padding: 28px 15px;
        text-align: center;
        color: #5a6f95;
    }
</style>

<div class="ann-page">
    <section class="ann-hero">
        <div>
            <h2>Manage Announcements</h2>
            <p>Create and control what appears to users across the system.</p>
        </div>
        <a href="{{ route('announcements.create') }}" class="ann-add">
            <i class="icon-plus"></i> New Announcement
        </a>
    </section>

    @include('includes.alerts')

    @if($announcements->count() > 0)
        <section class="ann-grid">
            @foreach($announcements as $announcement)
                <article class="ann-card">
                    <div class="ann-card-head">
                        <h3 class="ann-title">{{ $announcement->title }}</h3>
                        <div class="ann-meta">
                            By {{ $announcement->createdBy?->name ?? 'System' }} | {{ $announcement->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div class="ann-body">
                        <p class="ann-desc">{{ $announcement->description }}</p>

                        <div class="ann-line">
                            @if($announcement->is_active)
                                <span class="ann-pill ann-active">Active</span>
                            @else
                                <span class="ann-pill ann-inactive">Inactive</span>
                            @endif

                            @if($announcement->publish_date)
                                <span class="ann-date">Published: {{ $announcement->publish_date->format('d/m/Y') }}</span>
                            @endif
                        </div>

                        <div class="ann-actions">
                            <a href="{{ route('announcements.edit', $announcement->id) }}" class="ann-btn btn-edit">
                                <i class="icon-pencil"></i> Edit
                            </a>

                            <form action="{{ route('announcements.toggleStatus', $announcement) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="ann-btn btn-toggle">
                                    <i class="icon-eye"></i> {{ $announcement->is_active ? 'Disable' : 'Enable' }}
                                </button>
                            </form>

                            <form action="{{ route('announcements.destroy', $announcement->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ann-btn btn-delete">
                                    <i class="icon-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <div class="ann-pagination">
            {{ $announcements->links() }}
        </div>
    @else
        <div class="ann-empty">
            <h5 style="margin:0 0 5px; color:#1b3a6e;">No announcements yet</h5>
            <p style="margin:0 0 10px;">Create your first announcement to get started.</p>
            <a href="{{ route('announcements.create') }}" class="ann-add" style="background:#0f2f66;border-color:#0f2f66;">
                <i class="icon-plus"></i> Create Announcement
            </a>
        </div>
    @endif
</div>
@endsection
