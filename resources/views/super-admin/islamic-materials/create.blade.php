@extends('layouts.master')

@section('content')
<style>
    .imc-page {
        max-width: 1260px;
        margin: 0 auto;
        padding: 8px 6px 14px;
    }

    .imc-hero {
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

    .imc-hero h2 {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 800;
    }

    .imc-hero p {
        margin: 5px 0 0;
        font-size: .82rem;
        opacity: .93;
    }

    .imc-back {
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

    .imc-back:hover {
        color: #fff;
        background: rgba(255,255,255,.25);
    }

    .imc-grid {
        display: grid;
        grid-template-columns: 1.7fr .9fr;
        gap: 12px;
    }

    .imc-card {
        background: #fff;
        border: 1px solid #dce4f2;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(13, 34, 70, .06);
        overflow: hidden;
        margin-bottom: 12px;
    }

    .imc-card-head {
        border-bottom: 1px solid #e7edf8;
        background: #f8fbff;
        padding: 10px 12px;
        color: #173867;
        font-size: .88rem;
        font-weight: 800;
    }

    .imc-card-body {
        padding: 12px;
    }

    .imc-field {
        margin-bottom: 10px;
    }

    .imc-field label {
        display: block;
        margin-bottom: 5px;
        font-size: .78rem;
        color: #344f7b;
        font-weight: 700;
    }

    .imc-field .form-control,
    .imc-field .form-select {
        border-radius: 8px;
        border: 1px solid #ccd8ec;
        font-size: .83rem;
        min-height: 38px;
    }

    .imc-field select.form-control,
    .imc-field select.form-select {
        height: 44px !important;
        line-height: 1.35;
        padding-top: 8px;
        padding-bottom: 8px;
    }

    .imc-field .form-control:focus,
    .imc-field .form-select:focus {
        border-color: #0f2f66;
        box-shadow: 0 0 0 .12rem rgba(15, 47, 102, .12);
    }

    .imc-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .imc-note {
        font-size: .72rem;
        color: #6a81a7;
        margin-top: 4px;
    }

    .imc-actions {
        margin-top: 8px;
        padding-top: 10px;
        border-top: 1px solid #e7edf8;
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .imc-actions .btn {
        border-radius: 8px !important;
        font-size: .78rem !important;
        font-weight: 700 !important;
    }

    .imc-table th,
    .imc-table td {
        font-size: .78rem;
        vertical-align: middle;
    }

    .imc-tip-list {
        margin: 0;
        padding-left: 0;
        list-style: none;
    }

    .imc-tip-list li {
        font-size: .77rem;
        color: #48658f;
        margin-bottom: 8px;
    }

    @media (max-width: 1024px) {
        .imc-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .imc-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="imc-page">
    <section class="imc-hero">
        <div>
            <h2>Add New Islamic Material</h2>
            <p>Create a new material with schedule, media, and publishing settings.</p>
        </div>
        <a href="{{ route('islamic-materials.index') }}" class="imc-back">
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

    <div class="imc-grid">
        <div>
            <form action="{{ route('islamic-materials.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <article class="imc-card">
                    <div class="imc-card-head">Basic Information</div>
                    <div class="imc-card-body">
                        <div class="imc-field">
                            <label>Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="imc-field">
                            <label>Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="imc-field">
                            <label>Content</label>
                            <textarea name="content" id="editor" class="form-control @error('content') is-invalid @enderror" rows="10">{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </article>

                <article class="imc-card">
                    <div class="imc-card-head">Assignment</div>
                    <div class="imc-card-body">
                        <div class="imc-row">
                            <div class="imc-field">
                                <label>Target Class</label>
                                <select name="my_class_id" class="form-control @error('my_class_id') is-invalid @enderror">
                                    <option value="">-- All Classes --</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('my_class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('my_class_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="imc-field">
                                <label>Teacher (Assigned To)</label>
                                <select name="teacher" class="form-control @error('teacher') is-invalid @enderror">
                                    <option value="">-- Select Teacher --</option>
                                    @foreach($teachers as $id => $name)
                                        <option value="{{ $name }}" {{ old('teacher') === $name ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('teacher')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="imc-field">
                            <label>Schedule (Days & Times)</label>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm imc-table mb-0">
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
                                            $schedule = old('time_table') ? json_decode(old('time_table'), true) : [];
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
                                                <td><input type="time" class="form-control form-control-sm" name="schedule_start_time[]" value="{{ $daySchedule['start_time'] ?? '' }}"></td>
                                                <td><input type="time" class="form-control form-control-sm" name="schedule_end_time[]" value="{{ $daySchedule['end_time'] ?? '' }}"></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <input type="hidden" id="time_table" name="time_table" value="{{ old('time_table') }}">
                            </div>
                            @error('time_table')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </article>

                <article class="imc-card">
                    <div class="imc-card-head">Media & Attachments</div>
                    <div class="imc-card-body">
                        <div class="imc-field">
                            <label>Cover Image</label>
                            <input type="file" name="image_path" class="form-control @error('image_path') is-invalid @enderror" accept="image/*">
                            <div class="imc-note">Max 2MB.</div>
                            @error('image_path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="imc-field">
                            <label>Video URL</label>
                            <input type="url" name="video_url" class="form-control @error('video_url') is-invalid @enderror" value="{{ old('video_url') }}" placeholder="https://youtube.com/watch?v=...">
                            @error('video_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="imc-field">
                            <label>Attachment (PDF, DOC, etc)</label>
                            <input type="file" name="attachment_path" class="form-control @error('attachment_path') is-invalid @enderror">
                            <div class="imc-note">Max 5MB.</div>
                            @error('attachment_path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </article>

                <article class="imc-card">
                    <div class="imc-card-head">Publish Settings</div>
                    <div class="imc-card-body">
                        <div class="imc-field">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active"><strong>Publish this material immediately</strong></label>
                            </div>
                        </div>

                        <div class="imc-field">
                            <label>Publish Date</label>
                            <input type="datetime-local" name="published_at" class="form-control @error('published_at') is-invalid @enderror" value="{{ old('published_at') ? date('Y-m-d\TH:i', strtotime(old('published_at'))) : '' }}">
                            @error('published_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="imc-actions">
                            <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fas fa-save mr-1"></i> Create Material</button>
                            <a href="{{ route('islamic-materials.index') }}" class="btn btn-light border">Cancel</a>
                        </div>
                    </div>
                </article>
            </form>
        </div>

        <aside>
            <article class="imc-card">
                <div class="imc-card-head">Tips</div>
                <div class="imc-card-body">
                    <ul class="imc-tip-list">
                        <li>Use a clear title and concise description.</li>
                        <li>Assign a class only when needed; leave empty for all classes.</li>
                        <li>Include at least one schedule entry for live sessions.</li>
                        <li>Add media for better engagement.</li>
                        <li>Set publish date if material should go live later.</li>
                    </ul>
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
