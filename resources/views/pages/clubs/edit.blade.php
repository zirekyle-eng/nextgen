@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-titles">
        <div class="row">
            <div class="col-sm-6">
                <h4>{{ $page_title }}</h4>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('clubs.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    @include('includes.alerts')
    @include('includes.errors')

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <h5>Edit Club: {{ $club->name }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('clubs.update', $club) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Club Name *</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $club->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $club->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="leader_id">Club Leader *</label>
                            <select name="leader_id" id="leader_id" class="form-control @error('leader_id') is-invalid @enderror" required>
                                <option value="">Select Leader</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('leader_id', $club->leader_id) == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('leader_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" id="is_active" class="custom-control-input" value="1"
                                       {{ old('is_active', $club->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">
                                    Club is Active
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-save"></i> Save Changes
                            </button>
                            <a href="{{ route('clubs.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
