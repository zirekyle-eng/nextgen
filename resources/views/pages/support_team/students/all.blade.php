@extends('layouts.master')
@section('page_title', 'All Students')
@section('content')

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-5">
        <div class="col-md-8">
            <div>
                <h1 class="display-6 fw-bold text-dark mb-2">
                    <i class="fas fa-graduation-cap text-primary me-2"></i> Students Management
                </h1>
                <p class="text-muted">Manage all students and their information</p>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if (session('flash_success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Success!</strong> {{ session('flash_success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Students Table -->
    @if($students->count() > 0)
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <tr class="text-white">
                            <th class="ps-4">ID</th>
                            <th>Full Name</th>
                            <th>ADM No</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Email</th>
                            <th class="pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $s)
                            <tr class="border-bottom">
                                <td class="ps-4">
                                    <span class="badge bg-light text-dark">#{{ $loop->iteration }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-md me-3 rounded-circle bg-light p-2">
                                            <img src="{{ $s->user->photo }}" alt="photo" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">
                                                <a href="{{ route('students.show', Qs::hash($s->id)) }}" class="text-decoration-none text-dark">
                                                    {{ $s->user->name }}
                                                </a>
                                            </h6>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $s->adm_no }}</td>
                                <td>{{ $s->my_class->name }}</td>
                                <td>{{ $s->section->name }}</td>
                                <td>{{ $s->user->email }}</td>
                                <td class="pe-4">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('students.show', Qs::hash($s->id)) }}" 
                                            class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(Qs::userIsTeamSA())
                                            <a href="{{ route('students.edit', Qs::hash($s->id)) }}" 
                                                class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if(Qs::userIsSuperAdmin())
                                            <a id="{{ Qs::hash($s->id) }}" onclick="confirmDelete(this.id)" href="#" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <form method="post" id="item-delete-{{ Qs::hash($s->id) }}" action="{{ route('students.destroy', Qs::hash($s->id)) }}" class="d-none">@csrf @method('delete')</form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-info alert-lg shadow-sm text-center py-5" role="alert">
            <i class="fas fa-inbox fa-3x text-info mb-3"></i>
            <h5 class="mt-3">No Students Found</h5>
            <p class="text-muted">There are no student records yet.</p>
        </div>
    @endif
</div>

<style>
    .display-6 {
        font-weight: 700;
        letter-spacing: -0.5px;
    }
    
    .avatar {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }
    
    .btn-group .btn {
        padding: 0.5rem 0.75rem;
    }
</style>
@endsection
