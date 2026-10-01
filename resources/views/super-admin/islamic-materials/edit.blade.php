@extends('layouts.master')

@section('content')
<style>
    .ime-page {
        max-width: 1260px;
        margin: 0 auto;
        padding: 8px 6px 14px;
    }

    .ime-hero {
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

    .ime-hero h2 {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 800;
    }

    .ime-hero p {
        margin: 5px 0 0;
        font-size: .82rem;
        opacity: .93;
    }

    .ime-back {
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

    .ime-back:hover {
        color: #fff;
        background: rgba(255,255,255,.25);
    }

    .ime-grid {
        display: grid;
        grid-template-columns: 1.7fr .9fr;
        gap: 12px;
    }

    .ime-card {
        background: #fff;
        border: 1px solid #dce4f2;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(13, 34, 70, .06);
        overflow: hidden;
        margin-bottom: 12px;
    }

    .ime-card-head {
        border-bottom: 1px solid #e7edf8;
        background: #f8fbff;
        padding: 10px 12px;
        color: #173867;
        font-size: .88rem;
        font-weight: 800;
    }

    .ime-card-body {
        padding: 12px;
    }

    .ime-field {
        margin-bottom: 10px;
    }

    .ime-field label {
        display: block;
        margin-bottom: 5px;
        font-size: .78rem;
        color: #344f7b;
        font-weight: 700;
    }

    .ime-field .form-control,
    .ime-field .form-select {
        border-radius: 8px;
        border: 1px solid #ccd8ec;
        font-size: .83rem;
        min-height: 38px;
    }

    .ime-field select.form-control,
    .ime-field select.form-select {
        height: 44px !important;
        line-height: 1.35;
        padding-top: 8px;
        padding-bottom: 8px;
    }

    .ime-field .form-control:focus,
    .ime-field .form-select:focus {
        border-color: #0f2f66;
        box-shadow: 0 0 0 .12rem rgba(15, 47, 102, .12);
    }

    .ime-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .ime-status {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border-radius: 999px;
        padding: 4px 8px;
        font-size: .68rem;
        font-weight: 800;
    }

    .st-active {
        background: #eaf7ee;
        color: #1f6f38;
        border: 1px solid #c6e8d1;
    }

    .st-inactive {
        background: #eef2f8;
        color: #4d638a;
        border: 1px solid #d4deec;
    }

    .ime-note {
        font-size: .72rem;
        color: #6a81a7;
        margin-top: 4px;
    }

    .ime-actions {
        margin-top: 8px;
        padding-top: 10px;
        border-top: 1px solid #e7edf8;
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .ime-actions .btn {
        border-radius: 8px !important;
        font-size: .78rem !important;
        font-weight: 700 !important;
    }

    .ime-table th,
    .ime-table td {
        font-size: .78rem;
        vertical-align: middle;
    }

    .ime-preview {
        max-height: 150px;
        border-radius: 8px;
        border: 1px solid #dce5f4;
    }

    @media (max-width: 1024px) {
        .ime-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .ime-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="ime-page">
    <section class="ime-hero">
        <div>
            <h2>Edit Islamic Material</h2>
            <p>Update content, assignments, schedule, and publishing options.</p>
        </div>
        <a href="{{ route('islamic-materials.index') }}" class="ime-back">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </section>

    @include('includes.alerts')

    @if ($errors->any())
        <div class="alert alert-danger mb-3">
            <h6 style="margin:0 0 6px;">Please fix the following errors:</h6>
            <ul style="margin:0;padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="ime-grid">
        <div>
            <form action="{{ route('islamic-materials.update', $islamicMaterial) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <article class="ime-card">
                    <div class="ime-card-head">Basic Information</div>
                    <div class="ime-card-body">
                        <div class="ime-field">
                            <label>Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $islamicMaterial->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="ime-field">
                            <label>Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $islamicMaterial->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="ime-field">
                            <label>Content</label>
                            <textarea name="content" id="editor" class="form-control @error('content') is-invalid @enderror" rows="10">{{ old('content', $islamicMaterial->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </article>

                <article class="ime-card">
                    <div class="ime-card-head">Assignment</div>
                    <div class="ime-card-body">
                        <div class="ime-row">
                            <div class="ime-field">
                                <label>Target Class</label>
                                <select name="my_class_id" class="form-control @error('my_class_id') is-invalid @enderror">
                                    <option value="">-- All Classes --</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('my_class_id', $islamicMaterial->my_class_id) == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('my_class_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="ime-field">
                                <label>Teacher (Assigned To)</label>
                                <select name="teacher" class="form-control @error('teacher') is-invalid @enderror">
                                    <option value="">-- Select Teacher --</option>
                                    @foreach($teachers as $id => $name)
                                        <option value="{{ $name }}" {{ old('teacher', $islamicMaterial->teacher) === $name ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('teacher')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="ime-field">
                            <label>Schedule (Days & Times)</label>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm ime-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30%;">Day</th>
                                            <th style="width: 35%;">Start Time</th>
                                            <th style="width: 35%;">End Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                                            $schedule = old('time_table') ? json_decode(old('time_table'), true) : json_decode($islamicMaterial->time_table, true);
                                            $schedule = is_array($schedule) ? $schedule : [];
                                        @endphp
                                        @foreach($days as $index => $day)
                                            @php
                                                $daySchedule = collect($schedule)->firstWhere('day', $day) ?? [];
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input day-checkbox" id="day{{ $index }}" name="schedule_days[]" value="{{ $day }}" {{ isset($daySchedule['day']) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="day{{ $index }}">{{ $day }}</label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="time" class="form-control form-control-sm" name="schedule_start_time[]" value="{{ $daySchedule['start_time'] ?? '' }}">
                                                </td>
                                                <td>
                                                    <input type="time" class="form-control form-control-sm" name="schedule_end_time[]" value="{{ $daySchedule['end_time'] ?? '' }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <input type="hidden" id="time_table" name="time_table" value="{{ old('time_table', $islamicMaterial->time_table) }}">
                            </div>
                            @error('time_table')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </article>

                <article class="ime-card">
                    <div class="ime-card-head">Media & Attachments</div>
                    <div class="ime-card-body">
                        <div class="ime-field">
                            <label>Cover Image</label>
                            @if($islamicMaterial->image_path)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $islamicMaterial->image_path) }}" class="ime-preview">
                                </div>
                            @endif
                            <input type="file" name="image_path" class="form-control @error('image_path') is-invalid @enderror" accept="image/*">
                            <div class="ime-note">Max 2MB. Leave empty to keep current image.</div>
                            @error('image_path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="ime-field">
                            <label>Video URL</label>
                            <input type="url" name="video_url" class="form-control @error('video_url') is-invalid @enderror" value="{{ old('video_url', $islamicMaterial->video_url) }}" placeholder="https://youtube.com/watch?v=...">
                            @error('video_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="ime-field">
                            <label>Attachment (PDF, DOC, etc)</label>
                            @if($islamicMaterial->attachment_path)
                                <div class="mb-2">
                                    <a href="{{ asset('storage/' . $islamicMaterial->attachment_path) }}" class="btn btn-sm btn-outline-primary" download>
                                        <i class="fas fa-download mr-1"></i> Download Current
                                    </a>
                                </div>
                            @endif
                            <input type="file" name="attachment_path" class="form-control @error('attachment_path') is-invalid @enderror">
                            <div class="ime-note">Max 5MB. Leave empty to keep current attachment.</div>
                            @error('attachment_path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </article>

                <article class="ime-card">
                    <div class="ime-card-head">Publish Settings</div>
                    <div class="ime-card-body">
                        <div class="ime-field">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $islamicMaterial->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active"><strong>Active</strong></label>
                            </div>
                        </div>

                        <div class="ime-field">
                            <label>Publish Date</label>
                            <input type="datetime-local" name="published_at" class="form-control @error('published_at') is-invalid @enderror" value="{{ old('published_at', $islamicMaterial->published_at ? $islamicMaterial->published_at->format('Y-m-d\TH:i') : '') }}">
                            @error('published_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="ime-actions">
                            <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fas fa-save mr-1"></i> Update Material</button>
                            <a href="{{ route('islamic-materials.index') }}" class="btn btn-light border">Cancel</a>
                        </div>
                    </div>
                </article>
            </form>
        </div>

        <aside>
            <article class="ime-card">
                <div class="ime-card-head">Material Info</div>
                <div class="ime-card-body">
                    <div class="ime-field">
                        <label>Created</label>
                        <div>{{ $islamicMaterial->created_at->format('M d, Y H:i') }}</div>
                    </div>
                    <div class="ime-field">
                        <label>Last Updated</label>
                        <div>{{ $islamicMaterial->updated_at->format('M d, Y H:i') }}</div>
                    </div>
                    <div class="ime-field">
                        <label>Status</label>
                        @if($islamicMaterial->is_active)
                            <span class="ime-status st-active">Active</span>
                        @else
                            <span class="ime-status st-inactive">Inactive</span>
                        @endif
                    </div>
                </div>
            </article>
        </aside>
    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/35.0.0/classic/ckeditor.js"></script>
<script>
ClassicEditor
    .create(document.querySelector('#editor'))
    .catch(error => console.error(error));

document.getElementById('submitBtn').closest('form').addEventListener('submit', function() {
    const schedule = [];
    const startTimes = document.querySelectorAll('input[name="schedule_start_time[]"]');
    const endTimes = document.querySelectorAll('input[name="schedule_end_time[]"]');

    document.querySelectorAll('.day-checkbox').forEach((checkbox, i) => {
        if (checkbox.checked && startTimes[i].value && endTimes[i].value) {
            schedule.push({
                day: checkbox.value,
                start_time: startTimes[i].value,
                end_time: endTimes[i].value
            });
        }
    });

    document.getElementById('time_table').value = JSON.stringify(schedule);
});
</script>
@endsection
