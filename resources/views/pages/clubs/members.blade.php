@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-titles">
        <div class="row">
            <div class="col-sm-6">
                <h4>{{ $page_title }}</h4>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('clubs.show', $club) }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    @include('includes.alerts')

    <div class="card">
        <div class="card-header">
            <h5>Club Members: {{ $club->name }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Join Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $member)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $member->user->name }}</td>
                                <td>{{ $member->user->email }}</td>
                                <td>{{ $member->joined_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <form action="{{ route('clubs.removeMember', [$club, $member]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to remove this member?')">
                                            <i class="fa fa-trash"></i> Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No members in this club</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $members->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
