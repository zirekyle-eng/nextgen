@extends('layouts.master')
@section('page_title', 'Select Exam Year')
@section('content')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title"><i class="icon-alarm mr-2"></i> Select Exam Year</h5>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    @if($years && $years->count() > 0)
                        <!-- DEBUG: student_id = {{ $student_id }} -->
                        <form method="post" action="{{ route('student.marks.year_select', '#') }}">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $student_id }}">
                            <div class="form-group">
                                <label for="year" class="font-weight-bold col-form-label-lg">Select Exam Year:</label>
                                <select required id="year" name="year" data-placeholder="Select Exam Year" class="form-control select select-lg">
                                    @foreach($years as $y)
                                        <option value="{{ $y->year }}">{{ $y->year }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="text-center mt-2">
                                <button type="submit" class="btn btn-primary btn-lg">Submit <i class="icon-paperplane ml-2"></i></button>
                            </div>

                        </form>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fa fa-info-circle fa-2x mb-3"></i>
                            <h5>No Marks Available</h5>
                            <p>Your marks have not been published yet. Please check back later.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
