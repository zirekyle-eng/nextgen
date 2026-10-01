@extends('layouts.master')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>درجات {{ $conversation->student->name }}</h2>
                <div>
                    <a href="{{ route('advisory.downloadGradesReport', $conversation->id) }}" class="btn btn-success">
                        <i class="fas fa-download"></i> تحميل التقرير
                    </a>
                    <a href="{{ route('advisory.show', $conversation->id) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> العودة
                    </a>
                </div>
            </div>

            @if($grades->count() > 0)
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>المادة</th>
                                    <th>الفحص</th>
                                    <th>T1</th>
                                    <th>T2</th>
                                    <th>T3</th>
                                    <th>T4</th>
                                    <th>TCA</th>
                                    <th>الامتحان</th>
                                    <th>المجموع</th>
                                    <th>التقدير</th>
                                    <th>النسبة %</th>
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
                    <h5>لا توجد درجات</h5>
                    <p>لم يتم تسجيل أي درجات لهذا الطالب بعد</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .table td {
        vertical-align: middle;
        padding: 12px 8px;
    }
    
    .table thead th {
        font-weight: 600;
        text-align: center;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endsection
