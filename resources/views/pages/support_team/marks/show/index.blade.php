@extends('layouts.master')
@section('page_title', 'Student Marksheet')
@section('content')

    <div class="card">
        <div class="card-header text-center">
            <h4 class="card-title font-weight-bold">Student Marksheet for =>  {{ $sr->user->name.' ('.$my_class->name.' '.$my_class->section->first()->name.')' }} </h4>
        </div>
    </div>


    @foreach($exams as $ex)
        @foreach($exam_records->where('exam_id', $ex->id) as $exr)
            @if(Qs::userIsTeamSAT() || Qs::userIsTeamSA())
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="font-weight-bold">{{ $ex->name.' - '.$ex->year }}</h6>
                        {!! Qs::getPanelOptions() !!}
                    </div>
                    <div class="card-body collapse">
                        @include('pages.support_team.marks.show.sheet')
                        <div class="text-center mt-3">
                            <a target="_blank" href="{{ route('marks.print', [Qs::hash($student_id), $ex->id, $year]) }}" class="btn btn-secondary btn-lg">Print Marksheet <i class="icon-printer ml-2"></i></a>
                        </div>
                    </div>
                </div>
                {{--    EXAM COMMENTS   --}}
                @include('pages.support_team.marks.show.comments')
                {{-- SKILL RATING --}}
                @include('pages.support_team.marks.show.skills')
            @else
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="font-weight-bold">{{ $ex->name.' - '.$ex->year }}</h6>
                    </div>
                    <div class="card-body">
                        @include('pages.support_team.marks.show.sheet')
                        <div class="text-center mt-3">
                            <a target="_blank" href="{{ route('student.marks.print', [Qs::hash($student_id), $ex->id, $year]) }}" class="btn btn-secondary btn-lg">Print Marksheet <i class="icon-printer ml-2"></i></a>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endforeach

@endsection
