@extends('layouts.master')
@section('page_title', 'Edit Announcement')

@section('content')
<style>
    .ann-edit-page {
        max-width: 980px;
        margin: 0 auto;
        padding: 8px 6px 14px;
    }

    .ann-edit-hero {
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

    .ann-edit-hero h2 {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 800;
    }

    .ann-edit-hero p {
        margin: 5px 0 0;
        font-size: .82rem;
        opacity: .93;
    }

    .ann-back {
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

    .ann-back:hover {
        color: #fff;
        background: rgba(255,255,255,.25);
    }

    .ann-form-shell {
        background: #fff;
        border: 1px solid #dce4f2;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(13, 34, 70, .06);
        overflow: hidden;
    }

    .ann-form-head {
        border-bottom: 1px solid #e7edf8;
        background: #f8fbff;
        padding: 10px 12px;
        color: #173867;
        font-size: .88rem;
        font-weight: 800;
    }

    .ann-form-body {
        padding: 12px;
    }

    .ann-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .ann-grid.full {
        grid-template-columns: 1fr;
    }

    .ann-field {
        margin-bottom: 10px;
    }

    .ann-field label {
        display: block;
        margin-bottom: 5px;
        font-size: .78rem;
        color: #344f7b;
        font-weight: 700;
    }

    .ann-field .form-control {
        border-radius: 8px;
        border: 1px solid #ccd8ec;
        font-size: .83rem;
        min-height: 38px;
    }

    .ann-field .form-control:focus {
        border-color: #0f2f66;
        box-shadow: 0 0 0 .12rem rgba(15, 47, 102, .12);
    }

    .ann-field textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .ann-field textarea#content {
        min-height: 200px;
    }

    .ann-check {
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #dce5f4;
        border-radius: 8px;
        background: #fbfdff;
        padding: 8px 10px;
    }

    .ann-check input[type="checkbox"] {
        width: 16px;
        height: 16px;
        margin: 0;
    }

    .ann-check span {
        font-size: .8rem;
        color: #29436c;
        font-weight: 700;
    }

    .ann-actions {
        margin-top: 12px;
        padding-top: 10px;
        border-top: 1px solid #e7edf8;
        display: flex;
        justify-content: flex-end;
        gap: 6px;
        flex-wrap: wrap;
    }

    .ann-actions .btn {
        border-radius: 8px !important;
        font-size: .78rem !important;
        font-weight: 700 !important;
        padding: .5rem .95rem !important;
    }

    .ann-error {
        color: #c32033;
        font-size: .72rem;
        margin-top: 4px;
        font-weight: 700;
    }

    @media (max-width: 768px) {
        .ann-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="ann-edit-page">
    <section class="ann-edit-hero">
        <div>
            <h2>Edit Announcement</h2>
            <p>Update content, schedule, and visibility settings.</p>
        </div>
        <a href="{{ route('announcements.index') }}" class="ann-back">
            <i class="icon-arrow-left"></i> Back
        </a>
    </section>

    @include('includes.alerts')

    <section class="ann-form-shell">
        <div class="ann-form-head">Announcement Form</div>
        <div class="ann-form-body">
            <form action="{{ route('announcements.update', $announcement->id) }}" method="POST">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="alert alert-danger mb-3">
                        <ul style="margin:0;padding-left:18px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="ann-grid full">
                    <div class="ann-field">
                        <label for="title">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $announcement->title) }}" required>
                        @error('title')
                            <div class="ann-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="ann-grid full">
                    <div class="ann-field">
                        <label for="description">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" required>{{ old('description', $announcement->description) }}</textarea>
                        @error('description')
                            <div class="ann-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="ann-grid full">
                    <div class="ann-field">
                        <label for="content">Content <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" required>{{ old('content', $announcement->content) }}</textarea>
                        @error('content')
                            <div class="ann-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="ann-grid">
                    <div class="ann-field">
                        <label for="publish_date">Publish Date</label>
                        <input type="datetime-local" class="form-control @error('publish_date') is-invalid @enderror" id="publish_date" name="publish_date" value="{{ old('publish_date', $announcement->publish_date ? $announcement->publish_date->format('Y-m-d\TH:i') : '') }}">
                        @error('publish_date')
                            <div class="ann-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="ann-field">
                        <label for="expire_date">Expire Date</label>
                        <input type="datetime-local" class="form-control @error('expire_date') is-invalid @enderror" id="expire_date" name="expire_date" value="{{ old('expire_date', $announcement->expire_date ? $announcement->expire_date->format('Y-m-d\TH:i') : '') }}">
                        @error('expire_date')
                            <div class="ann-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="ann-grid full">
                    <div class="ann-check">
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $announcement->is_active) ? 'checked' : '' }}>
                        <span>Active (visible to users)</span>
                    </div>
                </div>

                <div class="ann-actions">
                    <a href="{{ route('announcements.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="icon-check mr-1"></i> Update Announcement</button>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
