@extends('layouts.master')

@section('content')
<style>
    body {
        background-color: #f5f7fa;
    }

    .advisory-create-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
    }

    .advisory-create-header h1 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .advisory-create-header p {
        margin: 0.3rem 0 0 0;
        opacity: 0.95;
        font-size: 0.9rem;
    }

    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        border: none;
        margin-bottom: 1.5rem;
    }

    .form-card .card-body {
        padding: 1.5rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .form-row.full {
        grid-template-columns: 1fr;
    }

    .form-group-wrapper {
        margin-bottom: 0;
    }

    .form-label {
        font-weight: 700;
        color: #2d3748;
        font-size: 0.85rem;
        margin-bottom: 0.4rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .form-label i {
        color: #667eea;
        font-size: 0.95rem;
    }

    .form-control, .form-select {
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.6rem 0.8rem;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        background-color: #fafbfc;
    }

    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        background-color: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-control::placeholder {
        color: #a0aec0;
        font-weight: 500;
    }

    .form-text {
        font-size: 0.75rem;
        color: #718096 !important;
        margin-top: 0.3rem;
        display: block;
    }

    .invalid-feedback {
        color: #e53e3e;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .is-invalid {
        border-color: #e53e3e !important;
    }

    .required-asterisk {
        color: #e53e3e;
        font-weight: 700;
    }

    .form-actions {
        display: flex;
        gap: 0.8rem;
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1.5px solid #e2e8f0;
    }

    .btn-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 0.7rem 1.5rem;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        flex: 1;
    }

    .btn-submit:hover {
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
        transform: translateY(-1px);
    }

    .btn-back {
        background: white;
        color: #667eea;
        border: 1.5px solid #667eea;
        padding: 0.7rem 1.5rem;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        flex: 1;
    }

    .btn-back:hover {
        background: #667eea;
        color: white;
    }

    .tips-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        border-left: 4px solid #f6ad55;
        overflow: hidden;
    }

    .tips-card-header {
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, #fffaf0 0%, #fef5e7 100%);
        border-bottom: 1.5px solid #fbe8d3;
    }

    .tips-card-header h5 {
        margin: 0;
        color: #2d3748;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.95rem;
    }

    .tips-card-header i {
        color: #f6ad55;
        font-size: 1.1rem;
    }

    .tips-card-body {
        padding: 1rem 1.5rem;
    }

    .tip-item {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .tip-item:last-child {
        margin-bottom: 0;
    }

    .tip-icon {
        flex-shrink: 0;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 1rem;
    }

    .tip-success .tip-icon {
        background: #c6f6d5;
        color: #22543d;
    }

    .tip-info .tip-icon {
        background: #bee3f8;
        color: #2c5282;
    }

    .tip-warning .tip-icon {
        background: #fed7d7;
        color: #742a2a;
    }

    .tip-danger .tip-icon {
        background: #fed7d7;
        color: #742a2a;
    }

    .tip-content p {
        margin: 0 0 0.15rem 0;
        font-weight: 700;
        color: #2d3748;
        font-size: 0.85rem;
    }

    .tip-content small {
        color: #718096;
        font-size: 0.75rem;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
            gap: 0.8rem;
        }

        .advisory-create-header {
            padding: 1rem 1rem;
        }

        .advisory-create-header h1 {
            font-size: 1.2rem;
        }

        .form-card .card-body {
            padding: 1rem;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn-submit, .btn-back {
            justify-content: center;
        }
    }
</style>

<div class="container-fluid py-5">
    <!-- Header -->
    <div class="advisory-create-header">
        <h1><i class="fas fa-comments"></i> Start New Conversation</h1>
        <p>Discuss your child's academic progress with their advisor</p>
    </div>

    <!-- Form Card -->
    <div class="form-card">
        <div class="card-body">
            <form action="{{ route('advisory.store') }}" method="POST" novalidate>
                @csrf

                <!-- Student and Teacher Selection Row -->
                <div class="form-row">
                    <!-- Student Selection -->
                    <div class="form-group-wrapper">
                        <label for="student_id" class="form-label">
                            <i class="fas fa-user-graduate"></i>
                            Select Student
                            <span class="required-asterisk">*</span>
                        </label>
                        <select class="form-select @error('student_id') is-invalid @enderror" 
                                id="student_id" name="student_id" required>
                            <option value="">-- Choose Student --</option>
                            @forelse($students as $student)
                                <option value="{{ $student->id }}">{{ $student->name }}</option>
                            @empty
                                <option value="" disabled>No students available</option>
                            @endforelse
                        </select>
                        @error('student_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Teacher Selection -->
                    <div class="form-group-wrapper">
                        <label for="advisor_id" class="form-label">
                            <i class="fas fa-chalkboard-user"></i>
                            Select Teacher/Advisor
                            <span class="required-asterisk">*</span>
                        </label>
                        <select class="form-select @error('advisor_id') is-invalid @enderror" 
                                id="advisor_id" name="advisor_id" required>
                            <option value="">-- Choose Teacher --</option>
                            @forelse($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @empty
                                <option value="" disabled>No teachers available</option>
                            @endforelse
                        </select>
                        @error('advisor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Subject -->
                <div class="form-row full">
                    <div class="form-group-wrapper">
                        <label for="subject" class="form-label">
                            <i class="fas fa-heading"></i>
                            Topic
                            <span class="required-asterisk">*</span>
                        </label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                               id="subject" name="subject" placeholder="e.g. Academic Progress" 
                               value="{{ old('subject') }}" required>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Description and Priority Row -->
                <div class="form-row">
                    <!-- Description -->
                    <div class="form-group-wrapper">
                        <label for="description" class="form-label">
                            <i class="fas fa-pen-fancy"></i>
                            Details
                            <span class="required-asterisk">*</span>
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Provide detailed information..." 
                                  required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <span class="form-text"><i class="fas fa-info-circle"></i> Min 10 characters</span>
                    </div>

                    <!-- Priority -->
                    <div class="form-group-wrapper">
                        <label for="priority" class="form-label">
                            <i class="fas fa-flag"></i>
                            Priority Level
                        </label>
                        <select class="form-select @error('priority') is-invalid @enderror" 
                                id="priority" name="priority">
                            <option value="low" @selected(old('priority') == 'low')>Low</option>
                            <option value="medium" @selected(old('priority', 'medium') == 'medium')>Medium (Default)</option>
                            <option value="high" @selected(old('priority') == 'high')>High</option>
                            <option value="urgent" @selected(old('priority') == 'urgent')>Urgent</option>
                        </select>
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Start Conversation
                    </button>
                    <a href="{{ route('advisory.index') }}" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tips Section -->
    <div class="tips-card">
        <div class="tips-card-header">
            <h5><i class="fas fa-lightbulb"></i> Tips for Better Communication</h5>
        </div>
        <div class="tips-card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="tip-item tip-success">
                        <div class="tip-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div class="tip-content">
                            <p>Be Specific</p>
                            <small>Clearly state the issue or question you want to discuss</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="tip-item tip-info">
                        <div class="tip-icon">
                            <i class="fas fa-details"></i>
                        </div>
                        <div class="tip-content">
                            <p>Provide Context</p>
                            <small>Include relevant details to help the advisor understand</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="tip-item tip-warning">
                        <div class="tip-icon">
                            <i class="fas fa-sliders-h"></i>
                        </div>
                        <div class="tip-content">
                            <p>Set Priority</p>
                            <small>Choose the appropriate urgency level for your concern</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="tip-item tip-danger">
                        <div class="tip-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div class="tip-content">
                            <p>Quick Response</p>
                            <small>Your advisor will reply as soon as possible</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
