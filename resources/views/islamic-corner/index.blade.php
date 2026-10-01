@extends('layouts.master')

@section('content')
@php
    $isStudent = strtolower((string) optional(auth()->user())->user_type) === 'student';
@endphp

<style>
    .ic-page {
        max-width: 1180px;
        margin: 0 auto;
        padding: 6px 4px 14px;
    }

    .ic-hero {
        border-radius: 14px;
        background: linear-gradient(120deg, #0f2f66 0%, #1b4f9d 60%, #c32033 100%);
        color: #fff;
        padding: 16px 18px;
        margin-bottom: 12px;
        box-shadow: 0 10px 24px rgba(15, 47, 102, .2);
    }

    .ic-hero h2 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 800;
    }

    .ic-hero p {
        margin: 6px 0 0;
        opacity: .94;
        font-size: .85rem;
    }

    .ic-toolbar {
        background: #fff;
        border: 1px solid #d9e3f2;
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 12px;
    }

    .ic-toolbar .form-control {
        border: 1px solid #ccd8ec;
        height: 39px;
        border-radius: 8px;
    }

    .ic-toolbar .form-control:focus {
        border-color: #0f2f66;
        box-shadow: 0 0 0 .12rem rgba(15, 47, 102, .12);
    }

    .ic-btn {
        height: 39px;
        border: 0;
        border-radius: 8px;
        padding: 0 14px;
        font-weight: 700;
        font-size: .82rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .ic-btn-primary {
        background: #0f2f66;
        color: #fff;
    }

    .ic-btn-primary:hover {
        background: #0b2450;
        color: #fff;
    }

    .ic-btn-light {
        background: #eef3fb;
        color: #274270;
        border: 1px solid #d5e0f1;
    }

    .ic-btn-light:hover {
        background: #e3ecfa;
    }

    .ic-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 12px;
    }

    .ic-card {
        background: #fff;
        border: 1px solid #dce4f2;
        border-radius: 12px;
        padding: 12px;
        box-shadow: 0 7px 18px rgba(13, 34, 70, .06);
        display: flex;
        flex-direction: column;
        min-height: 190px;
    }

    .ic-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 9px;
    }

    .ic-chip {
        font-size: .68rem;
        font-weight: 700;
        color: #214374;
        background: #eef4ff;
        border: 1px solid #d8e3f7;
        padding: 3px 8px;
        border-radius: 999px;
    }

    .ic-chip.ic-date {
        background: #fff3f5;
        border-color: #ffd3da;
        color: #8f1f30;
    }

    .ic-title {
        margin: 0 0 7px;
        font-size: .95rem;
        line-height: 1.35;
        color: #112b4f;
        font-weight: 800;
    }

    .ic-desc {
        margin: 0;
        color: #566c90;
        font-size: .8rem;
        line-height: 1.45;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .ic-card-foot {
        margin-top: auto;
        padding-top: 10px;
    }

    .ic-open {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 36px;
        border-radius: 8px;
        background: #c32033;
        color: #fff;
        font-size: .8rem;
        font-weight: 700;
        text-decoration: none;
    }

    .ic-open:hover {
        color: #fff;
        background: #aa1a2a;
    }

    .ic-empty {
        background: #fff;
        border: 1px dashed #cfdcf0;
        border-radius: 12px;
        padding: 30px 18px;
        text-align: center;
        color: #5a6f95;
    }

    .ic-pagination {
        margin-top: 14px;
        display: flex;
        justify-content: center;
    }

    @media (max-width: 768px) {
        .ic-hero h2 {
            font-size: 1.05rem;
        }
    }
</style>

<div class="ic-page">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <section class="ic-hero">
        @if($isStudent)
            <h2>Islamic Corner For Students</h2>
            <p>Lessons, reminders, and activities tailored to your class.</p>
        @else
            <h2>Islamic Corner</h2>
            <p>Manage and review assigned Islamic materials.</p>
        @endif
    </section>

    <!-- <section class="ic-toolbar">
        <form method="GET">
            <div class="row">
                <div class="col-md-8 mb-2 mb-md-0">
                    <input type="text" name="search" class="form-control" placeholder="Search by title or keyword..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4 d-flex" style="gap:8px;">
                    <button type="submit" class="ic-btn ic-btn-primary flex-fill">
                        <i class="fas fa-search"></i> Search
                    </button>
                    @if(request()->filled('search'))
                        <a href="{{ route('islamic-corner.index') }}" class="ic-btn ic-btn-light flex-fill">
                            <i class="fas fa-redo"></i> Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </section> -->

    @if($materials->count())
        <section class="ic-grid">
            @foreach($materials as $material)
                <article class="ic-card">
                    <div class="ic-chip-row">
                        @if($material->stage)
                            <span class="ic-chip">{{ ucfirst($material->stage) }} Stage</span>
                        @endif
                        @if($material->class)
                            <span class="ic-chip">{{ $material->class->name }}</span>
                        @endif
                        <span class="ic-chip ic-date">{{ optional($material->published_at)->format('M d, Y') }}</span>
                    </div>

                    <h3 class="ic-title">{{ $material->title }}</h3>
                    <p class="ic-desc">{{ $material->description ?: 'No description available.' }}</p>

                    <div class="ic-card-foot">
                        <a href="{{ route('islamic-corner.show', $material) }}" class="ic-open">
                            <i class="fas fa-arrow-right"></i> Open Material
                        </a>
                    </div>
                </article>
            @endforeach
        </section>

        <div class="ic-pagination">
            {{ $materials->links() }}
        </div>
    @else
        <div class="ic-empty">
            <div style="font-size:2rem; line-height:1;">&#9785;</div>
            <h5 style="margin:10px 0 6px; color:#1b3a6e;">No materials found</h5>
            <p style="margin:0;">Try changing your search keywords or check again later.</p>
        </div>
    @endif
</div>
@endsection
