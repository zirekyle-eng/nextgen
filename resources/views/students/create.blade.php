@extends('layouts.master')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-6 fw-bold text-dark mb-2">
                <i class="fas fa-user-plus text-primary me-2"></i> Add New Student
            </h1>
            <p class="text-muted">Register a new student and assign to a guardian</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('students.index') }}" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient py-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="text-white mb-0">
                        <i class="fas fa-info-circle me-2"></i> Student Information
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('students.store') }}" novalidate>
                        @csrf

                        <!-- Guardian Selection -->
                        <div class="mb-4">
                            <label for="guardian_id" class="form-label fw-bold">Guardian <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg @error('guardian_id') is-invalid @enderror" 
                                id="guardian_id" name="guardian_id" required>
                                <option value="">Select guardian...</option>
                                @foreach($guardians as $guardian)
                                    <option value="{{ $guardian->id }}" {{ old('guardian_id') == $guardian->id ? 'selected' : '' }}>
                                        {{ $guardian->first_name }} {{ $guardian->last_name }} ({{ ucfirst($guardian->role ?? 'Guardian') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('guardian_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <!-- Name Section -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                    id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                @error('first_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                    id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                @error('last_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Birth & Location -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="dob" class="form-label fw-bold">Date of Birth</label>
                                <input type="date" class="form-control @error('dob') is-invalid @enderror" 
                                    id="dob" name="dob" value="{{ old('dob') }}">
                                @error('dob')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label fw-bold">Country</label>
                                <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                    id="country" name="country" value="{{ old('country') }}">
                                @error('country')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Academic Information -->
                        <hr class="my-4">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-book text-primary me-2"></i> Academic Information
                        </h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="stage" class="form-label fw-bold">Academic Stage</label>
                                <input type="text" class="form-control @error('stage') is-invalid @enderror" 
                                    id="stage" name="stage" value="{{ old('stage') }}" 
                                    placeholder="e.g. Primary, Secondary, Elementary">
                                @error('stage')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="preferred_start_date" class="form-label fw-bold">Preferred Start Date</label>
                                <input type="text" class="form-control @error('preferred_start_date') is-invalid @enderror" 
                                    id="preferred_start_date" name="preferred_start_date" value="{{ old('preferred_start_date') }}" 
                                    placeholder="e.g. September 2024">
                                @error('preferred_start_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label fw-bold">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="religion_status" class="form-label fw-bold">Religion Status</label>
                                <select class="form-select @error('religion_status') is-invalid @enderror" id="religion_status" name="religion_status">
                                    <option value="">-- Select --</option>
                                    <option value="Muslim" {{ old('religion_status') == 'Muslim' ? 'selected' : '' }}>Muslim</option>
                                    <option value="Non-Muslim" {{ old('religion_status') == 'Non-Muslim' ? 'selected' : '' }}>Non-Muslim</option>
                                </select>
                                @error('religion_status')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-end mt-5">
                            <button type="reset" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-redo me-2"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i> Save Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .display-6 {
        font-weight: 700;
        letter-spacing: -0.5px;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
</style>
@endsection
