@extends('layouts.master')
@section('page_title', 'Upload Attendance')
@section('content')

<style>
    .upload-container {
        max-width: 700px;
        margin: 0 auto;
    }

    .upload-box {
        border: 3px dashed #667eea;
        border-radius: 15px;
        padding: 40px;
        text-align: center;
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        cursor: pointer;
        transition: all 0.3s ease;
        min-height: 300px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .upload-box:hover {
        border-color: #764ba2;
        background: linear-gradient(135deg, #edf2f7 0%, #e0e7ff 100%);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
    }

    .upload-box i {
        font-size: 50px;
        color: #667eea;
        margin-bottom: 15px;
    }

    .upload-box.drag-over {
        border-color: #764ba2;
        background: linear-gradient(135deg, #e0e7ff 0%, #dbeafe 100%);
    }

    .upload-text {
        margin: 15px 0;
        font-size: 16px;
        color: #2d3748;
        font-weight: 600;
    }

    .upload-hint {
        color: #718096;
        font-size: 14px;
        margin: 10px 0 0 0;
    }

    #file-input {
        display: none;
    }

    .file-preview {
        margin-top: 20px;
        padding: 15px;
        background: white;
        border-radius: 10px;
        border-left: 4px solid #667eea;
    }

    .file-name {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 5px;
    }

    .form-section {
        margin-bottom: 25px;
    }

    .form-label {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 8px;
        display: block;
    }

    .form-control {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px;
        font-size: 15px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .submit-btn {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    }

    .submit-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .alert {
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert i {
        font-size: 18px;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-left: 4px solid #10b981;
    }

    .alert-error {
        background: #fee2e2;
        color: #7f1d1d;
        border-left: 4px solid #ef4444;
    }

    .sample-header {
        background: #f3f4f6;
        padding: 10px;
        border-radius: 6px;
        font-family: monospace;
        font-size: 12px;
        color: #374151;
        margin: 15px 0;
        overflow-x: auto;
    }
</style>

<div class="container">
     <a href="{{ route('attendance.index') }}" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Attendance
    </a>
    <div class="upload-container">
        {{-- الرسائل --}}
        @if ($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        {{-- الفورم --}}
        <form method="POST" action="{{ route('attendance.store') }}" enctype="multipart/form-data" id="uploadForm">
            @csrf

            {{-- العنوان --}}
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="font-size: 28px; font-weight: 800; color: #2d3748; margin-bottom: 8px;">
                    Upload Attendance
                </h2>
                <p style="color: #718096; font-size: 15px;">
                    Upload a CSV file with attendance data from BigBlueButton
                </p>
            </div>

            {{-- البيانات --}}
            <div class="form-section">
                <label for="subject_name" class="form-label">Subject Name <span style="color: #ef4444;">*</span></label>
                <input type="text" 
                       id="subject_name" 
                       name="subject_name" 
                       class="form-control"
                       placeholder="e.g., Mathematics, English"
                       value="{{ old('subject_name') }}"
                       required>
            </div>

            <div class="form-section">
                <label for="attendance_date" class="form-label">Attendance Date <span style="color: #ef4444;">*</span></label>
                <input type="date" 
                       id="attendance_date" 
                       name="attendance_date" 
                       class="form-control"
                       value="{{ old('attendance_date', date('Y-m-d')) }}"
                       required>
            </div>

            {{-- رفع الملف --}}
            <div class="form-section">
                <label for="file" class="form-label">Select CSV File <span style="color: #ef4444;">*</span></label>
                <div class="upload-box" id="dropZone">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <div class="upload-text">Drag & drop your CSV file here</div>
                    <div class="upload-hint">or click to select file</div>
                    <input type="file" id="file-input" name="file" accept=".csv" required>
                </div>
                <div id="filePreview" class="file-preview" style="display: none;">
                    <div class="file-name" id="fileName"></div>
                    <small style="color: #718096;" id="fileSize"></small>
                </div>
            </div>

            {{-- معلومات الملف المطلوبة --}}
            <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div style="font-weight: 600; color: #92400e; margin-bottom: 10px;">
                    <i class="fas fa-info-circle"></i> Required CSV Columns
                </div>
                <div class="sample-header">
Name | Moderator | Activity Score | Talk time | Webcam Time | Messages | Reactions | Poll Votes | Raise Hands | Join | Left | Duration
                </div>
                <small style="color: #92400e;">
                    Make sure your CSV file has these exact column names in the first row.
                </small>
            </div>

            {{-- الزر --}}
            <button type="submit" class="submit-btn" id="submitBtn">
                <i class="fas fa-upload mr-2"></i> Upload Attendance
            </button>
        </form>
    </div>
</div>

<script>
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('file-input');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');

    // Drag & Drop
    dropZone.addEventListener('click', () => fileInput.click());

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('drag-over');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            updateFilePreview();
        }
    });

    fileInput.addEventListener('change', updateFilePreview);

    function updateFilePreview() {
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            fileName.textContent = file.name;
            fileSize.textContent = (file.size / 1024).toFixed(2) + ' KB';
            filePreview.style.display = 'block';
        } else {
            filePreview.style.display = 'none';
        }
    }
</script>

@endsection
