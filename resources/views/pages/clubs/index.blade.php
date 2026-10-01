@extends('layouts.master')

@php use Illuminate\Support\Str; @endphp

@section('content')
<style>
    .clubs-page {
        max-width: 1220px;
        margin: 0 auto;
        padding: 6px 6px 14px;
    }

    .clubs-hero {
        border-radius: 14px;
        background: linear-gradient(120deg, #0f2f66 0%, #1b4f9d 58%, #c32033 100%);
        color: #fff;
        padding: 16px 18px;
        margin-bottom: 12px;
        box-shadow: 0 10px 24px rgba(15, 47, 102, .2);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .clubs-hero h2 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 800;
    }

    .clubs-hero p {
        margin: 5px 0 0;
        font-size: .84rem;
        opacity: .93;
    }

    .clubs-create-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 36px;
        border-radius: 8px;
        padding: 0 12px;
        font-size: .8rem;
        font-weight: 700;
        text-decoration: none;
        color: #fff;
        border: 1px solid rgba(255,255,255,.35);
        background: rgba(255,255,255,.18);
    }

    .clubs-create-btn:hover {
        color: #fff;
        background: rgba(255,255,255,.28);
    }

    .clubs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 12px;
    }

    .club-card {
        background: #fff;
        border: 1px solid #dce4f2;
        border-radius: 12px;
        box-shadow: 0 7px 18px rgba(13, 34, 70, .06);
        padding: 12px;
        display: flex;
        flex-direction: column;
        min-height: 210px;
    }

    .club-name {
        margin: 0 0 6px;
        color: #102f5d;
        font-size: .98rem;
        line-height: 1.35;
        font-weight: 800;
    }

    .club-desc {
        margin: 0 0 8px;
        color: #5d7398;
        font-size: .8rem;
        line-height: 1.45;
        min-height: 52px;
    }

    .club-meta {
        display: grid;
        gap: 5px;
        margin-bottom: 9px;
    }

    .club-meta-item {
        font-size: .76rem;
        color: #2f4b78;
        background: #f4f8ff;
        border: 1px solid #dce7f7;
        border-radius: 7px;
        padding: 5px 7px;
    }

    .club-actions {
        margin-top: auto;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .club-btn {
        border: 0;
        border-radius: 7px;
        padding: 6px 9px;
        font-size: .75rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
        cursor: pointer;
    }

    .club-btn-view {
        background: #0f2f66;
        color: #fff;
    }

    .club-btn-view:hover {
        color: #fff;
        background: #0b2450;
    }

    .club-btn-join {
        background: #e6f7eb;
        color: #1f7a3b;
        border: 1px solid #bfe8cc;
    }

    .club-btn-leave {
        background: #fff1f3;
        color: #a72132;
        border: 1px solid #ffd3da;
    }

    .club-btn-edit {
        background: #fff6e8;
        color: #94621a;
        border: 1px solid #ffe3b3;
    }

    .club-btn-delete {
        background: #ffe9ed;
        color: #aa2032;
        border: 1px solid #ffc9d1;
    }

    .club-empty {
        background: #fff;
        border: 1px dashed #cfdcf0;
        border-radius: 12px;
        padding: 28px 15px;
        text-align: center;
        color: #5a6f95;
    }

    .clubs-pagination {
        margin-top: 14px;
        display: flex;
        justify-content: center;
    }
</style>

<div class="clubs-page">
    <section class="clubs-hero">
        <div>
            <h2>{{ $page_title }}</h2>
            <p>Explore student communities, activities, and engagement spaces.</p>
        </div>
        @if(Qs::userIsTeamSAT())
            <a href="{{ route('clubs.create') }}" class="clubs-create-btn">
                <i class="fa fa-plus"></i> Create Club
            </a>
        @endif
    </section>

    @include('includes.alerts')

    @if($clubs->count())
        <section class="clubs-grid">
            @foreach($clubs as $club)
                @php
                    $isMember = isset($my_clubs) && in_array($club->id, $my_clubs ?? []);
                    $canJoin = Qs::userIsStudent() && !$isMember;
                @endphp
                <article class="club-card">
                    <h3 class="club-name">{{ $club->name }}</h3>
                    <p class="club-desc">{{ Str::limit($club->description, 110) ?: 'No description added yet.' }}</p>

                    <div class="club-meta">
                        @if($club->leader)
                            <div class="club-meta-item"><i class="fa fa-user"></i> Leader: {{ $club->leader->name }}</div>
                        @endif
                        @if(isset($club->members_count))
                            <div class="club-meta-item"><i class="fa fa-users"></i> Members: {{ $club->members_count }}</div>
                        @endif
                    </div>

                    <div class="club-actions">
                        <a href="{{ route('clubs.show', $club) }}" class="club-btn club-btn-view">
                            <i class="fa fa-eye"></i> View
                        </a>

                        @if($isMember)
                            <form action="{{ route('clubs.leave', $club) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="club-btn club-btn-leave" onclick="return confirm('Are you sure you want to leave this club?')">
                                    <i class="fa fa-sign-out"></i> Leave
                                </button>
                            </form>
                        @elseif($canJoin)
                            <form action="{{ route('clubs.join', $club) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="club-btn club-btn-join">
                                    <i class="fa fa-sign-in"></i> Join
                                </button>
                            </form>
                        @endif

                        @if(Qs::userIsTeamSAT())
                            <a href="{{ route('clubs.edit', $club) }}" class="club-btn club-btn-edit">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('clubs.destroy', $club) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="club-btn club-btn-delete" onclick="return confirm('Are you sure you want to delete this club?')">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>

        <div class="clubs-pagination">
            {{ $clubs->links('pagination::bootstrap-4') }}
        </div>
    @else
        <div class="club-empty">
            <h5 style="margin: 0 0 5px; color:#1b3a6e;">No clubs available</h5>
            <p style="margin:0;">Create a new club to get started.</p>
        </div>
    @endif
</div>
@endsection
