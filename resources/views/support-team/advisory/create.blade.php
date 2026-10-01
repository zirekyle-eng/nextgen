@extends('layouts.master')

@section('content')
<style>
    body {
        background-color: #f5f7fa;
    }

    .advisory-container {
        padding: 2rem 0;
    }

    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: none;
        overflow: hidden;
    }

    .form-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem 1.5rem;
        text-align: center;
    }

    .form-header h5 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .form-header i {
        margin-right: 0.8rem;
        font-size: 1.3rem;
    }

    .form-body {
        padding: 2rem 1.5rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .form-row.full {
        grid-template-columns: 1fr;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: #2d3748;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-control, .form-select {
        padding: 0.8rem 1rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    .form-control.is-invalid, .form-select.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 120px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .invalid-feedback {
        font-size: 0.8rem;
        color: #dc3545;
        margin-top: 0.3rem;
        display: block;
    }

    .btn-group-form {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #e2e8f0;
    }

    .btn-submit {
        flex: 1;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 0.9rem 2rem;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(102, 126, 234, 0.4);
    }

    .btn-cancel {
        flex: 1;
        background: white;
        color: #667eea;
        border: 2px solid #667eea;
        padding: 0.9rem 2rem;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-cancel:hover {
        background: #667eea;
        color: white;
    }

    .page-header {
        margin-bottom: 2rem;
    }

    .page-header h4 {
        color: #2d3748;
        font-weight: 700;
        margin: 0;
        font-size: 1.8rem;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .btn-group-form {
            flex-direction: column;
        }

        .form-body {
            padding: 1.5rem;
        }
    }
</style>

<div class="container-fluid advisory-container">
    <div class="page-header text-center">
        <h4><i class="fas fa-comments"></i> Start New Advisory Conversation</h4>
    </div>

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="form-card">
                <div class="form-header">
                    <h5><i class="fas fa-plus-circle"></i> Create New Conversation</h5>
                </div>

                <div class="form-body">
                    <form action="{{ route('advisory.management.store') }}" method="POST" novalidate>
                        @csrf

                        <!-- Select Parent & Student Row -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="parent_id" class="form-label">Select Parent <span style="color: #dc3545;">*</span></label>
                                <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id" required>
                                    <option value="">-- Choose Parent --</option>
                                    @foreach($parents as $parent)
                                        <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->name }} ({{ $parent->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('parent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="student_id" class="form-label">Select Student <span style="color: #dc3545;">*</span></label>
                                <select class="form-select @error('student_id') is-invalid @enderror" id="student_id" name="student_id" required>
                                    <option value="">-- Choose Student --</option>
                                </select>
                                @error('student_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Subject & Priority Row -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="subject" class="form-label">Subject <span style="color: #dc3545;">*</span></label>
                                <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" placeholder="Enter conversation subject" value="{{ old('subject') }}" required>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="priority" class="form-label">Priority <span style="color: #dc3545;">*</span></label>
                                <select class="form-select" id="priority" name="priority">
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }} selected>Medium</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="form-row full">
                            <div class="form-group">
                                <label for="description" class="form-label">Initial Message <span style="color: #dc3545;">*</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" placeholder="Write your initial message here..." required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="btn-group-form">
                            <button type="submit" class="btn-submit">
                                <i class="fas fa-check-circle"></i> Start Conversation
                            </button>
                            <a href="{{ route('advisory.management.index') }}" class="btn-cancel">
                                <i class="fas fa-times-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Load students when parent is selected
document.getElementById('parent_id').addEventListener('change', function() {
    const parentId = this.value;
    const studentSelect = document.getElementById('student_id');
    
    studentSelect.innerHTML = '<option value="">-- Choose Student --</option>';
    
    if (parentId) {
        fetch(`/api/students-of-parent/${parentId}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(student => {
                    const option = document.createElement('option');
                    option.value = student.id;
                    option.textContent = student.name;
                    studentSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error:', error));
    }
});
</script>
@endsection

