@extends('layouts.master')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-5">
        <div class="col-md-8">
            <div class="d-flex align-items-center mb-3">
                <div class="avatar avatar-lg me-3 rounded-circle bg-light p-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-user-tie fa-2x text-primary"></i>
                </div>
                <div>
                    <h1 class="display-6 fw-bold text-dark mb-0">{{ $guardian->first_name }} {{ $guardian->last_name }}</h1>
                    <p class="text-muted mb-0">Guardian Profile</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('guardians.edit', $guardian) }}" class="btn btn-warning btn-lg me-2">
                <i class="fas fa-edit me-2"></i> Edit
            </a>
            <a href="{{ route('guardians.index') }}" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <!-- Personal Information -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-gradient py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="text-white mb-0">
                        <i class="fas fa-id-card me-2"></i> Personal Information
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block fw-bold mb-1">First Name</small>
                            <h6 class="text-dark">{{ $guardian->first_name }}</h6>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block fw-bold mb-1">Last Name</small>
                            <h6 class="text-dark">{{ $guardian->last_name }}</h6>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block fw-bold mb-1">Title</small>
                            <h6 class="text-dark">{{ $guardian->title ?? 'N/A' }}</h6>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block fw-bold mb-1">Relationship</small>
                            <h6 class="text-dark">
                                @if($guardian->role)
                                    <span class="badge bg-info">{{ ucfirst($guardian->role) }}</span>
                                @else
                                    N/A
                                @endif
                            </h6>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-gradient py-3" style="background: linear-gradient(135deg, #0093E9 0%, #80D0C7 100%);">
                    <h5 class="text-white mb-0">
                        <i class="fas fa-phone me-2"></i> Contact Information
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block fw-bold mb-1">Email Address</small>
                            <h6 class="text-dark">
                                <a href="mailto:{{ $guardian->email }}" class="text-decoration-none">
                                    {{ $guardian->email }}
                                </a>
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block fw-bold mb-1">Phone Number</small>
                            <h6 class="text-dark">
                                @if($guardian->phone)
                                    <i class="fas fa-phone text-success me-2"></i>
                                    {{ $guardian->phone_prefix }}{{ $guardian->phone }}
                                @else
                                    <span class="text-muted">Not provided</span>
                                @endif
                            </h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted d-block fw-bold mb-1">Country</small>
                            <h6 class="text-dark">
                                @if($guardian->country)
                                    <i class="fas fa-globe me-2 text-info"></i> {{ $guardian->country }}
                                @else
                                    N/A
                                @endif
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block fw-bold mb-1">Status</small>
                            <h6>
                                @if($guardian->status === 'pending')
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-hourglass-half me-1"></i> Pending
                                    </span>
                                @elseif($guardian->status === 'approved')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i> Approved
                                    </span>
                                @elseif($guardian->status === 'rejected')
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle me-1"></i> Rejected
                                    </span>
                                @else
                                    <span class="badge bg-secondary">{{ $guardian->status }}</span>
                                @endif
                            </h6>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            @if($guardian->address_line1 || $guardian->city)
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient py-3" style="background: linear-gradient(135deg, #FD6585 0%, #FC8D5C 100%);">
                        <h5 class="text-white mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i> Address Information
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block fw-bold mb-1">Address Line 1</small>
                                <p class="text-dark">{{ $guardian->address_line1 ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block fw-bold mb-1">Address Line 2</small>
                                <p class="text-dark">{{ $guardian->address_line2 ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted d-block fw-bold mb-1">City</small>
                                <p class="text-dark">{{ $guardian->city ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block fw-bold mb-1">Postal Code</small>
                                <p class="text-dark">{{ $guardian->postal_code ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Children/Students -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient py-3" style="background: linear-gradient(135deg, #56CCF2 0%, #2F80ED 100%);">
                    <h5 class="text-white mb-0">
                        <i class="fas fa-graduation-cap me-2"></i> Children ({{ $students->total() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($students->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Name</th>
                                        <th>DOB</th>
                                        <th>Age</th>
                                        <th>Stage</th>
                                        <th>Status</th>
                                        <th class="pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $student)
                                        <tr>
                                            <td class="ps-4">
                                                <a href="{{ route('students.show', $student) }}" class="text-decoration-none text-dark fw-bold">
                                                    <i class="fas fa-child me-2 text-primary"></i>
                                                    {{ $student->first_name }} {{ $student->last_name }}
                                                </a>
                                            </td>
                                            <td>
                                                @if($student->dob)
                                                    {{ \Carbon\Carbon::parse($student->dob)->format('M d, Y') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($student->dob)
                                                    <span class="badge bg-light text-dark">{{ $student->age }} yrs</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $student->stage ?? '-' }}</td>
                                            <td>
                                                @if($student->status === 'pending')
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                @elseif($student->status === 'approved')
                                                    <span class="badge bg-success">Approved</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $student->status }}</span>
                                                @endif
                                            </td>
                                            <td class="pe-4">
                                                <a href="{{ route('students.show', $student) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($students->hasPages())
                            <div class="card-footer bg-light">
                                {{ $students->links('pagination::bootstrap-4') }}
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info m-4 mb-0">
                            <i class="fas fa-info-circle me-2"></i> No children registered yet
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Statistics Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <div class="avatar avatar-lg mx-auto mb-3 rounded-circle bg-light p-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-child fa-2x text-primary"></i>
                        </div>
                        <h2 class="text-primary fw-bold">{{ $guardian->students->count() }}</h2>
                        <p class="text-muted mb-0">Children</p>
                    </div>
                </div>
            </div>

            <!-- Created Info -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-clock me-2 text-primary"></i> Record Information
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <small class="text-muted d-block fw-bold mb-1">Created At</small>
                        {{ $guardian->created_at->format('M d, Y h:i A') }}
                    </p>
                    <p class="mb-0">
                        <small class="text-muted d-block fw-bold mb-1">Last Updated</small>
                        {{ $guardian->updated_at->format('M d, Y h:i A') }}
                    </p>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body p-4">
                    <h6 class="card-title fw-bold mb-3">
                        <i class="fas fa-cogs text-primary me-2"></i> Actions
                    </h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('guardians.edit', $guardian) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i> Edit Guardian
                        </a>
                        <a href="{{ route('students.create') }}" class="btn btn-success">
                            <i class="fas fa-plus-circle me-2"></i> Add Child
                        </a>
                        <form action="{{ route('guardians.destroy', $guardian) }}" method="POST" 
                            onsubmit="return confirm('Are you sure? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash me-2"></i> Delete Guardian
                            </button>
                        </form>
                    </div>
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
    
    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection
