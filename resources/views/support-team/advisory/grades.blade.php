@extends('layouts.master')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Grades for {{ $conversation->student->name }}</h2>
                <a href="{{ route('advisory.management.show', $conversation->id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            @if($grades->count() > 0)
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Subject</th>
                                    <th>Exam</th>
                                    <th>T1</th>
                                    <th>T2</th>
                                    <th>T3</th>
                                    <th>T4</th>
                                    <th>TCA</th>
                                    <th>Exam</th>
                                    <th>Total</th>
                                    <th>Grade</th>
                                    <th>Percentage %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($grades as $grade)
                                    <tr>
                                        <td><strong>{{ $grade->subject->name ?? 'N/A' }}</strong></td>
                                        <td>{{ $grade->exam->name ?? 'N/A' }}</td>
                                        <td>{{ $grade->t1 ?? '-' }}</td>
                                        <td>{{ $grade->t2 ?? '-' }}</td>
                                        <td>{{ $grade->t3 ?? '-' }}</td>
                                        <td>{{ $grade->t4 ?? '-' }}</td>
                                        <td>{{ $grade->tca ?? '-' }}</td>
                                        <td>{{ $grade->exm ?? '-' }}</td>
                                        <td>
                                            <strong>{{ $grade->total ?? 0 }}</strong>
                                        </td>
                                        <td>
                                            @if($grade->grade)
                                                <span class="badge bg-primary">{{ $grade->grade }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($grade->cum_ave)
                                                {{ number_format($grade->cum_ave, 2) }}%
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $grades->links() }}
                </div>
            @else
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-ban fa-3x mb-3"></i>
                    <h5>No Grades Available</h5>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
