@extends('layouts.master')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-6 fw-bold text-dark mb-2">
                <i class="fas fa-user-plus text-primary me-2"></i> Add New Guardian
            </h1>
            <p class="text-muted">Fill in the form below to create a new guardian profile</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('guardians.index') }}" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient py-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="text-white mb-0">
                        <i class="fas fa-info-circle me-2"></i> Guardian Information
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('guardians.store') }}" novalidate>
                        @csrf

                        <!-- Name Section -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="title" class="form-label fw-bold">Title</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                    id="title" name="title" value="{{ old('title') }}" 
                                    placeholder="e.g. Mr, Dr, Prof">
                                @error('title')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="first_name" class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                    id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                @error('first_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="last_name" class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                    id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                @error('last_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Contact Section -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0">
                                        <i class="fas fa-envelope text-primary"></i>
                                    </span>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                        id="email" name="email" value="{{ old('email') }}" required>
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label fw-bold">Relationship</label>
                                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role">
                                    <option value="">Select relationship...</option>
                                    <option value="father" {{ old('role') == 'father' ? 'selected' : '' }}>Father</option>
                                    <option value="mother" {{ old('role') == 'mother' ? 'selected' : '' }}>Mother</option>
                                    <option value="guardian" {{ old('role') == 'guardian' ? 'selected' : '' }}>Guardian</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Phone Section -->
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="phone_prefix" class="form-label fw-bold">Country Code</label>
                                <input type="text" class="form-control @error('phone_prefix') is-invalid @enderror" 
                                    id="phone_prefix" name="phone_prefix" value="{{ old('phone_prefix') }}" placeholder="+966">
                                @error('phone_prefix')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="phone" class="form-label fw-bold">Phone Number</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                    id="phone" name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-5 mb-3">
                                <label for="country" class="form-label fw-bold">Country</label>
                                <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                    id="country" name="country" value="{{ old('country') }}">
                                @error('country')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Address Section -->
                        <hr class="my-4">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-map-marker-alt text-primary me-2"></i> Address Information
                        </h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="address_line1" class="form-label fw-bold">Address Line 1</label>
                                <input type="text" class="form-control @error('address_line1') is-invalid @enderror" 
                                    id="address_line1" name="address_line1" value="{{ old('address_line1') }}">
                                @error('address_line1')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="address_line2" class="form-label fw-bold">Address Line 2</label>
                                <input type="text" class="form-control @error('address_line2') is-invalid @enderror" 
                                    id="address_line2" name="address_line2" value="{{ old('address_line2') }}">
                                @error('address_line2')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label fw-bold">City</label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                    id="city" name="city" value="{{ old('city') }}">
                                @error('city')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label fw-bold">Postal Code</label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                    id="postal_code" name="postal_code" value="{{ old('postal_code') }}">
                                @error('postal_code')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Status Section -->
                        <hr class="my-4">
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
                                <label class="form-label fw-bold">&nbsp;</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="keep_updated" name="keep_updated" 
                                        value="1" {{ old('keep_updated') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="keep_updated">
                                        Keep Updated with News
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-end mt-5">
                            <button type="reset" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-redo me-2"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i> Save Guardian
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
