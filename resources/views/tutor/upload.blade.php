@extends('layouts.master')

@section('title', 'Upload Files - Smart Tutor')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Upload Curriculum Files</h5>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('tutor.files.store') }}"
                          method="POST"
                          enctype="multipart/form-data"
                          id="uploadForm">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-bold">Year / Grade</label>
                            <select name="year" class="form-select" required>
                                <option value="">Select Year</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->name }}" data-class-id="{{ $class->id }}" {{ old('year') === $class->name ? 'selected' : '' }}>{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Subject</label>
                            <select name="subject" id="subjectSelect" class="form-select" required>
                                <option value="">Select Subject</option>
                            </select>
                            <small class="text-muted">
                                Subjects are loaded from assigned class subjects.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Activity Type (Moodle)</label>
                            <select name="activity_type" id="activityTypeSelect" class="form-select">
                                <option value="resource" {{ old('activity_type', 'resource') === 'resource' ? 'selected' : '' }}>File Resource</option>
                                <option value="assignment" {{ old('activity_type') === 'assignment' ? 'selected' : '' }}>Assignment</option>
                                <option value="quiz" {{ old('activity_type') === 'quiz' ? 'selected' : '' }}>Quiz</option>
                            </select>
                            <small class="text-muted">
                                Choose where Moodle should publish this upload. Resource is the default option.
                            </small>
                        </div>

                        <div id="assignmentFields" class="border rounded p-3 mb-3 d-none">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Assignment Name</label>
                                <input type="text" name="activity_title" id="activityTitleInput" class="form-control"
                                       value="{{ old('activity_title') }}" maxlength="255"
                                       placeholder="e.g. Term 2 Homework 3">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <textarea name="activity_intro" id="activityIntroInput" class="form-control" rows="3"
                                          placeholder="Instructions for students...">{{ old('activity_intro') }}</textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Available From</label>
                                    <input type="datetime-local" name="available_from" id="availableFromInput"
                                           class="form-control" value="{{ old('available_from') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Due Date</label>
                                    <input type="datetime-local" name="due_at" id="dueAtInput"
                                           class="form-control" value="{{ old('due_at') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Cutoff Date</label>
                                    <input type="datetime-local" name="cutoff_at" id="cutoffAtInput"
                                           class="form-control" value="{{ old('cutoff_at') }}">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Files</label>
                            <input type="file"
                                   name="files[]"
                                   class="form-control"
                                   multiple
                                   required
                                   accept=".pdf,.docx,.xlsx,.pptx,.txt"
                                   id="fileInput">
                            <small class="text-muted">
                                Supported: PDF, Word, Excel, PowerPoint, Text (Max: 50MB per file)
                            </small>
                        </div>

                        <div class="mb-3">
                            <div id="filePreview" class="row g-2"></div>
                        </div>

                        <div class="progress mb-3 d-none" id="uploadProgress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar"
                                 style="width: 0%">0%</div>
                        </div>

                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            Upload Files
                        </button>

                        <a href="{{ route('tutor.index') }}" class="btn btn-secondary">
                            Back to Tutor
                        </a>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Upload Instructions</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Files are automatically organized by year and subject</li>
                        <li>Supported formats: PDF, DOCX, XLSX, PPTX, TXT</li>
                        <li>Maximum file size: 50MB per file</li>
                        <li>You can select multiple files at once</li>
                        <li>Files will be available immediately in the tutor</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const selectedSubject = @json(old('subject', ''));
const selectedActivityType = @json(old('activity_type', 'resource'));
const yearSelect = document.querySelector('select[name="year"]');
const subjectSelect = document.getElementById('subjectSelect');
const subjectsUrlTemplate = @json(route('get_class_subjects', ['class_id' => '__CLASS_ID__']));
const activityTypeSelect = document.getElementById('activityTypeSelect');
const assignmentFields = document.getElementById('assignmentFields');
const activityTitleInput = document.getElementById('activityTitleInput');
const availableFromInput = document.getElementById('availableFromInput');
const dueAtInput = document.getElementById('dueAtInput');

function renderSubjectOptions(subjects) {
    subjectSelect.innerHTML = '';

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = subjects.length > 0 ? 'Select Subject' : 'No subjects available for this class';
    subjectSelect.appendChild(placeholder);

    subjects.forEach(subjectName => {
        const option = document.createElement('option');
        option.value = subjectName;
        option.textContent = subjectName;
        if (selectedSubject && selectedSubject === subjectName) {
            option.selected = true;
        }
        subjectSelect.appendChild(option);
    });
}

async function loadSubjectsForSelectedClass() {
    if (!yearSelect || !subjectSelect) {
        return;
    }

    const selectedOption = yearSelect.options[yearSelect.selectedIndex];
    const classId = selectedOption ? selectedOption.getAttribute('data-class-id') : '';
    if (!classId) {
        renderSubjectOptions([]);
        return;
    }

    try {
        const url = subjectsUrlTemplate.replace('__CLASS_ID__', encodeURIComponent(classId));
        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!response.ok) {
            renderSubjectOptions([]);
            return;
        }

        const data = await response.json();
        const rawSubjects = Array.isArray(data.subjects)
            ? data.subjects
            : (data.subjects && typeof data.subjects === 'object' ? Object.values(data.subjects) : []);
        const subjects = rawSubjects.map(s => (s && s.name ? String(s.name) : '').trim()).filter(Boolean);
        renderSubjectOptions(subjects);
    } catch (error) {
        renderSubjectOptions([]);
    }
}

if (yearSelect && subjectSelect) {
    loadSubjectsForSelectedClass();
    yearSelect.addEventListener('change', loadSubjectsForSelectedClass);
}

function toggleActivityFields() {
    const type = (activityTypeSelect ? activityTypeSelect.value : 'resource');
    const isAssignment = type === 'assignment';
    assignmentFields.classList.toggle('d-none', !isAssignment);
    if (activityTitleInput) {
        activityTitleInput.required = isAssignment;
    }
    if (availableFromInput) {
        availableFromInput.required = isAssignment;
    }
    if (dueAtInput) {
        dueAtInput.required = isAssignment;
    }
}

if (activityTypeSelect) {
    activityTypeSelect.value = selectedActivityType || 'resource';
    toggleActivityFields();
    activityTypeSelect.addEventListener('change', toggleActivityFields);
}

document.getElementById('fileInput').addEventListener('change', function() {
    const preview = document.getElementById('filePreview');
    preview.innerHTML = '';

    Array.from(this.files).forEach(file => {
        const col = document.createElement('div');
        col.className = 'col-md-4';
        col.innerHTML = `
            <div class="card mb-2">
                <div class="card-body p-2">
                    <small class="d-block text-truncate">${file.name}</small>
                    <small class="text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                </div>
            </div>
        `;
        preview.appendChild(col);
    });
});

document.getElementById('uploadForm').addEventListener('submit', function() {
    const submitBtn = document.getElementById('submitBtn');
    const progressBar = document.getElementById('uploadProgress');

    submitBtn.disabled = true;
    progressBar.classList.remove('d-none');

    let progress = 0;
    const interval = setInterval(() => {
        progress += 10;
        progressBar.querySelector('.progress-bar').style.width = progress + '%';
        progressBar.querySelector('.progress-bar').textContent = progress + '%';

        if (progress >= 100) {
            clearInterval(interval);
        }
    }, 300);
});
</script>
@endpush
@endsection
