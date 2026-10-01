@extends('layouts.master')
@section('page_title', 'Select Class - Students')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Select a Class to View Students</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            @if($my_classes->count() < 1)
                <div class="alert alert-warning">
                    <span>لا توجد فصول متاحة</span>
                </div>
            @else
                <div class="row">
                    @foreach($my_classes as $class)
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('students.list', $class->id) }}" class="card text-center text-decoration-none">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $class->name }}</h5>
                                    <p class="card-text text-muted">
                                        <i class="icon-book"></i>
                                        {{ $class->section->count() }} Sections
                                    </p>
                                    <button class="btn btn-primary btn-sm">
                                        <i class="icon-arrow-right"></i> View Students
                                    </button>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

@endsection
